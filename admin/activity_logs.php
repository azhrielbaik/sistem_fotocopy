<?php
session_start();
// Cek Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit();
}

include '../includes/config.php';

// --- BAGIAN 1: LOGIKA PAGINATION ---
$batas = 5; // <--- UBAH JADI 5 DATA PER HALAMAN
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// Hitung Total Data untuk menentukan jumlah halaman
$previous_query = "SELECT COUNT(*) as total FROM activity_logs";
$data_count = mysqli_query($conn, $previous_query);
$jumlah_data = mysqli_fetch_assoc($data_count)['total'];
$total_halaman = ceil($jumlah_data / $batas);

// Variabel Penomoran Urut (Agar Halaman 2 mulai dari angka 6, dst)
$nomor = $halaman_awal + 1;
// ------------------------------------
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
            background-color: var(--primary, #007bff); /* Mengambil var primary atau biru default */
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
                <small style="color:#888;">Halaman <?= $halaman ?> dari <?= $total_halaman ?> | Total <?= $jumlah_data ?> Data</small>
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
                        // --- BAGIAN 2: QUERY DIMODIFIKASI (Pakai LIMIT) ---
                        $query = "SELECT activity_logs.*, users.username, users.role 
                                  FROM activity_logs 
                                  LEFT JOIN users ON activity_logs.user_id = users.id 
                                  ORDER BY activity_logs.created_at DESC 
                                  LIMIT $halaman_awal, $batas";
                        
                        $q = mysqli_query($conn, $query);
                        
                        while($row = mysqli_fetch_assoc($q)):
                            
                            // LOGIKA STATUS ONLINE/OFFLINE
                            $currentUserId = $row['user_id'];
                            
                            // Query cek status terakhir user ini secara spesifik
                            $qStatus = mysqli_query($conn, "SELECT action FROM activity_logs WHERE user_id='$currentUserId' ORDER BY id DESC LIMIT 1");
                            $lastAction = mysqli_fetch_assoc($qStatus);
                            
                            $statusDotClass = 'offline';
                            $statusText = 'Offline';
                            
                            if ($lastAction && $lastAction['action'] == 'Login') {
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
                            <td><?= $nomor++ ?></td>
                            
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

                <nav>
                    <ul class="pagination">
                        <li class="<?php if($halaman <= 1) { echo 'disabled'; } ?>">
                            <a href="<?php if($halaman <= 1) { echo '#'; } else { echo "?halaman=".($halaman - 1); } ?>">Previous</a>
                        </li>

                        <?php 
                        // Logic agar nomor halaman tidak terlalu panjang (opsional, disederhanakan)
                        for($x = 1; $x <= $total_halaman; $x++): 
                        ?>
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