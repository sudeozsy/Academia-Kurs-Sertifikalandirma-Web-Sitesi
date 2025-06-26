<?php
session_start(); // Oturum başlatma

// Veritabanı bağlantısını ekleyin
$host = "localhost"; // Veritabanı sunucu adresi
$dbname = "kurs_sertifikalandirma"; // Veritabanı adı
$username = "root"; // Veritabanı kullanıcı adı
$password = "12345678"; // Veritabanı şifresi

try {
    // PDO ile veritabanı bağlantısı oluşturuluyor
    $dbConnection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Hata yönetimi ayarları
} catch (PDOException $e) {
    // Bağlantı hatası durumunda hata mesajı gösteriliyor
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

$user_id = $_SESSION['user_id']; // Oturumdaki kullanıcı ID'sini al
$role = $_SESSION['role']; // Oturumdaki kullanıcı rolünü al

// Kullanıcı bilgilerini al
$query = $dbConnection->prepare("SELECT * FROM " . ($role === 'student' ? 'ogrenciler' : 'egitmenler') . " WHERE id = :id");
$query->execute([':id' => $user_id]); // Kullanıcı bilgilerini veritabanından al
$user = $query->fetch(PDO::FETCH_ASSOC); // Kullanıcı bilgilerini al ve $user değişkenine ata
?>


<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Sayfası</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="app.js"></script>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Profil Bilgileri ve Güncelleme Formu için temel stil */
        #profile-info {
            border-radius: 8px;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 60%;
            /* Ana divin genişliği küçültüldü */
            margin: 0 auto;
        }

        .profile-details {
            margin-bottom: 20px;
        }

        .hidden-form {
            visibility: hidden;
            /* Başlangıçta gizli */
            opacity: 0;
            /* Görünür değil */
            transition: opacity 0.3s ease, visibility 0s 0.3s;
            /* Geçiş efekti */
        }

        #edit-form.active {
            visibility: visible;
            /* Görünür yap */
            opacity: 1;
            /* Görünür yap */
            transition: opacity 0.3s ease, visibility 0s 0s;
            /* Geçişi hemen yap */
        }

        #edit-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            margin: auto;
            width: 50%;
            /* Tabloya uygun şekilde genişlik azaltıldı */
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 26px;
            margin-bottom: 20px;
        }

        p {
            color: #555;
        }

        .profile-details {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-details p {
            margin: 10px 0;
            font-size: 16px;
        }

        /* Tablo Stilleri */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: auto;
        }

        table td {
            padding: 12px;
            vertical-align: middle;
            font-size: 16px;
            color: #333;
        }

        table input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            /* Input font boyutunu küçülttük */
            margin-top: 10px;
        }

        /* Ad ve Soyad Inputları İçin */
        table td input {
            width: 75%;
            /* Ad ve Soyad inputları daha küçük */
            display: inline-block;
        }

        table input:focus {
            border-color: #28a745;
            outline: none;
        }

        /* Buton Stilleri */
        .btn-primary {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        /* Gizli Form ve Bölümler */
        .hidden-form {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
        }

        .hidden-form table {
            margin: 0 auto;
        }

        .hidden-form button {
            margin-top: 10px;
            width: 100%;
        }

        /* Profil Bilgileri */
        .profile-details h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .profile-details p {
            font-size: 16px;
            margin: 10px 0;
            color: #555;
        }

        .profile-details strong {
            color: #333;
        }

        .profile-details span {
            font-weight: 500;
            color: #333;
        }

        /* Profil Güncelleme Formu */
        .hidden-form h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .hidden-form p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        /* Form Alanları */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table td {
            padding: 10px;
            font-size: 16px;
            color: #333;
        }

        table input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }

        table input:focus {
            border-color: #28a745;
            outline: none;
        }

        /* E-posta ve Şifre Güncelleme */
        #email-update-row,
        #password-update-row {
            display: none;
        }

        #email-update-row input,
        #password-update-row input {
            width: 48%;
            margin-right: 4%;
        }

        #email-update-row input:last-child,
        #password-update-row input:last-child {
            margin-right: 0;
        }

        #email-update-row button,
        #password-update-row button {
            padding: 12px 24px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        #email-update-row button:hover,
        #password-update-row button:hover {
            background-color: #218838;
        }

        /* Responsive Tasarım */
        @media (max-width: 768px) {

            .content-section,
            .hidden-form {
                width: 90%;
            }

            table {
                width: 90%;
            }

            button {
                font-size: 12px;
            }
        }
    </style>

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
                <li><a href="anasayfa.php">Ana Sayfa</a></li>
                <li><a href="kurslar.php">Kurslar</a></li>
                <li><a href="profil.php">Profil</a></li>
                <li><a href="cikis.php">Çıkış Yap</a></li>
            </ul>
        </nav>

    </header>

    <!-- Content Area -->
    <div class="content">
        <div id="con-div">
            <div class="profile-info">
                <h2>Hesap İçeriği</h2>
            </div>

            <div class="tabs">
                <?php if ($role === 'student'): ?>
                    <div class="tab" onclick="showSection('all-courses')">Alınan Tüm Kurslar</div>
                    <div class="tab" onclick="showSection('completed-courses')">Başarıyla Tamamlanan Kurslar</div>
                    <div class="tab" onclick="showSection('failed-courses')">Başarısız Olunan Kurslar</div>
                    <div class="tab" onclick="showSection('ongoing-courses')">Devam Eden Kurslar</div>
                    <div class="tab" class="tab" onclick="showSection('wish-list')">İstek Listesi</div>
                    <div class="tab" class="tab" onclick="showSection('profile-info')">Profil Bilgileri</div>
                <?php elseif ($role === 'instructor'): ?>
                    <div class="tab active" onclick="showSection('given-courses')">Verilen Kurslar</div>
                    <div class="tab" onclick="showSection('completed-courses-ins')">Tamamlanan Kurslar</div>
                    <div class="tab" onclick="showSection('ongoing-courses-ins')">Devam Eden Kurslar</div>
                    <div class="tab" class="tab" onclick="showSection('profile-info')">Profil Bilgileri</div>
                <?php endif; ?>
            </div>
        </div>

        <div id="profile-courses">
            <div id="all-courses" class="content-section active">
                <div class="course-grid">
                    <?php
                    $userId = $_SESSION['user_id']; // Kullanıcı ID'si oturumdan alınır
                    
                    // Veritabanından alınan kursları çek
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                                    FROM kurslar
                                    INNER JOIN eğitimler ON kurslar.id = eğitimler.kurs_id
                                    WHERE eğitimler.ogr_id = :ogr_id";
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':ogr_id' => $userId]);

                    $allCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($allCourses)):
                        foreach ($allCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>Alınan kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div id="completed-courses" class="content-section hidden">
                <div class="course-grid">
                    <?php
                    // Başarıyla tamamlanan kurslar
                    $completedCourses = [];
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                                FROM kurslar
                                INNER JOIN eğitimler ON kurslar.id = eğitimler.kurs_id
                                WHERE eğitimler.ogr_id = :ogr_id AND eğitimler.basari_drm = 1";
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':ogr_id' => $userId]);

                    $completedCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($completedCourses)):
                        foreach ($completedCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>Başarıyla tamamlanan kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div id="failed-courses" class="content-section hidden">
                <div class="course-grid">
                    <?php
                    // Başarısız olunan kurslar
                    $failedCourses = [];
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                                FROM kurslar
                                INNER JOIN eğitimler ON kurslar.id = eğitimler.kurs_id
                                WHERE eğitimler.ogr_id = :ogr_id 
                                AND eğitimler.basari_drm = 0
                                AND kurslar.bit_t < CURDATE()"; // Kursun bitiş tarihi geçmiş olmalı
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':ogr_id' => $userId]);

                    $failedCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($failedCourses)):
                        foreach ($failedCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>Başarısız olunan kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div id="ongoing-courses" class="content-section hidden">
                <div class="course-grid">
                    <?php
                    // Başarısız olunan kurslar
                    $ongoingCourses = [];
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                                FROM kurslar
                                INNER JOIN eğitimler ON kurslar.id = eğitimler.kurs_id
                                WHERE eğitimler.ogr_id = :ogr_id 
                                AND kurslar.bit_t > CURDATE()"; // Kursun bitiş tarihi geçmiş olmalı
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':ogr_id' => $userId]);

                    $ongoingCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($ongoingCourses)):
                        foreach ($ongoingCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>Başarısız olunan kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div id="given-courses" class="content-section hidden">
                
                <!-- Kurs Ekle Butonu -->
                <div class="add-course-btn" style="text-align: center; margin-bottom:25px;">
                    <a href="kursEkle.php?egitmen_id=<?= urlencode($userId) ?>" class="btn btn-primary">Kurs Ekle</a>
                </div>
                <div class="course-grid">
                    <?php

                    $ongoingCourses = [];
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                    FROM kurslar
                    WHERE kurslar.egitmen_id = :egitmen_id";
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':egitmen_id' => $userId]);

                    $ongoingCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($ongoingCourses)):
                        foreach ($ongoingCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>Verilen kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>

            </div>


            <div id="completed-courses-ins" class="content-section hidden">
                <div class="course-grid">
                    <?php
                    $completedInsCourses = [];
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                                FROM kurslar
                                WHERE kurslar.egitmen_id = :egitmen_id
                                AND kurslar.bit_t < CURDATE()";
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':egitmen_id' => $userId]);

                    $completedInsCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($completedInsCourses)):
                        foreach ($completedInsCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>Tamamlanan kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>

            </div>
            <div id="ongoing-courses-ins" class="content-section hidden">
                <div class="course-grid">
                    <?php
                    $ongoingInsCourses = [];
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                                FROM kurslar
                                WHERE kurslar.egitmen_id = :egitmen_id
                                AND kurslar.bit_t > CURDATE()";
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':egitmen_id' => $userId]);

                    $ongoingInsCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($ongoingInsCourses)):
                        foreach ($ongoingInsCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>Devam eden kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div id="wish-list" class="content-section hidden">
                <div class="course-grid">
                    <?php
                    // Kullanıcının istek listesini çekme
                    $userId = $_SESSION['user_id'];
                    $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                    FROM kurslar
                    JOIN istekler ON kurslar.id = istekler.kurs_id
                    WHERE istekler.ogr_id = :ogr_id";
                    $query = $dbConnection->prepare($sql);
                    $query->execute([':ogr_id' => $userId]);

                    $wishListCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                    // Kursları listele
                    if (!empty($wishListCourses)):
                        foreach ($wishListCourses as $kurs): ?>
                            <div class="course-item">
                                <a href="kursDetay.php?id=<?= urlencode($kurs['id']) ?>" style="text-decoration: none;">
                                    <img src="<?= htmlspecialchars($kurs['resim_yolu']) ?>"
                                        alt="<?= htmlspecialchars($kurs['ad']) ?>">
                                    <h3><?= htmlspecialchars($kurs['ad']) ?></h3>
                                    <p><strong>Başlangıç:</strong> <?= htmlspecialchars($kurs['bas_t']) ?></p>
                                    <p><strong>Bitiş:</strong> <?= htmlspecialchars($kurs['bit_t']) ?></p>
                                </a>
                            </div>
                        <?php endforeach;
                    else: ?>
                        <p>İstek listenizde kurs bulunamadı.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div id="profile-info" class="content-section hidden">
                <div class="profile-details">
                    <h2>Profil Bilgileri</h2>

                    <!-- Profil Bilgileri Gösterme (Normal Görünüm) -->
                    <p><strong>Ad Soyad:</strong> <span
                            id="full-name"><?php echo htmlspecialchars($user['ad'] . ' ' . $user['soyad']); ?></span>
                    </p>
                    <p><strong>E-posta:</strong> <span
                            id="email"><?php echo htmlspecialchars($user['e_posta']); ?></span></p>

                    <!-- Güncelleme Butonu -->
                    <button class="btn btn-primary" id="edit-btn">Bilgileri Güncelle</button>
                </div>
            </div>

            <!-- Profil Güncelleme Formu (Gizli Başlangıçta) -->
            <div id="edit-form" class="hidden-form hidden">
                <?php
                $message = $_GET['message'] ?? null;
                $error_message = $_GET['error_message'] ?? null;
                ?>
                <?php if ($message): ?>
                    <div style="color: green;"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div style="color: red;"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <h2>Profil</h2>
                <p>Profil bilgilerinizi düzenleme ve şifre değiştirme işlemlerinizi buradan yapabilirsiniz.</p>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td><label for="first-name">Ad:</label></td>
                        <td><input type="text" id="first-name" name="first_name"
                                value="<?php echo htmlspecialchars($user['ad']); ?>" required></td>
                    </tr>
                    <tr>
                        <td><label for="last-name">Soyad:</label></td>
                        <td><input type="text" id="last-name" name="last_name"
                                value="<?php echo htmlspecialchars($user['soyad']); ?>" required></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;">
                            <button type="button" onclick="updateName()">Ad ve Soyad Güncelle</button>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="email">E-posta:</label></td>
                        <td>
                            <p id="current-email">E-posta adresiniz: <?php echo htmlspecialchars($user['e_posta']); ?>
                            </p>
                            <button type="button" onclick="showEmailUpdate()">E-postayı güncelle</button>
                        </td>
                    </tr>
                    <tr id="email-update-row" style="display: none;">
                        <td colspan="2">
                            <input type="email" id="new-email" placeholder="Yeni e-posta adresinizi girin">
                            <input type="password" id="email-password" placeholder="Mevcut şifrenizi girin">
                            <button type="button" onclick="updateEmail()">E-postayı Güncelle</button>
                        </td>
                    </tr>
                    <tr>
                        <td><label>Şifre:</label></td>
                        <td>
                            <button type="button" onclick="showPasswordUpdate()">Şifreyi değiştir</button>
                        </td>
                    </tr>
                    <tr id="password-update-row" style="display: none;">
                        <td colspan="2">
                            <input type="password" id="old-password" placeholder="Mevcut şifreyi girin">
                            <input type="password" id="new-password" placeholder="Yeni şifreyi girin">
                            <input type="password" id="confirm-password" placeholder="Yeni şifreyi tekrar yazın">
                            <button type="button" onclick="updatePassword()">Şifreyi Güncelle</button>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="error-message" class="hidden-form" style="color: red;"></div>

        </div>
    </div>
    </div>
    </div>

    <script>
        // Eğer kullanıcı eğitmense, all-courses div'inin class'ını değiştir
        <?php if ($role === 'instructor'): ?>
            document.getElementById('all-courses').classList.remove('active');
            document.getElementById('all-courses').classList.add('hidden');

            document.getElementById('given-courses').classList.remove('hidden');
            document.getElementById('given-courses').classList.add('active');
        <?php endif; ?>

        function showSection(sectionId) {
            const tabs = document.querySelectorAll('.tab');
            const sections0 = document.querySelectorAll('.content-section');

            tabs.forEach(tab => tab.classList.remove('active'));
            sections0.forEach(section => section.classList.remove('active'));

            document.querySelector(`.tab[onclick="showSection('${sectionId}')"]`).classList.add('active');
            document.getElementById(sectionId).classList.add('active');

            document.getElementById('edit-form').classList.remove('active');
            document.getElementById('edit-form').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function () {

            document.getElementById('edit-btn').addEventListener('click', function () {
                document.getElementById('profile-info').classList.remove('active');
                document.getElementById('profile-info').classList.add('hidden');

                document.getElementById('edit-form').classList.remove('hidden');
                document.getElementById('edit-form').classList.add('active');
            });
            // Form submit işlemi
            document.querySelector('form').addEventListener('submit', function (event) {
                event.preventDefault();  // Formun otomatik olarak gönderilmesini engelle

                const oldPassword = document.getElementById('old-password').value;
                const newPassword = document.getElementById('new-password').value;
                const confirmNewPassword = document.getElementById('confirm-new-password').value;

                // Şifre doğrulama (yerel kontrol)
                if (newPassword !== confirmNewPassword) {
                    document.getElementById('error-message').innerText = 'Yeni şifreler uyuşmuyor.';
                    document.getElementById('error-message').style.display = 'block';
                    return;
                }

                // Burada formu normal şekilde gönderebilirsiniz
                this.submit(); // Formu normal şekilde gönder
            });

        });

    </script>
</body>

</html>