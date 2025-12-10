<?php
// user/get_order_list.php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['user_id'])){ exit(); }
$userId = $_SESSION['user_id'];

// --- 1. CONFIG PAGINATION (UBAH DISINI) ---
$limit = 2; // MENAMPILKAN 2 PESANAN PER HALAMAN
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// --- FUNGSI BANTUAN ---
function getOrderStatusState($rawStatus) {
    $s = empty($rawStatus) ? 'Pending' : $rawStatus;
    $state = ['badgeClass' => 'bg-pending', 'textShow' => 'Pending', 'step1' => 'active', 'step2' => '', 'step3' => ''];

    if($s == 'Processing') { 
        $state['badgeClass'] = 'bg-process'; $state['textShow'] = 'Diproses';
        $state['step1'] = 'finish'; $state['step2'] = 'active'; 
    }
    elseif($s == 'Completed' || $s == 'Selesai') { 
        $state['badgeClass'] = 'bg-success'; $state['textShow'] = 'Selesai';
        $state['step1'] = 'finish'; $state['step2'] = 'finish'; $state['step3'] = 'finish'; 
    }
    elseif($s == 'Cancelled' || $s == 'Batal') {
        $state['badgeClass'] = 'bg-danger'; $state['textShow'] = 'Dibatalkan';
        $state['step1'] = ''; $state['step2'] = ''; $state['step3'] = '';
    }
    return $state;
}

function formatRupiahSimple($angka){ return 'Rp ' . number_format($angka,0,',','.'); }

// --- 2. QUERY UTAMA DENGAN LIMIT ---
$query = "SELECT * FROM orders WHERE user_id='$userId' ORDER BY created_at DESC LIMIT $offset, $limit";
$orders = mysqli_query($conn, $query);

// --- 3. HITUNG TOTAL HALAMAN ---
$queryTotal = "SELECT COUNT(*) as total FROM orders WHERE user_id='$userId'";
$resultTotal = mysqli_query($conn, $queryTotal);
$rowTotal = mysqli_fetch_assoc($resultTotal);
$totalRecords = $rowTotal['total'];
$totalPages = ceil($totalRecords / $limit);


// --- TAMPILAN ---
if(mysqli_num_rows($orders) == 0): 
    if($page == 1) {
        echo "<div style='text-align:center; margin-top:50px; color:#9CA3AF; display:flex; flex-direction:column; align-items:center;'>
                <i class='ri-file-list-3-line' style='font-size:48px; margin-bottom:10px;'></i>
                <p>Belum ada pesanan.</p>
              </div>";
    } else {
        echo "<div style='text-align:center; padding:30px; color:#888;'>Tidak ada data di halaman ini.</div>";
    }
else:
    while($r = mysqli_fetch_assoc($orders)):
        $statusState = getOrderStatusState($r['status']);
?>
    <div class="status-card">
        <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
            <div>
                <h4 style="margin:0; font-size:16px;">Order #<?= $r['id'] ?></h4>
                <p style="margin:5px 0; font-size:13px; color:#6B7280;"><?= substr($r['items'], 0, 50) ?>...</p>
                <small style="color:#9CA3AF; font-size:11px;">
                    <i class="ri-time-line" style="vertical-align:middle;"></i> 
                    <?= date('d M Y H:i', strtotime($r['created_at'])) ?>
                </small>
            </div>
            <div style="text-align:right;">
                <span class="badge <?= $statusState['badgeClass'] ?>"><?= $statusState['textShow'] ?></span>
                <div style="margin-top:8px; font-weight:700; color:var(--primary); font-size:16px;">
                    <?= (function_exists('formatRupiah')) ? formatRupiah($r['total_price']) : formatRupiahSimple($r['total_price']) ?>
                </div>
            </div>
        </div>
        
        <?php if($statusState['textShow'] != 'Dibatalkan'): ?>
        <div class="timeline">
            <div class="timeline-step <?= $statusState['step1'] ?>">
                <div class="step-circle">
                    <?php if($statusState['step1']=='finish'): ?><i class="ri-check-line"></i><?php else: ?><i class="ri-file-list-3-line"></i><?php endif; ?>
                </div>
                <small>Pesanan Diterima</small>
            </div>

            <div class="timeline-step <?= $statusState['step2'] ?>">
                <div class="step-circle">
                    <?php if($statusState['step2']=='finish'): ?><i class="ri-check-line"></i><?php else: ?><i class="ri-printer-cloud-line"></i><?php endif; ?>
                </div>
                <small>Sedang Diproses</small>
            </div>

            <div class="timeline-step <?= $statusState['step3'] ?>">
                <div class="step-circle">
                    <?php if($statusState['step3']=='finish'): ?><i class="ri-shopping-bag-3-fill"></i><?php else: ?><i class="ri-shopping-bag-3-line"></i><?php endif; ?>
                </div>
                <small>Siap Diambil</small>
            </div>
        </div>
        <?php else: ?>
            <div style="background:#FEF2F2; color:#DC2626; padding:10px; border-radius:8px; margin-top:15px; text-align:center; font-size:13px; border:1px solid #FECACA;">
                <i class="ri-close-circle-line" style="vertical-align:middle;"></i> Pesanan ini telah dibatalkan.
            </div>
        <?php endif; ?>
    </div>
<?php 
    endwhile; 

    // --- 4. RENDER TOMBOL PAGINATION ---
    if($totalPages > 1):
?>
    <div class="pagination-container">
        <?php if($page > 1): ?>
            <button class="page-btn" onclick="changePage(<?= $page - 1 ?>)"> < Prev </button>
        <?php endif; ?>

        <span class="page-btn active"><?= $page ?> / <?= $totalPages ?></span>

        <?php if($page < $totalPages): ?>
            <button class="page-btn" onclick="changePage(<?= $page + 1 ?>)"> Next > </button>
        <?php endif; ?>
    </div>
<?php 
    endif;
endif;
?>