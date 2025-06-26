<?php
session_start();

// Veritabanı bağlantısını ekleyin
$host = "localhost";
$dbname = "kurs_sertifikalandirma";
$username = "root";
$password = "12345678";

try {
    $dbConnection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Kullanıcı bilgilerini alın
$query = $dbConnection->prepare("SELECT * FROM " . ($role === 'student' ? 'ogrenciler' : 'egitmenler') . " WHERE id = :id");
$query->execute([':id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Sayfası</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
            max-width: 900px;
            margin: auto;
        }

        /* Profil Bilgileri */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            background-color: #f4f4f4;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }

        .profile-header h2 {
            margin: 0;
        }

        /* Başlık */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Sekmeler */
        .tabs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 2px solid #ccc;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            text-align: center;
            flex-grow: 1;
            color: #333;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab:hover,
        .tab.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
        }

        /* İçerik Bölümleri */
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .course-item {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .course-item:hover {
            transform: translateY(-5px);
        }

        .course-item img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .course-item h3 {
            margin: 0 0 10px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Profil Bilgileri -->
        <div class="profile-header">
            <img src="profile.jpg" alt="Profil Resmi">
            <div>
                <h2><?php echo htmlspecialchars($user['ad'] . ' ' . $user['soyad']); ?></h2>
                <p>E-posta: <?php echo htmlspecialchars($user['e_posta']); ?></p>
            </div>
        </div>

        <!-- Başlık -->
        <div class="header">
            <h1>Öğrenim İçeriğim</h1>
        </div>

        <!-- Sekmeler -->
        <div class="tabs">
            <div class="tab active" onclick="showSection('all-courses')">Tüm Kurslar</div>
            <div class="tab" onclick="showSection('completed-courses')">Bitirilen Kurslar</div>
            <div class="tab" onclick="showSection('wish-list')">İstek Listesi</div>
        </div>

        <!-- İçerik Bölümleri -->
        <div id="all-courses" class="content-section active">
            <h3>Tüm Kurslar</h3>
            <div class="course-grid">
                <?php
                $sql = "SELECT kurslar.id, kurslar.ad, kurslar.bas_t, kurslar.bit_t, kurslar.resim_yolu 
                        FROM kurslar
                        INNER JOIN eğitimler ON kurslar.id = eğitimler.kurs_id
                        WHERE eğitimler.ogr_id = :ogr_id";
                $query = $dbConnection->prepare($sql);
                $query->execute([':ogr_id' => $user_id]);
                $allCourses = $query->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($allCourses)):
                    foreach ($allCourses as $course): ?>
                        <div class="course-item">
                            <img src="<?= htmlspecialchars($course['resim_yolu']) ?>" alt="<?= htmlspecialchars($course['ad']) ?>">
                            <h3><?= htmlspecialchars($course['ad']) ?></h3>
                            <p><strong>Başlangıç:</strong> <?= htmlspecialchars($course['bas_t']) ?></p>
                            <p><strong>Bitiş:</strong> <?= htmlspecialchars($course['bit_t']) ?></p>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p>Hiç kurs bulunamadı.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="completed-courses" class="content-section">
            <h3>Bitirilen Kurslar</h3>
            <div class="course-grid">
                <!-- Bitirilen kurslar buraya gelecek -->
            </div>
        </div>

        <div id="wish-list" class="content-section">
            <h3>İstek Listesi</h3>
            <div class="course-grid">
                <!-- İstek listesi buraya gelecek -->
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            const tabs = document.querySelectorAll('.tab');
            const sections = document.querySelectorAll('.content-section');

            tabs.forEach(tab => tab.classList.remove('active'));
            sections.forEach(section => section.classList.remove('active'));

            document.querySelector(`.tab[onclick="showSection('${sectionId}')"]`).classList.add('active');
            document.getElementById(sectionId).classList.add('active');
        }
    </script>
</body>

</html>
