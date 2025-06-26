<?php
// Oturum başlatılıyor
session_start(); // Oturum başlatma işlemi

// Hata raporlama ayarları yapılıyor
ini_set('display_errors', 1); // Hata gösterimi aktif ediliyor
ini_set('display_startup_errors', 1); // Başlangıç hatalarını da göster
error_reporting(E_ALL); // Tüm hata seviyeleri için raporlama yapılıyor

// Veritabanı bağlantısı açılıyor
$db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma'); // Veritabanına bağlantı kuruluyor
if ($db->connect_error) {
    die("Bağlantı hatası: " . $db->connect_error); // Bağlantı hatası durumunda hata mesajı
}

// Kurs ID'si URL'den alınıyor
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Kurs ID belirtilmedi!"); // Kurs ID yoksa hata mesajı
}
$course_id = (int)$_GET['id']; // Kurs ID'si sayısal değere dönüştürülüyor

// Kurs bilgisi ve bitiş tarihi kontrol ediliyor
$query = "SELECT bit_t FROM kurslar WHERE id = ?"; // Kurs bitiş tarihini sorgulayan SQL
$stmt = $db->prepare($query); // SQL sorgusu hazırlama
$stmt->bind_param("i", $course_id); // Kurs ID'si parametre olarak bağlanıyor
$stmt->execute(); // Sorgu çalıştırılıyor
$stmt->bind_result($course_end_date); // Bitiş tarihi değişkene bağlanıyor
$stmt->fetch(); // Sonuç alınıyor
$stmt->close(); // Sorgu kapatılıyor

if (!$course_end_date) {
    die("Kurs bulunamadı!"); // Eğer kurs bulunamazsa hata mesajı
}

$current_date = date("Y-m-d"); // Bugünün tarihi alınıyor
$is_course_ongoing = $course_end_date >= $current_date; // Kurs devam ediyorsa true

// Öğrencileri listelemek için sorgu hazırlanıyor
$query = "
    SELECT 
        eğitimler.id AS egitim_id,
        ogrenciler.ad, 
        ogrenciler.soyad, 
        ogrenciler.e_posta,
        eğitimler.basari_drm
    FROM 
        eğitimler 
    JOIN 
        ogrenciler ON eğitimler.ogr_id = ogrenciler.id 
    WHERE 
        eğitimler.kurs_id = ?"; // Kursa ait öğrencileri listeleyen SQL
$stmt = $db->prepare($query); // SQL sorgusu hazırlama
$stmt->bind_param("i", $course_id); // Kurs ID'si parametre olarak bağlanıyor
$stmt->execute(); // Sorgu çalıştırılıyor
$result = $stmt->get_result(); // Sonuç alınıyor

// Silme işlemini ve başarı durumlarını veritabanına kaydet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_changes'])) { // Eğer form gönderildiyse
    // Silme işlemleri
    if (isset($_POST['deleted_students']) && is_array($_POST['deleted_students'])) { // Silinmesi istenen öğrenciler var mı?
        $deleted_students = $_POST['deleted_students']; // Silinmesi istenen öğrenciler alınır

        foreach ($deleted_students as $education_id) { // Her öğrenci için
            $delete_query = "DELETE FROM eğitimler WHERE id = ?"; // Eğitim kaydını silme SQL
            $delete_stmt = $db->prepare($delete_query); // SQL sorgusu hazırlanıyor
            $delete_stmt->bind_param("i", $education_id); // Eğitim ID'si parametre olarak bağlanıyor
            $delete_stmt->execute(); // Sorgu çalıştırılıyor
            $delete_stmt->close(); // Sorgu kapatılıyor
        }
    }
    // Tüm eğitimler için başarı durumunu sıfırlıyoruz
    $reset_query = "UPDATE eğitimler SET basari_drm = 0 WHERE kurs_id = ?"; // Tüm başarı durumlarını sıfırlama SQL
    $reset_stmt = $db->prepare($reset_query); // SQL sorgusu hazırlanıyor
    $reset_stmt->bind_param("i", $course_id); // Kurs ID'si parametre olarak bağlanıyor
    $reset_stmt->execute(); // Sorgu çalıştırılıyor
    $reset_stmt->close(); // Sorgu kapatılıyor

    // Başarı durumları güncelleniyor
    if (isset($_POST['basari_drm']) && is_array($_POST['basari_drm'])) { // Eğer başarı durumu gönderilmişse
        foreach ($_POST['basari_drm'] as $education_id => $value) { // Her eğitim için
            // Eğitim bilgilerini çek
            $query = "
                SELECT 
                    eğitimler.kurs_id, eğitimler.ogr_id, kurslar.egitmen_id
                FROM 
                    eğitimler
                JOIN 
                    kurslar ON eğitimler.kurs_id = kurslar.id
                WHERE 
                    eğitimler.id = ?"; // Eğitim bilgilerini sorgulayan SQL
            $stmt = $db->prepare($query); // SQL sorgusu hazırlanıyor
            $stmt->bind_param("i", $education_id); // Eğitim ID'si parametre olarak bağlanıyor
            $stmt->execute(); // Sorgu çalıştırılıyor
            $stmt->bind_result($kurs_id, $ogr_id, $egitmen_id); // Sonuçları al
            $stmt->fetch(); // Sonuçları çek
            $stmt->close(); // Sorgu kapatılıyor

            // Sertifikalar tablosuna ekle
            $unique_code = uniqid('CERT-', true); // Benzersiz sertifika kodu oluşturuluyor
            $insert_query = "
                INSERT INTO sertifikalar (ad, kurs_id, ogr_id, egitmen_id)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE ad = VALUES(ad)"; // Sertifika ekleme SQL
            $insert_stmt = $db->prepare($insert_query); // SQL sorgusu hazırlanıyor
            $insert_stmt->bind_param("siii", $unique_code, $kurs_id, $ogr_id, $egitmen_id); // Parametreler bağlanıyor
            $insert_stmt->execute(); // Sorgu çalıştırılıyor
            $insert_stmt->close(); // Sorgu kapatılıyor

            // Eğitimler tablosunda başarı durumu güncelle
            $update_query = "UPDATE eğitimler SET basari_drm = 1 WHERE id = ?"; // Başarı durumunu güncelleyen SQL
            $update_stmt = $db->prepare($update_query); // SQL sorgusu hazırlanıyor
            $update_stmt->bind_param("i", $education_id); // Eğitim ID'si parametre olarak bağlanıyor
            $update_stmt->execute(); // Sorgu çalıştırılıyor
            $update_stmt->close(); // Sorgu kapatılıyor
        }
    }

    // Sertifikayı silme işlemi
    $delete_query = "
        DELETE FROM sertifikalar 
        WHERE ogr_id IN (
            SELECT ogr_id 
            FROM eğitimler 
            WHERE kurs_id = ? AND basari_drm = 0
        )"; // Başarısız öğrencilerin sertifikalarını silme SQL
    $delete_stmt = $db->prepare($delete_query); // SQL sorgusu hazırlanıyor
    $delete_stmt->bind_param("i", $course_id); // Kurs ID'si parametre olarak bağlanıyor
    $delete_stmt->execute(); // Sorgu çalıştırılıyor
    $delete_stmt->close(); // Sorgu kapatılıyor

    echo "<script>
        alert('Değişiklikler başarıyla kaydedildi.'); // Başarı mesajı
        window.location.href = 'anasayfa.php'; // Kullanıcıyı anasayfaya yönlendir
    </script>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Karakter seti tanımlanıyor -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Sayfa boyutunun cihaz ekranına göre ayarlanması -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet"> <!-- Google Fonts ile Poppins fontu ekleniyor -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"> <!-- Font Awesome simgeleri için stil dosyası ekleniyor -->
    <title>Kurs Öğrenci Listesi</title> <!-- Sayfa başlığı tanımlanıyor -->
    <link rel="stylesheet" href="style.css"> <!-- CSS stil dosyası ekleniyor -->
