<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; margin:0; background-color:#f4f4f4;">

    <div class="card" style="width:400px; padding:20px; background:white; border-radius:8px;">
        <h2 style="text-align:center;">Daftar Akun</h2>
        
        <form action="proses_register.php" method="POST">
            
            <div style="margin-bottom:15px;">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Buat Username" required style="width:100%; padding:10px;">
            </div>

            <div style="margin-bottom:20px;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password" required style="width:100%; padding:10px;">
            </div>

            <button type="submit" name="register" class="btn" style="width:100%; padding:10px; background-color:#28a745; color:white; border:none; cursor:pointer;">Daftar</button>
        </form>
        
        <br>
        <div style="text-align:center;">
            <a href="login.php">Sudah punya akun? Login</a>
        </div>
    </div>

</body>
</html>