<?php
session_start();
include '../includes/config.php';

class ReviewManager {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkAuth();
    }

    // 1. CEK OTENTIKASI ADMIN
    private function checkAuth() {
        if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
            header("Location: ../login.php"); 
            exit();
        }
    }

    // 2. AMBIL DATA ULASAN (Rating > 0)
    public function getAllReviews() {
        $query = "SELECT orders.*, users.username 
                  FROM orders 
                  LEFT JOIN users ON orders.user_id = users.id 
                  WHERE orders.rating IS NOT NULL AND orders.rating > 0 
                  ORDER BY orders.created_at DESC";
        
        return mysqli_query($this->conn, $query);
    }
}

// --- EKSEKUSI PROGRAM ---
$manager = new ReviewManager($conn);
$reviews = $manager->getAllReviews();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Ulasan & Rating User</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .star-gold { color: #FFD700; }
        .star-gray { color: #E5E7EB; }
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
                <li><a href="reviews.php" class="active"><i class="ri-star-line"></i> Ulasan User</a></li>
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
                <h2 style="color: var(--primary); font-size: 18px; margin:0;">Ulasan & Rating Pengguna</h2>
                <small style="color:#888;">Feedback dari pesanan yang selesai</small>
            </div>
            
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">User</th>
                            <th width="15%">Rating</th>
                            <th width="40%">Ulasan</th>
                            <th width="20%">Detail Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Cek jika kosong
                        if(mysqli_num_rows($reviews) == 0){
                            echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#999;'>Belum ada ulasan masuk.</td></tr>";
                        }

                        while($row = mysqli_fetch_assoc($reviews)):
                            $namaUser = $row['username'] ?? 'User Dihapus';
                            $rating = $row['rating'];
                            $review = $row['review'];
                            if(empty($review)) $review = "<em style='color:#ccc;'>Tidak ada komentar</em>";
                        ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <strong><?= $namaUser ?></strong><br>
                                <small style="color:#888;">ID: <?= $row['user_id'] ?></small>
                            </td>
                            <td>
                                <div style="display:flex;">
                                    <?php for($i=1; $i<=5; $i++): ?>
                                        <i class="ri-star-fill <?= ($i <= $rating) ? 'star-gold' : 'star-gray' ?>"></i>
                                    <?php endfor; ?>
                                    <span style="margin-left:5px; font-weight:bold; font-size:12px;">(<?= $rating ?>)</span>
                                </div>
                            </td>
                            <td>
                                <div style="background:#f8f9fa; padding:10px; border-radius:8px; font-size:13px; color:#444;">
                                    "<?= $review ?>"
                                </div>
                            </td>
                            <td>
                                <small style="display:block; color:#666; margin-bottom:3px;"><?= substr($row['items'], 0, 30) ?>...</small>
                                <span class="badge bg-success" style="font-size:10px;">Selesai</span>
                                <br>
                                <small style="color:#999; font-size:10px;"><?= date('d M Y', strtotime($row['created_at'])) ?></small>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>