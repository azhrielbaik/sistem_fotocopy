<?php
// Panggil koneksi (pastikan path-nya benar)
include 'includes/config.php';

class RegisterProcess {

    private $conn;

    // Constructor untuk menerima koneksi database dari config.php
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function register($username, $password) {
        // 2. Set Role jadi 'user' (Sesuai kode asli)
        $role = 'user'; 

        // 3. Query INSERT (Sesuai kode asli, variabel langsung dimasukkan)
        $query = "INSERT INTO users (username, password, role) 
                  VALUES ('$username', '$password', '$role')";

        // 4. Jalankan Query
        if (mysqli_query($this->conn, $query)) {
            // Jika Berhasil
            echo "<script>
                    alert('Pendaftaran Berhasil! Silakan Login.');
                    window.location.assign('login.php');
                  </script>";
        } else {
            // Jika Gagal
            echo "Gagal mendaftar: " . mysqli_error($this->conn);
        }
    }
}

// --- EKSEKUSI PROGRAM ---

if (isset($_POST['register'])) {
    
    // 1. Instansiasi Class Register
    $process = new RegisterProcess($conn);

    // 2. Ambil data dari form dan jalankan fungsi register
    $process->register($_POST['username'], $_POST['password']);
}
?>