<?php
session_start();

// تحقق من تسجيل الدخول كجامعة
if (!isset($_SESSION['university_id'])) {
    echo "<script>alert('❌ يرجى تسجيل الدخول أولاً كجامعة'); window.location.href='login.php';</script>";
    exit();
}

$university_id = $_SESSION['university_id'];
$university_name = $_SESSION['university_name'];

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// التحقق من وجود الرسالة عبر GET
if (isset($_GET['message_id']) && !empty($_GET['message_id'])) {
    $message_id = $_GET['message_id'];

    // جلب تفاصيل الرسالة
    $sql = "SELECT messages.*, users.name AS student_name 
            FROM messages 
            JOIN users ON messages.user_id = users.id
            WHERE messages.id = ? AND messages.university_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("فشل في تحضير الاستعلام: " . $conn->error);
    }

    $stmt->bind_param("ii", $message_id, $university_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // التحقق من وجود الرسالة
    if ($result->num_rows > 0) {
        $message = $result->fetch_assoc();
    } else {
        echo "<script>alert('❌ الرسالة غير موجودة.'); window.location.href='messages.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('❌ الرسالة غير موجودة.'); window.location.href='messages.php';</script>";
    exit();
}

// معالجة الرد على الرسالة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    $reply_message = $_POST['reply_message'];

    // تحديث الرسالة كتم الرد عليها
    $update_sql = "UPDATE messages 
                   SET response = 1, reply_message = ?, reply_date = NOW() 
                   WHERE id = ? AND university_id = ?";

    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die("فشل في تحضير الاستعلام: " . $conn->error);
    }

    $update_stmt->bind_param("sii", $reply_message, $message_id, $university_id);
    $update_stmt->execute();

    // التحقق من نجاح التحديث
    if ($update_stmt->affected_rows > 0) {
        echo "<script>alert('تم الرد على الرسالة بنجاح.'); window.location.href='messages.php';</script>";
    } else {
        echo "<script>alert('❌ حدث خطأ أثناء الرد على الرسالة.'); window.location.href='messages.php';</script>";
    }

    $update_stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الرد على الرسالة</title>
    <style>
        /* التنسيق العام */
        body {
            font-family: Arial, sans-serif;
            direction: rtl;
            background-color: #f9f9f9;
            padding: 20px;
            margin: 0;
            color: #333;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            border-radius: 5px;
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .form-container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .form-container textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .form-container button {
            width: 100%;
            background-color: #3498db;
            color: white;
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <!-- زر تسجيل الخروج -->
    <a href="index.html"><button class="logout-btn">تسجيل الخروج</button></a>

    <h2>الرد على الرسالة</h2>

    <div class="form-container">
        <p><strong>اسم الطالب:</strong> <?= htmlspecialchars($message['student_name']) ?></p>
        <p><strong>الموضوع:</strong> <?= htmlspecialchars($message['subject']) ?></p>
        <p><strong>الرسالة:</strong> <?= nl2br(htmlspecialchars($message['message'])) ?></p>

        <form method="POST" action="handle_reply.php?message_id=<?= $message_id ?>">
            <textarea name="reply_message" placeholder="اكتب ردك هنا..." required></textarea>
            <button type="submit">إرسال الرد</button>
        </form>
    </div>

</body>
</html>
