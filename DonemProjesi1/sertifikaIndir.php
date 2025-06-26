<?php
ini_set('display_errors', 1); // Hata görüntülemeyi etkinleştiriyoruz
ini_set('display_startup_errors', 1); // Başlangıç hatalarını gösteriyoruz
error_reporting(E_ALL); // Tüm hata seviyelerini gösteriyoruz

session_start(); // Oturum başlatıyoruz

// tFPDF Kütüphanesinin doğru şekilde dahil edilmesi
require('tfpdf/tfpdf.php'); // tFPDF kütüphanesini dahil ediyoruz

// Veritabanı bağlantısı
$db = new mysqli('localhost', 'root', '12345678', 'kurs_sertifikalandirma'); // Veritabanına bağlanıyoruz
if ($db->connect_error) { // Bağlantı hatası kontrolü
    die("Bağlantı hatası: " . $db->connect_error); // Hata mesajı
}

$course_id = $_GET['id']; // Kurs ID'si URL'den alınacak
$user_id = $_SESSION['user_id']; // Giriş yapan kullanıcının ID'si

// Sertifikalar tablosundaki ilgili bilgileri almak için sorgu
$query = "SELECT s.id AS sertifika_no, s.ad AS sertifika_ad, c.ad AS kurs_ad, c.bas_t AS baslangic_tarihi, 
                 c.bit_t AS bitis_tarihi, e.ad AS egitmen_ad, e.soyad AS egitmen_soyad
          FROM sertifikalar s
          JOIN kurslar c ON s.kurs_id = c.id
          JOIN egitmenler e ON c.egitmen_id = e.id
          WHERE s.kurs_id = ? AND s.ogr_id = ?"; // Sertifika bilgilerini almak için sorgu

$stmt = $db->prepare($query); // Sorguyu hazırlıyoruz
$stmt->bind_param('ii', $course_id, $user_id); // Parametreleri bağlıyoruz
$stmt->execute(); // Sorguyu çalıştırıyoruz
$result = $stmt->get_result(); // Sonuçları alıyoruz
$certificate = $result->fetch_assoc(); // Veriyi alıyoruz

// Öğrencinin adını almak için ek sorgu
$query_student = "SELECT ad, soyad FROM ogrenciler WHERE id = ?"; // Öğrenci bilgilerini almak için sorgu
$stmt_student = $db->prepare($query_student); // Sorguyu hazırlıyoruz
$stmt_student->bind_param('i', $user_id); // Parametreyi bağlıyoruz
$stmt_student->execute(); // Sorguyu çalıştırıyoruz
$student_result = $stmt_student->get_result(); // Sonuçları alıyoruz
$student = $student_result->fetch_assoc(); // Veriyi alıyoruz

if (!$certificate || !$student) { // Eğer sertifika veya öğrenci bulunmazsa
    echo "<p>Bu kursu başarıyla tamamlamadınız veya geçersiz bir kurs seçtiniz.</p>"; // Hata mesajı
    exit(); // Çıkıyoruz
}

$stmt->close(); // İlk sorguyu kapatıyoruz
$stmt_student->close(); // Öğrenci sorgusunu kapatıyoruz
$db->close(); // Veritabanı bağlantısını kapatıyoruz

// PDF Dosyasını Oluşturma
$pdf = new tFPDF(); // tFPDF sınıfından bir nesne oluşturuyoruz

$pdf->AddPage(); // Yeni bir sayfa ekliyoruz
$pdf->Image('logo.jpg', 10, 10, 30); // Logo görselini ekliyoruz

// Arial fontunu yüklüyoruz
$pdf->AddFont('Arial', '', 'Arial.ttf', true);  // Arial fontunu yüklüyoruz
$pdf->AddFont('ArialBold', '', 'FontsFree-Net-arial-bold.ttf', true); // Arial bold fontunu yüklüyoruz
$pdf->Ln(30); // Satır boşluğu ekliyoruz
$pdf->SetFont('ArialBold', '', 16); // Arial bold fontunu kullanıyoruz
$pdf->SetTextColor(255, 0, 0); // Yazı rengini kırmızı yapıyoruz
$pdf->Cell(0, 20, 'BAŞARI SERTİFİKASI', 0, 1, 'C'); // Sertifika başlığını ekliyoruz
$pdf->Ln(5); // Satır boşluğu ekliyoruz

