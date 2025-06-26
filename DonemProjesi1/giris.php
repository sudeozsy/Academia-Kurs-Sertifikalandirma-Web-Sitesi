<!DOCTYPE html>
<html lang="tr">

<head>
    <!-- Sayfanın karakter seti ve mobil uyumlu olması için meta bilgileri -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Kurs Platformu</title>

    <!-- Font Awesome kütüphanesi ile ikonlar için CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts: Poppins yazı tipi -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS: responsive tasarım ve hazır stiller için -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Özel stiller için harici CSS dosyası -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Üst kısımda gezinme menüsünü içeren header -->
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

                <!-- Menü bağlantıları -->
                <li><a href="anasayfa.php">Ana Sayfa</a></li>
                <li><a href="kurslar.php">Kurslar</a></li>
                <li><a href="giris.php">Giriş Yap</a></li>
                <li><a href="kayıt.php">Kayıt Ol</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Giriş Yap bölümünü içeren ana form -->
        <section id="login">
            <form action="girisIslem.php" method="POST">
                <!-- Başlık -->
                <h2>Giriş Yap</h2>

                <!-- E-posta alanı -->
                <i class="fas fa-envelope"></i>
                <input type="text" id="e_posta" name="e_posta" placeholder="E-posta" required> <br>

                <!-- Şifre alanı -->
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Şifre" required> <br>

                <!-- Rol seçimi (Öğrenci veya Eğitmen) -->
                <div id="role">
                    <!-- Öğrenci rolü için radio butonu -->
                    <div class="role-option"><i class="fas fa-user"></i>
                        <input type="radio" class="btn-check" name="role" id="role_student" value="student"
                            autocomplete="off" checked>
                        <label class="btn btn-outline-custom" for="role_student">Öğrenci</label>
                    </div>

                    <!-- Eğitmen rolü için radio butonu -->
                    <div class="role-option">
                        <input type="radio" class="btn-check" name="role" id="role_instructor" value="instructor"
                            autocomplete="off">
                        <label class="btn btn-outline-custom" for="role_instructor">Eğitmen</label>
                    </div>
                </div>

                <!-- Giriş Yap butonu -->
                <button type="submit">Giriş Yap</button>
            </form>
        </section>
    </main>

    <?php
    // Eğer URL'de 'status' parametresi varsa ve değeri 'error' ise bu blok çalışır
    if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>

        <!-- 'message' parametresine göre farklı hata mesajları gösterilir -->
        <?php if ($_GET['message'] == 'invalid_credentials'): ?>
            <center>
                <!-- Hatalı giriş bilgileri için hata mesajı -->
                <p style="color:red;">E-posta veya şifre yanlış.</p>
            </center>

        <?php elseif ($_GET['message'] == 'invalid_role'): ?>
            <center>
                <!-- Geçersiz rol seçimi için hata mesajı -->
                <p style="color:red;">Geçersiz rol seçimi.</p>
            </center>
        <?php endif; ?>

    <?php endif; ?>



    <script src="app.js"></script>

</body>

</html>