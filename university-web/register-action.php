<?php
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

// استلام البيانات من النموذج
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// التحقق من تطابق كلمة المرور
if ($password != $confirm_password) {
    echo "<script>alert('كلمة المرور وتأكيد كلمة المرور لا يتطابقان!'); window.history.back();</script>";
    exit();
}

// التحقق مما إذا كان البريد الإلكتروني مسجلًا مسبقًا
$check_email = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($check_email);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('البريد الإلكتروني مسجل بالفعل!'); window.history.back();</script>";
    exit();
}

// تشفير كلمة المرور
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// استعلام لإدخال البيانات باستخدام prepared statement
$sql = "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $email, $phone, $hashed_password);

if ($stmt->execute()) {
    echo "<script>alert('تم إنشاء الحساب بنجاح!'); window.location.href='login.php';</script>";
} else {
    echo "<script>alert('حدث خطأ أثناء تسجيل الحساب. حاول مرة أخرى!'); window.history.back();</script>";
}

$conn->close();
?>
