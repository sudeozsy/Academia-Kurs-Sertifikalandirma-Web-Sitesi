<?php
// Veritabanı bağlantısı
$host = "localhost";
$dbname = "kurs_sertifikalandirma";
$username = "root";
$password = "12345678";

try {
    $dbConnection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Giriş kontrolü
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $e_posta = htmlspecialchars($_POST['e_posta']);
    $sifre = $_POST['password'];
    $rol = $_POST['role'];

    $table = $rol === "student" ? "ogrenciler" : ($rol === "instructor" ? "egitmenler" : null);

    if (!$table) {
        die("Geçersiz rol seçimi.");
    }

    $query = $dbConnection->prepare("SELECT * FROM $table WHERE e_posta = :e_posta");
    $query->execute([':e_posta' => $e_posta]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($sifre, $user['sifre'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $rol;
        header("Location: profil.php");
        exit();
    } else {
        header("Location: giris.php?status=error&message=invalid_credentials");
        exit();
    }
}
?>