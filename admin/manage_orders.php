<?php
session_start();
include '../includes/config.php';

class OrderManager {
    private $conn;
    private $adminId;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkAuth();
        $this->setAdminId();
    }

    // 1. CEK OTENTIKASI ADMIN
    private function checkAuth() {
        if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
            header("Location: ../login.php"); 
            exit();
        }
    }

    // 2. SET ADMIN ID (Helper Function)
    private function setAdminId() {
        if (isset($_SESSION['id'])) { $this->adminId = $_SESSION['id']; return; }
        if (isset($_SESSION['user_id'])) { $this->adminId = $_SESSION['user_id']; return; }
        if (isset($_SESSION['id_user'])) { $this->adminId = $_SESSION['id_user']; return; }

        if (isset($_SESSION['username'])) {
            $user = $_SESSION['username'];
            $q = mysqli_query($this->conn, "SELECT id FROM users WHERE username = '$user'");
            if ($row = mysqli_fetch_assoc($q)) {
                $this->adminId = $row['id'];
                return;
            }
        }
        $this->adminId = 0;
    }

    // 3. LOG AKTIVITAS (Reusable)
    private function logActivity($action) {
        $query_log = "INSERT INTO activity_logs (user_id, action, created_at) 
                      VALUES ('$this->adminId', '$action', NOW())";
        mysqli_query($this->conn, $query_log);
    }

    // 4. HANDLE REQUEST (Update & Delete)
    public function handleRequests() {
        // A. JIKA UPDATE
        if(isset($_POST['update_status'])) {
            $id = $_POST['order_id'];
            $stat = $_POST['status']; 
            
            // Update DB
            mysqli_query($this->conn, "UPDATE orders SET status='$stat' WHERE id='$id'");
            
            // Log
            $this->logActivity("Mengubah status Pesanan #$id menjadi $stat");
            
            echo "<script>window.location='manage_orders.php';</script>";
            exit;
        }

        // B. JIKA DELETE
        if(isset($_GET['delete'])) {
            $id = $_GET['delete'];
            
            // Delete DB
            mysqli_query($this->conn, "DELETE FROM orders WHERE id='$id'");

            // Log
            $this->logActivity("Menghapus Pesanan #$id");

            echo "<script>window.location='manage_orders.php';</script>";
            exit;
        }
    }

    // 5. HITUNG PAGINATION
    public function getPaginationData($limit) {
        $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
        $halaman_awal = ($halaman > 1) ? ($halaman * $limit) - $limit : 0;

        // Hitung Total Data
        $data_count = mysqli_query($this->conn, "SELECT COUNT(*) as total FROM orders");
        $jumlah_data = mysqli_fetch_assoc($data_count)['total'];
        $total_halaman = ceil($jumlah_data / $limit);

        return [
            'halaman' => $halaman,
            'start' => $halaman_awal,
            'total_data' => $jumlah_data,
            'total_halaman' => $total_halaman,
            'nomor' => $halaman_awal + 1
        ];
    }

    // 6. AMBIL DATA PESANAN
    public function getOrders($start, $limit) {
        $query = "SELECT orders.*, users.username 
                  FROM orders 
                  LEFT JOIN users ON orders.user_id = users.id 
                  ORDER BY orders.created_at DESC 
                  LIMIT $start, $limit";
        return mysqli_query($this->conn, $query);
    }

    // 7. HELPER: WARNA STATUS (Untuk View)
    public function getStatusStyle($statusDB) {
        if(empty($statusDB)) $statusDB = 'Pending';
        
        $class = 'bg-pending';
        $label = 'Pending';

        if($statusDB == 'Processing' || $statusDB == 'Proses') { 
            $class = 'bg-process'; $label = 'Diproses'; 
        } elseif($statusDB == 'Completed' || $statusDB == 'Selesai') { 
            $class = 'bg-success'; $label = 'Selesai'; 
        } elseif($statusDB == 'Cancelled' || $statusDB == 'Batal') {
            $class = 'bg-danger'; $label = 'Dibatalkan';
        }

        return ['class' => $class, 'label' => $label, 'raw' => $statusDB];
    }
}

// --- EKSEKUSI PROGRAM ---

$limit = 5; // Batas data per halaman
$manager = new OrderManager($conn);

// 1. Cek update/delete
$manager->handleRequests();

// 2. Hitung pagination
$paging = $manager->getPaginationData($limit);

