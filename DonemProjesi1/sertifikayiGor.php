<?php
// Eğer fpdf.php dosyasını 'fpdf' klasörüne koyduysanız

session_start(); // Oturum başlatılıyor
if (!isset($_SESSION['user_id'])) { // Kullanıcı giriş yapmamışsa
    // Kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir.
    header("Location: giris.php");
    exit();
}

$course_id = $_GET['id']; // Kurs ID'si URL'den alınacak
$user_id = $_SESSION['user_id']; // Giriş yapan kullanıcının ID'si

// Veritabanı bağlantısı
$db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');
if ($db->connect_error) { // Bağlantı hatası kontrolü
    die("Bağlantı hatası: " . $db->connect_error);
}

// Kurs ve eğitmen bilgilerini almak için sorgu
$query = "SELECT c.ad AS kurs_ad, c.bas_t AS baslangic_tarihi, c.bit_t AS bitis_tarihi, 
                 e.ad AS egitmen_ad, e.soyad AS egitmen_soyad
          FROM kurslar c
          JOIN egitmenler e ON c.egitmen_id = e.id
          JOIN eğitimler et ON c.id = et.kurs_id
          WHERE c.id = ? AND et.ogr_id = ? AND et.basari_drm = 1"; // Kursun başarıyla tamamlandığına dair kontrol

$stmt = $db->prepare($query); // SQL sorgusunu hazırlıyoruz
$stmt->bind_param('ii', $course_id, $user_id); // Parametreleri bağlıyoruz
$stmt->execute(); // Sorguyu çalıştırıyoruz
$result = $stmt->get_result(); // Sonuçları alıyoruz

// Kurs bilgisi ve eğitmen bilgisi
$course = $result->fetch_assoc(); // Veritabanından kurs bilgisini alıyoruz

// Öğrencinin ad ve soyad bilgilerini almak için sorgu
$query_student = "SELECT ad, soyad FROM ogrenciler WHERE id = ?"; // Öğrenci bilgilerini alıyoruz
$stmt_student = $db->prepare($query_student); // Öğrenci sorgusunu hazırlıyoruz
$stmt_student->bind_param('i', $user_id); // Öğrenci ID'sini bağlıyoruz
$stmt_student->execute(); // Sorguyu çalıştırıyoruz
$student_result = $stmt_student->get_result(); // Sonuçları alıyoruz
$student = $student_result->fetch_assoc(); // Öğrenci bilgilerini alıyoruz

if (!$course || !$student) { // Eğer kurs veya öğrenci bilgisi bulunamazsa
    // Eğer kurs veya başarıyla tamamlanmış eğitim bulunmazsa
    echo "<p>Bu kursu başarıyla tamamlamadınız ya da geçersiz bir kurs seçtiniz.</p>";
    exit();
}

$stmt->close(); // Sorgu bağlantılarını kapatıyoruz
$stmt_student->close(); // Öğrenci sorgusu bağlantısını kapatıyoruz
$db->close(); // Veritabanı bağlantısını kapatıyoruz
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifika Görüntüle</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="style.css"> <!-- Stil dosyası ekleniyor -->
    <script src="app.js"></script> <!-- JavaScript dosyası ekleniyor -->
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><img src="logo.jpg" alt="Logo" class="logo"></li> <!-- Logo görseli ekleniyor -->
                <li>
                    <form method="GET" action="arama.php" class="search-form"> <!-- Arama formu -->
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i> <!-- Arama ikonunun eklenmesi -->
                            <input type="text" name="search" placeholder="Kurs Ara..." required> <!-- Arama inputu -->
                        </div>
                    </form>
                </li>
                <li><a href="anasayfa.php">Ana Sayfa</a></li> <!-- Ana sayfa bağlantısı -->
                <li><a href="kurslar.php">Kurslar</a></li> <!-- Kurslar sayfasına bağlantı -->
                <li><a href="profil.php">Profil</a></li> <!-- Profil sayfasına bağlantı -->
                <li><a href="cikis.php">Çıkış Yap</a></li> <!-- Çıkış yapma bağlantısı -->
            </ul>
        </nav>
    </header>

    <div class="certificate-container">
        <h1>Sertifikanız</h1> <!-- Başlık: Sertifika Görüntüleme -->

        <div class="course-info">
            <p><strong>Kurs Adı:</strong> <?php echo htmlspecialchars($course['kurs_ad']); ?></p> <!-- Kurs adı bilgisi -->
            <p><strong>Eğitmen:</strong>
                <?php echo htmlspecialchars($course['egitmen_ad']) . " " . htmlspecialchars($course['egitmen_soyad']); ?></p> <!-- Eğitmen adı ve soyadı -->
            <p><strong>Başlangıç Tarihi:</strong> <?php echo htmlspecialchars($course['baslangic_tarihi']); ?></p> <!-- Başlangıç tarihi -->
            <p><strong>Bitiş Tarihi:</strong> <?php echo htmlspecialchars($course['bitis_tarihi']); ?></p> <!-- Bitiş tarihi -->
        </div>

        <div class="certificate-info">
            <h2>Kursu Başarıyla Tamamladınız!</h2> <!-- Başarı mesajı -->
            <p>Bu sertifika, <?php echo htmlspecialchars($course['kurs_ad']); ?> kursunu başarıyla tamamladığınızı
                gösterir.</p> <!-- Kurs tamamlama mesajı -->
            <p><strong>Sertifika Sahibi:</strong>
                <?php echo htmlspecialchars($student['ad']) . " " . htmlspecialchars($student['soyad']); ?> </p> <!-- Sertifika sahibi bilgisi -->
        </div>

        <div class="certificate-actions">
            <a href="sertifikaIndir.php?id=<?php echo $course_id; ?>" class="btn-sertifika">
                <img id="gif" src="download.gif" >Sertifikayı İndir </a> <!-- Sertifika indirme butonu -->
        </div>

    </div>
</body>
</html>