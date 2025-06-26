<?php
// kursEkle.php
session_start(); // Oturum başlatılır

// Eğitmen ID'sini alın
if (isset($_GET['egitmen_id'])) { // URL üzerinden 'egitmen_id' parametresi alınıyor
    $_SESSION['egitmen_id'] = $_GET['egitmen_id']; // Oturumda saklayın
}

if (!isset($_SESSION['egitmen_id'])) { // Eğitmen ID'si oturumda bulunmazsa
    die("Eğitmen ID'si alınamadı. Lütfen tekrar giriş yapın."); // Hata mesajı verilir ve işlem sonlandırılır
}

$egitmenId = $_SESSION['egitmen_id']; // Oturumdan eğitmen ID'si alınır

// Veritabanı bağlantısı
$db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma'); // Veritabanı bağlantısı kurulur

if ($db->connect_error) { // Bağlantı hatası varsa
    die("Bağlantı hatası: " . $db->connect_error); // Hata mesajı verilir ve işlem sonlandırılır
}

// Kategorileri getir
$query = "SELECT * FROM kategoriler"; // Kategorileri getiren SQL sorgusu
$result = $db->query($query); // Sorgu çalıştırılır
$kategoriler = []; // Kategorileri tutacak dizi oluşturuluyor
while ($row = $result->fetch_assoc()) { // Sorgu sonucunda her bir kategori için döngü
    $kategoriler[] = $row; // Kategori verisi diziye eklenir
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kurs Ekle</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet"> <!-- Google Fonts bağlantısı -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"> <!-- Font Awesome ikonları -->
    <link rel="stylesheet" href="style.css"> <!-- Sayfa stil dosyası -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column; /* Sayfa içeriğini dikeyde ortalar */
        }

        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #333333;
            margin-bottom: 20px;
            text-align: center; /* Başlık ortalanır */
        }
        
    </style>
</head>

<body>

    <h1>Kurs Ekle</h1>
    <form id="kursEkleForm" action="" method="POST"> <!-- Form başlatılır -->
        <label for="ad">Kurs Adı:</label> <!-- Kurs adı için etiket -->
        <input type="text" name="ad" id="ad" required><br> <!-- Kurs adı girişi -->
        
        <label for="bas_t">Başlangıç Tarihi:</label> <!-- Başlangıç tarihi için etiket -->
        <input type="date" name="bas_t" id="bas_t" required><br> <!-- Başlangıç tarihi girişi -->
        
        <label for="bit_t">Bitiş Tarihi:</label> <!-- Bitiş tarihi için etiket -->
        <input type="date" name="bit_t" id="bit_t" required><br> <!-- Bitiş tarihi girişi -->
        
        <label for="kategori">Kategori:</label> <!-- Kategori seçimi için etiket -->
        <select name="kategori" id="kategori" required> <!-- Kategori seçimi dropdown -->
            <option value="">Kategori Seçin</option> <!-- Varsayılan seçenek -->
            <?php foreach ($kategoriler as $kategori): ?> <!-- Kategoriler döngüsü -->
                <option value="<?php echo htmlspecialchars($kategori['id']); ?>"> <!-- Kategori ID'sini seçeneğe ekler -->
                    <?php echo htmlspecialchars($kategori['ad']); ?> <!-- Kategori adını yazdırır -->
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="aciklama">Açıklama:</label> <!-- Açıklama için etiket -->
        <textarea name="aciklama" id="aciklama" required></textarea><br> <!-- Açıklama metni girişi -->
        
        <label for="resim_yolu">Resim Yolu:</label> <!-- Resim yolu için etiket -->
        <input type="text" name="resim_yolu" id="resim_yolu"><br> <!-- Resim yolu girişi -->
        
        <label for="fiyat">Fiyat:</label> <!-- Fiyat için etiket -->
        <input type="number" name="fiyat" id="fiyat" step="0.01" required><br> <!-- Fiyat girişi -->
        
        <label for="iletisim">İletişim:</label> <!-- İletişim için etiket -->
        <input type="text" name="iletişim" id="iletişim" required><br> <!-- İletişim girişi -->
        
        <label for="adres">Adres:</label> <!-- Adres için etiket -->
        <input type="text" name="adres" id="adres" required><br> <!-- Adres girişi -->

        <button type="submit" name="submit">Kaydet</button> <!-- Formu gönder butonu -->
        <a href="profil.php">Profil Sayfasına Dön</a> <!-- Profil sayfasına dönüş linki -->
    </form>

</body>

</html>

<?php
// Form gönderildiğinde işlemleri yap
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) { // Form gönderildiğinde işlem yapılır
    $ad = $_POST['ad']; // Kurs adı alınır
    $bas_t = $_POST['bas_t']; // Başlangıç tarihi alınır
    $bit_t = $_POST['bit_t']; // Bitiş tarihi alınır
    $kategori = $_POST['kategori']; // Kategori seçimi alınır
    $aciklama = str_replace("\n", " - ", $_POST['aciklama']); // Açıklamayı - ile ayırarak düzenler
    $resim_yolu = $_POST['resim_yolu']; // Resim yolu alınır
    $fiyat = $_POST['fiyat']; // Fiyat alınır
    $iletişim = $_POST['iletişim']; // İletişim bilgisi alınır
    $adres = $_POST['adres']; // Adres bilgisi alınır

    // Eğitmen ID'si oturumdan alınır
    $egitmenId = $_SESSION['egitmen_id']; // Oturumda saklanan eğitmen ID'si alınır

    // SQL sorgusu
    $stmt = $db->prepare("INSERT INTO kurslar (ad, bas_t, bit_t, kategori_id, aciklama, resim_yolu, fiyat, iletişim, adres, egitmen_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); // SQL sorgusu hazırlanır
    $stmt->bind_param("ssssssdssi", $ad, $bas_t, $bit_t, $kategori, $aciklama, $resim_yolu, $fiyat, $iletişim, $adres, $egitmenId); // Parametreler bağlanır

    if ($stmt->execute()) { // Sorgu başarıyla çalıştırılırsa
        echo "<p>Kurs başarıyla eklendi.</p>"; // Başarı mesajı gösterilir
    } else { // Hata durumunda
        echo "<p>Kurs eklenirken hata oluştu: " . $stmt->error . "</p>"; // Hata mesajı gösterilir
    }
}
?>
