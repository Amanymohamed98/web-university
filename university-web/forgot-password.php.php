<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استلام البريد الإلكتروني
    $email = $_POST['email'];

    // الاتصال بقاعدة البيانات
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "university_system";

    // إنشاء الاتصال
    $conn = new mysqli($servername, $username, $password, $dbname);

    // التحقق من الاتصال
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // التحقق إذا كان البريد موجودًا في قاعدة البيانات
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // إرسال رابط إعادة تعيين كلمة المرور عبر البريد الإلكتروني
        echo "تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.";
        // إضافة الكود الخاص بإرسال البريد الإلكتروني باستخدام mail() أو مكتبة مثل PHPMailer
    } else {
        echo "البريد الإلكتروني غير موجود في النظام.";
    }

    $conn->close();
}
?>

<form action="forgot-password.php" method="POST">
    <label for="email">البريد الإلكتروني</label>
    <input type="email" name="email" required>
    <button type="submit">إرسال رابط إعادة تعيين كلمة المرور</button>
</form>
