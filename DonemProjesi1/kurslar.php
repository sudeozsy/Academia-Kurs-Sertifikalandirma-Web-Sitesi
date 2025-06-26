<?php
session_start(); // Oturum başlatılıyor

// Veritabanı bağlantısı
$servername = "localhost";
$username = "root";
$password = "12345678";
$dbname = "kurs_sertifikalandirma";

$conn = new mysqli($servername, $username, $password, $dbname); // Veritabanı bağlantısı açılıyor

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error); // Bağlantı hatası varsa program sonlandırılıyor
}

// Kategorileri ve her kategorideki kurs sayısını çek
$sql = "
    SELECT kategoriler.id, kategoriler.ad, kategoriler.kat_icon, COUNT(kurslar.id) AS kurs_sayisi
    FROM kategoriler
    LEFT JOIN kurslar ON kategoriler.id = kurslar.kategori_id
    GROUP BY kategoriler.id
"; // Kategoriler ve her kategorideki kurs sayısı sorgusu
$result = $conn->query($sql); // Sorgu çalıştırılıyor

$kategoriler = []; // Kategoriler dizisi oluşturuluyor
if ($result->num_rows > 0) { // Sonuç varsa
    while ($row = $result->fetch_assoc()) {
        $kategoriler[] = $row; // Her kategori verisi dizisine ekleniyor
    }
}

// Seçilen kategoriye göre kursları çek
$selectedCategory = isset($_GET['kategori']) ? $_GET['kategori'] : 'tum-kurslar'; // Kategori parametresi URL'den alınıyor

$devamEdenKurslar = []; // Devam eden kurslar dizisi
$zamaniGecmisKurslar = []; // Zamanı geçmiş kurslar dizisi

$currentDate = date("Y-m-d"); // Şu anki tarih alınıyor

if ($selectedCategory === 'tum-kurslar') { // Tüm kurslar seçilmişse
    $kurs_query = "SELECT * FROM kurslar"; // Tüm kurslar sorgusu
    $kurslar_result = $conn->query($kurs_query); // Kurslar sorgusu çalıştırılıyor

    while ($row = $kurslar_result->fetch_assoc()) { // Her kurs verisi için döngü başlatılıyor
        // Kursları bitiş tarihine göre ayır
        if ($row['bit_t'] >= $currentDate) { // Kurs bitiş tarihi bugünden sonra ise
            $devamEdenKurslar[] = $row; // Devam eden kurslara ekleniyor
        } else {
            $zamaniGecmisKurslar[] = $row; // Zamanı geçmiş kurslara ekleniyor
        }
    }
} else {
    $kurs_query = "SELECT * FROM kurslar WHERE kategori_id = ?"; // Seçilen kategoriye göre kursları çekme sorgusu
    $stmt = $conn->prepare($kurs_query); // Sorgu hazırlanıyor
    $stmt->bind_param("i", $selectedCategory); // Parametre bağlanıyor
    $stmt->execute(); // Sorgu çalıştırılıyor
    $kurslar_result = $stmt->get_result(); // Sonuç alınıyor

    while ($row = $kurslar_result->fetch_assoc()) { // Her kurs verisi için döngü başlatılıyor
        // Kursları bitiş tarihine göre ayır
        if ($row['bit_t'] >= $currentDate) { // Kurs bitiş tarihi bugünden sonra ise
            $devamEdenKurslar[] = $row; // Devam eden kurslara ekleniyor
        } else {
            $zamaniGecmisKurslar[] = $row; // Zamanı geçmiş kurslara ekleniyor
        }
    }
    $stmt->close(); // Prepared statement kapatılıyor
}

