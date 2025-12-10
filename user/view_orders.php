<?php 
session_start();
include '../includes/config.php'; 

// Class hanya untuk cek login awal
class OrderHistory {
    public function __construct() {
        if(!isset($_SESSION['user_id'])){
            header("Location: ../login.php"); 
            exit();
        }
    }
}
$history = new OrderHistory();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Status Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        /* Kartu Status */
        .status-card { 
            background: white; 
            border: 1px solid #E5E7EB; 
            border-radius: 16px; /* Lebih bulat */
            padding: 25px; 
            margin-bottom: 25px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .status-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Container Timeline */
        .timeline { 
            display: flex; 
            justify-content: space-between; 
            position: relative; 
            margin-top: 30px; 
            padding: 0 10px; 
        }
        
        /* Garis Abu-abu di Belakang */
        .timeline::before { 
            content: ''; 
            position: absolute; 
            top: 22px; /* Sesuaikan agar pas tengah lingkaran */
            left: 40px; 
            right: 40px; 
            height: 4px; 
            background: #F3F4F6; 
            z-index: 0; 
            border-radius: 4px;
        }

        /* Wrapper per Step */
        .timeline-step { 
            position: relative; 
            z-index: 1; 
            text-align: center; 
            padding: 0 10px; 
            width: 33%;
        }

        /* Lingkaran Ikon */
        .step-circle { 
            width: 45px; 
            height: 45px; 
            background: #F3F4F6; /* Warna default mati */
            color: #9CA3AF; 
            border-radius: 50%; 
            margin: 0 auto 10px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 20px; /* Ukuran Ikon */
            transition: all 0.4s ease;
            border: 3px solid white; /* Border putih agar garis belakang tertutup rapi */
        }

        /* Teks Label di Bawah Ikon */
        .timeline-step small {
            font-weight: 600;
            color: #9CA3AF;
            transition: color 0.3s;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }

        /* --- STATE 1: ACTIVE (Sedang Berjalan) --- */
        .timeline-step.active .step-circle { 
            background: white; 
            color: var(--primary); 
            border: 2px solid var(--primary); 
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2); /* Efek Glow Biru */
            animation: pulse-border 2s infinite;
        }
        .timeline-step.active small { color: var(--primary); }

        /* --- STATE 2: FINISH (Sudah Selesai) --- */
        .timeline-step.finish .step-circle { 
            background: #10B981; /* Hijau */
            color: white; 
            border: 2px solid #10B981; 
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
        }
        .timeline-step.finish small { color: #10B981; }

        /* Animasi Berdenyut untuk Active */
        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-fill" style="font-size: 32px;"></i>
                <div><h3 style="margin:0;">Dashboard</h3><small>Si-Foprint</small></div>
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
            <h4 style="margin:0;"><?= $_SESSION['username'] ?? 'User' ?></h4>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        <div class="header"><h4 style="color: var(--primary);">Status Pesanan</h4></div>

        <div id="orderListContainer">
            <div style="text-align:center; margin-top:50px; color:#888;">Memuat data...</div>
        </div>
        
    </div>

    <script>
        function loadOrderList() {
            fetch('get_order_list.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('orderListContainer').innerHTML = data;
                })
                .catch(error => console.error('Error fetching orders:', error));
        }

        // Load pertama kali
        document.addEventListener('DOMContentLoaded', loadOrderList);

        // Refresh setiap 3 detik
        setInterval(loadOrderList, 3000);
    </script>

</body>
</html>