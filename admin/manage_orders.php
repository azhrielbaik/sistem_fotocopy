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
                        $q = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
                        while($row = mysqli_fetch_assoc($q)):
                            $statusClass = 'bg-pending';
                            if($row['status'] == 'Diproses') $statusClass = 'bg-process';
                            if($row['status'] == 'Selesai' || $row['status'] == 'Completed') $statusClass = 'bg-success';
                        ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td>User ID: <?= $row['user_id'] ?></td>
                            <td><span class="badge-type"><?= $row['type'] ?></span></td>
                            <td><?= substr($row['items'], 0, 40) ?>...</td>
                            <td><?= formatRupiah($row['total_price']) ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= ($row['status']=='Completed')?'Selesai':$row['status'] ?></span></td>
                            <td>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                    
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="Pending" <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                                        <option value="Diproses" <?= $row['status']=='Diproses'?'selected':'' ?>>Proses</option>
                                        <option value="Completed" <?= ($row['status']=='Selesai'||$row['status']=='Completed')?'selected':'' ?>>Selesai</option>
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