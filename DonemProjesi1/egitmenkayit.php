<?php
// Veritabanı bağlantısı (mysqli ile örnek)
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "kurs_sertifikalandirma";

// Veritabanına bağlan
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantıyı kontrol et
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Kullanıcıdan gelen şifre
$sifre = "12345678";

// Şifreyi hashle
$hashli_sifre = password_hash($sifre, PASSWORD_DEFAULT);

// Eğitmen bilgilerini veritabanına kaydetme
$ad = 'Ali';
$soyad = 'Tasci';
$e_posta = 'alitasci@gmail.com';

// SQL sorgusunu hazırla ve çalıştır
$sql = "INSERT INTO egitmenler (ad, soyad, e_posta, sifre) VALUES ('$ad', '$soyad', '$e_posta', '$hashli_sifre')";

// Sorguyu çalıştır
if ($conn->query($sql) === TRUE) {
    echo "Yeni kayıt başarıyla eklendi.";
} else {
    echo "Hata: " . $sql . "<br>" . $conn->error;
}

// Bağlantıyı kapat
$conn->close();
?>