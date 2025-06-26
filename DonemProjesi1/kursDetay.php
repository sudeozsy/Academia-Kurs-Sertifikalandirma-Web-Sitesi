<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs Detayı - Kurs Değerlendirme ve Sertifikalandırma Sistemi</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php
    session_start(); // Oturumu başlat
    // Veritabanı bağlantısını kur
    $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');
    if ($db->connect_error) {
        die("Bağlantı hatası: " . $db->connect_error); // Bağlantı hatası durumunda mesaj göster
    }

    $course_id = $_GET['id']; // URL'den kurs id'sini al
    // Kurs bilgilerini ve eğitmenin adını sorgulayan SQL sorgusu
    $query = "SELECT kurslar.*, CONCAT(egitmenler.ad, ' ', egitmenler.soyad) AS egitmen_ad, kurslar.bit_t, kurslar.bas_t, kurslar.aciklama 
              FROM kurslar 
              JOIN egitmenler ON kurslar.egitmen_id = egitmenler.id 
              WHERE kurslar.id = ?";
    // Sorguyu hazırla ve parametreyi bağla
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $course_id);
    $stmt->execute(); // Sorguyu çalıştır
    $result = $stmt->get_result(); // Sonuçları al
    $course = $result->fetch_assoc(); // Sonuçları ilişkilendir
    if (!$course) {
        echo "<p>Kurs bilgileri bulunamadı.</p>"; // Eğer kurs bulunamazsa hata mesajı göster
        exit;
    }
    $db->close(); // Veritabanı bağlantısını kapat
    ?>

    <header>
        <nav>
            <ul>
                <li><img src="logo.jpg" alt="Logo" class="logo"></li> <!-- Logo görüntülenir -->
                <li>
                    <!-- Arama formu -->
                    <form method="GET" action="arama.php" class="search-form">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i> <!-- Arama simgesi -->
                            <input type="text" name="search" placeholder="Kurs Ara..." required>
                            <!-- Arama input alanı -->
                        </div>
                    </form>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?> <!-- Kullanıcı giriş yapmış mı? -->
                    <li><a href="anasayfa.php">Ana Sayfa</a></li> <!-- Ana sayfa bağlantısı -->
                    <li><a href="kurslar.php">Kurslar</a></li> <!-- Kurslar sayfasına bağlantı -->
                    <li><a href="profil.php">Profil</a></li> <!-- Profil sayfasına bağlantı -->
                    <li><a href="cikis.php">Çıkış Yap</a></li> <!-- Çıkış yapma bağlantısı -->
                <?php else: ?>
                    <li><a href="anasayfa.php">Ana Sayfa</a></li> <!-- Ana sayfa bağlantısı -->
                    <li><a href="kurslar.php">Kurslar</a></li> <!-- Kurslar sayfasına bağlantı -->
                    <li><a href="giris.php">Giriş Yap</a></li> <!-- Giriş yapma sayfasına bağlantı -->
                    <li><a href="kayıt.php">Kayıt Ol</a></li> <!-- Kayıt olma sayfasına bağlantı -->
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section id="course-detail">
            <div class="hero" style="background-image: url('<?php echo htmlspecialchars($course['resim_yolu']); ?>');">
                <div class="hero-overlay"></div>
                <div class="hero-content">
                    <h1><?php echo htmlspecialchars($course['ad']); ?></h1> <!-- Kurs adı burada görüntüleniyor -->
                    <p><strong>Eğitmen:</strong> <?php echo htmlspecialchars($course['egitmen_ad']); ?></p>
                    <!-- Eğitmen adı burada görüntüleniyor -->
                </div>
            </div>

            <div class="course-content">
                <?php
                // Kullanıcının kursa katılıp katılmadığını kontrol et
                if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'student'): // Eğer kullanıcı giriş yaptıysa ve öğrenci rolü varsa
                    $user_id = $_SESSION['user_id']; // Kullanıcı id'sini oturumdan al
                
                    try {
                        // Veritabanı bağlantısını kur
                        $dbConnection = new PDO('mysql:host=localhost;dbname=kurs_sertifikalandirma', 'root', '12345678');
                        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        // Kullanıcının kursa katılıp katılmadığını kontrol et
                        $enrollmentQuery = "SELECT 1 FROM eğitimler WHERE ogr_id = :ogr_id AND kurs_id = :kurs_id";
                        $enrollmentStmt = $dbConnection->prepare($enrollmentQuery);
                        $enrollmentStmt->execute([':ogr_id' => $user_id, ':kurs_id' => $course_id]);
                        $isEnrolled = $enrollmentStmt->rowCount() > 0; // Eğer kullanıcı kursa katılmışsa, bu değer true olur
                
                        // Kurs zaten istek listesinde mi? Kontrol et
                        $wishlistQuery = "SELECT 1 FROM istekler WHERE ogr_id = :ogr_id AND kurs_id = :kurs_id";
                        $wishlistStmt = $dbConnection->prepare($wishlistQuery);
                        $wishlistStmt->execute([':ogr_id' => $user_id, ':kurs_id' => $course_id]);
                        $isInWishlist = $wishlistStmt->rowCount() > 0; // Kurs istek listesinde mi?
                
                        // İstek listesine ekleme ya da silme işlemi
                        if ($_SERVER['REQUEST_METHOD'] === 'POST'): // Eğer form gönderildiyse
                            // Eğer kurs istek listesinde varsa, sil
                            if ($isInWishlist) {
                                $deleteQuery = "DELETE FROM istekler WHERE ogr_id = :ogr_id AND kurs_id = :kurs_id";
                                $deleteStmt = $dbConnection->prepare($deleteQuery);
                                $deleteStmt->execute([':ogr_id' => $user_id, ':kurs_id' => $course_id]);
                                $isInWishlist = false; // Silindikten sonra durum güncellenir
                            } else {
                                // Eğer kurs istek listesinde yoksa, ekle
                                $insertQuery = "INSERT INTO istekler (ogr_id, kurs_id) VALUES (:ogr_id, :kurs_id)";
                                $insertStmt = $dbConnection->prepare($insertQuery);
                                $insertStmt->execute([':ogr_id' => $user_id, ':kurs_id' => $course_id]);
                                $isInWishlist = true; // Ekledikten sonra durum güncellenir
                            }
                        endif;

                        // Kursa katılmamışsa ve istek listesinde değilse
                        if (!$isEnrolled): // Eğer kullanıcı kursa katılmadıysa
                            ?>
                            <form action="" method="post">
                                <input type="hidden" name="kurs_id" value="<?= $course_id ?>">
                                <!-- Kurs id'si gizli alanda gönderilir -->
                                <input type="hidden" name="ogr_id" value="<?= $_SESSION['user_id'] ?>">
                                <!-- Kullanıcı id'si gizli alanda gönderilir -->
                                <button type="submit" class="btn-primary"
                                    style="position: absolute; top: 10px; right: 10px; background: none; border: none; cursor: pointer;">
                                    <!-- Kalp simgesi, istek listesine ekleme ya da çıkarma durumuna göre değişir -->
                                    <i class="<?= $isInWishlist ? 'fa-solid' : 'fa-regular' ?> fa-heart"
                                        style="font-size: 24px; color: <?= $isInWishlist ? '#4caf50' : '#4caf50'; ?>;"></i>
                                </button>
                            </form>
                            <?php
                        endif;

                    } catch (PDOException $e) {
                        echo "Hata: " . $e->getMessage(); // Hata mesajını göster
                    }
                endif;
                ?>


                <section class="course-stats">
                    <?php
                    $course_id = $course['id']; // Kursun ID'sini al
                    $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');

                    if ($db->connect_error) {
                        die("Bağlantı hatası: " . $db->connect_error); // Veritabanı bağlantısı hatası kontrolü
                    }

                    // Katılımcı sayısını hesapla
                    $query = "SELECT COUNT(*) AS katilimci_sayisi FROM eğitimler WHERE kurs_id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param('i', $course_id); // Kurs ID'si ile parametreyi bağla
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $data = $result->fetch_assoc();
                    $katilimci_sayisi = $data['katilimci_sayisi']; // Katılımcı sayısını al
                    
                    $stmt->close();
                    $db->close();
                    ?>

                    <!-- Kurs bilgilerini görüntüle -->
                    <p><strong>Kurs Adı:</strong> <?php echo htmlspecialchars($course['ad']); ?></p>
                    <p><strong>Başlangıç Tarihi:</strong> <?php echo htmlspecialchars($course['bas_t']); ?></p>
                    <p><strong>Bitiş Tarihi:</strong> <?php echo htmlspecialchars($course['bit_t']); ?></p>
                    <p><strong>Katılımcı Sayısı:</strong> <?php echo htmlspecialchars($katilimci_sayisi); ?></p>

                    <?php
                    $course_instructor_id = $course['egitmen_id']; // Kursun eğitmen ID'sini al
                    $course_end_date = $course['bit_t']; // Kursun bitiş tarihi
                    $current_date = date("Y-m-d"); // Bugünün tarihi
                    
                    if (isset($_SESSION['user_id'])): // Eğer kullanıcı giriş yapmışsa
                        if ($_SESSION['role'] == 'instructor' && $_SESSION['user_id'] == $course_instructor_id): ?>
                            <a href="kursOgrenciListesi.php?id=<?php echo $course_id; ?>" class="btn-primary">Kursu Alan
                                Öğrenciler</a>
                            <!-- Eğitmen ise ve kursun eğitmeni ise öğrenci listesine git -->
                        <?php elseif ($_SESSION['role'] == 'student'): // Eğer kullanıcı öğrenci ise
                            $user_id = $_SESSION['user_id'];
                            $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');
                            if ($db->connect_error) {
                                die("Bağlantı hatası: " . $db->connect_error); // Veritabanı bağlantısı hatası kontrolü
                            }

                            $query = "SELECT COUNT(*) AS count, basari_drm FROM eğitimler WHERE ogr_id = ? AND kurs_id = ?";
                            $stmt = $db->prepare($query);
                            $stmt->bind_param('ii', $user_id, $course_id); // Öğrenci ID ve kurs ID'sini bağla
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $data = $result->fetch_assoc();
                            $kurs_alinmis = $data['count'] > 0; // Kurs alınmış mı?
                            $kurs_basarili = $data['basari_drm'] == 1; // Kurs başarıyla tamamlanmış mı?
                    
                            $stmt->close();
                            $db->close();

                            if ($kurs_alinmis): // Eğer kurs alınmışsa
                                if ($course_end_date < $current_date): // Eğer kursun bitiş tarihi geçmişse
                                    if ($kurs_basarili): ?>
                                        <a href="sertifikayiGor.php?id=<?php echo $course_id; ?>" class="btn-primary">Sertifikayı Gör</a>
                                        <!-- Başarıyla tamamlanmışsa sertifikayı gör -->
                                    <?php else: ?>
                                        <p>Kurs süresi dolmuştur. Bu kursu başarıyla tamamlamadınız.</p>
                                        <!-- Kurs bitmiş ve başarıyla tamamlanmamışsa mesaj göster -->
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p>Kursa katıldınız. Kursunuz devam ediyor.</p>
                                    <!-- Kurs devam ediyorsa bilgi göster -->
                                <?php endif; ?>
                            <?php elseif (!$kurs_alinmis): // Eğer kurs alınmamışsa
                                if ($course_end_date < $current_date): ?>
                                    <p>Kurs süresi dolmuştur. Benzer kurslara bakabilirsiniz.</p>
                                    <!-- Kurs süresi dolmuşsa benzer kursları öner -->
                                <?php else: ?>
                                    <a href="kursaKatil.php?id=<?php echo $course_id; ?>" class="btn-primary">Bu Kursa Katıl</a>
                                    <!-- Kursa katılma butonu -->
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: // Eğer kullanıcı giriş yapmamışsa
                        if ($course_end_date < $current_date): ?>
                            <p>Kurs süresi dolmuştur. Benzer kurslara bakabilirsiniz.</p>
                            <!-- Kurs süresi dolmuşsa benzer kursları öner -->
                        <?php else: ?>
                            <p>Bu kursa katılmak için <a href="giris.php">giriş yapın</a> ya da <a href="kayıt.php">kayıt
                                    olun</a>.</p>
                            <!-- Kullanıcı giriş yapmamışsa giriş yapma veya kayıt olma linkleri -->
                        <?php endif; ?>
                    <?php endif; ?>
            </div>
        </section>

        </div>

        </section>

        <h3 id="h3">Kurs İçeriği</h3>
