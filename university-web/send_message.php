<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<div class='message error'>❌ فشل الاتصال بقاعدة البيانات: " . $conn->connect_error . "</div>");
}

// جلب بيانات الطالب
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message_content = $_POST['message_content'];

    // إدخال الرسالة في قاعدة البيانات
    $sql = "INSERT INTO messages (user_id, message_content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("فشل في تجهيز الاستعلام: " . $conn->error);
    }

    $stmt->bind_param("is", $user_id, $message_content);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<div class='message success'>تم إرسال الرسالة بنجاح!</div>";
    } else {
        echo "<div class='message error'>❌ فشل في إرسال الرسالة.</div>";
    }

    $stmt->close();
}

$conn->close();
?>
