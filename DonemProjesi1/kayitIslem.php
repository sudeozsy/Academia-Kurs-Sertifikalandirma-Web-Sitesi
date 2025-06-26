<?php
// Veritabanı bağlantısını oluşturun
$host = "localhost"; // Veritabanı sunucusu
$dbname = "kurs_sertifikalandirma"; // Veritabanı adı
$username = "root"; // Varsayılan XAMPP kullanıcı adı
$password = "12345678"; // Varsayılan XAMPP şifresi

try {
    // PDO ile veritabanı bağlantısını oluştur
    $dbConnection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Hata modunu etkinleştir
} catch (PDOException $e) {
    // Bağlantı hatası durumunda işlemi sonlandır
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Formdan gelen verileri alın
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Formdan gelen değerleri temizle ve al
    $ad = htmlspecialchars($_POST['name']); // Kullanıcı adı
    $soyad = htmlspecialchars($_POST['surname']); // Kullanıcı soyadı
    $e_posta = htmlspecialchars($_POST['e_posta']); // Kullanıcı e-posta
    $sifre = $_POST['password']; // Kullanıcı şifresi

    // Şifre uzunluğunu kontrol et (en az 5, en fazla 15 karakter)
    if (strlen($sifre) < 5 || strlen($sifre) > 15) {
        // Hatalı şifre uzunluğu durumunda kullanıcıyı yönlendir
        header("Location: kayıt.php?status=password_error");
        exit();
    }

    // E-posta benzersizliğini kontrol et
    $query = $dbConnection->prepare("SELECT COUNT(*) FROM ogrenciler WHERE e_posta = :e_posta");
    $query->execute([':e_posta' => $e_posta]); // E-posta adresini sorgula
    $emailCount = $query->fetchColumn(); // E-posta sayısını al

    if ($emailCount > 0) {
        // E-posta zaten mevcutsa kullanıcıyı yönlendir
        header("Location: kayıt.php?status=email_exists");
        exit();
    }

    // Şifreyi hash'leyerek güvenli bir şekilde sakla
    $hashedPassword = password_hash($sifre, PASSWORD_DEFAULT);

    // Veritabanına kaydet
    try {
        $query = $dbConnection->prepare("INSERT INTO ogrenciler (ad, soyad, e_posta, sifre) VALUES (:ad, :soyad, :e_posta, :sifre)");
        $query->execute([
            ':ad' => $ad, // Adı bağla
            ':soyad' => $soyad, // Soyadı bağla
            ':e_posta' => $e_posta, // E-posta bağla
            ':sifre' => $hashedPassword // Hashlenmiş şifreyi bağla
        ]);

        // Başarı durumunda giriş sayfasına yönlendir
        header("Location: giris.php");
    } catch (PDOException $e) {
        // Veritabanı hatası durumunda kullanıcıyı yönlendir
        header("Location: kayıt.php?status=error");
        die("Hata: " . $e->getMessage()); // Hata mesajını göster
    }
}
?>
