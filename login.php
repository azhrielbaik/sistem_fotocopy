<?php
session_start();
include 'includes/config.php';

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek user di database
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
            header("Location: admin/manage_orders.php");
        } else {
            header("Location: user/dashboard.php");
        }
    } else {
        echo "<script>alert('Username/Password Salah!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title><link rel="stylesheet" href="css/style.css"></head>
<body style="justify-content:center; align-items:center; background:#f3f4f6;">
    <div class="card" style="width:400px; text-align:center; padding:40px;">
        <h2 style="margin-bottom:20px; color:#2563EB;">Login PrintCopy</h2>
        <form method="POST">
            <input type="text" name="username" class="form-control" placeholder="Username" required style="margin-bottom:15px;">
            <input type="password" name="password" class="form-control" placeholder="Password" required style="margin-bottom:25px;">
            <button name="login" class="btn" style="width:100%;">Masuk</button>
        </form>
    </div>
</body>
</html>