$pdf->SetTextColor(0, 0, 0); // Yazı rengini siyah yapıyoruz
// Kurs süresi hesaplama (gün cinsinden)
$start_date = new DateTime($certificate['baslangic_tarihi']); // Başlangıç tarihini alıyoruz
$end_date = new DateTime($certificate['bitis_tarihi']); // Bitiş tarihini alıyoruz
$interval = $start_date->diff($end_date); // İki tarih arasındaki farkı hesaplıyoruz

$days = $interval->days; // Gün sayısını alıyoruz

$pdf->SetFont('Arial', '', 16); // Arial fontunu kullanıyoruz
$pdf->MultiCell(0, 10, '    ' . htmlspecialchars($student['ad']) .' '. htmlspecialchars($student['soyad']) . ", " . $days . " günlük kurs programını başarıyla tamamlayarak bu sertifikayı almaya hak kazanmıştır."); // Öğrenci bilgilerini ekliyoruz
$pdf->Ln(10); // Satır boşluğu ekliyoruz

$pdf->SetFont('ArialBold', '', 16); // Arial bold fontunu kullanıyoruz
$pdf->Cell(50, 10, 'Kurs Adı: ', 0, 0); // Kurs adı kısmını yazıyoruz
$pdf->SetFont('Arial', '', 16); // Arial fontunu kullanıyoruz
$pdf->Cell(140, 10, htmlspecialchars($certificate['kurs_ad']), 0, 1); // Kurs adını ekliyoruz

$pdf->SetFont('ArialBold', '', 16); // Arial bold fontunu kullanıyoruz
$pdf->Cell(50, 10, 'Eğitmen: ', 0, 0); // Eğitmen kısmını yazıyoruz
$pdf->SetFont('Arial', '', 16); // Arial fontunu kullanıyoruz
$pdf->Cell(140, 10, htmlspecialchars($certificate['egitmen_ad']) . ' ' . htmlspecialchars($certificate['egitmen_soyad']), 0, 1); // Eğitmen adı ve soyadını ekliyoruz

$pdf->SetFont('ArialBold', '', 16); // Arial bold fontunu kullanıyoruz
$pdf->Cell(50, 10, 'Başlangıç Tarihi: ', 0, 0); // Başlangıç tarihi kısmını yazıyoruz
$pdf->SetFont('Arial', '', 16); // Arial fontunu kullanıyoruz
$pdf->Cell(140, 10, htmlspecialchars($certificate['baslangic_tarihi']), 0, 1); // Başlangıç tarihini ekliyoruz

$pdf->SetFont('ArialBold', '', 16); // Arial bold fontunu kullanıyoruz
$pdf->Cell(50, 10, 'Bitiş Tarihi: ', 0, 0); // Bitiş tarihi kısmını yazıyoruz
$pdf->SetFont('Arial', '', 16); // Arial fontunu kullanıyoruz
$pdf->Cell(140, 10, htmlspecialchars($certificate['bitis_tarihi']), 0, 1); // Bitiş tarihini ekliyoruz

$pdf->Ln(10); // Satır boşluğu ekliyoruz

// Sertifika Sahibi kısmında öğrencinin adını yazıyoruz
$pdf->SetFont('ArialBold', '', 16); // Arial bold fontunu kullanıyoruz
$pdf->Cell(50, 10, 'Sertifika Sahibi: ', 0, 0); // Sertifika sahibi kısmını yazıyoruz
$pdf->SetFont('Arial', '', 16); // Arial fontunu kullanıyoruz
$pdf->Cell(140, 10, htmlspecialchars($student['ad']) . ' ' . htmlspecialchars($student['soyad']), 0, 1); // Sertifika sahibinin adını ve soyadını ekliyoruz

$pdf->Ln(10); // Satır boşluğu ekliyoruz

$pdf->SetFont('ArialBold', '', 16); // Arial bold fontunu kullanıyoruz
$pdf->Cell(50, 10, 'Sertifika No: ', 0, 0); // Sertifika numarası kısmını yazıyoruz
$pdf->SetFont('Arial', '', 16); // Arial fontunu kullanıyoruz
$pdf->Cell(140, 10, htmlspecialchars($certificate['sertifika_ad']), 0, 1); // Sertifika numarasını ekliyoruz

$pdf->Ln(10); // Satır boşluğu ekliyoruz

// Sertifika tarihi (Bugünün tarihi)
$pdf->Cell(0, 10, date('Y-m-d'), 0, 1, 'C'); // Bugünün tarihini ekliyoruz

// PDF Dosyasını İndirme
$pdf->Output('D', 'sertifika_' . $certificate['sertifika_ad'] . '.pdf'); // Sertifikayı indirmeye başlatıyoruz
?>