<?php
session_start();
include '../includes/config.php';

class UserManager {
    private $conn;
    public $limit = 5; 
    public $page;
    public $offset;
    public $totalData;
    public $totalPages;
    public $startNumber;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkAuth();
        
        if(isset($_GET['delete'])) {
            $this->deleteUser($_GET['delete']);
        }

        $this->calculatePagination();
    }

    private function checkAuth() {
        if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
            header("Location: ../login.php"); 
            exit();
        }
    }

    private function deleteUser($id) {
        if($id == $_SESSION['user_id']) {
            echo "<script>alert('Tidak dapat menghapus akun yang sedang digunakan!'); window.location='manage_users.php';</script>";
            return;
        }
        mysqli_query($this->conn, "DELETE FROM users WHERE id='$id'");
        mysqli_query($this->conn, "DELETE FROM activity_logs WHERE user_id='$id'");
        echo "<script>alert('User berhasil dihapus'); window.location='manage_users.php';</script>";
        exit();
    }

    private function calculatePagination() {
        $this->page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
        $this->offset = ($this->page > 1) ? ($this->page * $this->limit) - $this->limit : 0;
        $queryCount = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM users");
        $data = mysqli_fetch_assoc($queryCount);
        $this->totalData = $data['total'];
        $this->totalPages = ceil($this->totalData / $this->limit);
        $this->startNumber = $this->offset + 1;
    }
}

$userManager = new UserManager($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pengguna</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .online { background-color: #10B981; box-shadow: 0 0 5px #10B981; }
        .offline { background-color: #9CA3AF; }
        .pagination { display: flex; justify-content: flex-end; list-style: none; padding: 0; margin-top: 20px; gap: 5px; }
        .pagination li a { padding: 8px 12px; border: 1px solid #ddd; color: #333; text-decoration: none; border-radius: 4px; background: white; font-size: 14px; }
        .pagination li a:hover { background-color: #f0f0f0; }
        .pagination li.active a { background-color: var(--primary, #007bff); color: white; border-color: var(--primary, #007bff); }
        .pagination li.disabled a { color: #ccc; pointer-events: none; background: #f9f9f9; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-line" style="font-size: 28px;"></i>
                <div><h3 style="margin:0; font-size:16px;">Admin Panel</h3><small>Si-Foprint</small></div>
            </div>
            <ul class="menu">
                <li><a href="charts.php"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="manage_orders.php"><i class="ri-dashboard-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-shopping-bag-3-line"></i> Data Barang ATK</a></li>
                <li><a href="activity_logs.php"><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php"><i class="ri-star-line"></i> Ulasan User</a></li>
                <li><a href="manage_users.php" class="active"><i class="ri-user-settings-line"></i> Kelola User</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <small>Admin</small>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        <div class="card" style="padding: 25px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="color: var(--primary); font-size: 18px; margin:0;">Kelola Pengguna</h2>
                <small style="color:#888;">Halaman <?= $userManager->page ?> dari <?= $userManager->totalPages ?> | Total <?= $userManager->totalData ?> User</small>
            </div>
            
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="50px">No</th> 
                            <th>User & Role</th>
                            <th>Status Akun</th>
                            <th>Bergabung Sejak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    
                    <tbody id="userTableBody">
                        <tr><td colspan="5" style="text-align:center;">Memuat data...</td></tr>
                    </tbody>
                </table>

                <nav>
                    <ul class="pagination">
                        <li class="<?php if($userManager->page <= 1) { echo 'disabled'; } ?>">
                            <a href="<?php if($userManager->page <= 1) { echo '#'; } else { echo "?halaman=".($userManager->page - 1); } ?>">Previous</a>
                        </li>
                        <?php for($x = 1; $x <= $userManager->totalPages; $x++): ?>
                            <li class="<?php if($userManager->page == $x) { echo 'active'; } ?>">
                                <a href="?halaman=<?php echo $x; ?>"><?php echo $x; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="<?php if($userManager->page >= $userManager->totalPages) { echo 'disabled'; } ?>">
                            <a href="<?php if($userManager->page >= $userManager->totalPages) { echo '#'; } else { echo "?halaman=".($userManager->page + 1); } ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script>
        // Ambil halaman saat ini dari URL (agar tetap di page yg sama saat refresh)
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get('halaman') || 1;

        function loadUsers() {
            // Panggil file get_users_table.php dengan parameter halaman
            fetch(`get_users_table.php?halaman=${currentPage}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('userTableBody').innerHTML = data;
                })
                .catch(error => console.error('Error fetching users:', error));
        }

        // Jalankan saat load pertama
        document.addEventListener('DOMContentLoaded', loadUsers);

        // Jalankan ulang setiap 3 detik
        setInterval(loadUsers, 3000);
    </script>

</body>
</html>