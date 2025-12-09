<?php
session_start();
// Panggil config yang ada variabel $conn
include 'includes/config.php'; 

class LoginProcess {
    
    private $conn;

    // Constructor untuk mengambil koneksi database
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function login($username, $password) {
        // Query cek user & password (Sesuai kode asli)
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $result = mysqli_query($this->conn, $query);

        // Cek apakah ada data?
        if (mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_assoc($result);

            // Simpan sesi login (Sesuai kode asli)
            $_SESSION['user_id'] = $data['id']; 
            $_SESSION['id_user'] = $data['id']; 
            $_SESSION['username'] = $data['username'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['status'] = "login";

            // --- TAMBAHAN: CATAT LOG LOGIN --- (Sesuai kode asli)
            $uid = $data['id'];
            $logQuery = "INSERT INTO activity_logs (user_id, action) VALUES ('$uid', 'Login')";
            mysqli_query($this->conn, $logQuery);
            // ---------------------------------

            // Cek Role untuk Redirection
            $this->redirectByRole($data['role']);

        } else {
            // Jika username/password salah
            echo "<script>
                    alert('Username atau Password Salah!');
                    window.location.href='login.php';
                  </script>";
        }
    }

    // Fungsi tambahan untuk memisahkan logika redirect (biar rapi tapi tetap sama isinya)
    private function redirectByRole($role) {
        if ($role == "admin") {
            echo "<script>
                    alert('Login Admin Berhasil!'); 
                    window.location.href='admin/charts.php';
                  </script>";
        } else if ($role == "user") {
            echo "<script>
                    alert('Login Berhasil!'); 
                    window.location.href='user/dashboard.php';
                  </script>";
        } else {
            echo "<script>alert('Role tidak dikenali!'); window.location.href='login.php';</script>";
        }
    }
}

// --- EKSEKUSI PENGGUNAAN CLASS ---

if (isset($_POST['login'])) {
    // 1. Inisialisasi Class dengan memasukkan variabel $conn dari config.php
    $process = new LoginProcess($conn);

    // 2. Jalankan fungsi login
    $process->login($_POST['username'], $_POST['password']);
}
?>