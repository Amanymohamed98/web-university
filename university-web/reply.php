<?php
session_start();

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "university_system");
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// التحقق من وجود معرف الرسالة
if (!isset($_GET['message_id'])) {
    header("Location: messages.php");
    exit();
}

$message_id = intval($_GET['message_id']);

// جلب بيانات الرسالة (بدون التحقق من البريد الإلكتروني)
$sql = "SELECT m.*, u.name AS student_name, u.email AS student_email 
        FROM messages m
        JOIN users u ON m.user_id = u.id
        WHERE m.id = ?";
$stmt = $conn->prepare($sql);

// Check if prepare() succeeded
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $message_id);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();
$message = $result->fetch_assoc();

if (!$message) {
    header("Location: messages.php");
    exit();
}

// معالجة إرسال الرد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['response'])) {
    $response = trim($_POST['response']);
    
    if (!empty($response)) {
        // تحديث الرسالة بإضافة الرد
        $update_sql = "UPDATE messages SET response = ?, response_date = NOW(), responded_by = 'admin' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if ($update_stmt === false) {
            die("Error preparing update statement: " . $conn->error);
        }
        
        $update_stmt->bind_param("si", $response, $message_id);
        
        if ($update_stmt->execute()) {
            // إرسال إشعار للطالب
            $_SESSION['success'] = "تم إرسال الرد بنجاح";
            header("Location: messages.php");
            exit();
        } else {
            $error = "حدث خطأ أثناء حفظ الرد: " . $update_stmt->error;
        }
    } else {
        $error = "يجب كتابة محتوى الرد";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرد على الرسالة - لوحة التحكم</title>
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .message-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            border-right: 4px solid #3498db;
        }
        
        .message-details p {
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .message-details strong {
            color: #2c3e50;
            min-width: 100px;
            display: inline-block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 150px;
            font-family: inherit;
            resize: vertical;
            font-size: 16px;
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            line-height: 38px;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #fde8e8;
            border-radius: 5px;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .action-buttons {
            text-align: left;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✉️ الرد على رسالة الطالب</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="message-details">
            <p><strong>الطالب:</strong> <?php echo htmlspecialchars($message['student_name']); ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($message['student_email']); ?></p>
            <p><strong>تاريخ الرسالة:</strong> 
                <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
            </p>
            <p><strong>الرسالة:</strong></p>
            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="response">الرد:</label>
                <textarea id="response" name="response" required placeholder="اكتب ردك هنا..."></textarea>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn">إرسال الرد</button>
                <a href="messages.php" class="btn btn-secondary">العودة للرسائل</a>
            </div>
        </form>
    </div>
</body>
</html>