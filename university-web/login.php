<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1f0761;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fbf8ff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .login-container h1 {
            text-align: center;
            color: #2c3e50;
        }
        .login-container input {
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .login-container .btn {
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
        }
        .login-container .btn:hover {
            background-color: #2980b9;
        }
        .login-container .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
        .login-container .forgot-password a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>تسجيل الدخول</h1>
        <form action="login-action.php" method="POST">
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit" class="btn">دخول</button>
        </form>
        <div class="forgot-password">
            <a href="forgot-password.php">نسيت كلمة المرور؟</a>
            <br>
            <a href="register.php">انشاء حساب جديد</a>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
