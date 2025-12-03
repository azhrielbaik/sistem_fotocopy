<?php
session_start();
include '../includes/config.php';

// 1. Cek Login
if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php"); exit();
}
$user_id = $_SESSION['user_id'];

// 2. LOGIK KIRIM RATING
if(isset($_POST['submit_review'])) {
    $id = $_POST['order_id'];
    $stars = $_POST['rating_score'];
    $review = mysqli_real_escape_string($conn, $_POST['review_text']);
    
    // --- PERBAIKAN DI SINI ---
    // HAPUS "status='Selesai'" agar status pesanan tidak berubah/ter-reset.
    // Cukup update rating & review saja.
    $q = "UPDATE orders SET rating='$stars', review='$review' WHERE id='$id'";
    
    if(mysqli_query($conn, $q)){
        echo "<script>alert('Terima kasih atas penilaian Anda!'); window.location='rate_order.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan rating.');</script>";
    }
}

// 3. AMBIL ID PESANAN YANG DIPILIH
$selected_id = "";
if(isset($_GET['select_id'])) {
    $selected_id = $_GET['select_id'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Beri Rating</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* Gaya Khusus Halaman Rating */
        .info-banner { background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; padding: 30px; border-radius: 15px; display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .icon-box { background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; }
        .rating-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        .order-list-col { max-height: 500px; overflow-y: auto; }
        .order-card-select { background: white; border: 1px solid #eee; border-radius: 10px; padding: 15px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; }
        .order-card-select:hover { border-color: var(--primary); transform: translateX(5px); }
        .order-card-select.active { border: 2px solid var(--primary); background: #eff6ff; }
        .rating-form-col { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .star-container { display: flex; gap: 10px; margin: 10px 0; }
        .star-icon { font-size: 35px; color: #E5E7EB; cursor: pointer; transition: color 0.2s; }
        .star-icon.active { color: #F59E0B; }
        .tags-container { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .tag-pill { background: #F3F4F6; padding: 5px 12px; border-radius: 20px; font-size: 12px; cursor: pointer; transition: 0.2s; border: 1px solid #eee; }
        .tag-pill:hover { background: var(--primary); color: white; }
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
                <li><a href="view_orders.php"><i class="ri-file-list-3-line"></i> Status Pesanan</a></li>
                <li><a href="rate_order.php" class="active"><i class="ri-star-line"></i> Beri Rating</a></li>
                <li><a href="profile.php"><i class="ri-user-settings-line"></i> Profil Saya</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;"><?= $_SESSION['username'] ?></h4>
                <small style="opacity: 0.8;">User Account</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        <div class="header" style="justify-content: center; position: relative;">
            <a href="dashboard.php" style="position: absolute; left: 0; color: #333; text-decoration: none;">
                <i class="ri-close-line" style="font-size: 24px;"></i>
            </a>
            <h4 style="color: var(--primary);">Beri Rating</h4>
        </div>

        <div class="info-banner">
            <div class="icon-box"><i class="ri-star-line"></i></div>
            <div>
                <h3 style="margin: 0;">Beri Rating & Ulasan</h3>
                <small style="opacity: 0.9;">Bantu kami meningkatkan layanan dengan feedback Anda</small>
            </div>
        </div>

        <div class="rating-layout">
            
            <div class="order-list-col">
                <h4 style="margin-bottom: 15px; color: var(--primary);">Pilih Pesanan</h4>
                
                <?php
                // --- PERBAIKAN QUERY SELECT ---
                // Hanya ambil pesanan Completed/Selesai yang BELUM dirating
                // Kita gunakan logika OR untuk menangkap kedua kemungkinan bahasa status
                $q_pending = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='$user_id' AND (status='Completed' OR status='Selesai') AND (rating IS NULL OR rating = 0) ORDER BY created_at DESC");
                
                if(mysqli_num_rows($q_pending) == 0) {
                    echo "<div class='card' style='padding:20px; text-align:center; color:#888;'><small>Tidak ada pesanan yang perlu dinilai.</small></div>";
                }

                $first = true;
                while($row = mysqli_fetch_assoc($q_pending)):
                    // Auto-select ID pertama jika belum ada pilihan
                    if($selected_id == "" && $first) { $selected_id = $row['id']; }
                    $first = false;

                    $isActive = ($selected_id == $row['id']) ? 'active' : '';
                ?>
                <div class="order-card-select <?= $isActive ?>" onclick="window.location='rate_order.php?select_id=<?= $row['id'] ?>'">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: bold; color: var(--primary);"><?= $row['id'] ?></span>
                        <span class="badge bg-pending" style="font-size:10px;"><?= $row['type'] ?></span>
                    </div>
                    <p style="margin: 5px 0; font-size: 13px; color: #555;"><?= substr($row['items'], 0, 40) ?>...</p>
                    <small style="color: #999;"><?= date('d M Y', strtotime($row['created_at'])) ?></small>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="rating-form-col">
                <?php if($selected_id != ""): ?>
                    <h4 style="margin-top: 0;">Berikan Penilaian</h4>
                    <p style="font-size: 13px; color: #666;">ID Pesanan: <strong><?= $selected_id ?></strong></p>
                    
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= $selected_id ?>">
                        <input type="hidden" name="rating_score" id="rating_score" value="5">

                        <div style="margin: 20px 0;">
                            <label style="font-weight: 500;">Rating Layanan</label>
                            <div class="star-container">
                                <i class="ri-star-fill star-icon active" onclick="setStar(1)"></i>
                                <i class="ri-star-fill star-icon active" onclick="setStar(2)"></i>
                                <i class="ri-star-fill star-icon active" onclick="setStar(3)"></i>
                                <i class="ri-star-fill star-icon active" onclick="setStar(4)"></i>
                                <i class="ri-star-fill star-icon active" onclick="setStar(5)"></i>
                            </div>
                            <span id="rating-text" style="font-weight: bold; color: #F59E0B;">Sangat Puas!</span>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="font-weight: 500;">Ulasan (Opsional)</label>
                            <textarea name="review_text" id="review_area" class="form-control bg-gray" rows="4" placeholder="Ceritakan pengalaman Anda..."></textarea>
                            
                            <div class="tags-container">
                                <span class="tag-pill" onclick="addTag('Cepat')">Cepat</span>
                                <span class="tag-pill" onclick="addTag('Berkualitas')">Berkualitas</span>
                                <span class="tag-pill" onclick="addTag('Ramah')">Ramah</span>
                                <span class="tag-pill" onclick="addTag('Harga Terjangkau')">Harga Terjangkau</span>
                                <span class="tag-pill" onclick="addTag('Profesional')">Profesional</span>
                            </div>
                        </div>

                        <button type="submit" name="submit_review" class="btn btn-full" style="border-radius: 30px;">
                            <i class="ri-send-plane-fill"></i> Kirim Rating
                        </button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center; color: #999; padding: 50px 0;">
                        <i class="ri-star-smile-line" style="font-size: 40px;"></i>
                        <p>Pilih pesanan di sebelah kiri untuk dinilai.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="history-section">
            <h4 style="color: var(--primary);">Rating Sebelumnya</h4>
            <div class="card" style="margin-top: 15px;">
                <?php
                // --- PERBAIKAN QUERY HISTORY ---
                // Hanya ambil history milik user yang sedang login
                $q_history = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='$user_id' AND rating IS NOT NULL AND rating > 0 ORDER BY created_at DESC");
                
                if(mysqli_num_rows($q_history) == 0) echo "<small style='color:#888;'>Belum ada history rating.</small>";
                
                while($h = mysqli_fetch_assoc($q_history)):
                ?>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding: 15px 0;">
                    <div>
                        <strong><?= $h['id'] ?></strong> <span class="badge bg-success" style="font-size: 10px;"><?= $h['type'] ?></span>
                        <p style="margin: 5px 0; color:#555; font-size:13px;">"<?= $h['review'] ? $h['review'] : '-' ?>"</p>
                    </div>
                    <div style="color: #F59E0B;">
                        <?php for($i=0; $i<$h['rating']; $i++) echo '<i class="ri-star-fill"></i>'; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

    </div>

    <script>
        // Fungsi Ubah Bintang
        function setStar(n) {
            let stars = document.querySelectorAll('.star-icon');
            let text = document.getElementById('rating-text');
            document.getElementById('rating_score').value = n;

            const labels = ["Sangat Buruk", "Buruk", "Cukup", "Puas", "Sangat Puas!"];
            text.innerText = labels[n-1];

            stars.forEach((s, index) => {
                if(index < n) s.classList.add('active');
                else s.classList.remove('active');
            });
        }

        // Fungsi Tambah Tag
        function addTag(tag) {
            let area = document.getElementById('review_area');
            if(area.value === "") { area.value = tag; } 
            else { area.value += ", " + tag; }
        }
    </script>
</body>
</html>