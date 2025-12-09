<?php 
session_start();
include '../includes/config.php'; 

class OrderHistory {
    private $conn;
    private $userId;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkLogin();
        $this->userId = $_SESSION['user_id'];
    }

    // 1. Cek Login
    private function checkLogin() {
        if(!isset($_SESSION['user_id'])){
            header("Location: ../login.php"); 
            exit();
        }
    }

    // 2. Ambil Semua Pesanan User
    public function getUserOrders() {
        $query = "SELECT * FROM orders WHERE user_id='$this->userId' ORDER BY created_at DESC";
        return mysqli_query($this->conn, $query);
    }

    // 3. Logika Penentuan Status & Timeline (Dipisah biar rapi)
    public function getOrderStatusState($rawStatus) {
        // Ambil status, default Pending jika kosong
        $s = empty($rawStatus) ? 'Pending' : $rawStatus;

        // Default State (Pending)
        $state = [
            'badgeClass' => 'bg-pending',
            'textShow'   => 'Pending',
            'step1'      => 'active',
            'step2'      => '',
            'step3'      => ''
        ];

        // Cek Status PROCESSING
        if($s == 'Processing') { 
            $state['badgeClass'] = 'bg-process'; 
            $state['textShow']   = 'Diproses';
            $state['step1']      = 'finish'; // Step 1 lewat (Hijau)
            $state['step2']      = 'active'; // Step 2 jalan (Biru)
        }

        // Cek Status COMPLETED
        else if($s == 'Completed' || $s == 'Selesai') { 
            $state['badgeClass'] = 'bg-success'; 
            $state['textShow']   = 'Selesai';
            $state['step1']      = 'finish'; 
            $state['step2']      = 'finish'; 
            $state['step3']      = 'finish'; 
        }
        
        // Cek Status CANCELLED
        else if($s == 'Cancelled' || $s == 'Batal') {
            $state['badgeClass'] = 'bg-danger';
            $state['textShow']   = 'Dibatalkan';
            // Matikan stepper
            $state['step1'] = ''; 
            $state['step2'] = ''; 
            $state['step3'] = '';
        }

        return $state;
    }
}

// --- EKSEKUSI PROGRAM ---
$history = new OrderHistory($conn);
$orders = $history->getUserOrders();
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
        /* Warna Active (Sedang berjalan) */
        .timeline-step.active .step-circle { background: var(--primary); color: white; border: 2px solid var(--primary); }
        /* Warna Finish (Sudah lewat) */
        .timeline-step.finish .step-circle { background: #10B981; color: white; border: 2px solid #10B981; }
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

        <?php
        if(mysqli_num_rows($orders) == 0): 
            echo "<div style='text-align:center; margin-top:50px; color:#888;'>Belum ada pesanan.</div>";
        else:
            while($r = mysqli_fetch_assoc($orders)):
                // --- PANGGIL FUNGSI LOGIKA STATUS DARI CLASS ---
                $statusState = $history->getOrderStatusState($r['status']);
        ?>
        <div class="status-card">
            <div style="display:flex; justify-content:space-between;">
                <div>
                    <h4 style="margin:0;">Order #<?= $r['id'] ?></h4>
                    <p style="margin:5px 0; font-size:13px; color:#555;"><?= substr($r['items'], 0, 50) ?>...</p>
                    <small style="color:#888;"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></small>
                </div>
                <div style="text-align:right;">
                    <span class="badge <?= $statusState['badgeClass'] ?>"><?= $statusState['textShow'] ?></span>
                    <div style="margin-top:5px; font-weight:bold; color:var(--primary);">
                        <?= (function_exists('formatRupiah')) ? formatRupiah($r['total_price']) : 'Rp ' . number_format($r['total_price'],0,',','.') ?>
                    </div>
                </div>
            </div>
            
            <?php if($statusState['textShow'] != 'Dibatalkan'): ?>
            <div class="timeline">
                <div class="timeline-step <?= $statusState['step1'] ?>">
                    <div class="step-circle"><?= ($statusState['step1']=='finish')?'&#10003;':'1' ?></div>
                    <small>Pending</small>
                </div>
                <div class="timeline-step <?= $statusState['step2'] ?>">
                    <div class="step-circle"><?= ($statusState['step2']=='finish')?'&#10003;':'2' ?></div>
                    <small>Diproses</small>
                </div>
                <div class="timeline-step <?= $statusState['step3'] ?>">
                    <div class="step-circle"><?= ($statusState['step3']=='finish')?'&#10003;':'3' ?></div>
                    <small>Selesai</small>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        <?php 
            endwhile; 
        endif;
        ?>
    </div>
</body>
</html>