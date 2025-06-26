<?php
session_start();
session_destroy(); // Oturumu sonlandır
header("Location: anasayfa.php"); // Ana sayfaya yönlendir
exit;
?>