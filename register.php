<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | PrintCopy</title>
    
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
            /* Background Gradient Modern (Sama persis dengan Login) */
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            padding: 20px;
        }

        .register-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }

        .register-header {
            margin-bottom: 30px;
        }

        .register-header h2 {
            font-size: 26px;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .register-header p {
            font-size: 14px;
            color: #64748b;
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
            padding: 12px 15px 12px 45px; /* Padding kiri agar teks tidak menabrak ikon */
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            color: #334155;
            outline: none;
            transition: 0.3s ease;
            background: #f8fafc;
        }

        .input-wrapper input:focus {
            border-color: #10b981; /* Warna hijau saat fokus */
            background: #fff;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        .input-wrapper input:focus + i {
            color: #10b981;
        }

        /* Tombol Register (Hijau Modern) */
        .btn-register {
            width: 100%;
            padding: 14px;
            background: #10b981; /* Hijau Emerald */
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-register:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
        }

        /* Link Login */
        .login-link {
            margin-top: 25px;
            font-size: 14px;
            color: #64748b;
        }

        .login-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .login-link a:hover {
            text-decoration: underline;
            color: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="register-card">
        <div class="register-header">
            <h2>Daftar Akun</h2>
            <p>Silakan isi form di bawah ini</p>
        </div>

        <form action="proses_register.php" method="POST">
            
            <div class="input-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" placeholder="Buat Username" required autocomplete="off">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <button type="submit" name="register" class="btn-register">
                Daftar Sekarang
            </button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login disini</a>
        </div>
    </div>

</body>
</html>