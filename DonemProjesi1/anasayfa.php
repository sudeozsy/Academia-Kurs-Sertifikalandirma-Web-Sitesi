<?php session_start(); ?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs Değerlendirme ve Sertifikalandırma Sistemi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="app.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><img src="logo.jpg" alt="Logo" class="logo"></li>
                <li>
                    <form method="GET" action="arama.php" class="search-form">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" placeholder="Kurs Ara..." required>
                        </div>
                    </form>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="anasayfa.php">Ana Sayfa</a></li>
                    <li><a href="kurslar.php">Kurslar</a></li>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="cikis.php">Çıkış Yap</a></li>
                <?php else: ?>
                    <li><a href="anasayfa.php">Ana Sayfa</a></li>
                    <li><a href="kurslar.php">Kurslar</a></li>
                    <li><a href="giris.php">Giriş Yap</a></li>
                    <li><a href="kayıt.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
    <section id="hero">
        <div class="hero-content1">
            <div class="text-content">
                <h1>Kurslara Katılın ve Kendinizi Geliştirin</h1>
                <p>Kursları başarıyla tamamlayın ve başarılarınızı sertifikalandırın.</p>
                <div class="btn-group">
                    <a href="kurslar.php" class="btn">TÜM KURSLAR <span>&#x2192;</span></a>
                </div>
            </div>
            <?php
            // Veritabanı bağlantısı
            $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');

            if ($db->connect_error) {
                die("Bağlantı hatası: " . $db->connect_error);
            }

            // Arama işlemi
            $search_results = [];
            $search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

            if (!empty($search_query)) {
                $query = "SELECT id, ad, resim_yolu FROM kurslar WHERE ad LIKE ? ORDER BY id DESC";
                $stmt = $db->prepare($query);
                $like_query = '%' . $search_query . '%';
                $stmt->bind_param("s", $like_query);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $search_results[] = $row;
                }

                $stmt->close();
            }

            // Varsayılan kurslar (arama yapılmazsa)
            if (empty($search_results) && empty($search_query)) {
                // Popüler kurslar
                $popular_courses = [];
                $query = "
                    SELECT k.id, k.ad, k.resim_yolu, COUNT(e.kurs_id) AS ogrenci_sayisi
                    FROM kurslar k
                    LEFT JOIN eğitimler e ON k.id = e.kurs_id
                    GROUP BY k.id
                    ORDER BY ogrenci_sayisi DESC
                    LIMIT 5
                ";
                $result = $db->query($query);
                while ($row = $result->fetch_assoc()) {
                    $popular_courses[] = $row;
                }

                // En son eklenen kurslar
                $latest_courses = [];
                $query = "SELECT id, ad, resim_yolu FROM kurslar ORDER BY id DESC LIMIT 5";
                $result = $db->query($query);
                while ($row = $result->fetch_assoc()) {
                    $latest_courses[] = $row;
                }
            }
            ?>
        </div>
    </section>

    <!-- Popüler Kurslar Bölümü -->
    <section id="popular-courses">
        <h2>Popüler Kurslar</h2>
        <div class="course-grid">
            <?php
            // Veritabanı bağlantısı
            $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');

            if ($db->connect_error) {
                die("Bağlantı hatası: " . $db->connect_error);
            }

            // En çok alınan kursları getiren sorgu
            $query = "
                SELECT k.id, k.ad, k.bas_t, k.bit_t, k.resim_yolu, COUNT(e.kurs_id) AS ogrenci_sayisi
                FROM kurslar k
                LEFT JOIN eğitimler e ON k.id = e.kurs_id
                GROUP BY k.id
                ORDER BY ogrenci_sayisi DESC, k.ad ASC LIMIT 5
            ";
            $result = $db->query($query);

            if ($result->num_rows > 0):
                // Kursları döngü ile listele
                while ($row = $result->fetch_assoc()): ?>
                    <div class="course-item">
                        <a href="kursDetay.php?id=<?= urlencode($row['id']) ?>" style="text-decoration: none;">
                            <img src="<?= htmlspecialchars($row['resim_yolu']) ?>"
                                alt="<?= htmlspecialchars($row['ad']) ?>">
                            <h3><?= htmlspecialchars($row['ad']) ?></h3>
                            <p><strong>Başlangıç:</strong> <?= htmlspecialchars($row['bas_t']) ?></p>
                            <p><strong>Bitiş:</strong> <?= htmlspecialchars($row['bit_t']) ?></p>
                        </a>
                    </div>
                <?php endwhile;
            else: ?>
                <p>Henüz kayıtlı bir kurs bulunmamaktadır.</p>
            <?php endif;

            // Veritabanı bağlantısını kapat
            $db->close();
            ?>
        </div>
    </section>

    <section id="latest-courses">
        <h2>En Son Eklenen Kurslar</h2>
        <div class="course-grid">
            <?php
            // Veritabanı bağlantısı
            $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');

            if ($db->connect_error) {
                die("Bağlantı hatası: " . $db->connect_error);
            }

            // En son eklenen ilk 5 kursu getiren sorgu
            $query = "
                SELECT id, ad, resim_yolu,bas_t,bit_t
                FROM kurslar
                ORDER BY id DESC
                LIMIT 5
            ";
            $result = $db->query($query);

            if ($result->num_rows > 0):
                // Kursları döngü ile listele
                while ($row = $result->fetch_assoc()): ?>
                    <div class="course-item">
                        <a href="kursDetay.php?id=<?= urlencode($row['id']) ?>" style="text-decoration: none;">
                            <img src="<?= htmlspecialchars($row['resim_yolu']) ?>"
                                alt="<?= htmlspecialchars($row['ad']) ?>">
                            <h3><?= htmlspecialchars($row['ad']) ?></h3>
                            <p><strong>Başlangıç:</strong> <?= htmlspecialchars($row['bas_t']) ?></p>
                            <p><strong>Bitiş:</strong> <?= htmlspecialchars($row['bit_t']) ?></p>
                        </a>
                    </div>
                <?php endwhile;
            else: ?>
                <p>Henüz yeni eklenen bir kurs bulunmamaktadır.</p>
            <?php endif;

            // Veritabanı bağlantısını kapat
            $db->close();
            ?>
        </div>
    </section>
</main>
    <footer>
        <img src="logo.jpg" alt="Logo" class="logo">
        <p>&copy; Academia. Tüm hakları saklıdır.</p>
    </footer>
</body>

</html>