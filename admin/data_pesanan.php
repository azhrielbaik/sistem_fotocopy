<?php
include '../includes/config.php';

class OrderDataManager {
    private $conn;

    // Property untuk menyimpan data statistik
    public $total_order;
    public $print_order;
    public $atk_order;
    public $revenue;
    
    // Property untuk persentase
    public $percent_print;
    public $percent_atk;
    public $percent_bind;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->calculateStatistics();
    }

    // --- LOGIC MENGHITUNG STATISTIK (Dipindah ke Method) ---
    private function calculateStatistics() {
        // 1. Total Pesanan
        $this->total_order = mysqli_num_rows(mysqli_query($this->conn, "SELECT * FROM orders"));

        // 2. Pesanan Print
        $this->print_order = mysqli_num_rows(mysqli_query($this->conn, "SELECT * FROM orders WHERE type='Print' OR type='Fotocopy'"));

        // 3. Pesanan ATK
        $this->atk_order = mysqli_num_rows(mysqli_query($this->conn, "SELECT * FROM orders WHERE type='ATK'"));

        // 4. Total Pendapatan
        $rev_query = mysqli_query($this->conn, "SELECT SUM(total_price) as total FROM orders");
        $rev_data = mysqli_fetch_assoc($rev_query);
        $this->revenue = $rev_data['total'];

        // Hitung Persentase Sederhana
        $total_safe = $this->total_order > 0 ? $this->total_order : 1; 
        $this->percent_print = ($this->print_order / $total_safe) * 100;
        $this->percent_atk = ($this->atk_order / $total_safe) * 100;
        $this->percent_bind = 100 - ($this->percent_print + $this->percent_atk); 
    }

    // --- AMBIL DATA TABEL TERBARU ---
    public function getRecentOrders() {
        $query = "SELECT orders.*, users.username AS nama_pelanggan 
                  FROM orders 
                  LEFT JOIN users ON orders.user_id = users.id 
                  ORDER BY orders.created_at DESC LIMIT 7";
        
        return mysqli_query($this->conn, $query);
    }
}

