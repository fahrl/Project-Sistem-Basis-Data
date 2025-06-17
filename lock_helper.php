<?php
function ambil_lock($conn, $nama_tabel, $user) {
    // Ambil status awal lock
    $sql = "SELECT is_locked, user_locked, updated_at FROM lock_status WHERE nama_tabel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nama_tabel);
    $stmt->execute();
    $stmt->store_result();

    $is_locked = 0;
    $user_locked = null;
    $updated_at = null;

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($is_locked, $user_locked, $updated_at);
        $stmt->fetch();
    } else {
        // Jika record tidak ada, insert baris kosong untuk tabel ini
        $stmt->close();
        $insert = $conn->prepare("INSERT INTO lock_status (nama_tabel, is_locked, user_locked, updated_at) VALUES (?, 0, NULL, NOW())");
        $insert->bind_param("s", $nama_tabel);
        $insert->execute();
        $insert->close();

        // Set default agar lanjut ambil lock
        $is_locked = 0;
        $user_locked = null;
        $updated_at = null;
    }
    $stmt->close();

    // Cek timeout
    $now = new DateTime();
    $last_updated = new DateTime($updated_at ?? '2000-01-01 00:00:00');
    $interval = $now->getTimestamp() - $last_updated->getTimestamp();

    if ($interval >= 60 && $user_locked !== $user) {
        $reset = $conn->prepare("UPDATE lock_status SET is_locked = 0, user_locked = NULL WHERE nama_tabel = ?");
        $reset->bind_param("s", $nama_tabel);
        $reset->execute();
        $reset->close();

        // Perbarui state
        $is_locked = 0;
        $user_locked = null;
    }

    if ($is_locked == 1 && $user_locked !== $user) {
        $_SESSION['lock_error'] = "Record is edited by another user (by user: $user_locked)";
        return false;
    }

    // Ambil lock untuk user ini
    $stmt = $conn->prepare("UPDATE lock_status SET is_locked = 1, user_locked = ?, updated_at = NOW() WHERE nama_tabel = ?");
    $stmt->bind_param("ss", $user, $nama_tabel);
    $stmt->execute();
    $stmt->close();

    return true;
}



function lepas_lock($conn, $nama_tabel, $user) {
    $sql = "UPDATE lock_status SET is_locked = 0, user_locked = NULL, updated_at = NOW() WHERE nama_tabel = ? AND user_locked = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nama_tabel, $user);
    $stmt->execute();
    $stmt->close();
}
?>
