<?php
session_start();
include '../includes/config.php';

class ActivityLogManager {
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
        $this->calculatePagination();
    }

    // 1. CEK OTENTIKASI ADMIN
    private function checkAuth() {
        if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
            header("Location: ../login.php"); 
            exit();
        }
    }

    // 2. LOGIKA PAGINATION (Hitung Halaman & Offset)
    private function calculatePagination() {
        $this->page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
        $this->offset = ($this->page > 1) ? ($this->page * $this->limit) - $this->limit : 0;

        // Hitung Total Data
        $queryCount = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM activity_logs");
        $data = mysqli_fetch_assoc($queryCount);
        $this->totalData = $data['total'];
        
        // Hitung Total Halaman
        $this->totalPages = ceil($this->totalData / $this->limit);

        // Set Nomor Urut Awal
        $this->startNumber = $this->offset + 1;
    }

    // 3. AMBIL DATA LOG (Query Utama dengan LIMIT)
    public function getLogs() {
        $query = "SELECT activity_logs.*, users.username, users.role 
                  FROM activity_logs 
                  LEFT JOIN users ON activity_logs.user_id = users.id 
                  ORDER BY activity_logs.created_at DESC 
                  LIMIT $this->offset, $this->limit";
        
        return mysqli_query($this->conn, $query);
    }

    // 4. LOGIKA CEK STATUS USER (Online/Offline)
    // Mengecek aksi terakhir user tersebut di database
    public function getUserStatus($userId) {
        $qStatus = mysqli_query($this->conn, "SELECT action FROM activity_logs WHERE user_id='$userId' ORDER BY id DESC LIMIT 1");
        $lastAction = mysqli_fetch_assoc($qStatus);

        if ($lastAction && $lastAction['action'] == 'Login') {
            return ['class' => 'online', 'text' => 'Online'];
        }
        return ['class' => 'offline', 'text' => 'Offline'];
    }
}

// --- EKSEKUSI PROGRAM ---
$logManager = new ActivityLogManager($conn);
$logs = $logManager->getLogs();
$nomor = $logManager->startNumber;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Log Aktivitas User</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* CSS Tambahan untuk Status Dot */
        .status-dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .online { background-color: #10B981; box-shadow: 0 0 5px #10B981; } /* Hijau */
        .offline { background-color: #9CA3AF; } /* Abu-abu */

        /* CSS Tambahan untuk Pagination */
        .pagination {
            display: flex;
            justify-content: flex-end;
            list-style: none;
            padding: 0;
            margin-top: 20px;
            gap: 5px;
        }
        .pagination li a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            background: white;
            font-size: 14px;
        }
        .pagination li a:hover {
            background-color: #f0f0f0;
        }
        .pagination li.active a {
            background-color: var(--primary, #007bff); 
            color: white;
            border-color: var(--primary, #007bff);
        }
        .pagination li.disabled a {
            color: #ccc;
            pointer-events: none;
            background: #f9f9f9;
        }
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
                <li><a href="activity_logs.php" class="active"><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php"><i class="ri-star-line"></i> Ulasan User</a></li>
                <li><a href="manage_users.php"><i class="ri-user-settings-line"></i> Kelola User</a></li>
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
                <h2 style="color: var(--primary); font-size: 18px; margin:0;">Log Aktivitas User</h2>
                <small style="color:#888;">Halaman <?= $logManager->page ?> dari <?= $logManager->totalPages ?> | Total <?= $logManager->totalData ?> Data</small>
            </div>
            
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="50px">No</th> <th>User & Status</th>
                            <th>Aktivitas</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_assoc($logs)):
                            
                            // Panggil Method Class untuk Status Online/Offline
                            $status = $logManager->getUserStatus($row['user_id']);
                            
                            // Warna Badge Aktivitas
                            $badgeClass = ($row['action'] == 'Login') ? 'bg-success' : 'bg-danger';
                            $tanggal = date('d M Y, H:i', strtotime($row['created_at']));
                            $namaUser = $row['username'] ?? '<span style="color:red;">User Dihapus</span>';
                            $roleUser = $row['role'] ?? '-';
                        ?>
                        <tr>
                            <td><?= $nomor++ ?></td>
                            
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <span class="status-dot <?= $status['class'] ?>" title="Status Saat Ini: <?= $status['text'] ?>"></span>
                                    <div>
                                        <strong><?= $namaUser ?></strong>
                                        <br>
                                        <small style="color:#888;">Role: <?= ucfirst($roleUser) ?> (ID: <?= $row['user_id'] ?>)</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $badgeClass ?>" style="padding: 5px 10px;">
                                    <?= $row['action'] ?>
                                </span>
                            </td>
                            <td><?= $tanggal ?> WIB</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <nav>
                    <ul class="pagination">
                        <li class="<?php if($logManager->page <= 1) { echo 'disabled'; } ?>">
                            <a href="<?php if($logManager->page <= 1) { echo '#'; } else { echo "?halaman=".($logManager->page - 1); } ?>">Previous</a>
                        </li>

                        <?php 
                        // Loop Halaman
                        for($x = 1; $x <= $logManager->totalPages; $x++): 
                        ?>
                            <li class="<?php if($logManager->page == $x) { echo 'active'; } ?>">
                                <a href="?halaman=<?php echo $x; ?>"><?php echo $x; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="<?php if($logManager->page >= $logManager->totalPages) { echo 'disabled'; } ?>">
                            <a href="<?php if($logManager->page >= $logManager->totalPages) { echo '#'; } else { echo "?halaman=".($logManager->page + 1); } ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                </div>
        </div>
    </div>
</body>
</html>