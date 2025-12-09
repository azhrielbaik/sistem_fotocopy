<?php
session_start();
include '../includes/config.php';

class UserProfile {
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

    // 2. PROSES UPDATE PROFIL
    public function handleUpdate() {
        if(isset($_POST['update_profile'])) {
            $username = mysqli_real_escape_string($this->conn, $_POST['username']);
            $email = mysqli_real_escape_string($this->conn, $_POST['email']);
            $phone = mysqli_real_escape_string($this->conn, $_POST['phone']);
            $address = mysqli_real_escape_string($this->conn, $_POST['address']);
            
            // Update ke Database
            $queryStr = "UPDATE users SET 
                         username = '$username',
                         email = '$email',
                         phone = '$phone',
                         address = '$address'
                         WHERE id = '$this->userId'";

            $update = mysqli_query($this->conn, $queryStr);

            if($update) {
                // Update session biar nama di sidebar langsung berubah
                $_SESSION['username'] = $username;
                echo "<script>alert('Profil berhasil diperbarui!'); window.location='profile.php';</script>";
            } else {
                echo "<script>alert('Gagal update profil.');</script>";
            }
        }
    }

    // 3. AMBIL DATA USER TERBARU
    public function getUserData() {
        $query = mysqli_query($this->conn, "SELECT * FROM users WHERE id='$this->userId'");
        return mysqli_fetch_assoc($query);
    }
}

// --- EKSEKUSI PROGRAM ---

// 1. Instansiasi Class
$profile = new UserProfile($conn);

// 2. Cek apakah ada request update profil
$profile->handleUpdate();

// 3. Ambil data user untuk ditampilkan di form
$d = $profile->getUserData();

// 4. Masukkan ke variabel (Logic Null Coalescing tetap dipertahankan)
$nama = $d['username'] ?? '';
$email = $d['email'] ?? '';
$hp = $d['phone'] ?? '';
$alamat = $d['address'] ?? '';
$foto = 'default.png'; 
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
                    <small style="opacity: 0.8; font-size: 12px;">Si-Foprint</small>
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
                <h4 style="margin: 0; font-weight: 600;"><?= $nama ?></h4>
                <small style="opacity: 0.8;">User Account</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div style="background: var(--primary); color: white; padding: 40px; border-radius: 15px; margin-bottom: -50px; position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 40px; font-weight: bold;">
                    <?= strtoupper(substr($nama, 0, 1)) ?>
                </div>
                <div>
                    <h2 style="margin: 0;"><?= $nama ?></h2>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">Kelola informasi profil dan akun Anda</p>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 0; padding-top: 70px; position: relative; z-index: 0;">
            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                    
                    <div style="text-align: center; border-right: 1px solid #eee; padding-right: 30px;">
                        <div style="width: 120px; height: 120px; background: #f3f4f6; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 40px;">
                            <i class="ri-image-add-line"></i>
                        </div>
                        <h4 style="margin: 0;">Foto Profil</h4>
                        <small style="color: #888;">Format: JPG, PNG (Max 2MB)</small>
                        <button type="button" class="btn-outline" style="margin-top: 15px; width: 100%; border: 1px solid #ddd; padding: 8px; border-radius: 5px; background: white; cursor: pointer;">Pilih Foto</button>
                    </div>

                    <div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nama Lengkap (Username)</label>
                            <input type="text" name="username" class="form-control" value="<?= $nama ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= $email ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nomor Telepon</label>
                                <input type="text" name="phone" class="form-control" value="<?= $hp ?>" placeholder="08..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Alamat Lengkap</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Masukkan alamat pengiriman..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?= $alamat ?></textarea>
                        </div>

                        <div style="text-align: right;">
                            <button type="submit" name="update_profile" class="btn" style="background: var(--primary); color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">Simpan Perubahan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</body>
</html>