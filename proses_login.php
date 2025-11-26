<?php
session_start();
// Panggil config yang ada variabel $conn
include 'includes/config.php'; 

if (isset($_POST['login'])) {

    // Ambil data dari form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query cek user & password
    // Kita pakai $conn sesuai config.php kamu
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    // Cek apakah ada data?
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // Simpan sesi login
        $_SESSION['id_user'] = $data['id'];
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['status'] = "login";

        // Cek Role untuk Redirection
        if ($data['role'] == "admin") {
            // Jika admin, arahkan ke folder admin
            echo "<script>
                    alert('Login Admin Berhasil!'); 
                    window.location.href='admin/manage_orders.php';
                  </script>";
        } else if ($data['role'] == "user") {
            // Jika user biasa, arahkan ke folder user
            echo "<script>
                    alert('Login Berhasil!'); 
                    window.location.href='user/dashboard.php';
                  </script>";
        } else {
            // Jaga-jaga error role tidak dikenal
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