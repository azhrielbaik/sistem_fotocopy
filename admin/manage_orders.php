<?php
session_start();
// Cek Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit();
}

include '../includes/config.php';

// --- FUNGSI BANTUAN: AMBIL ID ADMIN DENGAN CARA APAPUN ---
function getAdminId($conn) {
    if (isset($_SESSION['id'])) return $_SESSION['id'];
    if (isset($_SESSION['user_id'])) return $_SESSION['user_id'];
    if (isset($_SESSION['id_user'])) return $_SESSION['id_user'];

    if (isset($_SESSION['username'])) {
        $user = $_SESSION['username'];
        $q = mysqli_query($conn, "SELECT id FROM users WHERE username = '$user'");
        if ($row = mysqli_fetch_assoc($q)) {
            return $row['id'];
        }
    }
    return 0;
}
// -----------------------------------------------------------


// --- BAGIAN 1: LOGIKA UPDATE & DELETE (+ FITUR LOG) ---

// A. JIKA TOMBOL UPDATE DITEKAN
if(isset($_POST['update_status'])) {
    $id = $_POST['order_id'];
    $stat = $_POST['status']; 
    
    // 1. Update data di tabel orders
    mysqli_query($conn, "UPDATE orders SET status='$stat' WHERE id='$id'");
    
    // 2. CATAT KE LOG AKTIVITAS
    $admin_id = getAdminId($conn);
    $action_log = "Mengubah status Pesanan #$id menjadi $stat";
    
    $query_log = "INSERT INTO activity_logs (user_id, action, created_at) 
                  VALUES ('$admin_id', '$action_log', NOW())";
    mysqli_query($conn, $query_log);
    
    echo "<script>window.location='manage_orders.php';</script>";
}

// B. JIKA TOMBOL DELETE DITEKAN
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // 1. Hapus data dari tabel orders
    mysqli_query($conn, "DELETE FROM orders WHERE id='$id'");

    // 2. CATAT KE LOG AKTIVITAS
    $admin_id = getAdminId($conn);
    $action_log = "Menghapus Pesanan #$id";
    
    $query_log = "INSERT INTO activity_logs (user_id, action, created_at) 
                  VALUES ('$admin_id', '$action_log', NOW())";
    mysqli_query($conn, $query_log);

    echo "<script>window.location='manage_orders.php';</script>";
}

// --- BAGIAN 2: LOGIKA PAGINATION ---
$batas = 5; // Tampilkan 5 data per halaman
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// Hitung Total Data Pesanan
$previous_query = "SELECT COUNT(*) as total FROM orders";
$data_count = mysqli_query($conn, $previous_query);
$jumlah_data = mysqli_fetch_assoc($data_count)['total'];
$total_halaman = ceil($jumlah_data / $batas);

// Variabel Penomoran Urut
$nomor = $halaman_awal + 1;
// ------------------------------------
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
                <li><a href="manage_orders.php" class="active"><i class="ri-dashboard-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-shopping-bag-3-line"></i> Data Barang ATK</a></li>
                <li><a href="charts.php"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="activity_logs.php"><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php"><i class="ri-star-line"></i> Ulasan User</a></li>
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
                <small style="color:#888;">Halaman <?= $halaman ?> dari <?= $total_halaman ?> | Total <?= $jumlah_data ?> Data</small>
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
                        // --- BAGIAN 3: QUERY UTAMA DENGAN LIMIT ---
                        $query = "SELECT orders.*, users.username 
                                  FROM orders 
                                  LEFT JOIN users ON orders.user_id = users.id 
                                  ORDER BY orders.created_at DESC 
                                  LIMIT $halaman_awal, $batas";
                                  
                        $q = mysqli_query($conn, $query);
                        
                        while($row = mysqli_fetch_assoc($q)):
                            
                            // Logika Status
                            $statusDB = $row['status'];
                            if(empty($statusDB)) $statusDB = 'Pending';

                            $statusClass = 'bg-pending';
                            $statusLabel = 'Pending';

                            if($statusDB == 'Processing' || $statusDB == 'Proses') { 
                                $statusClass = 'bg-process';
                                $statusLabel = 'Diproses'; 
                            } 
                            elseif($statusDB == 'Completed' || $statusDB == 'Selesai') { 
                                $statusClass = 'bg-success';
                                $statusLabel = 'Selesai'; 
                            }
                            elseif($statusDB == 'Cancelled' || $statusDB == 'Batal') {
                                $statusClass = 'bg-danger';
                                $statusLabel = 'Dibatalkan';
                            }

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
                                    if(empty($pay)) $pay = 'Cash'; // Default Cash untuk data lama
                                    
                                    $colorPay = ($pay == 'QRIS') ? '#007bff' : '#28a745';
                                    $iconPay = ($pay == 'QRIS') ? 'ri-qr-code-line' : 'ri-money-dollar-circle-line';
                                ?>
                                <span style="color:<?= $colorPay ?>; font-weight:600; font-size:12px; display:flex; align-items:center; gap:5px;">
                                    <i class="<?= $iconPay ?>"></i> <?= $pay ?>
                                </span>
                            </td>
                            
                            <td><?= (function_exists('formatRupiah')) ? formatRupiah($row['total_price']) : 'Rp ' . number_format($row['total_price'],0,',','.') ?></td>
                            
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>

                            <td>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                    
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="Pending" <?= ($statusDB == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="Processing" <?= ($statusDB == 'Processing' || $statusDB == 'Proses') ? 'selected' : '' ?>>Proses</option>
                                        <option value="Completed" <?= ($statusDB == 'Completed' || $statusDB == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                                        <option value="Cancelled" <?= ($statusDB == 'Cancelled' || $statusDB == 'Batal') ? 'selected' : '' ?>>Batal</option>
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
                        <li class="<?php if($halaman <= 1) { echo 'disabled'; } ?>">
                            <a href="<?php if($halaman <= 1) { echo '#'; } else { echo "?halaman=".($halaman - 1); } ?>">Previous</a>
                        </li>

                        <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                            <li class="<?php if($halaman == $x) { echo 'active'; } ?>">
                                <a href="?halaman=<?php echo $x; ?>"><?php echo $x; ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="<?php if($halaman >= $total_halaman) { echo 'disabled'; } ?>">
                            <a href="<?php if($halaman >= $total_halaman) { echo '#'; } else { echo "?halaman=".($halaman + 1); } ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>