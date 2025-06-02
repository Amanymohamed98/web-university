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
    die("<div class='alert alert-danger'>❌ فشل الاتصال بقاعدة البيانات: " . $conn->connect_error . "</div>");
}

// جلب بيانات الطالب
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM Users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// جلب الرسائل من قاعدة البيانات مع معلومات المرسل
$sql = "SELECT m.*, u.name AS user_name, univ.name AS university_name 
        FROM messages m
        LEFT JOIN Users u ON m.user_id = u.id
        LEFT JOIN universities univ ON m.university_id = univ.id
        WHERE m.user_id = ? OR m.university_id = ?
        ORDER BY m.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result();

// معالجة إرسال الرسالة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $university_id = (int)$_POST['university_id'];
    
    if (!empty($message)) {
        $sql = "INSERT INTO messages (user_id, university_id, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $user_id, $university_id, $message);
        $stmt->execute();
        
        // إعادة تحميل الصفحة بعد الإرسال
        if ($stmt->affected_rows > 0) {
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الدردشة مع الجامعة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a237e;
            --student-color: #4a6baf;
            --university-color: #5d6bc0;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #333;
            --text-light: #777;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }
        
        .chat-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 15px;
        }
        
        .chat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .chat-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #283593 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .chat-header h3 {
            margin: 0;
            font-weight: 700;
        }
        
        .chat-body {
            padding: 20px;
            background-color: white;
            height: 500px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        .message {
            max-width: 70%;
            margin-bottom: 15px;
            padding: 12px 15px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }
        
        .student-message {
            align-self: flex-end;
            background-color: var(--student-color);
            color: white;
            border-bottom-right-radius: 5px;
        }
        
        .university-message {
            align-self: flex-start;
            background-color: var(--university-color);
            color: white;
            border-bottom-left-radius: 5px;
        }
        
        .message-info {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            font-size: 0.8rem;
        }
        
        .student-message .message-info {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .university-message .message-info {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .message-sender {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .chat-input {
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
        }
        
        .message-form textarea {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px 15px;
            resize: none;
            transition: all 0.3s;
        }
        
        .message-form textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 35, 126, 0.1);
            outline: none;
        }
        
        .btn-send {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        .btn-send:hover {
            background-color: #0d1a6b;
            transform: translateY(-2px);
        }
        
        .student-info-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .student-info-card h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        /* تأثيرات التمرير */
        .chat-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .chat-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .chat-body::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
        
        .chat-body::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* رسوم متحركة للرسائل الجديدة */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .chat-container {
                padding: 0 10px;
            }
            
            .message {
                max-width: 85%;
            }
            
            .chat-body {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- معلومات الطالب -->
        <div class="student-info-card">
            <h4><i class="bi bi-person-circle"></i> معلومات الطالب</h4>
            <div class="row">
                <div class="col-md-6 info-item">
                    <span class="info-label"><i class="bi bi-person"></i> الاسم:</span>
                    <span><?php echo htmlspecialchars($student['name']); ?></span>
                </div>
                <div class="col-md-6 info-item">
                    <span class="info-label"><i class="bi bi-envelope"></i> البريد الإلكتروني:</span>
                    <span><?php echo htmlspecialchars($student['email']); ?></span>
                </div>
                
            </div>
        </div>
        
        <!-- بطاقة الدردشة -->
        <div class="chat-card">
            <div class="chat-header">
                <h3><i class="bi bi-chat-dots"></i> الدردشة مع الجامعة</h3>
            </div>
            <div class="chat-body" id="chat-messages">
    <?php if ($messages->num_rows > 0): ?>
        <?php while ($message = $messages->fetch_assoc()): ?>
            <!-- رسالة الطالب -->
            <?php if (!empty($message['message'])): ?>
                <div class="message <?php echo $message['user_id'] == $user_id ? 'student-message' : 'university-message'; ?>">
                    <div class="message-sender">
                        <?php echo $message['user_id'] == $user_id ? 'أنت' : htmlspecialchars($message['university_name']); ?>
                    </div>
                    <div class="message-text"><?php echo htmlspecialchars($message['message']); ?></div>
                    <div class="message-info">
                        <span><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($message['created_at'])); ?></span>
                        <span><?php echo date('Y-m-d', strtotime($message['created_at'])); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- رد الجامعة -->
            <?php if (!empty($message['response'])): ?>
                <div class="message university-message">
                    <div class="message-sender">
                        <?php echo htmlspecialchars($message['university_name']); ?>
                    </div>
                    <div class="message-text"><?php echo htmlspecialchars($message['response']); ?></div>
                    <div class="message-info">
                        <span><i class="bi bi-reply"></i> رد</span>
                        <span><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($message['created_at'])); ?></span>
                        <span><?php echo date('Y-m-d', strtotime($message['created_at'])); ?></span>
                    </div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center text-muted py-4">
            لا توجد رسائل بعد. ابدأ المحادثة مع الجامعة الآن!
        </div>
    <?php endif; ?>
</div>

            
            <div class="chat-input">
                <form method="POST" class="message-form" id="messageForm">
                    <textarea name="message" rows="3" placeholder="اكتب رسالتك هنا..." required></textarea>
                    <input type="hidden" name="university_id" value="1"> <!-- يمكن جعله ديناميكيًا -->
                    <button type="submit" class="btn btn-send">
                        <i class="bi bi-send"></i> إرسال
                    </button>
                </form>
            </div>
        </div>
        
        <!-- روابط التنقل -->
        <div class="d-flex justify-content-between mt-3">
            <a href="dashboard.php" class="btn btn-outline-primary">
                <i class="bi bi-house-door"></i> لوحة التحكم
            </a>
            <a href="status.php" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-text"></i> متابعة حالة الطلب
            </a>
            <a href="index.html" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-left"></i> تسجيل الخروج
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // التمرير إلى أحدث رسالة عند تحميل الصفحة
        window.onload = function() {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };
        
        // إرسال النموذج باستخدام AJAX لتجربة أفضل
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload(); // إعادة تحميل الصفحة لعرض الرسالة الجديدة
                }
            })
            .catch(error => console.error('Error:', error));
        });
        
        // تحديث الدردشة تلقائياً كل 10 ثواني
        setInterval(function() {
            fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMessages = doc.getElementById('chat-messages').innerHTML;
                const currentMessages = document.getElementById('chat-messages').innerHTML;
                
                if (newMessages !== currentMessages) {
                    document.getElementById('chat-messages').innerHTML = newMessages;
                    const chatMessages = document.getElementById('chat-messages');
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            })
            .catch(error => console.error('Error refreshing chat:', error));
        }, 10000); // 10 ثواني
    </script>
</body>
</html>