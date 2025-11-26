<?php include '../includes/config.php'; ?>
<?php $userName = "User Demo"; $userEmail = "user@example.com"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - PrintCopy Pro</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
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
                <li><a href="dashboard.php" class="active"><i class="ri-home-4-line"></i> Beranda</a></li>
                <li><a href="place_order.php"><i class="ri-shopping-cart-2-line"></i> Buat Pesanan</a></li>
                <li><a href="view_orders.php"><i class="ri-file-list-3-line"></i> Status Pesanan</a></li>
                <li><a href="rate_order.php"><i class="ri-star-line"></i> Beri Rating</a></li>
                 <li><a href="profile.php" class=><i class="ri-user-settings-line"></i> Profil Saya</a></li>
            </ul>
        </div>

        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;">Selamat datang,</h4>
                <h4 style="margin: 0; font-weight: 600;"><?= $userName ?></h4>
                <small style="opacity: 0.8;"><?= $userEmail ?></small>
            </div>
            <a href="../logout.php" class="btn-logout">
                <i class="ri-logout-box-r-line"></i> Logout
            </a>
            <a href="#" style="display: block; text-align: center; color: white; font-size: 12px; margin-top: 10px; text-decoration: none; opacity: 0.8;">
                Kembali ke Landing
            </a>
        </div>
    </div>
    <div class="main-content" style="background-color: #F3F4F6;"> <div class="header" style="align-items: center; justify-content: space-between;">
            <i class="ri-close-line" style="font-size: 24px; color: #9CA3AF; cursor: pointer;"></i>
            <h4 style="color: var(--primary);">Beranda</h4>
            <div></div> </div>

        <div class="welcome-banner">
            <h2>Selamat Datang, <?= $userName ?>!</h2>
            <p>Kelola pesanan print, fotocopy, dan ATK Anda dengan mudah.</p>
        </div>

        <div class="dashboard-grid">
            
            <a href="place_order.php" class="dashboard-card">
                <div class="card-icon-wrapper">
                    <i class="ri-shopping-cart-2-fill"></i>
                </div>
                <h3>Buat Pesanan</h3>
                <p>Pesan layanan print, fotocopy, atau beli produk ATK</p>
            </a>

            <a href="view_orders.php" class="dashboard-card">
                <div class="card-icon-wrapper">
                    <i class="ri-file-text-fill"></i>
                </div>
                <h3>Status Pesanan</h3>
                <p>Lihat dan pantau status pesanan Anda</p>
            </a>

            <a href="rate_order.php" class="dashboard-card">
                <div class="card-icon-wrapper">
                    <i class="ri-star-smile-fill"></i>
                </div>
                <h3>Beri Rating</h3>
                <p>Berikan penilaian untuk layanan kami</p>
            </a>

        </div>
    </div>
    </body>
</html>