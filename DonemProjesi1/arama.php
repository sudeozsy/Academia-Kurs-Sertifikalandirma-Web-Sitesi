<?php
// Oturum başlatılır. Kullanıcının oturum bilgileri bu sayfada kullanılabilir.
session_start();
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arama Sonuçları - Kurslar</title>
    <!-- Yazı tipi ve ikonlar için harici kaynaklar -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Başlık için özel stil */
        h2 {
            text-align: center;
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <ul>
                <!-- Logo -->
                <li><img src="logo.jpg" alt="Logo" class="logo"></li>
                <!-- Arama formu -->
                <li>
                    <form method="GET" action="arama.php" class="search-form">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" name="search" placeholder="Kurs Ara..." required>
                        </div>
                    </form>
                </li>
                <!-- Oturum duruma göre menü -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Kullanıcı giriş yapmışsa -->
                    <li><a href="anasayfa.php">Ana Sayfa</a></li>
                    <li><a href="kurslar.php">Kurslar</a></li>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="cikis.php">Çıkış Yap</a></li>
                <?php else: ?>
                    <!-- Kullanıcı giriş yapmamışsa -->
                    <li><a href="anasayfa.php">Ana Sayfa</a></li>
                    <li><a href="kurslar.php">Kurslar</a></li>
                    <li><a href="giris.php">Giriş Yap</a></li>
                    <li><a href="kayit.php">Kayıt Ol</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section id="search-results">
            <h2>Arama Sonuçları</h2>
            <div class="course-grid">
                <?php
                // Veritabanı bağlantısı oluşturuluyor.
                $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');

                // Eğer bağlantıda hata varsa, hata mesajı görüntülenir ve işlem sonlanır.
                if ($db->connect_error) {
                    die("Bağlantı hatası: " . $db->connect_error);
                }

                // Eğer kullanıcı arama yapmışsa
                if (isset($_GET['search'])) {
                    $search_query = trim($_GET['search']); // Arama terimi alınır ve gereksiz boşluklar temizlenir.
                    
                    // SQL sorgusu: Kurs adı, eğitmen adı veya tarihlerde arama yapar.
                    $query = "
                        SELECT k.id, k.ad, k.bas_t, k.bit_t, k.resim_yolu, e.ad AS egitmen_ad 
                        FROM kurslar k
                        LEFT JOIN egitmenler e ON k.egitmen_id = e.id
                        WHERE k.ad LIKE ? OR e.ad LIKE ? OR k.bas_t LIKE ? OR k.bit_t LIKE ?
                        ORDER BY k.id DESC
                    ";

                    // Sorgu hazırlanır ve kullanıcı girdisi parametre olarak bağlanır.
                    $stmt = $db->prepare($query);
                    $like_query = '%' . $search_query . '%';
                    $stmt->bind_param("ssss", $like_query, $like_query, $like_query, $like_query);
                    $stmt->execute();
                    $result = $stmt->get_result(); // Sorgunun sonucu alınır.

                    if ($result->num_rows > 0) {
                        // Eğer sonuç varsa, kurslar listelenir.
                        while ($row = $result->fetch_assoc()): ?>
                            <div class="course-item">
                                <!-- Her bir kurs için bir bağlantı oluşturulur -->
                                <a href="kursDetay.php?id=<?= urlencode($row['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($row['resim_yolu']) ?>" alt="<?= htmlspecialchars($row['ad']) ?>">
                                    <h3><?= htmlspecialchars($row['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($row['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($row['bit_t']) ?></p>
                                    <p><strong>Eğitmen:</strong> <?= htmlspecialchars($row['egitmen_ad']) ?></p>
                                </a>
                            </div>
                        <?php endwhile;
                    } else {
                        // Eğer sonuç yoksa, kullanıcıya mesaj gösterilir.
                        echo "<p>Arama sonuçları bulunamadı.</p>";
                    }

                    $stmt->close(); // Sorgu kapatılır.
                } else {
                    // Eğer arama terimi yoksa, mesaj gösterilir.
                    echo "<p>Lütfen bir arama terimi girin.</p>";
                }

                // Veritabanı bağlantısı kapatılır.
                $db->close();
                ?>
            </div>
        </section>
    </main>


</body>
<footer>
    <!-- Alt bilgi -->
    <img src="logo.jpg" alt="Logo" class="logo">
    <p>&copy; Academia. Tüm hakları saklıdır.</p>
</footer>
</html>
