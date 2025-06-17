<?php
$host = "localhost";  // atau "127.0.0.1"
$user = "root";
$password = ""; // pastikan password benar
$database = "uas_sbd";

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Koneksi gagal: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>