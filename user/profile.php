<?php
include '../includes/config.php';

// --- LOGIC UPDATE PROFIL ---
if(isset($_POST['save_profile'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $id = 1; // ID User Demo (Hardcoded sementara)

    // Query Update
    $q = "UPDATE users SET username='$name', phone='$phone', address='$address' WHERE id='$id'";
    // mysqli_query($conn, $q); // Uncomment baris ini jika tabel users sudah siap
    
    echo "<script>alert('Profil berhasil diperbarui!');</script>";
}

// Simulasi Data User (Nanti ambil dari Database pakai SELECT)
$user = [
    'name' => 'User Demo',
    'email' => 'user@example.com',
    'phone' => '08123456789',
    'address' => 'Jl. Merdeka No. 45, Bandung'
];
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
                <h4 style="margin: 0; font-weight: 600;"><?= $user['name'] ?></h4>
                <small style="opacity: 0.8;"><?= $user['email'] ?></small>
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
            <div class="profile-avatar-large">
                <i class="ri-user-smile-line"></i>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 22px;"><?= $user['name'] ?></h2>
                <p style="margin: 5px 0 0; opacity: 0.9;">Kelola informasi profil dan akun Anda</p>
            </div>
        </div>

        <div class="card" style="border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
            <form method="POST">
                <div class="profile-form-grid">
                    
                    <div>
                        <div class="avatar-upload-box">
                            <div style="width: 100px; height: 100px; background: #E5E7EB; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: #9CA3AF;">
                                <i class="ri-image-add-line" style="font-size: 32px;"></i>
                            </div>
                            <h5 style="margin: 0;">Foto Profil</h5>
                            <p style="font-size: 12px; color: #666; margin-bottom: 15px;">Format: JPG, PNG (Max 2MB)</p>
                            <button type="button" class="btn-outline-gray" style="width: 100%; font-size: 13px;">Upload Foto</button>
                        </div>
                    </div>

                    <div>
                        <div style="margin-bottom: 20px;">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" value="<?= $user['name'] ?>">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" readonly style="background: #F3F4F6;">
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
                            <button type="button" class="btn-outline-gray">Batal</button>
                            <button type="submit" name="save_profile" class="btn-blue" style="background: var(--primary); border:none; color:white; padding:10px 25px; border-radius:8px; font-weight:600;">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
</body>
</html>