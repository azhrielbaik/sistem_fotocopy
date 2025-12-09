<?php 
session_start();
include '../includes/config.php'; 

class UserDashboard {
    private $conn;
    public $userName;
    public $userEmail;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // 1. Method untuk Cek Login
    public function validateSession() {
        if(!isset($_SESSION['user_id'])){
            header("Location: ../login.php"); 
            exit();
        }
    }

    // 2. Method untuk Ambil Data User
    public function loadUserData() {
        // Ambil ID dari session
        $my_id = $_SESSION['user_id'];

        // Query database (Sesuai kode asli)
        $query = mysqli_query($this->conn, "SELECT * FROM users WHERE id='$my_id'");
        $dataUser = mysqli_fetch_assoc($query);

        // Masukkan data ke property class (Logika Null Coalescing tetap sama)
        $this->userName = $dataUser['username'] ?? $dataUser['name'] ?? 'User'; 
        $this->userEmail = $dataUser['email'] ?? 'email@tidak.ada'; 
    }
}

// --- EKSEKUSI LOGIKA ---

// 1. Inisialisasi Class
$dashboard = new UserDashboard($conn);

// 2. Cek apakah user sudah login
$dashboard->validateSession();

// 3. Ambil data user untuk ditampilkan
$dashboard->loadUserData();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - PrintCopy Pro</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-fill" style="font-size: 32px;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 18px;">Dashboard</h3>
                    <small style="opacity: 0.8; font-size: 12px;">Si-Foprint</small>
                </div>
            </div>

            <ul class="menu">
                <li><a href="dashboard.php" class="active"><i class="ri-home-4-line"></i> Beranda</a></li>
                <li><a href="place_order.php"><i class="ri-shopping-cart-2-line"></i> Buat Pesanan</a></li>
                <li><a href="view_orders.php"><i class="ri-file-list-3-line"></i> Status Pesanan</a></li>
                <li><a href="rate_order.php"><i class="ri-star-line"></i> Beri Rating</a></li>
                <li><a href="profile.php"><i class="ri-user-settings-line"></i> Profil Saya</a></li>
            </ul>
        </div>

        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;">Selamat datang,</h4>
                <h4 style="margin: 0; font-weight: 600;"><?= $dashboard->userName ?></h4>
                <small style="opacity: 0.8;"><?= $dashboard->userEmail ?></small>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="ri-logout-box-r-line"></i> Logout
            </a>
            <a href="../index.php" style="display: block; text-align: center; color: white; font-size: 12px; margin-top: 10px; text-decoration: none; opacity: 0.8;">
                Kembali ke Landing
            </a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F3F4F6;"> 
        <div class="header" style="align-items: center; justify-content: space-between;">
            <i class="ri-close-line" style="font-size: 24px; color: #9CA3AF; cursor: pointer; visibility: hidden;"></i> <h4 style="color: var(--primary);">Beranda</h4>
            <div></div> 
        </div>

        <div class="welcome-banner">
            <h2>Selamat Datang, <?= $dashboard->userName ?>!</h2>
            <p>Kelola pesanan print, fotocopy, dan ATK Anda dengan mudah.</p>
        </div>

        <div class="dashboard-grid">
            
            <a href="place_order.php" class="dashboard-card">
                <div class="card-icon-wrapper">
                    <i class="ri-shopping-cart-2-fill"></i>
                </div>
                <h3>Buat Pesanan</h3>
                <p>Pesan layanan print, fotocopy, atau beli produk ATK</p>
            </a>

            <a href="view_orders.php" class="dashboard-card">
                <div class="card-icon-wrapper">
                    <i class="ri-file-text-fill"></i>
                </div>
                <h3>Status Pesanan</h3>
                <p>Lihat dan pantau status pesanan Anda</p>
            </a>

            <a href="rate_order.php" class="dashboard-card">
                <div class="card-icon-wrapper">
                    <i class="ri-star-smile-fill"></i>
                </div>
                <h3>Beri Rating</h3>
                <p>Berikan penilaian untuk layanan kami</p>
            </a>

        </div>
    </div>
</body>
</html>