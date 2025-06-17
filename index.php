<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistem Inventori</title>
    <style>
        
        .container {
            background-color:black;
            padding: 20px;
            border: 2px solid #000;
            margin-bottom: 20px;
            
        }

        .h1 {
            color: white;
        }

        .p {
            color: white;
        }
        
        body { 
            margin: 0;
            padding: 0; 
            background-image: url(bl.jpg);
            background-size: cover; /* agar gambar menutupi seluruh layar */
            background-repeat: no-repeat;
            min-height: 100vh;
            background-attachment: fixed; /* agar background tidak ikut scroll */
        }
        .menu { margin: 20px 0; }
        .menu a {
            display: inline-block;
            padding: 10px 15px;
            margin-right: 10px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .menu a:hover { background-color: #45a049; }
        .user-info { float: right; color: aliceblue; }
    </style>
</head>
<body>
    <div class="container">
        <div class="user-info">
        <?php 
        echo "User: " . $_SESSION['username'] ?? 'Guest';
        if (isset($_SESSION['username'])) {
            echo ' | <a href="logout.php">Logout</a>';
        }
        ?>
    </div>
<hr>

    
    <div class="h1">
    <h1>Sistem Inventori Sederhana</h1>
    </div>

    <div class="menu">
        <a href="form_barang.php">Manajemen Barang</a>
        <a href="form_beli.php">Manajemen Pembelian</a>
    </div>
    
    <div class="p">
    <p>Selamat datang di sistem inventori sederhana.</p>
    </div>

    </div>
</body>
</html>