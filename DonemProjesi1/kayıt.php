<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8"> <!-- Karakter seti ayarlandı -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobil uyumlu görünüm için meta tag -->
    <title>Kayıt Ol - Kurs Platformu</title> <!-- Sayfa başlığı -->

    <!-- Font Awesome ikonları için bağlantı -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts üzerinden Poppins fontu ekleniyor -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <!-- Stil sayfası bağlantısı -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Sayfanın üst kısmındaki navigasyon menüsü -->
    <header>
        <nav>
            <ul>
                <li><img src="logo.jpg" alt="Logo" class="logo"></li> <!-- Logo görseli -->
                <li>
                    <!-- Kurs arama formu -->
                    <form method="GET" action="arama.php" class="search-form">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i> <!-- Arama simgesi -->
                            <input type="text" name="search" placeholder="Kurs Ara..." required> <!-- Arama input alanı -->
                        </div>
                    </form>
                </li>
                <!-- Ana menü linkleri -->
                <li><a href="anasayfa.php">Ana Sayfa</a></li>
                <li><a href="kurslar.php">Kurslar</a></li>
                <li><a href="giris.php">Giriş Yap</a></li>
                <li><a href="kayıt.php">Kayıt Ol</a></li>
            </ul>
        </nav>
    </header>

    <!-- Kayıt formu kısmı -->
    <main>
        <section id="signup">
            <form action="kayitIslem.php" method="POST"> <!-- Kayıt formunun post methodu ile gönderilmesi -->
                <h2>Öğrenci Kayıt</h2> <!-- Başlık -->

                <!-- Ad alanı -->
                <i class="fas fa-user"></i>
                <input type="text" id="name" name="name" placeholder="Ad" required> <br>

                <!-- Soyad alanı -->
                <i class="fas fa-user"></i>
                <input type="text" id="surname" name="surname" placeholder="Soyad" required><br>

                <!-- E-posta alanı -->
                <i class="fas fa-envelope"></i>
                <input type="text" id="e_posta" name="e_posta" placeholder="E-posta" required><br>

                <!-- Şifre alanı -->
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Şifre" required><br>

                <!-- Kayıt ol butonu -->
                <button type="submit">Kayıt Ol</button>
            </form>
        </section>
    </main>

    <!-- Durum mesajı -->
    <?php if (isset($_GET['status'])): ?>
        <div>
            <!-- Başarı mesajı -->
            <?php if ($_GET['status'] === "success"): ?>
                <p>Kayıt başarılı!</p>
            <?php elseif ($_GET['status'] === "error"): ?>
                <!-- Hata mesajı -->
                <p>Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyin.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Hata mesajları -->
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] == 'password_error'): ?>
            <center>
                <p style="color:red;">Şifre en az 5, en fazla 15 karakter olmalıdır!</p> <!-- Şifre uzunluğu hatası -->
            </center>
        <?php elseif ($_GET['status'] == 'email_exists'): ?>
            <center>
                <p style="color:red; ">Bu e-posta adresi zaten kayıtlı!</p> <!-- E-posta adresi zaten mevcut -->
            </center>
        <?php elseif ($_GET['status'] == 'error'): ?>
            <center>
                <p style="color:red; ">Bir hata oluştu. Lütfen tekrar deneyin.</p> <!-- Genel hata mesajı -->
            </center>
        <?php endif; ?>
    <?php endif; ?>


    <!-- JavaScript dosyasının bağlantısı -->
    <script src="app.js"></script>

</body>

</html>
