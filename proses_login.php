<?php
session_start();
// Panggil config yang ada variabel $conn
include 'includes/config.php'; 

if (isset($_POST['login'])) {

    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query cek user & password
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    // Cek apakah ada data?
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // Simpan sesi login
        // SAYA UBAH SEDIKIT AGAR KONSISTEN DENGAN LOGOUT
        $_SESSION['user_id'] = $data['id']; // Tambahkan ini biar logout.php bisa baca ID nya
        $_SESSION['id_user'] = $data['id']; // Biarkan yang lama tetap ada (biar codingan lain ga error)
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['status'] = "login";

        // --- TAMBAHAN: CATAT LOG LOGIN DI SINI ---
        $uid = $data['id'];
        $logQuery = "INSERT INTO activity_logs (user_id, action) VALUES ('$uid', 'Login')";
        mysqli_query($conn, $logQuery);
        // -----------------------------------------

        // Cek Role untuk Redirection
        if ($data['role'] == "admin") {
            echo "<script>
                    alert('Login Admin Berhasil!'); 
                    window.location.href='admin/manage_orders.php';
                  </script>";
        } else if ($data['role'] == "user") {
            echo "<script>
                    alert('Login Berhasil!'); 
                    window.location.href='user/dashboard.php';
                  </script>";
        } else {
            echo "<script>alert('Role tidak dikenali!'); window.location.href='login.php';</script>";
        }

    } else {
        // Jika username/password salah
        echo "<script>
                alert('Username atau Password Salah!');
                window.location.href='login.php';
              </script>";
    }
}
?>