<?php
session_start();
include '../includes/config.php';

class UserProfile {
    private $conn;
    private $userId;
    public $message = []; // Menyimpan status untuk SweetAlert

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

    // 2. PROSES UPDATE PROFIL (Tanpa Foto)
    public function handleUpdate() {
        if(isset($_POST['update_profile'])) {
            $username = mysqli_real_escape_string($this->conn, trim($_POST['username']));
            $email = mysqli_real_escape_string($this->conn, trim($_POST['email']));
            $phone = mysqli_real_escape_string($this->conn, trim($_POST['phone']));
            $address = mysqli_real_escape_string($this->conn, trim($_POST['address']));
            
            // Validasi sederhana
            if(empty($username) || empty($email)) {
                $this->message = ['status' => 'error', 'text' => 'Nama dan Email wajib diisi!'];
                return;
            }

            // Validasi Email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->message = ['status' => 'error', 'text' => 'Format email tidak valid!'];
                return;
            }

            // Update ke Database (Hanya data teks)
            $queryStr = "UPDATE users SET 
                         username = '$username',
                         email = '$email',
                         phone = '$phone',
                         address = '$address'
                         WHERE id = '$this->userId'";

            $update = mysqli_query($this->conn, $queryStr);

            if($update) {
                // Update session agar nama di sidebar berubah real-time
                $_SESSION['username'] = $username;
                $this->message = ['status' => 'success', 'text' => 'Profil berhasil diperbarui!'];
            } else {
                $this->message = ['status' => 'error', 'text' => 'Gagal update database: ' . mysqli_error($this->conn)];
            }
        }
    }

    // 3. AMBIL DATA USER
    public function getUserData() {
        $query = mysqli_query($this->conn, "SELECT * FROM users WHERE id='$this->userId'");
        return mysqli_fetch_assoc($query);
    }
}

// --- EKSEKUSI PROGRAM ---

$profile = new UserProfile($conn);
$profile->handleUpdate();
$d = $profile->getUserData();

// Null Coalescing & Sanitasi Output (XSS Prevention)
$nama = htmlspecialchars($d['username'] ?? '');
$email = htmlspecialchars($d['email'] ?? '');
$hp = htmlspecialchars($d['phone'] ?? '');
$alamat = htmlspecialchars($d['address'] ?? '');

// Inisial untuk avatar (Ganti foto dengan huruf depan nama)
$inisial = strtoupper(substr($nama, 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <?= $inisial ?>
                </div>
                <div>
                    <h2 style="margin: 0;"><?= $nama ?></h2>
                    <p style="margin: 5px 0 0 0; opacity: 0.9;">Kelola informasi profil dan akun Anda</p>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 0; padding-top: 70px; position: relative; z-index: 0;">
            <form method="POST">
                <div style="max-width: 800px;">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nama Lengkap (Username) <span style="color:red">*</span></label>
                        <input type="text" name="username" class="form-control" value="<?= $nama ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email <span style="color:red">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= $email ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nomor Telepon</label>
                            <input type="number" name="phone" class="form-control" value="<?= $hp ?>" placeholder="Contoh: 08123456789" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Alamat Lengkap</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Masukkan alamat lengkap untuk pengiriman..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: vertical;"><?= $alamat ?></textarea>
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" name="update_profile" class="btn" style="background: var(--primary); color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight:600; transition:0.2s; display:inline-flex; align-items:center; gap:8px;">
                            <i class="ri-save-line" style="font-size:18px;"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        <?php if(!empty($profile->message)): ?>
            Swal.fire({
                icon: '<?= $profile->message['status'] ?>',
                title: '<?= ($profile->message['status'] == 'success') ? "Berhasil!" : "Gagal!" ?>',
                text: '<?= $profile->message['text'] ?>',
                confirmButtonColor: 'var(--primary)',
                timer: 2000,
                timerProgressBar: true
            });
        <?php endif; ?>
    </script>
</body>
</html>