<?php
session_start();
ini_set('display_errors', 1);  // Hata mesajlarını ekrana yazdır
error_reporting(E_ALL);  // Hata raporlama seviyesini ayarla

// Kullanıcı giriş yapmış mı kontrol edin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: giris.php");  // Eğer kullanıcı giriş yapmamışsa, giriş sayfasına yönlendir
    exit;
}

// Veritabanı bağlantısını ekleyin
$host = "localhost";
$dbname = "kurs_sertifikalandirma";
$username = "root";  // Veritabanı kullanıcı adı
$password = "12345678";  // Veritabanı şifresi

try {
    // Veritabanı bağlantısı oluşturuluyor
    $dbConnection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  // Hata raporlama modeunu ayarla
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());  // Bağlantı hatası durumunda hata mesajını yazdır
}

// Kurs bilgilerini alın
$course_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;  // URL'den gelen kurs id'sini al
$query = $dbConnection->prepare("SELECT kurslar.*, egitmenler.ad AS egitmen_ad, egitmenler.soyad AS egitmen_soyad
                                  FROM kurslar 
                                  JOIN egitmenler ON kurslar.egitmen_id = egitmenler.id 
                                  WHERE kurslar.id = :id");  // Kurs ve eğitmen bilgilerini almak için SQL sorgusu
$query->execute([':id' => $course_id]);
$course = $query->fetch(PDO::FETCH_ASSOC);  // Veritabanından kurs bilgilerini çek

if (!$course) {
    die("Kurs bilgileri bulunamadı.");  // Kurs bulunamazsa hata mesajı ver
}

// Kursu al işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // Eğer form POST ile gönderildiyse
    $student_id = $_SESSION['user_id'];  // Giriş yapan öğrencinin id'si

    // İstekler tablosunda kurs var mı kontrol et
    $wishlistQuery = "SELECT 1 FROM istekler WHERE ogr_id = :ogr_id AND kurs_id = :kurs_id";  // İstekler tablosunda kurs var mı kontrol et
    $wishlistStmt = $dbConnection->prepare($wishlistQuery);  // SQL sorgusunu doğru şekilde veriyoruz
    $wishlistStmt->execute([':ogr_id' => $student_id, ':kurs_id' => $course_id]);
    $isInWishlist = $wishlistStmt->rowCount() > 0;  // Eğer kurs istek listesinde varsa

    if ($isInWishlist) {
        // İstek listesinden kursu sil
        $deleteQuery = "DELETE FROM istekler WHERE ogr_id = :ogr_id AND kurs_id = :kurs_id";  // İstekler tablosundan kursu sil
        $deleteStmt = $dbConnection->prepare($deleteQuery);
        $deleteStmt->execute([':ogr_id' => $student_id, ':kurs_id' => $course_id]);  // Kursu istekler tablosundan sil
    }

    // Kursu eğitimler tablosuna ekle
    $insertQuery = "INSERT INTO eğitimler (ogr_id, kurs_id, basari_drm) VALUES (:ogr_id, :kurs_id, :basari_drm)";  // Eğitimler tablosuna yeni bir kayıt ekle
    $insertStmt = $dbConnection->prepare($insertQuery);
    $insertStmt->execute([
        ':ogr_id' => $student_id,
        ':kurs_id' => $course_id,
        ':basari_drm' => false  // Kurs başlangıçta başarı durumu false olarak kaydediliyor
    ]);
    echo "<script>alert('Kurs alınmıştır!'); window.location.href = 'anasayfa.php';</script>";  // Kurs başarıyla alındığında uyarı ver ve anasayfaya yönlendir
    exit;
}
?>