$conn->close(); // Veritabanı bağlantısı kapatılıyor
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8"> <!-- Karakter seti UTF-8 olarak ayarlanıyor -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobil uyumlu görüntüleme için ayar -->
    <title>Kurslar - Kurs Platformu</title> <!-- Sayfa başlığı -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome ikonları için stil dosyası -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet"> <!-- Google font bağlantısı -->
    <script src="app.js"></script> <!-- JavaScript dosyası bağlantısı -->
    <link rel="stylesheet" href="style.css"> <!-- Kendi stil dosyanız -->
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><img src="logo.jpg" alt="Logo" class="logo"></li> <!-- Logo -->
                <li>
                    <form method="GET" action="arama.php" class="search-form"> <!-- Arama formu -->
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i> <!-- Arama ikonu -->
                            <input type="text" name="search" placeholder="Kurs Ara..." required> <!-- Arama kutusu -->
                        </div>
                    </form>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?> <!-- Kullanıcı giriş yapmışsa -->
                    <li><a href="anasayfa.php">Ana Sayfa</a></li> <!-- Ana sayfaya git -->
                    <li><a href="kurslar.php">Kurslar</a></li> <!-- Kurslar sayfasına git -->
                    <li><a href="profil.php">Profil</a></li> <!-- Profil sayfasına git -->
                    <li><a href="cikis.php">Çıkış Yap</a></li> <!-- Çıkış yap -->
                <?php else: ?> <!-- Kullanıcı giriş yapmamışsa -->
                    <li><a href="anasayfa.php">Ana Sayfa</a></li> <!-- Ana sayfaya git -->
                    <li><a href="kurslar.php">Kurslar</a></li> <!-- Kurslar sayfasına git -->
                    <li><a href="giris.php">Giriş Yap</a></li> <!-- Giriş yap sayfasına git -->
                    <li><a href="kayıt.php">Kayıt Ol</a></li> <!-- Kayıt ol sayfasına git -->
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>

    <section id="courses-categories"> <!-- Kurs kategorileri bölümü -->
    <h2>Kurs Kategorileri</h2>
    <ul>
        <?php foreach ($kategoriler as $kategori): ?> <!-- Her kategori için döngü -->
            <li>
                <a href="kurslar.php?kategori=<?= urlencode($kategori['id']) ?>" 
                    class="<?= $selectedCategory == $kategori['id'] ? 'selected' : '' ?>"> <!-- Kategori seçiliyse 'selected' sınıfını ekle -->
                    <i class="<?= htmlspecialchars($kategori['kat_icon']) ?>"></i> <!-- Kategori ikonu -->
                    <?= htmlspecialchars($kategori['ad']) ?> <!-- Kategori adı -->
                </a>
            </li>
        <?php endforeach; ?>
        <li>
            <a href="kurslar.php?kategori=tum-kurslar"
                class="<?= $selectedCategory == 'tum-kurslar' ? 'selected' : '' ?>">Tüm Kurslar</a> <!-- Tüm kurslar linki -->
        </li>
    </ul>
</section>

<!-- Devam Eden Kurslar -->
        <section class="courses">
            <h2>Devam Eden Kurslar</h2>
            <div class="course-grid">
                <?php if (!empty($devamEdenKurslar)): ?> <!-- Devam eden kurslar varsa -->
                    <?php foreach ($devamEdenKurslar as $kurs): ?> <!-- Her devam eden kurs için döngü -->
                        <div class="course-item">
                            <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;"> <!-- Kurs detay sayfasına link -->
                                <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>" 
                                    alt="<?= htmlspecialchars($kurs['ad']) ?>"> <!-- Kurs adı alt yazısı -->
                                <h3><?= htmlspecialchars($kurs['ad']) ?></h3> <!-- Kurs adı -->
                                <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p> <!-- Başlangıç tarihi -->
                                <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p> <!-- Bitiş tarihi -->
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Devam eden kurs bulunamadı.</p> <!-- Devam eden kurs yoksa mesaj -->
                <?php endif; ?>
            </div>
        </section>

        <!-- Zamanı Geçmiş Kurslar -->
        <section class="courses">
            <h2>Süresi Dolmuş Kurslar</h2>
            <div class="course-grid">
                <?php if (!empty($zamaniGecmisKurslar)): ?> <!-- Zamanı geçmiş kurslar varsa -->
                    <?php foreach ($zamaniGecmisKurslar as $kurs): ?> <!-- Her zamanı geçmiş kurs için döngü -->
                        <div class="course-item">
                            <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;"> <!-- Kurs detay sayfasına link -->
                                <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>" 
                                    alt="<?= htmlspecialchars($kurs['ad']) ?>"> <!-- Kurs adı alt yazısı -->
                                <h3><?= htmlspecialchars($kurs['ad']) ?></h3> <!-- Kurs adı -->
                                <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p> <!-- Başlangıç tarihi -->
                                <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p> <!-- Bitiş tarihi -->
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Zamanı geçmiş kurs bulunamadı.</p> <!-- Zamanı geçmiş kurs yoksa mesaj -->
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <img src="logo.jpg" alt="Logo" class="logo"> <!-- Alt kısımda logo -->
        <p>&copy; Academia. Tüm hakları saklıdır.</p> <!-- Telif hakkı mesajı -->
    </footer>
</body>

</html>