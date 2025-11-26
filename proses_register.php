<?php
// Panggil koneksi (pastikan path-nya benar)
include 'includes/config.php';

if (isset($_POST['register'])) {
    
    // 1. Ambil data Username & Password
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // 2. Set Role jadi 'user' (sesuai contoh data di gambar kamu)
    $role = 'user'; 

    // 3. Query INSERT disesuaikan dengan nama kolom di database kamu:
    // Kolom: id (otomatis), username, password, role
    $query = "INSERT INTO users (username, password, role) 
              VALUES ('$username', '$password', '$role')";

    // 4. Jalankan (Pakai $conn sesuai config kamu)
    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Pendaftaran Berhasil! Silakan Login.');
                window.location.assign('login.php');
              </script>";
    } else {
        echo "Gagal mendaftar: " . mysqli_error($conn);
    }
}
?>