<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kursa Katıl</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Genel Stiller */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        /* Kurs içeriği */
        .course-content {
            display: flex;
            justify-content: flex-start;
            /* İçeriği sola hizala */
            align-items: flex-start;
            /* İçeriği üstten hizala */
            padding: 20px;
            gap: 30px;
            /* Elemanlar arasına boşluk ekler */
            flex-direction: row;
            width: 50%;
            margin-top: 3%;
        }

        /* Sol bölüm (kurs resmi) */
        .course-image-container {
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .course-image-container img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            object-fit: cover;
        }

        .course-image-container p {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        /* Sağ bölüm (kurs bilgileri) */
        .course-details {
            flex: 1;
            text-align: left;
            width: 100%;
            max-width: 600px;
            /* Kurs bilgileri için maksimum genişlik */
            padding-left: 5%;
        }

        .course-details h2 {
            font-size: 28px;
            color: #333;
        }

        .course-details p {
            font-size: 16px;
            color: #666;
            margin: 10px 0;
        }

        .course-details strong {
            color: #333;
        }

        /* Kursa Katıl Butonu */
        .course-footer {
            display: flex;
            flex-direction: column;
            /* Buton ve yazıyı dikey olarak sıralar */
            justify-content: center;
            /* Butonu ve yazıyı dikeyde ortalar */
            align-items: center;
            /* Butonu ve yazıyı yatayda ortalar */
            width: 100%;
            margin-top: 20px;
            /* Yazıyla buton arasına boşluk ekler */
        }

        .course-footer button {
            background-color: #4CAF50;
            /* Butonun yeşil rengini ayarlar */
            color: white;
            /* Buton yazı rengini beyaz yapar */
            border: none;
            /* Kenarlıkları kaldırır */
            padding: 10px 20px;
            /* İçerik etrafında boşluk bırakır */
            font-size: 16px;
            /* Yazı boyutunu ayarlar */
            cursor: pointer;
            /* Fare işaretçisini pointer yapar */
            border-radius: 5px;
            /* Butonun köşelerini yuvarlar */
            transition: background-color 0.3s;
            /* Hover efektini ekler */
            width: 200px;
            /* Buton genişliği */
            margin-top: 20px;
            /* Buton ile üstteki içerik arasında boşluk bırakır */
        }

        .course-footer button:hover {
            background-color: #45a049;
            /* Hover durumunda buton rengini değiştirir */
        }

        .course-footer p {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
            text-align: center;
        }

        /* Responsive Tasarım */
        @media (max-width: 768px) {
            .course-content {
                flex-direction: column;
                /* Küçük ekranlarda içerik dikeyde sıralanır */
                padding: 15px;
                margin: 20px;
            }

            .course-image-container {
                max-width: 100%;
                /* Resim boyutu küçük ekranlarda %100 olur */
            }

            .course-details {
                width: 100%;
                /* Bilgiler de %100 genişlikte olur */
            }

            .search-form input {
                width: 150px;
            }
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><img src="logo.jpg" alt="Logo" class="logo"></li> <!-- Logo resmi -->
                <li>
                    <!-- Arama formu -->
                    <form method="GET" action="arama.php" class="search-form">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i> <!-- Arama simgesi -->
                            <input type="text" name="search" placeholder="Kurs Ara..." required> <!-- Arama input alanı -->
                        </div>
                    </form>
                </li>
                <li><a href="anasayfa.php">Ana Sayfa</a></li> <!-- Ana sayfaya bağlantı -->
                <li><a href="kurslar.php">Kurslar</a></li> <!-- Kurslar sayfasına bağlantı -->
                <li><a href="profil.php">Profil</a></li> <!-- Profil sayfasına bağlantı -->
                <li><a href="cikis.php">Çıkış Yap</a></li> <!-- Çıkış yap sayfasına bağlantı -->
            </ul>
        </nav>
    </header>

    <div class="course-content">
        <!-- Kurs Resmi Sol Tarafta -->
        <div class="course-image-container">
            <p><?php echo htmlspecialchars($course['ad']); ?></p> <!-- Kurs ismi resmin altında -->
            <img src="<?php echo htmlspecialchars($course['resim_yolu']); ?>" alt="<?php echo htmlspecialchars($course['ad']); ?>"> <!-- Kurs resmi -->
        </div>

        <!-- Kurs Bilgileri Sağ Tarafta -->
        <div class="course-details">
            <p><strong>Eğitmen:</strong> <?php echo htmlspecialchars($course['egitmen_ad'] . ' ' . $course['egitmen_soyad']); ?></p> <!-- Eğitmenin adı ve soyadı -->
            <p><strong>İletişim:</strong> <?php echo htmlspecialchars($course['iletişim']); ?></p> <!-- Eğitmenin iletişim bilgisi -->
            <p><strong>Adres:</strong> <?php echo htmlspecialchars($course['adres']); ?></p> <!-- Kursun verildiği adres -->
            <p><strong>Fiyat:</strong> <?php echo htmlspecialchars($course['fiyat']); ?> TL</p> <!-- Kursun fiyatı -->
            <p><strong>Başlangıç Tarihi:</strong> <?php echo htmlspecialchars($course['bas_t']); ?></p> <!-- Kursun başlangıç tarihi -->
            <p><strong>Bitiş Tarihi:</strong> <?php echo htmlspecialchars($course['bit_t']); ?></p> <!-- Kursun bitiş tarihi -->
        </div>
    </div>

    <div class="course-footer">
        <!-- Kursa katılma formu -->
        <form method="post">
            <button type="submit" class="btn-primary">Kursa Katıl</button> <!-- Kursa katıl butonu -->
        </form>
        <!-- Kurs hakkında ek bilgi metni -->
        <p>Daha detaylı bilgi için kursunuzun verildiği adrese giderek ziyaret edebilir, ödeme işlemini gerçekleştirebilirsiniz.</p>
    </div>

</body>

</html>