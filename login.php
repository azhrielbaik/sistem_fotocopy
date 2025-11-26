<!DOCTYPE html>
<html>
<head>
    <title>Login - PrintCopy Pro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; margin:0; background-color:#f4f4f4;">

    <div class="card" style="width:400px; padding:20px; background:white; border-radius:8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="text-align:center;">Login</h2>
        
        <form action="proses_login.php" method="POST">
            
            <div style="margin-bottom:15px;">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan Username" required style="width:100%; padding:10px; box-sizing:border-box;">
            </div>

            <div style="margin-bottom:20px;">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required style="width:100%; padding:10px; box-sizing:border-box;">
            </div>

            <button type="submit" name="login" class="btn" style="width:100%; padding:10px; background-color:#007bff; color:white; border:none; border-radius:4px; cursor:pointer;">Masuk</button>
        </form>

        <br>
        <div style="text-align:center;">
            <a href="register.php" style="text-decoration:none; color:#28a745;">Belum punya akun? Daftar disini</a>
        </div>
    </div>

</body>
</html>