// --- EKSEKUSI PROGRAM ---
$dataManager = new OrderDataManager($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Pesanan - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(255,255,255,0.2); padding: 5px; border-radius: 8px;">
                    <i class="ri-printer-cloud-line" style="font-size: 28px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px;">Admin Panel</h3>
                    <small style="opacity: 0.7; font-size: 11px;">Si-Foprint</small>
                </div>
            </div>

            <ul class="menu">
                <li><a href="charts.php"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="manage_orders.php"><i class="ri-dashboard-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php" class="active"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-shopping-bag-3-line"></i> Data Barang ATK</a></li>
                <li><a href="activity_logs.php" class=><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php"><i class="ri-star-line"></i> Ulasan User</a></li>
                <li><a href="manage_users.php"><i class="ri-user-settings-line"></i> Kelola User</a></li>
            </ul>
        </div>

        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;">Admin</h4>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="page-header-row" style="border:none; padding-bottom:0;">
            <h2 style="color: var(--text-dark); font-size: 20px;">Data Pesanan</h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-blue"><i class="ri-shopping-cart-2-fill"></i></div>
                    <span class="badge-growth"><i class="ri-arrow-right-up-line"></i> +12%</span>
                </div>
                <div class="stat-number"><?= $dataManager->total_order ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-green"><i class="ri-printer-fill"></i></div>
                    <span class="badge-growth"><i class="ri-arrow-right-up-line"></i> +8%</span>
                </div>
                <div class="stat-number"><?= $dataManager->print_order ?></div>
                <div class="stat-label">Pesanan Print</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-purple"><i class="ri-box-3-fill"></i></div>
                    <span class="badge-growth"><i class="ri-arrow-right-up-line"></i> +15%</span>
                </div>
                <div class="stat-number"><?= $dataManager->atk_order ?></div>
                <div class="stat-label">Pesanan ATK</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon icon-orange"><i class="ri-money-dollar-circle-fill"></i></div>
                    <span class="badge-growth"><i class="ri-arrow-right-up-line"></i> +20%</span>
                </div>
                <div class="stat-number"><?= number_format($dataManager->revenue/1000000, 1) ?>jt</div> 
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid #E5E7EB; box-shadow: none;">
            <div style="padding: 20px; border-bottom: 1px solid #F3F4F6;">
                <h3 style="margin:0; font-size:16px; color: var(--primary);">Data Pesanan Terbaru</h3>
                <p style="margin:5px 0 0; color:#6B7280; font-size:13px;">Riwayat pesanan dari pelanggan</p>
            </div>
            
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Tipe</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal & Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil Data Tabel dari Method Class
                        $q = $dataManager->getRecentOrders();
                        
                        if (!$q) {
                            echo "<tr><td colspan='7'>Error Query: " . mysqli_error($conn) . "</td></tr>";
                        } else {
                            while($row = mysqli_fetch_assoc($q)):
                                $custName = !empty($row['nama_pelanggan']) ? $row['nama_pelanggan'] : "Guest/Deleted User";
                                
                                $statusClass = 'bg-pending';
                                if($row['status']=='Diproses') $statusClass='bg-process';
                                if($row['status']=='Selesai' || $row['status']=='Completed') $statusClass='bg-success';
                        ?>
                        <tr>
                            <td style="font-weight: 500;"><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($custName) ?></td> 
                            <td><span class="badge-type"><?= $row['type'] ?></span></td>
                            <td style="font-size: 13px; color: #555;">
                                <?= substr($row['items'], 0, 30) ?>...
                            </td>
                            <td style="font-weight: 600;"><?= "Rp " . number_format($row['total_price'], 0, ',', '.') ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= $row['status'] ?></span></td>
                            <td style="color: #6B7280; font-size: 13px;"><?= $row['created_at'] ?></td>
                        </tr>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bottom-stats-grid">
            
            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:var(--primary);">Print & Fotocopy</h4>
                    <i class="ri-printer-line" style="font-size:24px; color:var(--primary);"></i>
                </div>
                <div style="margin-top:20px;">
                    <h2 style="margin:0;"><?= $dataManager->print_order ?> Pesanan</h2>
                    <p style="margin:5px 0 0; font-size:13px; color:#6B7280;"><?= round($dataManager->percent_print) ?>% dari total pesanan</p>
                </div>
                <div class="progress-container">
                    <div class="progress-fill" style="width: <?= $dataManager->percent_print ?>%; background: #2563EB;"></div>
                </div>
            </div>

            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#9333EA;">Produk ATK</h4>
                    <i class="ri-box-3-line" style="font-size:24px; color:#9333EA;"></i>
                </div>
                <div style="margin-top:20px;">
                    <h2 style="margin:0;"><?= $dataManager->atk_order ?> Pesanan</h2>
                    <p style="margin:5px 0 0; font-size:13px; color:#6B7280;"><?= round($dataManager->percent_atk) ?>% dari total pesanan</p>
                </div>
                <div class="progress-container">
                    <div class="progress-fill" style="width: <?= $dataManager->percent_atk ?>%; background: #9333EA;"></div>
                </div>
            </div>

            <div class="stat-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; color:#10B981;">Jilid & Binding</h4>
                    <i class="ri-file-text-line" style="font-size:24px; color:#10B981;"></i>
                </div>
                <div style="margin-top:20px;">
                    <h2 style="margin:0;"><?= $dataManager->total_order - ($dataManager->print_order + $dataManager->atk_order) ?> Pesanan</h2>
                    <p style="margin:5px 0 0; font-size:13px; color:#6B7280;"><?= round($dataManager->percent_bind) ?>% dari total pesanan</p>
                </div>
                <div class="progress-container">
                    <div class="progress-fill" style="width: <?= $dataManager->percent_bind ?>%; background: #10B981;"></div>
                </div>
            </div>

        </div>

    </div>
</body>
</html>