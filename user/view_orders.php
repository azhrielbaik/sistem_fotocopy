<?php 
session_start();
include '../includes/config.php'; 

// Cek Login User
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php"); exit();
}
$my_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Status Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .status-card { background: white; border: 1px solid #E5E7EB; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .timeline { display: flex; justify-content: space-between; position: relative; margin-top: 25px; padding: 0 20px; }
        .timeline::before { content: ''; position: absolute; top: 15px; left: 30px; right: 30px; height: 3px; background: #E5E7EB; z-index: 0; }
        .timeline-step { position: relative; z-index: 1; text-align: center; background: white; padding: 0 10px; }
        .step-circle { width: 30px; height: 30px; background: #E5E7EB; color: #999; border-radius: 50%; margin: 0 auto 5px; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .timeline-step.active .step-circle { background: var(--primary); color: white; }
        .timeline-step.finish .step-circle { background: #10B981; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-fill" style="font-size: 32px;"></i>
                <div><h3 style="margin:0;">Dashboard</h3><small>PrintCopy Pro</small></div>
            </div>
            <ul class="menu">
                <li><a href="dashboard.php"><i class="ri-home-4-line"></i> Beranda</a></li>
                <li><a href="place_order.php"><i class="ri-shopping-cart-2-line"></i> Buat Pesanan</a></li>
                <li><a href="view_orders.php" class="active"><i class="ri-file-list-3-line"></i> Status Pesanan</a></li>
                <li><a href="rate_order.php"><i class="ri-star-line"></i> Beri Rating</a></li>
                <li><a href="profile.php"><i class="ri-user-settings-line"></i> Profil Saya</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <h4 style="margin:0;"><?= $_SESSION['username'] ?></h4>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        <div class="header"><h4 style="color: var(--primary);">Status Pesanan</h4></div>

        <?php
        $q = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='$my_id' ORDER BY created_at DESC");
        if(mysqli_num_rows($q) == 0) echo "<div style='text-align:center; margin-top:50px;'>Belum ada pesanan.</div>";

        while($r = mysqli_fetch_assoc($q)):
            $s = $r['status'];
            $badgeClass = 'bg-pending'; $textShow = 'Pending';
            $step1='active'; $step2=''; $step3='';

            if($s == 'Diproses') { $badgeClass = 'bg-process'; $textShow = 'Diproses'; $step2='active'; }
            if($s == 'Selesai' || $s == 'Completed') { 
                $badgeClass = 'bg-success'; $textShow = 'Selesai'; 
                $step1='finish'; $step2='finish'; $step3='finish'; 
            }
        ?>
        <div class="status-card">
            <div style="display:flex; justify-content:space-between;">
                <div>
                    <h4 style="margin:0;"><?= $r['id'] ?></h4>
                    <p style="margin:5px 0; font-size:13px;"><?= $r['items'] ?></p>
                    <small><?= $r['created_at'] ?></small>
                </div>
                <div style="text-align:right;">
                    <span class="badge <?= $badgeClass ?>"><?= $textShow ?></span>
                    <div style="margin-top:5px; font-weight:bold;"><?= formatRupiah($r['total_price']) ?></div>
                </div>
            </div>
            <div class="timeline">
                <div class="timeline-step <?= $step1 ?>"><div class="step-circle">1</div><small>Pending</small></div>
                <div class="timeline-step <?= $step2 ?>"><div class="step-circle">2</div><small>Diproses</small></div>
                <div class="timeline-step <?= $step3 ?>"><div class="step-circle">3</div><small>Selesai</small></div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>