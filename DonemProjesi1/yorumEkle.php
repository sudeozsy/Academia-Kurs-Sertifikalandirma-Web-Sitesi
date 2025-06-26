<?php
session_start(); // Kullanıcının oturumunun başlatıldığını belirtir.

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    // Kullanıcının oturum açıp açmadığını ve 'student' rolüne sahip olup olmadığını kontrol eder.
    header("Location: giris.php"); // Eğer kontrol başarısızsa giriş sayfasına yönlendirilir.
    exit; // Yönlendirmeden sonra işlemi sonlandırır.
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    // Formun POST yöntemiyle gönderilip gönderilmediğini kontrol eder.
    $ogr_id = $_POST['ogr_id'] ?? null; // POST ile gönderilen 'ogr_id' değerini alır veya null atar.
    $kurs_id = $_POST['kurs_id'] ?? null; // POST ile gönderilen 'kurs_id' değerini alır veya null atar.
    $yorum = $_POST['yorum'] ?? ''; // POST ile gönderilen 'yorum' değerini alır veya boş string atar.

    if (empty($ogr_id) || empty($kurs_id) || empty($yorum)) { 
        // Gönderilen verilerden herhangi biri boşsa hatayı bildirir.
        die("Hata: Gönderilen veriler eksik veya hatalı."); // Hatalı veri gönderildiğini belirtir ve işlem durdurulur.
    }

    try {
        $dbConnection = new PDO('mysql:host=localhost;dbname=kurs_sertifikalandirma', 'root', '12345678'); 
        // Veritabanına bağlanmak için bir PDO nesnesi oluşturur.
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        // PDO'nun hata ayıklama modunu etkinleştirir (hataları istisna olarak fırlatır).

        $sql = "INSERT INTO yorumlar (ogr_id, kurs_id, yorum) VALUES (:ogr_id, :kurs_id, :yorum)"; 
        // Yorumları veritabanına eklemek için SQL sorgusunu hazırlar.
        $query = $dbConnection->prepare($sql); // PDO'dan sorgu hazırlığı yapar.
        $query->execute([
            ':ogr_id' => $ogr_id, // Öğrenci ID'sini bağlar.
            ':kurs_id' => $kurs_id, // Kurs ID'sini bağlar.
            ':yorum' => htmlspecialchars($yorum, ENT_QUOTES, 'UTF-8'), 
            // Yorum metnini HTML özel karakterlerinden arındırır (XSS saldırılarına karşı güvenlik sağlar).
        ]);

        echo "<script>alert('Yorumunuz başarıyla kaydedildi.');</script>"; 
        // Yorumun başarıyla kaydedildiğini kullanıcıya bildiren bir JavaScript uyarısı gösterir.
        header("Refresh: 0; url=kursDetay.php?id=" . $kurs_id); 
        // Kurs detay sayfasına yönlendirme yapar, URL'ye kurs ID'sini ekler.
        exit; // Yönlendirmeden sonra işlem durdurulur.
    } catch (PDOException $e) { 
        // PDO işlemleri sırasında bir hata meydana gelirse bu blok çalışır.
        die("Hata: " . $e->getMessage()); // Hatanın ayrıntısını kullanıcıya gösterir (geliştirme sırasında önerilir, üretimde gizlenmelidir).
    }
}
?>