<section class="course-content">
    <?php
    $course_content = htmlspecialchars($course['aciklama']); // Kurs açıklamasını al
    $content_items = explode('-', $course_content); // Açıklamayı - karakterine göre ayır
    ?>
    <ul>
        <?php foreach ($content_items as $item): ?>
            <li><?php echo trim($item); ?></li> <!-- İçerik öğelerini listele -->
        <?php endforeach; ?>
    </ul>
</section>

<div class="comment-div">
    <?php
    if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student'): // Eğer kullanıcı öğrenci olarak giriş yapmışsa
        $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');
        $user_id = $_SESSION['user_id'];

        // Öğrencinin bu kursu alıp almadığını kontrol et
        $query = "SELECT COUNT(*) AS count FROM eğitimler WHERE ogr_id = ? AND kurs_id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('ii', $user_id, $course_id); // Öğrenci ID'si ve kurs ID'sini bağla
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $kurs_alinmis = $data['count'] > 0; // Kurs alınmış mı?

        if ($kurs_alinmis): // Eğer kurs alınmışsa
            ?>
            <form action="yorumEkle.php" method="post" style="margin-top: 20px;">
                <input type="hidden" name="kurs_id" value="<?= $course_id ?>"> <!-- Kurs ID'sini gizli input olarak gönder -->
                <input type="hidden" name="ogr_id" value="<?= $_SESSION['user_id'] ?>"> <!-- Öğrenci ID'sini gizli input olarak gönder -->
                <label for="yorum" style="display: block; margin-top: 10px;">Yorumunuz:</label>
                <textarea name="yorum" id="yorum" rows="4" required placeholder="Yorumunuzu buraya yazın..." style="width: 100%;"></textarea>
                <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Gönder</button> <!-- Yorum gönderme butonu -->
            </form>
            <?php
        else:
            echo "<p>Yorum yapabilmek için bu kursu almış olmanız gerekmektedir.</p>"; // Kurs alınmamışsa uyarı mesajı
        endif;
    endif;
    ?>

    <!-- Yorumların listelendiği bölüm -->
    <div id="yorum-listesi" style="margin-top: 20px;">
        <h3>Yorumlar</h3>
        <?php
        // Veritabanından yorumları çek
        $db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma');

        if ($db->connect_error) {
            die("Bağlantı hatası: " . $db->connect_error); // Veritabanı bağlantısı hatası kontrolü
        }

        $yorumQuery = "
            SELECT y.yorum, o.ad 
            FROM yorumlar y 
            JOIN ogrenciler o ON y.ogr_id = o.id 
            WHERE y.kurs_id = ? 
            ORDER BY y.id DESC
        "; // Yorumları kursa göre sıralama
        $yorumStmt = $db->prepare($yorumQuery);
        $yorumStmt->bind_param('i', $course_id); // Kurs ID'sini parametre olarak bağla
        $yorumStmt->execute();
        $yorumResult = $yorumStmt->get_result();

        if ($yorumResult->num_rows > 0) { // Eğer yorum varsa
            while ($yorum = $yorumResult->fetch_assoc()) {
                echo "<div class='yorum-item' style='margin-bottom: 15px;'>";
                echo "<p><strong>{$yorum['ad']}:</strong></p>"; // Yorum yapan öğrencinin adı
                echo "<p>{$yorum['yorum']}</p>"; // Yorumun içeriği
                echo "<hr>"; // Yorumlar arasında ayırıcı çizgi
                echo "</div>";
            }
        } else {
            echo "<p>Henüz bu kurs için yorum yapılmamış.</p>"; // Yorum yapılmamışsa mesaj göster
        }

        $yorumStmt->close();
        $db->close();
        ?>
    </div>
</div>
    </main>

    <footer>
        <img src="logo.jpg" alt="Logo" class="logo">
        <p>&copy; Academia. Tüm hakları saklıdır.</p>
    </footer>
</body>

</html>