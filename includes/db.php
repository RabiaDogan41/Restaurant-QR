<?php
$host = 'localhost';
$db = 'restaurant_db'; // Veritabanı adınızı kontrol edin
$user = 'root'; // Varsayılan XAMPP kullanıcı adı
$pass = ''; // XAMPP için varsayılan şifre boş bırakılır

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
