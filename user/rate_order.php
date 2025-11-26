<?php
include '../includes/config.php';

// 1. LOGIK KIRIM RATING
if(isset($_POST['submit_review'])) {
    $id = $_POST['order_id'];
    $stars = $_POST['rating_score'];
    $review = $_POST['review_text'];
    
    // Update database
    $q = "UPDATE orders SET rating='$stars', review='$review', status='Selesai' WHERE id='$id'";
    if(mysqli_query($conn, $q)){
        echo "<script>alert('Terima kasih atas penilaian Anda!'); window.location='rate_order.php';</script>";
    }
}

// 2. AMBIL ID PESANAN YANG DIPILIH (Default ambil yang pertama yang belum dirating)
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
                // Ambil pesanan yang statusnya 'Selesai' DAN belum ada rating
                $q_pending = mysqli_query($conn, "SELECT * FROM orders WHERE status='Completed' AND rating IS NULL ORDER BY created_at DESC");
                
                // Jika kosong
                if(mysqli_num_rows($q_pending) == 0) {
                    echo "<div class='card'><small>Tidak ada pesanan yang perlu dinilai.</small></div>";
                }

                $first = true;
                while($row = mysqli_fetch_assoc($q_pending)):
                    // Logic Auto Select ID pertama jika tidak ada yang dipilih
                    if($selected_id == "" && $first) { $selected_id = $row['id']; }
                    $first = false;

                    $isActive = ($selected_id == $row['id']) ? 'active' : '';
                ?>
                <div class="order-card-select <?= $isActive ?>" onclick="window.location='rate_order.php?select_id=<?= $row['id'] ?>'">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: bold; color: var(--primary);"><?= $row['id'] ?></span>
                        <span class="badge bg-pending" style="font-size:10px;"><?= $row['type'] ?></span>
                    </div>
                    <p style="margin: 5px 0; font-size: 13px; color: #555;"><?= $row['items'] ?></p>
                    <small style="color: #999;"><?= $row['created_at'] ?></small>
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
                // Ambil rating yang sudah ada
                $q_history = mysqli_query($conn, "SELECT * FROM orders WHERE rating IS NOT NULL ORDER BY created_at DESC");
                if(mysqli_num_rows($q_history) == 0) echo "<small>Belum ada history rating.</small>";
                
                while($h = mysqli_fetch_assoc($q_history)):
                ?>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding: 15px 0;">
                    <div>
                        <strong><?= $h['id'] ?></strong> <span class="badge bg-success" style="font-size: 10px;"><?= $h['type'] ?></span>
                        <p style="margin: 5px 0;"><?= $h['review'] ? $h['review'] : '-' ?></p>
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

            // Text dinamis
            const labels = ["Sangat Buruk", "Buruk", "Cukup", "Puas", "Sangat Puas!"];
            text.innerText = labels[n-1];

            // Loop ubah warna
            stars.forEach((s, index) => {
                if(index < n) s.classList.add('active');
                else s.classList.remove('active');
            });
        }

        // Fungsi Tambah Tag ke Textarea
        function addTag(tag) {
            let area = document.getElementById('review_area');
            if(area.value === "") {
                area.value = tag;
            } else {
                area.value += ", " + tag;
            }
        }
    </script>
</body>
</html>