// 3. Ambil data
$orders = $manager->getOrders($paging['start'], $limit);
$nomor = $paging['nomor'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* CSS Tambahan untuk Pagination */
        .pagination { display: flex; justify-content: flex-end; list-style: none; padding: 0; margin-top: 20px; gap: 5px; }
        .pagination li a { padding: 8px 12px; border: 1px solid #ddd; color: #333; text-decoration: none; border-radius: 4px; background: white; font-size: 14px; }
        .pagination li a:hover { background-color: #f0f0f0; }
        .pagination li.active a { background-color: var(--primary, #007bff); color: white; border-color: var(--primary, #007bff); }
        .pagination li.disabled a { color: #ccc; pointer-events: none; background: #f9f9f9; }

        /* CSS Tambahan untuk Tombol Download File */
        .btn-download {
            background-color: #17a2b8; color: white; padding: 4px 8px;
            border-radius: 4px; text-decoration: none; font-size: 11px;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-download:hover { background-color: #138496; }
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
                <li><a href="manage_orders.php" class="active"><i class="ri-dashboard-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-shopping-bag-3-line"></i> Data Barang ATK</a></li>
                <li><a href="activity_logs.php"><i class="ri-history-line"></i> Log Aktivitas</a></li>
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
                <h2 style="color: var(--primary); font-size: 18px; margin:0;">Kelola Pesanan</h2>
                <small style="color:#888;">Halaman <?= $paging['halaman'] ?> dari <?= $paging['total_halaman'] ?> | Total <?= $paging['total_data'] ?> Data</small>
            </div>

            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="50px">No</th> <th>User</th>
                            <th>Tipe</th>
                            <th>Detail</th>
                            <th>File</th> <th>Pembayaran</th> <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_assoc($orders)):
                            // Ambil style status dari Helper Class
                            $style = $manager->getStatusStyle($row['status']);
                            $namaUser = $row['username'] ?? 'User Tidak Dikenal';
                        ?>
                        <tr>
                            <td><?= $nomor++ ?></td>
                            
                            <td>
                                <strong><?= $namaUser ?></strong>
                                <br>
                                <small style="color:#999; font-size:11px;">(ID: <?= $row['user_id'] ?>)</small>
                            </td>

                            <td><span class="badge-type"><?= $row['type'] ?></span></td>
                            <td><?= substr($row['items'], 0, 40) ?>...</td>
                            
                            <td>
                                <?php if(!empty($row['file_name'])): ?>
                                    <a href="../uploads/<?= $row['file_name'] ?>" target="_blank" class="btn-download">
                                        <i class="ri-download-cloud-2-line"></i> Unduh
                                    </a>
                                <?php else: ?>
                                    <span style="color:#ccc;">-</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php 
                                    $pay = $row['payment_method'];
                                    if(empty($pay)) $pay = 'Cash'; 
                                    
                                    $colorPay = ($pay == 'QRIS') ? '#007bff' : '#28a745';
                                    $iconPay = ($pay == 'QRIS') ? 'ri-qr-code-line' : 'ri-money-dollar-circle-line';
                                ?>
                                <span style="color:<?= $colorPay ?>; font-weight:600; font-size:12px; display:flex; align-items:center; gap:5px;">
                                    <i class="<?= $iconPay ?>"></i> <?= $pay ?>
                                </span>
                            </td>
                            
                            <td><?= (function_exists('formatRupiah')) ? formatRupiah($row['total_price']) : 'Rp ' . number_format($row['total_price'],0,',','.') ?></td>
                            
                            <td>
                                <span class="badge <?= $style['class'] ?>"><?= $style['label'] ?></span>
                            </td>

                            <td>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                    
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="Pending" <?= ($style['raw'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="Processing" <?= ($style['raw'] == 'Processing' || $style['raw'] == 'Proses') ? 'selected' : '' ?>>Proses</option>
                                        <option value="Completed" <?= ($style['raw'] == 'Completed' || $style['raw'] == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                                        <option value="Cancelled" <?= ($style['raw'] == 'Cancelled' || $style['raw'] == 'Batal') ? 'selected' : '' ?>>Batal</option>
                                    </select>
                                    
                                    <a href="manage_orders.php?delete=<?= $row['id'] ?>" class="action-btn delete" onclick="return confirm('Hapus?')"><i class="ri-delete-bin-line"></i></a>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <nav>
                    <ul class="pagination">
                        <li class="<?php if($paging['halaman'] <= 1) { echo 'disabled'; } ?>">
                            <a href="<?php if($paging['halaman'] <= 1) { echo '#'; } else { echo "?halaman=".($paging['halaman'] - 1); } ?>">Previous</a>
                        </li>

                        <?php for($x = 1; $x <= $paging['total_halaman']; $x++): ?>
                            <li class="<?php if($paging['halaman'] == $x) { echo 'active'; } ?>">
                                <a href="?halaman=<?php echo $x; ?>"><?php echo $x; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="<?php if($paging['halaman'] >= $paging['total_halaman']) { echo 'disabled'; } ?>">
                            <a href="<?php if($paging['halaman'] >= $paging['total_halaman']) { echo '#'; } else { echo "?halaman=".($paging['halaman'] + 1); } ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>