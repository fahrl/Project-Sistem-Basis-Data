<?php
session_start();
$lock_error = isset($_SESSION['lock_error']) ? $_SESSION['lock_error'] : '';
unset($_SESSION['lock_error']);
include 'koneksi.php';
include 'lock_helper.php';

// Handle form actions
$nama_tabel = 't_beli';
$user = $_SESSION['username'];
$action = isset($_GET['action']) ? $_GET['action'] : 'display';
$message = '';
$error_message = '';

// Tangani auto timeout dari browser (JavaScript)
if ($action == 'timeout') {
    lepas_lock($conn, $nama_tabel, $user);
    $_SESSION['lock_error'] = "Session expired due to inactivity. Lock released.";
    header("Location: form_beli.php?action=display");
    exit;
}

// Ambil lock hanya jika ingin insert/edit/delete
if (in_array($action, ['insert', 'edit', 'delete'])) {
    if (!ambil_lock($conn, $nama_tabel, $user)) {
        $_SESSION['lock_error'] = "Record is edited by another user";
        echo "Redirecting..."; // debug
        header("Location: form_beli.php?action=display");
        exit;
    }
}

// Handle Insert
if (isset($_POST['save']) && $action == 'insert') {
    $stmt = $conn->prepare("CALL insert_beli(?, ?, ?, ?)");
    $stmt->bind_param("sssi", $_POST['kd_trans'], $_POST['tgl_trans'], $_POST['kode_brg'], $_POST['jml_beli']);
    if ($stmt->execute()) {
        $message = "Data berhasil ditambahkan!";
    }
    $stmt->close();
    $action = 'display';
    lepas_lock($conn, $nama_tabel, $user); // Lepas lock setelah update
}

// Handle Update
if (isset($_POST['save']) && $action == 'edit') {
    $stmt = $conn->prepare("CALL update_beli(?, ?, ?, ?)");
    $stmt->bind_param("sssi", $_POST['kd_trans'], $_POST['tgl_trans'], $_POST['kode_brg'], $_POST['jml_beli']);
    if ($stmt->execute()) {
        $message = "Data berhasil diupdate!";
    }
    $stmt->close();
    $action = 'display';
    lepas_lock($conn, $nama_tabel, $user); // Lepas lock setelah update
}

// Handle Delete
if (isset($_POST['save']) && $action == 'delete') {
    $stmt = $conn->prepare("CALL delete_beli(?)");
    $stmt->bind_param("s", $_POST['kd_trans']);
    if ($stmt->execute()) {
        $message = "Data berhasil dihapus!";
    }
    $stmt->close();
    $action = 'display';
    lepas_lock($conn, $nama_tabel, $user); // Lepas lock setelah update
}

// Handle Cancel
if (isset($_POST['cancel']) && in_array($action, ['insert', 'edit', 'delete'])) {
    lepas_lock($conn, $nama_tabel, $user);
    $action = 'display';
}

// Handle Cancel (GET method)
if ($action == 'cancel') {
    lepas_lock($conn, $nama_tabel, $user);
    $action = 'display';
}

