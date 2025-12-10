<?php
session_start();
include '../includes/config.php';

class ReviewManager {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkAuth();
    }

    private function checkAuth() {
        if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
            // Jika request via AJAX, kirim status 401
            if(isset($_GET['live_update'])) { http_response_code(401); exit; }
            header("Location: ../login.php"); 
            exit();
        }
    }

    public function getAllReviews() {
        $query = "SELECT orders.*, users.username 
                  FROM orders 
                  LEFT JOIN users ON orders.user_id = users.id 
                  WHERE orders.rating IS NOT NULL AND orders.rating > 0 
                  ORDER BY orders.created_at DESC";
        return mysqli_query($this->conn, $query);
    }
}

// --- FUNGSI BANTUAN UNTUK MENCETAK BARIS TABEL (Agar tidak menulis kode HTML 2x) ---
function renderTableRows($reviews) {
    if(mysqli_num_rows($reviews) == 0){
        echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#999;'>Belum ada ulasan masuk.</td></tr>";
        return;
    }

    while($row = mysqli_fetch_assoc($reviews)):
        $namaUser = $row['username'] ?? 'User Dihapus';
        $rating = $row['rating'];
        $review = $row['review'];
        if(empty($review)) $review = "<em style='color:#ccc;'>Tidak ada komentar</em>";
        
        // Logika Bintang
        $stars = "";
        for($i=1; $i<=5; $i++){
            $class = ($i <= $rating) ? 'star-gold' : 'star-gray';
            $stars .= "<i class='ri-star-fill $class'></i>";
        }
    ?>
    <tr>
        <td>#<?= $row['id'] ?></td>
        <td>
            <strong><?= $namaUser ?></strong><br>
            <small style="color:#888;">ID: <?= $row['user_id'] ?></small>
        </td>
        <td>
            <div style="display:flex;">
                <?= $stars ?>
                <span style="margin-left:5px; font-weight:bold; font-size:12px;">(<?= $rating ?>)</span>
            </div>
        </td>
        <td>
            <div style="background:#f8f9fa; padding:10px; border-radius:8px; font-size:13px; color:#444;">
                "<?= $review ?>"
            </div>
        </td>
        <td>
            <small style="display:block; color:#666; margin-bottom:3px;"><?= substr($row['items'], 0, 30) ?>...</small>
            <span class="badge bg-success" style="font-size:10px;">Selesai</span>
            <br>
            <small style="color:#999; font-size:10px;"><?= date('d M Y', strtotime($row['created_at'])) ?></small>
        </td>
    </tr>
    <?php endwhile;
}

// --- LOGIKA UTAMA ---
$manager = new ReviewManager($conn);

// [BAGIAN PENTING 1] CEK REQUEST AJAX
// Jika Javascript meminta data, kita hanya berikan isi tabelnya saja, lalu STOP.
if (isset($_GET['live_update'])) {
    $reviews = $manager->getAllReviews();
    renderTableRows($reviews);
    exit(); // Stop di sini agar HTML header/footer tidak ikut termuat
}

// Jika bukan request AJAX, muat data untuk tampilan awal
$reviews = $manager->getAllReviews();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Ulasan & Rating User</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        .star-gold { color: #FFD700; }
        .star-gray { color: #E5E7EB; }
        /* Animasi halus saat update */
        #review-container { transition: opacity 0.3s; }
        .loading-blink { opacity: 0.5; }
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
                <li><a href="activity_logs.php"><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php" class="active"><i class="ri-star-line"></i> Ulasan User</a></li>
                <li><a href="manage_users.php"><i class="ri-user-settings-line"></i> Kelola User</a></li>
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
                <h2 style="color: var(--primary); font-size: 18px; margin:0;">Ulasan & Rating Pengguna</h2>
                <div style="display:flex; align-items:center; gap:5px;">
                    <div id="loading-indicator" style="display:none;"><small style="color:#888;">Mengupdate...</small></div>
                    <div style="width:10px; height:10px; background:#22c55e; border-radius:50%; box-shadow: 0 0 5px #22c55e;"></div>
                    <small style="color:#666;">Live Data</small>
                </div>
            </div>
            
            <div class="table-wrapper">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="20%">User</th>
                            <th width="15%">Rating</th>
                            <th width="40%">Ulasan</th>
                            <th width="20%">Detail Order</th>
                        </tr>
                    </thead>
                    <tbody id="review-container">
                        <?php 
                        // Tampilkan data awal saat halaman pertama dibuka
                        renderTableRows($reviews); 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Fungsi untuk mengambil data terbaru tanpa refresh halaman
        function fetchReviews() {
            // Tampilkan indikator loading kecil (opsional)
            // document.getElementById('loading-indicator').style.display = 'block';

            // Panggil file ini sendiri dengan parameter ?live_update=1
            fetch('reviews.php?live_update=1')
            .then(response => {
                if (!response.ok) throw new Error('Jaringan bermasalah');
                return response.text();
            })
            .then(html => {
                // Ganti isi tabel dengan HTML baru yang didapat dari PHP
                document.getElementById('review-container').innerHTML = html;
                
                // document.getElementById('loading-indicator').style.display = 'none';
            })
            .catch(error => console.error('Gagal mengambil data:', error));
        }

        // Jalankan fungsi fetchReviews setiap 3 detik (3000 ms)
        setInterval(fetchReviews, 3000);
    </script>
</body>
</html>