</head>
<body>
<header>
    <nav>
        <ul>
            <li><img src="logo.jpg" alt="Logo" class="logo"></li> <!-- Logo resmi -->
            <li>
                <form method="GET" action="arama.php" class="search-form"> <!-- Arama formu -->
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i> <!-- Arama simgesi -->
                        <input type="text" name="search" placeholder="Kurs Ara..." required> <!-- Arama metni girişi -->
                    </div>
                </form>
            </li>
            <li><a href="anasayfa.php">Ana Sayfa</a></li> <!-- Ana sayfaya yönlendiren bağlantı -->
            <li><a href="kurslar.php">Kurslar</a></li> <!-- Kurslar sayfasına yönlendiren bağlantı -->
            <li><a href="profil.php">Profil</a></li> <!-- Kullanıcı profil sayfasına yönlendiren bağlantı -->
            <li><a href="cikis.php">Çıkış Yap</a></li> <!-- Çıkış yapmaya yönlendiren bağlantı -->
        </ul>
    </nav>
</header>
<h1 style="text-align: center;">Kurs Öğrenci Listesi</h1> <!-- Başlık -->
<form id="kogrlForm" method="POST"> <!-- Form başlatılıyor, değişiklikleri kaydetmek için POST yöntemi -->
    <table>
        <thead>
            <tr>
                <th>Ad</th> <!-- Öğrenci adı için başlık -->
                <th>Soyad</th> <!-- Öğrenci soyadı için başlık -->
                <th>E-posta</th> <!-- Öğrenci e-posta adresi için başlık -->
                <th>Başarı Durumu</th> <!-- Başarı durumu için başlık -->
                <th>Sil</th> <!-- Öğrenciyi silmek için başlık -->
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?> <!-- Her öğrenci için satır oluşturuluyor -->
                <tr>
                    <td><?= htmlspecialchars($row['ad']); ?></td> <!-- Öğrencinin adı -->
                    <td><?= htmlspecialchars($row['soyad']); ?></td> <!-- Öğrencinin soyadı -->
                    <td><?= htmlspecialchars($row['e_posta']); ?></td> <!-- Öğrencinin e-posta adresi -->
                    <td>
                        <input type="checkbox" name="basari_drm[<?= $row['egitim_id']; ?>]" 
                            <?= $row['basari_drm'] ? 'checked' : ''; ?> <?= $is_course_ongoing ? 'disabled' : ''; ?>> <!-- Başarı durumu checkbox'ı, kurs devam ediyorsa devre dışı bırakılıyor -->
                    </td>
                    <td>
                        <input type="checkbox" name="deleted_students[]" value="<?= $row['egitim_id']; ?>" <?= !$is_course_ongoing ? 'disabled' : ''; ?>> <!-- Silme checkbox'ı, kurs devam etmiyorsa devre dışı bırakılıyor -->
                    </td>
                </tr>
            <?php endwhile; ?> <!-- Öğrenci satırlarını bitirir -->
        </tbody>
    </table>
    <button id="kursaKatilB" type="submit" name="save_changes">Değişiklikleri Kaydet</button> <!-- Değişiklikleri kaydetmek için buton -->
</form>
</body>
</html>