// Get data for edit  
$edit_data = null;
if ($action == 'edit' && isset($_GET['kode'])) {
    $stmt = $conn->prepare("SELECT * FROM t_beli WHERE Kd_trans = ?");
    $stmt->bind_param("s", $_GET['kode']);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

// Get data for delete confirmation
$delete_data = null;
if ($action == 'delete' && isset($_GET['kode'])) {
    $stmt = $conn->prepare("SELECT * FROM t_beli WHERE Kd_trans = ?");
    $stmt->bind_param("s", $_GET['kode']);
    $stmt->execute();
    $result = $stmt->get_result();
    $delete_data = $result->fetch_assoc();
    $stmt->close();
}

// Get Data for Display
$data = $conn->query("SELECT * FROM t_beli");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pembelian</title>
    <style>
        body {
            margin: 0;
            padding: 0; 
            background-image: url(bl.jpg);
            background-size: cover;
            background-repeat: no-repeat;
            min-height: 100vh;
            background-attachment: fixed;
        }
        
        .container {
            background-color:rgb(0, 0, 0);
            padding: 20px;
            border: 2px solid #000;
            margin-bottom: 20px;
            color: aliceblue;
        }
        
        .nav-buttons {
            margin-bottom: 20px;
        }
        
        .nav-btn {
            padding: 8px 15px;
            margin-right: 5px;
            border: none;
            cursor: pointer;
            /*font-weight: bold;*/
            text-decoration: none;
            display: inline-block;
            color: white;
            border-radius: 4px;
            
        }
        
        .btn-insert { background-color:#4CAF50; }
        .btn-edit { background-color: #4CAF50; }
        .btn-delete { background-color: #4CAF50; }
        .btn-display { background-color: #4CAF50; }
        .btn-exit { background-color:rgb(212, 17, 17); }
        .btn-back {background-color:rgb(212, 17, 17);}
        
        .form-container {
            background-color:rgb(0, 0, 0);
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #000;
        }
        
        .form-row {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .form-row label {
            width: 120px;
            display: inline-block;
            font-weight: bold;
        }
        
        .form-row input {
            padding: 5px;
            border: 1px solid #000;
        }
        
        .form-buttons {
            margin-top: 20px;
        }
        
        .form-btn {
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
            background-color:rgb(115, 100, 248);
            border-radius: 3px;
        }
        
        .data-table {
            background-color: white;
            border: 2px solid #000;
            width: 100%;
            border-collapse: collapse;
            color: #000;
        }
        
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .data-table th {
            background-color:rgb(255, 255, 255);
        }
        
        .message {
            color: red;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .error-message {
            color: red;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
    </style>

    <?php if (in_array($action, ['insert', 'edit', 'delete'])): ?>
    <script>
        setTimeout(function() {
            window.location.href = "form_beli.php?action=timeout";
        }, 35000); // 35 detik
    </script>
    <?php endif; ?>

</head>
<body>

<div class="container">
    <p style="font-weight: bold; margin-bottom: 10px;">
    User login: <?= htmlspecialchars($_SESSION['username']) ?>
    </p>

    <a href="index.php" class="nav-btn btn-back">Kembali ke Beranda</a>
    <h3>Data Pembelian</h3>

    <div class="nav-buttons">
        <a href="?action=insert" class="nav-btn btn-insert">Insert</a>
        <a href="?action=edit" class="nav-btn btn-edit">Edit</a>
        <a href="?action=delete" class="nav-btn btn-delete">Delete</a>
        <a href="#" class="nav-btn btn-display" onclick="toggleDisplay(); return false;">Display</a>
        <a href="login.php" class="nav-btn btn-exit" onclick="window.close()">Exit</a>
    </div>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <?php if (!empty($lock_error)) : ?>
        <div class="error-message"><?= $lock_error ?></div>
    <?php endif; ?>

    <?php if ($action == 'insert'): ?>
        <div class="form-container">
            <h4>Tambah Data Pembelian</h4>
            <form method="POST">
                <div class="form-row">
                    <label>Kode Transaksi</label>
                    <input type="text" name="kd_trans" required>
                </div>
                <div class="form-row">
                    <label>Tgl Transaksi</label>
                    <input type="date" name="tgl_trans" required>
                </div>
                <div class="form-row">
                    <label>Kode Barang</label>
                    <input type="text" name="kode_brg" required style="width: 150px;">
                </div>
                <div class="form-row">
                    <label>Jumlah Beli</label>
                    <input type="number" name="jml_beli" required style="width: 100px;">
                </div>
                <div class="form-buttons">
                    <button type="submit" name="save" class="form-btn">Save</button>
                    <button type="reset" class="form-btn">Reset</button>
                    <a href="?action=cancel" class="form-btn">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($action == 'edit'): ?>
        <?php if ($edit_data): ?>
            <div class="form-container">
                <h4>Edit Data Pembelian</h4>
                <form method="POST">
                    <div class="form-row">
                        <label>Kode Transaksi</label>
                        <input type="text" name="kd_trans" value="<?= htmlspecialchars($edit_data['Kd_trans']) ?>" readonly style="width: 120px;">
                    </div>
                    <div class="form-row">
                        <label>Tgl Transaksi</label>
                        <input type="date" name="tgl_trans" value="<?= htmlspecialchars($edit_data['Tgl_trans']) ?>" required>
                    </div>
                    <div class="form-row">
                        <label>Kode Barang</label>
                        <input type="text" name="kode_brg" value="<?= htmlspecialchars($edit_data['Kode_brg']) ?>" required style="width: 150px;">
                    </div>
                    <div class="form-row">
                        <label>Jumlah Beli</label>
                        <input type="number" name="jml_beli" value="<?= htmlspecialchars($edit_data['Jml_beli']) ?>" required style="width: 100px;">
                    </div>
                    <div class="form-buttons">
                        <button type="submit" name="save" class="form-btn">Save</button>
                        <button type="reset" class="form-btn">Reset</button>
                        <a href="?action=cancel" class="form-btn">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <p>Pilih data dari tabel di bawah untuk diedit:</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($action == 'delete'): ?>
        <?php if ($delete_data): ?>
            <div class="form-container">
                <h4>Hapus Data Pembelian</h4>
                <p><strong>Apakah Anda yakin ingin menghapus data berikut?</strong></p>
                <form method="POST">
                    <div class="form-row">
                        <label>Kode Transaksi</label>
                        <input type="text" name="kd_trans" value="<?= htmlspecialchars($delete_data['Kd_trans']) ?>" readonly style="width: 120px;">
                    </div>
                    <div class="form-row">
                        <label>Tgl Transaksi</label>
                        <input type="text" value="<?= htmlspecialchars($delete_data['Tgl_trans']) ?>" readonly style="width: 200px;">
                    </div>
                    <div class="form-row">
                        <label>Kode Barang</label>
                        <input type="text" value="<?= htmlspecialchars($delete_data['Kode_brg']) ?>" readonly style="width: 150px;">
                    </div>
                    <div class="form-row">
                        <label>Jumlah Beli</label>
                        <input type="text" value="<?= htmlspecialchars($delete_data['Jml_beli']) ?>" readonly style="width: 100px;">
                    </div>
                    <div class="form-buttons">
                        <button type="submit" name="save" class="form-btn">Save</button>
                        <button type="reset" class="form-btn">Reset</button>
                        <a href="?action=cancel" class="form-btn">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <p>Pilih data dari tabel di bawah untuk dihapus:</p>
        <?php endif; ?>
    <?php endif; ?>

</div>

<div class="container">
    <div id="displayData" style="display: none; margin-top: 20px;">
        <table class="data-table">
            <tr>
                <th>Kode Transaksi</th>
                <th>Tanggal Transaksi</th>
                <th>Kode Barang</th>
                <th>Jumlah Beli</th>
                <th>Action</th>
            </tr>
            <?php if ($data->num_rows > 0): ?>
                <?php while($row = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Kd_trans']) ?></td>
                    <td><?= htmlspecialchars($row['Tgl_trans']) ?></td>
                    <td><?= htmlspecialchars($row['Kode_brg']) ?></td>
                    <td><?= htmlspecialchars($row['Jml_beli']) ?></td>
                    <td>
                        <a href="?action=edit&kode=<?= urlencode($row['Kd_trans']) ?>">Edit</a> |
                        <a href="?action=delete&kode=<?= urlencode($row['Kd_trans']) ?>">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

   <!-- Script untuk menampilkan display -->
<script>
function toggleDisplay() {
    const displayDiv = document.getElementById('displayData');
    if (displayDiv.style.display === 'none' || displayDiv.style.display === '') {
        displayDiv.style.display = 'block';
    } else {
        displayDiv.style.display = 'none';
    }
}

const resetBtn = document.querySelector('form button[type="reset"]');
  resetBtn.addEventListener('click', function(e) {
    e.preventDefault(); // cegah reset bawaan
    const form = this.closest('form');
    form.querySelectorAll('input').forEach(input => {
      if (input.readOnly !== true) {
        input.value = '';
      }
    });
  });
</script>

</body>
</html>