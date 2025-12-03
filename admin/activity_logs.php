<?php
session_start();
// Cek Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit();
}

include '../includes/config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Log Aktivitas User</title>
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
                <li><a href="manage_orders.php"><i class="ri-shopping-bag-3-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-archive-line"></i> Data Barang ATK</a></li>
                <li><a href="charts.php"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="activity_logs.php" class="active"><i class="ri-history-line"></i> Log Aktivitas</a></li>
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
                <small style="color:#888;">Memantau siapa yang Login & Logout</small>
            </div>
            
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Aktivitas</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query Join ke tabel users untuk ambil nama
                        $query = "SELECT activity_logs.*, users.username, users.role 
                                  FROM activity_logs 
                                  LEFT JOIN users ON activity_logs.user_id = users.id 
                                  ORDER BY activity_logs.created_at DESC LIMIT 50";
                                  
                        $q = mysqli_query($conn, $query);
                        
                        while($row = mysqli_fetch_assoc($q)):
                            // Tentukan warna badge berdasarkan aksi
                            $badgeClass = ($row['action'] == 'Login') ? 'bg-success' : 'bg-danger';
                            
                            // Format Tanggal Indo
                            $tanggal = date('d M Y, H:i', strtotime($row['created_at']));
                            
                            // Nama User (jika user dihapus tetap tampil ID)
                            $namaUser = $row['username'] ?? '<span style="color:red;">User Dihapus</span>';
                            $roleUser = $row['role'] ?? '-';
                        ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <strong><?= $namaUser ?></strong><br>
                                <small style="color:#888;">Role: <?= ucfirst($roleUser) ?></small>
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
            </div>
        </div>
    </div>
</body>
</html>