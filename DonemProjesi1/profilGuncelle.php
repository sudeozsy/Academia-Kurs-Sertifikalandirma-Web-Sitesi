<?php
session_start(); // Oturumu başlat

// Veritabanı bağlantısı
$host = 'localhost';
$dbname = 'kurs_sertifikalandirma';
$username = 'root';
$password = '12345678';

$conn = new mysqli($host, $username, $password, $dbname); // MySQL veritabanına bağlantı

// Kullanıcı bilgilerini oturumdan al
$user_id = $_SESSION['user_id']; // Kullanıcının ID'si
$role = $_SESSION['role']; // Kullanıcının rolü: student veya instructor
$table = ($role === 'instructor') ? 'egitmenler' : 'ogrenciler'; // Rolüne göre tablo belirleme

// Mesaj değişkeni
$message = ''; // Başarı mesajı
$error_message = ''; // Hata mesajı

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Form verisi post edildiyse
    if (isset($_POST['action'])) { // Eğer 'action' parametresi varsa
        // Ad ve soyad güncelleme
        if ($_POST['action'] === 'update_name') { // Ad ve soyad güncelleme işlemi
            $new_first_name = $_POST['first_name']; // Yeni ad
            $new_last_name = $_POST['last_name']; // Yeni soyad

            $update_query = "UPDATE $table SET ad = ?, soyad = ? WHERE id = ?"; // Ad ve soyad güncelleme sorgusu
            $stmt = $conn->prepare($update_query); // Sorgu hazırlanıyor
            $stmt->bind_param("ssi", $new_first_name, $new_last_name, $user_id); // Parametreler bağlanıyor

            if ($stmt->execute()) { // Sorgu başarılıysa
                echo "Ad ve soyad başarıyla güncellendi."; // Başarı mesajı
                exit; // İşlem sonlandırılıyor
            } else { // Sorgu başarısızsa
                echo "Ad ve soyad güncellenirken hata oluştu."; // Hata mesajı
                exit; // İşlem sonlandırılıyor
            }
        }

        // E-posta güncelleme
        if ($_POST['action'] === 'update_email') { // E-posta güncelleme işlemi
            $new_email = $_POST['new_email']; // Yeni e-posta
            $password = $_POST['password']; // Mevcut şifre

            // Kullanıcı bilgilerini al
            $query = "SELECT sifre FROM $table WHERE id = ?"; // Kullanıcının şifresini sorgulama
            $stmt = $conn->prepare($query); // Sorgu hazırlanıyor
            $stmt->bind_param("i", $user_id); // Parametre bağlanıyor
            $stmt->execute(); // Sorgu çalıştırılıyor
            $stmt->bind_result($stored_hash); // Şifreyi al
            $stmt->fetch(); // Sonuçları al
            $stmt->close(); // Sorgu kapatılıyor

            // Şifre doğrulama
            if (password_verify($password, $stored_hash)) { // Şifre doğrulaması
                $update_query = "UPDATE $table SET e_posta = ? WHERE id = ?"; // E-posta güncelleme sorgusu
                $stmt = $conn->prepare($update_query); // Sorgu hazırlanıyor
                $stmt->bind_param("si", $new_email, $user_id); // Parametreler bağlanıyor

                if ($stmt->execute()) { // Sorgu başarılıysa
                    echo "E-posta başarıyla güncellendi."; // Başarı mesajı
                    exit; // İşlem sonlandırılıyor
                } else { // Sorgu başarısızsa
                    echo "E-posta güncellenirken hata oluştu."; // Hata mesajı
                    exit; // İşlem sonlandırılıyor
                }
            } else { // Şifre doğrulama başarısızsa
                echo "Mevcut şifre hatalı!"; // Hata mesajı
                exit; // İşlem sonlandırılıyor
            }
        }

        // Şifre güncelleme
        if ($_POST['action'] === 'update_password') { // Şifre güncelleme işlemi
            $old_password = $_POST['old_password']; // Eski şifre
            $new_password = $_POST['new_password']; // Yeni şifre
            $confirm_password = $_POST['confirm_password']; // Yeni şifrenin teyidi

            // Kullanıcı bilgilerini al
            $query = "SELECT sifre FROM $table WHERE id = ?"; // Kullanıcının şifresini sorgulama
            $stmt = $conn->prepare($query); // Sorgu hazırlanıyor
            $stmt->bind_param("i", $user_id); // Parametre bağlanıyor
            $stmt->execute(); // Sorgu çalıştırılıyor
            $stmt->bind_result($stored_hash); // Şifreyi al
            $stmt->fetch(); // Sonuçları al
            $stmt->close(); // Sorgu kapatılıyor

            // Şifre kontrolü
            if (!password_verify($old_password, $stored_hash)) { // Eski şifre yanlışsa
                echo "Mevcut şifre hatalı!"; // Hata mesajı
                exit; // İşlem sonlandırılıyor
            } elseif ($new_password !== $confirm_password) { // Yeni şifreler uyuşmuyorsa
                echo "Yeni şifreler uyuşmuyor!"; // Hata mesajı
                exit; // İşlem sonlandırılıyor
            } else { // Şifreler uyuyorsa
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT); // Yeni şifreyi hashliyoruz
                $update_query = "UPDATE $table SET sifre = ? WHERE id = ?"; // Şifre güncelleme sorgusu
                $stmt = $conn->prepare($update_query); // Sorgu hazırlanıyor
                $stmt->bind_param("si", $new_password_hash, $user_id); // Parametreler bağlanıyor

                if ($stmt->execute()) { // Sorgu başarılıysa
                    echo "Şifre başarıyla güncellendi."; // Başarı mesajı
                    exit; // İşlem sonlandırılıyor
                } else { // Sorgu başarısızsa
                    echo "Şifre güncellenirken hata oluştu."; // Hata mesajı
                    exit; // İşlem sonlandırılıyor
                }
            }
        }
    }
    // Yönlendirme yapma
    if ($message) { // Eğer başarı mesajı varsa
        header("Location: profil.php?message=" . urlencode($message)); // Başarı mesajı ile yönlendirme
    } elseif ($error_message) { // Eğer hata mesajı varsa
        header("Location: profil.php?error_message=" . urlencode($error_message)); // Hata mesajı ile yönlendirme
    }
    exit; // İşlem sonlandırılıyor
}

$conn->close(); // Veritabanı bağlantısı kapatılıyor
?>
