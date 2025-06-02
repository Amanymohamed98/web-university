<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1f0761;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fbf8ff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .register-container h1 {
            text-align: center;
            color: #2c3e50;
        }
        .register-container input {
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .register-container .btn {
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            padding: 10px;
            width: 100%;
        }
        .register-container .btn:hover {
            background-color: #2980b9;
        }
        .register-container .terms {
            text-align: center;
            margin-top: 10px;
        }
        .register-container .terms a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>إنشاء حساب جديد</h1>
        <form action="register-action.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="الاسم الكامل" required>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="tel" name="phone" placeholder="رقم الجوال" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <input type="password" name="confirm_password" placeholder="تأكيد كلمة المرور" required>
            <button type="submit" class="btn">إنشاء الحساب</button>
        </form>
        <div class="terms">
          
            <p>بالضغط على "إنشاء الحساب"، أنت توافق على <a href="terms-and-conditions.php">الشروط والأحكام</a></p>
            <a href="login.php"> لديك حساب بالفعل ؟ تسجيل دخول</a>
            

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
