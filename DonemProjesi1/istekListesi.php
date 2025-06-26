<?php
// Oturumu başlat
session_start();

// Kullanıcının giriş yapıp yapmadığını ve rolünün 'student' olup olmadığını kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: giris.php"); // Giriş sayfasına yönlendir
    exit; // Yönlendirme sonrası işlemi sonlandır
}

// Form gönderimi kontrolü
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Formdan gelen değerleri al
    $ogr_id = $_POST['ogr_id']; // Öğrenci ID'si
    $kurs_id = $_POST['kurs_id']; // Kurs ID'si

    try {
        // Veritabanı bağlantısını oluştur
        $dbConnection = new PDO('mysql:host=localhost;dbname=kurs_sertifikalandirma', 'root', '12345678');
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // PDO hata modunu etkinleştir

        // Daha önce aynı kursun eklenip eklenmediğini kontrol et
        $checkSql = "SELECT 1 FROM istekler WHERE ogr_id = :ogr_id AND kurs_id = :kurs_id";
        $checkQuery = $dbConnection->prepare($checkSql);
        $checkQuery->execute([':ogr_id' => $ogr_id, ':kurs_id' => $kurs_id]); // Parametreleri bağla ve sorguyu çalıştır

        if ($checkQuery->rowCount() > 0) {
            // Kurs zaten istek listesinde mevcut
            echo "<script>alert('Bu kurs zaten istek listenize eklenmiş.');</script>";
            header("Refresh: 0; url=kursDetay.php?id=" . $kurs_id); // Kurs detay sayfasına yönlendir
            exit; // Yönlendirme sonrası işlemi sonlandır
        } else {
            // Kursu 'istekler' tablosuna ekle
            $sql = "INSERT INTO istekler (ogr_id, kurs_id) VALUES (:ogr_id, :kurs_id)";
            $query = $dbConnection->prepare($sql);
            $query->execute([':ogr_id' => $ogr_id, ':kurs_id' => $kurs_id]); // Parametreleri bağla ve sorguyu çalıştır

            echo "<script>alert('Kurs istek listenize başarıyla eklendi.');</script>";
            header("Refresh: 0; url=kursDetay.php?id=" . $kurs_id); // Kurs detay sayfasına yönlendir
            exit; // Yönlendirme sonrası işlemi sonlandır
        }
    } catch (PDOException $e) {
        // PDO istisnası yakalandığında hata mesajını göster
        echo "Hata: " . $e->getMessage();
    }
}
?>
