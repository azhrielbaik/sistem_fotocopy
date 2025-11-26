<?php
include '../includes/config.php';

// UPDATE STATUS
if(isset($_POST['update_status'])) {
    $id = $_POST['order_id'];
    $stat = $_POST['status'];
    mysqli_query($conn, "UPDATE orders SET status='$stat' WHERE id='$id'");
    // Refresh otomatis agar perubahan langsung terlihat
    echo "<script>window.location='manage_orders.php';</script>";
}

// DELETE PESANAN
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM orders WHERE id='$id'");
    echo "<script>window.location='manage_orders.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pesanan - Admin</title>
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
                    <small style="opacity: 0.7; font-size: 11px;">PrintCopy Pro</small>
                </div>
            </div>

            <ul class="menu">
                <li><a href="manage_orders.php" class="active"><i class="ri-shopping-bag-3-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-archive-line"></i> Data Barang ATK</a></li>
                <li><a href="charts.php"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
            </ul>
        </div>

        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <small style="color: rgba(255,255,255,0.6);">Logged in as:</small>
                <h4 style="margin: 0; font-weight: 600;">Admin</h4>
                <small style="opacity: 0.8;">admin@printcopy.com</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="card" style="padding: 0; overflow: hidden; border: 1px solid #E5E7EB; box-shadow: none;">
            
            <div style="padding: 25px;">
                <div class="page-header-row">
                    <div>
                        <h2 style="color: var(--primary); font-size: 18px; margin-bottom: 5px;">Kelola Pesanan</h2>
                        <p style="color: #6B7280; font-size: 13px; margin: 0;">Kelola dan update status pesanan pelanggan</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn-outline-gray">Filter</button>
                        <button class="btn-blue">Export Data</button>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Tipe</th>
                                <th>Item Detail</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
                            while($row = mysqli_fetch_assoc($q)):
                                // Logic Warna Status (Handle 'Selesai' atau 'Completed')
                                $statusClass = 'bg-pending';
                                if($row['status'] == 'Diproses') $statusClass = 'bg-process';
                                if($row['status'] == 'Selesai' || $row['status'] == 'Completed') $statusClass = 'bg-success';
                                
                                $custName = ($row['user_id'] == 1) ? "User Demo" : "Guest " . $row['user_id'];
                            ?>
                            <tr>
                                <td style="font-weight: 500;"><?= $row['id'] ?></td>
                                <td><div style="font-weight: 500;"><?= $custName ?></div></td>
                                <td><span class="badge-type"><?= $row['type'] ?></span></td>
                                <td style="max-width: 250px; font-size: 13px; color: #555;">
                                    <?= substr($row['items'], 0, 50) . (strlen($row['items']) > 50 ? '...' : '') ?>
                                </td>
                                <td style="font-weight: 600;"><?= formatRupiah($row['total_price']) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= ($row['status'] == 'Completed') ? 'Selesai' : $row['status'] ?>
                                    </span>
                                </td>
                                <td style="color: #6B7280; font-size: 13px;">
                                    <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: flex; align-items: center; gap: 8px;">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                        
                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                            <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                                            <option value="Diproses" <?= $row['status']=='Diproses'?'selected':'' ?>>Proses</option>
                                            
                                            <option value="Completed" <?= ($row['status']=='Selesai' || $row['status']=='Completed')?'selected':'' ?>>Selesai</option>
                                        </select>
                                        
                                        <button type="button" class="action-btn view"><i class="ri-eye-line"></i></button>
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
    </div>
</body>
</html>