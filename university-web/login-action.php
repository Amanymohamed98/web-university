<?php
session_start();

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);

if (empty($email) || empty($password)) {
    echo "<script>alert('❌ جميع الحقول مطلوبة!'); window.location.href='login.php';</script>";
    exit();
}

// تحقق إذا كان البريد هو univ1@example.com
if ($email === 'univ1@example.com') {
    // البحث في جدول الجامعات فقط
    $sql = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $univ = $result->fetch_assoc();
        if ($password === $univ['password']) {
            $_SESSION['university_id'] = $univ['id'];
            $_SESSION['university_name'] = $univ['name'];
            session_regenerate_id(true);
            header("Location: university_dashboard.php");
            exit();
        } else {
            echo "<script>alert('❌ كلمة المرور غير صحيحة!'); window.location.href='login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('❌ البريد الإلكتروني غير مسجل كجامعة!'); window.location.href='login.php';</script>";
        exit();
    }
}

// إذا لم يكن البريد univ1@example.com، نتابع العملية العادية
// أولاً نحاول تسجيل الدخول كمستخدم (طالب)
$sql = "SELECT id, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        session_regenerate_id(true);
        header("Location: universities.php"); // صفحة الطالب
        exit();
    } else {
        echo "<script>alert('❌ كلمة المرور غير صحيحة!'); window.location.href='login.php';</script>";
        exit();
    }
}

// إذا لم يكن مستخدم، نحاول تسجيل الدخول كجامعة
$sql = "SELECT id, name, password FROM universities WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$univ_result = $stmt->get_result();

if ($univ_result->num_rows > 0) {
    $univ = $univ_result->fetch_assoc();
    if ($password === $univ['password']) {
        $_SESSION['university_id'] = $univ['id'];
        $_SESSION['university_name'] = $univ['name'];
        session_regenerate_id(true);
        header("Location: universities.php"); // توجيه جميع الجامعات الأخرى إلى هذه الصفحة
        exit();
    } else {
        echo "<script>alert('❌ كلمة المرور غير صحيحة للجامعة!'); window.location.href='login.php';</script>";
        exit();
    }
}

// لم يتم العثور على البريد في كلا الجدولين
echo "<script>alert('❌ البريد الإلكتروني غير مسجل كمستخدم أو جامعة!'); window.location.href='login.php';</script>";

$stmt->close();
$conn->close();
?>