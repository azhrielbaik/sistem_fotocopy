<?php
session_start();

// 1. WAJIB include koneksi database (karena kode aslimu belum ada ini)
include 'includes/config.php'; 

// 2. Cek apakah ada user yang login? (Ambil ID-nya sebelum dihapus)
// Pastikan kamu sudah update file login.php seperti saran sebelumnya agar $_SESSION['user_id'] tersedia
if(isset($_SESSION['user_id'])) {
    
    $uid = $_SESSION['user_id'];
    
    // 3. Catat ke Database Log
    mysqli_query($conn, "INSERT INTO activity_logs (user_id, action) VALUES ('$uid', 'Logout')");

} 
// Opsi cadangan: kalau variabel sesinya pakai nama lama 'id_user'
elseif(isset($_SESSION['id_user'])) {
    $uid = $_SESSION['id_user'];
    mysqli_query($conn, "INSERT INTO activity_logs (user_id, action) VALUES ('$uid', 'Logout')");
}

// 4. Baru hancurkan sesi
session_destroy();

// 5. Redirect ke halaman awal
header("Location: index.php");
exit();
?>