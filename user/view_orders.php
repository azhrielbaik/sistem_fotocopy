<?php include '../includes/config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Status Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* Style Tambahan Khusus Timeline Status */
        .status-card {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .timeline {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 25px;
            padding: 0 20px;
        }
        .timeline::before {
            content: ''; position: absolute; top: 15px; left: 30px; right: 30px;
            height: 3px; background: #E5E7EB; z-index: 0;
        }
        .timeline-step {
            position: relative; z-index: 1; text-align: center; background: white; padding: 0 10px;
        }
        .step-circle {
            width: 30px; height: 30px; background: #E5E7EB; color: #999;
            border-radius: 50%; margin: 0 auto 5px;
            display: flex; align-items: center; justify-content: center; font-weight: bold;
        }
        /* State Active */
        .timeline-step.active .step-circle { background: var(--primary); color: white; }
        .timeline-step.active small { color: var(--primary); font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-fill" style="font-size: 32px;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 18px;">Dashboard</h3>
                    <small style="opacity: 0.8; font-size: 12px;">PrintCopy Pro</small>
                </div>
            </div>
            <ul class="menu">
                <li><a href="dashboard.php"><i class="ri-home-4-line"></i> Beranda</a></li>
                <li><a href="place_order.php"><i class="ri-shopping-cart-2-line"></i> Buat Pesanan</a></li>
                <li><a href="view_orders.php" class="active"><i class="ri-file-list-3-line"></i> Status Pesanan</a></li>
                <li><a href="rate_order.php"><i class="ri-star-line"></i> Beri Rating</a></li>
                <li><a href="profile.php" class=><i class="ri-user-settings-line"></i> Profil Saya</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;">User Demo</h4>
                <small style="opacity: 0.8;">user@example.com</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <div class="header" style="justify-content: center; position: relative;">
            <a href="dashboard.php" style="position: absolute; left: 0; color: #333; text-decoration: none;">
                <i class="ri-close-line" style="font-size: 24px;"></i>
            </a>
            <h4 style="color: var(--primary);">Status Pesanan</h4>
        </div>

        <?php
        // PERBAIKAN 1: Menggunakan user_id='1' bukan customer_name
        // PERBAIKAN 2: Menggunakan nama kolom sesuai database (id, items, total_price)
        $q = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='1' ORDER BY created_at DESC");
        
        // Cek jika tidak ada pesanan
        if(mysqli_num_rows($q) == 0) {
            echo "<div style='text-align:center; margin-top:50px; color:#999;'>Belum ada pesanan aktif.</div>";
        }

        while($r = mysqli_fetch_assoc($q)):
            // Logika untuk Timeline Status
            $s = $r['status'];
            $step1 = 'active'; // Pending selalu aktif
            $step2 = ($s == 'Diproses' || $s == 'Selesai' || $s == 'Completed') ? 'active' : '';
            $step3 = ($s == 'Selesai' || $s == 'Completed') ? 'active' : '';
        ?>
        
        <div class="status-card">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="display: flex; gap: 15px;">
                    <div style="width: 40px; height: 40px; background: #EFF6FF; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                        <i class="<?= ($r['type'] == 'ATK') ? 'ri-box-3-fill' : 'ri-printer-fill' ?>" style="font-size: 20px;"></i>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: var(--text-dark);"><?= $r['id'] ?></h4>
                        <p style="margin: 5px 0 0; color: #6B7280; font-size: 14px;"><?= $r['items'] ?></p>
                        <small style="color: #9CA3AF;"><?= $r['created_at'] ?></small>
                    </div>
                </div>
                
                <div style="text-align: right;">
                    <span class="badge <?= ($s=='Pending')?'bg-pending':(($s=='Diproses')?'bg-process':'bg-success') ?>">
                        <?= $s ?>
                    </span>
                    <div style="margin-top: 10px; font-weight: bold; color: var(--primary);">
                        <?= formatRupiah($r['total_price']) ?>
                    </div>
                </div>
            </div>

            <div class="timeline">
                <div class="timeline-step <?= $step1 ?>">
                    <div class="step-circle"><i class="ri-file-list-2-line"></i></div>
                    <small>Pending</small>
                </div>
                <div class="timeline-step <?= $step2 ?>">
                    <div class="step-circle"><i class="ri-loader-2-line"></i></div>
                    <small>Diproses</small>
                </div>
                <div class="timeline-step <?= $step3 ?>">
                    <div class="step-circle"><i class="ri-check-double-line"></i></div>
                    <small>Selesai</small>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

    </div>

</body>
</html>