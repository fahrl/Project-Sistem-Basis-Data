<?php
include 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validasi user (contoh sederhana)
    if (($username == 'ali' || $username == 'ipal') && $password == 'kostjhon') {
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        
        body { font-family: Arial, sans-serif; 
            margin: 0;
            padding: 0; 
            background-image: url(bl.jpg);
            background-size: cover; /* agar gambar menutupi seluruh layar */
            background-repeat: no-repeat;
            min-height: 100vh;
            background-attachment: fixed; /* agar background tidak ikut scroll */
        }
        .login-form { max-width: 300px; 
            margin: 0 auto;
            background-color: black;
            width: 300px;
            height: 300px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            color: white;
            flex-direction: column;
            padding: 30px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        button { 
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .error { color: red; 
        
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="login-form">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
    </div>
</body>
</html>