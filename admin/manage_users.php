<?php
session_start();
include '../includes/config.php';

class UserManager {
    private $conn;
    public $limit = 5; // Batas data per halaman
    public $page;
    public $offset;
    public $totalData;
    public $totalPages;
    public $startNumber;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkAuth();
        
        // Handle Delete Request
        if(isset($_GET['delete'])) {
            $this->deleteUser($_GET['delete']);
        }

        $this->calculatePagination();
    }

    // 1. CEK OTENTIKASI ADMIN
    private function checkAuth() {
        if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
            header("Location: ../login.php"); 
            exit();
        }
    }

    // 2. LOGIKA HAPUS USER
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

    // 3. LOGIKA PAGINATION
    private function calculatePagination() {
        $this->page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
        $this->offset = ($this->page > 1) ? ($this->page * $this->limit) - $this->limit : 0;

        $queryCount = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM users");
        $data = mysqli_fetch_assoc($queryCount);
        $this->totalData = $data['total'];
        $this->totalPages = ceil($this->totalData / $this->limit);
        $this->startNumber = $this->offset + 1;
    }

    // 4. AMBIL DATA USER
    public function getUsers() {
        $query = "SELECT * FROM users ORDER BY created_at DESC LIMIT $this->offset, $this->limit";
        return mysqli_query($this->conn, $query);
    }

    // 5. CEK STATUS AKTIF
    public function getUserStatus($userId) {
        $q = mysqli_query($this->conn, "SELECT action, created_at FROM activity_logs WHERE user_id='$userId' ORDER BY id DESC LIMIT 1");
        
        if(mysqli_num_rows($q) > 0) {
            $row = mysqli_fetch_assoc($q);
            if($row['action'] == 'Login') {
                return ['dot' => 'online', 'text' => 'Online', 'last_seen' => $row['created_at']];
            }
        }
        return ['dot' => 'offline', 'text' => 'Offline', 'last_seen' => null];
    }
}

// --- EKSEKUSI PROGRAM ---
$userManager = new UserManager($conn);
$users = $userManager->getUsers();
$nomor = $userManager->startNumber;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pengguna</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* Styling Dot Status */
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .online { background-color: #10B981; box-shadow: 0 0 5px #10B981; }
        .offline { background-color: #9CA3AF; }

        /* Pagination Style */
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
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_assoc($users)):
                            $status = $userManager->getUserStatus($row['id']);
                            $joinDate = isset($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) : '-';
                        ?>
                        <tr>
                            <td><?= $nomor++ ?></td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <div style="width:35px; height:35px; background:#e0e7ff; color:#3730a3; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; margin-right:10px;">
                                        <?= strtoupper(substr($row['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= $row['username'] ?></strong>
                                        <br>
                                        <small style="color:#888;">ID: <?= $row['id'] ?> • <span style="text-transform:capitalize;"><?= $row['role'] ?></span></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <span class="status-dot <?= $status['dot'] ?>"></span>
                                    <span style="font-size:13px; font-weight:500; color:#555;"><?= $status['text'] ?></span>
                                </div>
                                <?php if($status['last_seen']): ?>
                                    <small style="color:#999; font-size:10px; margin-left:18px;">Last active: <?= date('H:i', strtotime($status['last_seen'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="color: #6B7280; font-size: 13px;">
                                <i class="ri-calendar-line" style="vertical-align:middle; margin-right:5px;"></i>
                                <?= $joinDate ?>
                            </td>
                            <td>
                                <?php if($row['id'] != $_SESSION['user_id']): ?>
                                    <a href="manage_users.php?delete=<?= $row['id'] ?>" class="action-btn delete" onclick="return confirm('Yakin ingin menghapus user ini?')" title="Hapus User">
                                        <i class="ri-delete-bin-line"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="font-size:11px; color:#aaa; font-style:italic;">(Akun Anda)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
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
</body>
</html>