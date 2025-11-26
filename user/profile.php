<?php
include '../includes/config.php';

// Ambil ID User (Hardcode ID 1 untuk simulasi login)
$user_id = 1;

// --- LOGIC 1: AMBIL DATA USER DARI DB ---
$q = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($q);

// Kalau data kosong (belum ada di DB), kita bikin dummy array biar gak error
if(!$user) {
    $user = [
        'username' => 'User Demo',
        'email' => 'user@example.com',
        'phone' => '-',
        'address' => '-',
        'photo' => 'default.png'
    ];
}

// --- LOGIC 2: UPDATE PROFIL & UPLOAD FOTO ---
if(isset($_POST['save_profile'])) {
    $name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // Cek apakah ada file foto yang diupload?
    $photo_query = "";
    if(!empty($_FILES['foto']['name'])) {
        $nama_file = time() . '_' . $_FILES['foto']['name']; // Kasih waktu biar unik
        $tmp_file = $_FILES['foto']['tmp_name'];
        $path = "../uploads/" . $nama_file;

        // Pindahkan file ke folder uploads
        if(move_uploaded_file($tmp_file, $path)) {
            $photo_query = ", photo='$nama_file'";
        }
    }

    // Update Database
    $query = "UPDATE users SET username='$name', phone='$phone', address='$address' $photo_query WHERE id='$user_id'";
    
    if(mysqli_query($conn, $query)) {
        echo "<script>alert('Profil & Foto berhasil diperbarui!'); window.location='profile.php';</script>";
    } else {
        echo "<script>alert('Gagal update: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya - PrintCopy Pro</title>
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
                <li><a href="rate_order.php"><i class="ri-star-line"></i> Beri Rating</a></li>
                <li><a href="profile.php" class="active"><i class="ri-user-settings-line"></i> Profil Saya</a></li>
            </ul>
        </div>

        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;"><?= $user['username'] ?></h4>
                <small style="opacity: 0.8;">user@example.com</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="header" style="justify-content: center; position: relative;">
            <a href="dashboard.php" style="position: absolute; left: 0; color: #333; text-decoration: none;">
                <i class="ri-arrow-left-line" style="font-size: 24px;"></i>
            </a>
            <h4 style="color: var(--primary);">Profil Saya</h4>
        </div>

        <div class="profile-header-card">
            <div class="profile-avatar-large" style="overflow: hidden; padding: 0;">
                <?php if($user['photo'] != 'default.png' && file_exists("../uploads/".$user['photo'])): ?>
                    <img src="../uploads/<?= $user['photo'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <i class="ri-user-smile-line"></i>
                <?php endif; ?>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 22px;"><?= $user['username'] ?></h2>
                <p style="margin: 5px 0 0; opacity: 0.9;">Kelola informasi profil dan akun Anda</p>
            </div>
        </div>

        <div class="card" style="border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
            <form method="POST" enctype="multipart/form-data">
                <div class="profile-form-grid">
                    
                    <div>
                        <div class="avatar-upload-box">
                            <div style="width: 100px; height: 100px; background: #E5E7EB; border-radius: 50%; margin: 0 auto 15px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <?php if($user['photo'] != 'default.png' && file_exists("../uploads/".$user['photo'])): ?>
                                    <img src="../uploads/<?= $user['photo'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="ri-image-add-line" style="font-size: 32px; color: #9CA3AF;"></i>
                                <?php endif; ?>
                            </div>
                            
                            <h5 style="margin: 0;">Foto Profil</h5>
                            <p style="font-size: 12px; color: #666; margin-bottom: 15px;">Format: JPG, PNG (Max 2MB)</p>
                            
                            <label for="upload_foto" class="btn-outline-gray" style="width: 100%; display: block; cursor: pointer; text-align: center;">
                                Pilih Foto
                            </label>
                            <input type="file" name="foto" id="upload_foto" style="display: none;" accept="image/*" onchange="previewImage(this)">
                        </div>
                    </div>

                    <div>
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" value="<?= $user['username'] ?>">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" value="user@example.com" class="form-control" readonly style="background: #F3F4F6;">
                            </div>
                            <div>
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone" class="form-control" value="<?= $user['phone'] ?>">
                            </div>
                        </div>

                        <div style="margin-bottom: 25px;">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="address" class="form-control" rows="3"><?= $user['address'] ?></textarea>
                        </div>

                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="submit" name="save_profile" class="btn-blue" style="background: var(--primary); border:none; color:white; padding:10px 25px; border-radius:8px; font-weight:600; cursor: pointer;">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                alert('Foto dipilih: ' + input.files[0].name + '. Klik Simpan Perubahan untuk mengupload.');
            }
        }
    </script>
</body>
</html>