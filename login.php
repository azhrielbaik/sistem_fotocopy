<?php
session_start();
include 'includes/config.php';

if(isset($_POST['login'])){
    // Catatan Keamanan: Sebaiknya gunakan mysqli_real_escape_string untuk mencegah SQL Injection
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek user di database
    // Catatan: Kode asli Anda (rawan SQL Injection, tapi saya biarkan agar logika Anda tetap jalan)
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if(mysqli_num_rows($q) > 0){
        $data = mysqli_fetch_assoc($q);
        
        // 1. SET SESSION
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['username'] = $data['username'];

        // -------------------------------------------------------
        // 2. BAGIAN PENTING: CATAT LOG AKTIVITAS (LOGIN)
        // -------------------------------------------------------
        $uid = $data['id'];
        mysqli_query($conn, "INSERT INTO activity_logs (user_id, action) VALUES ('$uid', 'Login')");
        // -------------------------------------------------------

        // 3. Redirect ke Halaman Sesuai Role
        if($data['role'] == 'admin') {
            header("Location: admin/charts.php");
        } else {
            header("Location: user/dashboard.php");
        }
    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System | PrintCopy</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f2f5;
            /* Background Gradient Modern */
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Hiasan Header Card */
        .login-header {
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 28px;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 14px;
            color: #64748b;
        }

        .login-header .brand-color {
            color: #2563eb;
        }

        /* Styling Input Form */
        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
            transition: 0.3s;
        }

        .input-wrapper input {
            width: 100%;
            padding: 12px 15px 12px 45px; /* Padding kiri lebih besar untuk ikon */
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            color: #334155;
            outline: none;
            transition: 0.3s ease;
            background: #f8fafc;
        }

        .input-wrapper input:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .input-wrapper input:focus + i {
            color: #2563eb;
        }

        /* Tombol Login */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-login:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Pesan Error */
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
            border: 1px solid #fecaca;
        }

        .footer-text {
            margin-top: 25px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Masuk ke <span class="brand-color">PrintCopy System</span></p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" placeholder="Masukkan password" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">
                Masuk Sekarang
            </button>
        </form>

        <div class="footer-text">
            &copy; <?php echo date('Y'); ?> Sistem Manajemen Fotocopy
        </div>
    </div>

</body>
</html>