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
    </style>
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
                            <th>ID Log</th>
                            <th>User & Status</th>
                            <th>Aktivitas</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 1. QUERY UTAMA: Ambil data log digabung dengan user
                        $query = "SELECT activity_logs.*, users.username, users.role 
                                  FROM activity_logs 
                                  LEFT JOIN users ON activity_logs.user_id = users.id 
                                  ORDER BY activity_logs.created_at DESC LIMIT 50";
                        $q = mysqli_query($conn, $query);
                        
                        while($row = mysqli_fetch_assoc($q)):
                            
                            // 2. LOGIKA STATUS ONLINE/OFFLINE
                            // Kita cek log TERAKHIR dari user ini.
                            // Jika log terakhirnya 'Login', berarti dia masih Online.
                            
                            $currentUserId = $row['user_id'];
                            
                            // Query cek status terakhir user ini secara spesifik
                            $qStatus = mysqli_query($conn, "SELECT action FROM activity_logs WHERE user_id='$currentUserId' ORDER BY id DESC LIMIT 1");
                            $lastAction = mysqli_fetch_assoc($qStatus);
                            
                            $statusDotClass = 'offline';
                            $statusText = 'Offline';
                            
                            if ($lastAction['action'] == 'Login') {
                                $statusDotClass = 'online';
                                $statusText = 'Online';
                            }
                            
                            // Warna Badge Aktivitas
                            $badgeClass = ($row['action'] == 'Login') ? 'bg-success' : 'bg-danger';
                            $tanggal = date('d M Y, H:i', strtotime($row['created_at']));
                            $namaUser = $row['username'] ?? '<span style="color:red;">User Dihapus</span>';
                            $roleUser = $row['role'] ?? '-';
                        ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <div style="display: flex; align-items: center;">
                                    <span class="status-dot <?= $statusDotClass ?>" title="Status Saat Ini: <?= $statusText ?>"></span>
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
            </div>
        </div>
    </div>
</body>
</html>