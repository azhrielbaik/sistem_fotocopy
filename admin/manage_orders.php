<?php
session_start();
// Cek Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit();
}

include '../includes/config.php';

// UPDATE STATUS
if(isset($_POST['update_status'])) {
    $id = $_POST['order_id'];
    $stat = $_POST['status']; 
    
    // Query update status
    mysqli_query($conn, "UPDATE orders SET status='$stat' WHERE id='$id'");
    
    echo "<script>window.location='manage_orders.php';</script>";
}

// DELETE
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM orders WHERE id='$id'");
    echo "<script>window.location='manage_orders.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-line" style="font-size: 28px;"></i>
                <div><h3 style="margin:0; font-size:16px;">Admin Panel</h3><small>PrintCopy Pro</small></div>
            </div>
            <ul class="menu">
                <li><a href="manage_orders.php" class="active"><i class="ri-shopping-bag-3-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-archive-line"></i> Data Barang ATK</a></li>
                <li><a href="charts.php"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="activity_logs.php" class=><i class="ri-history-line"></i> Log Aktivitas</a></li>
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
            <h2 style="color: var(--primary); font-size: 18px;">Kelola Pesanan</h2>
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr><th>ID</th><th>User</th><th>Tipe</th><th>Detail</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        // --- PERUBAHAN QUERY SQL DI SINI ---
                        // Menggabungkan tabel orders dan users untuk mengambil username
                        $query = "SELECT orders.*, users.username 
                                  FROM orders 
                                  LEFT JOIN users ON orders.user_id = users.id 
                                  ORDER BY orders.created_at DESC";
                                  
                        $q = mysqli_query($conn, $query);
                        
                        while($row = mysqli_fetch_assoc($q)):
                            
                            // 1. Logika Status (Tetap sama seperti sebelumnya)
                            $statusDB = $row['status'];
                            if(empty($statusDB)) $statusDB = 'Pending';

                            $statusClass = 'bg-pending';
                            $statusLabel = 'Pending';

                            if($statusDB == 'Processing') { 
                                $statusClass = 'bg-process';
                                $statusLabel = 'Diproses'; 
                            } 
                            elseif($statusDB == 'Completed' || $statusDB == 'Selesai') { 
                                $statusClass = 'bg-success';
                                $statusLabel = 'Selesai'; 
                            }
                            elseif($statusDB == 'Cancelled') {
                                $statusClass = 'bg-danger';
                                $statusLabel = 'Dibatalkan';
                            }

                            // 2. Ambil Nama User (Fallback jika null)
                            $namaUser = $row['username'] ?? 'User Tidak Dikenal';
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            
                            <td>
                                <strong><?= $namaUser ?></strong>
                                <br>
                                <small style="color:#999; font-size:11px;">(ID: <?= $row['user_id'] ?>)</small>
                            </td>

                            <td><span class="badge-type"><?= $row['type'] ?></span></td>
                            <td><?= substr($row['items'], 0, 40) ?>...</td>
                            
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
                                        <option value="Processing" <?= ($statusDB == 'Processing') ? 'selected' : '' ?>>Proses</option>
                                        <option value="Completed" <?= ($statusDB == 'Completed' || $statusDB == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                                        <option value="Cancelled" <?= ($statusDB == 'Cancelled') ? 'selected' : '' ?>>Batal</option>
                                    </select>
                                    
                                    <a href="manage_orders.php?delete=<?= $row['id'] ?>" class="action-btn delete" onclick="return confirm('Hapus?')"><i class="ri-delete-bin-line"></i></a>
                                </form>
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