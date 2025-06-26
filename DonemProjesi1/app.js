// Çıkış yapma işlemi
document.getElementById('logout-button').addEventListener('click', function(event) {
    event.preventDefault(); // Varsayılan link davranışını engelle

    // Onay kutusu
    const confirmed = confirm("Çıkış yapmak istediğinizden emin misiniz?");
    
    if (confirmed) {
        // Onay verildiyse cikis.php'ye yönlendir
        window.location.href = "cikis.php";
    }
});

// Belirli bir bölümü (section) gösterme fonksiyonu
function showSection(sectionId) {
    // Tüm bölümleri gizle
    const sections = ['all-courses', 'completed-courses', 'failed-courses', 'ongoing-courses', 'given-courses','completed-courses-ins','ongoing-courses-ins','wish-list'];
    sections.forEach(section => {
        document.getElementById(section).classList.add("hidden"); // Gizle
        document.getElementById(section).classList.remove("active"); // Aktif durumu kaldır
    });

    // Seçilen bölümü göster
    const section = document.getElementById(sectionId);
    section.classList.remove("hidden"); // Görünür yap
    section.classList.add("active"); // Aktif duruma getir
}

// Klavyede Enter tuşuna basıldığında formu gönderme işlemi
function checkEnter(event) {
    if (event.key === "Enter") { // Enter tuşuna basılmış mı kontrol et
        event.preventDefault(); // Varsayılan davranışı engelle
        event.target.submit(); // Formu gönder
    }
}

// Ad ve soyadı güncelleme işlemi
function updateName() {
    const firstName = document.getElementById('first-name').value.trim(); // Ad input alanındaki değeri al
    const lastName = document.getElementById('last-name').value.trim(); // Soyad input alanındaki değeri al

    // Eğer alanlar boşsa uyarı göster
    if (!firstName || !lastName) {
        alert("Ad ve soyad alanlarını doldurun.");
        return;
    }

    // Form verilerini oluştur
    const formData = new FormData();
    formData.append('action', 'update_name'); // PHP'ye gönderilecek işlem türü
    formData.append('first_name', firstName); // Ad değeri
    formData.append('last_name', lastName); // Soyad değeri

    sendForm(formData); // Veriyi gönder
}

// E-posta güncelleme işlemi
function updateEmail() {
    const newEmail = document.getElementById('new-email').value.trim(); // Yeni e-posta değeri
    const password = document.getElementById('email-password').value; // Şifre değeri

    // Eğer alanlar boşsa uyarı göster
    if (!newEmail || !password) {
        alert("E-posta ve şifre alanlarını doldurun.");
        return;
    }

    // Form verilerini oluştur
    const formData = new FormData();
    formData.append('action', 'update_email'); // PHP'ye gönderilecek işlem türü
    formData.append('new_email', newEmail); // Yeni e-posta değeri
    formData.append('password', password); // Şifre değeri

    sendForm(formData); // Veriyi gönder
}

// Şifre güncelleme işlemi
function updatePassword() {
    const oldPassword = document.getElementById('old-password').value; // Eski şifre
    const newPassword = document.getElementById('new-password').value; // Yeni şifre
    const confirmPassword = document.getElementById('confirm-password').value; // Yeni şifrenin tekrar doğrulaması

    // Eğer alanlar boşsa uyarı göster
    if (!oldPassword || !newPassword || !confirmPassword) {
        alert("Tüm şifre alanlarını doldurun.");
        return;
    }

    // Yeni şifreler eşleşmiyorsa uyarı göster
    if (newPassword !== confirmPassword) {
        alert("Yeni şifreler eşleşmiyor.");
        return;
    }

    // Form verilerini oluştur
    const formData = new FormData();
    formData.append('action', 'update_password'); // PHP'ye gönderilecek işlem türü
    formData.append('old_password', oldPassword); // Eski şifre
    formData.append('new_password', newPassword); // Yeni şifre
    formData.append('confirm_password', confirmPassword); // Yeni şifrenin doğrulaması

    sendForm(formData); // Veriyi gönder
}

// Form verilerini PHP'ye gönderme işlemi
function sendForm(formData) {
    fetch('profilGuncelle.php', { // PHP dosyasına gönder
        method: 'POST', // POST yöntemi kullan
        body: formData // Form verilerini gönder
    })
    .then(response => response.text()) // PHP'den dönen yanıtı al
    .then(data => {
        alert(data); // Yanıt mesajını göster
    })
    .catch(error => {
        console.error('Error:', error); // Hata varsa konsola yazdır
    });
}

// E-posta güncelleme alanını gösterme veya gizleme
function showEmailUpdate() {
    const emailUpdateRow = document.getElementById('email-update-row');
    // Görünürlük durumunu değiştir
    emailUpdateRow.style.display = (emailUpdateRow.style.display === 'none' || emailUpdateRow.style.display === '') ? 'table-row' : 'none';
}

// Şifre güncelleme alanını gösterme veya gizleme
function showPasswordUpdate() {
    const passwordUpdateRow = document.getElementById('password-update-row');
    // Görünürlük durumunu değiştir
    passwordUpdateRow.style.display = (passwordUpdateRow.style.display === 'none' || passwordUpdateRow.style.display === '') ? 'table-row' : 'none';
}
