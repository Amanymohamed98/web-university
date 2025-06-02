<?php
session_start();
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

// التحقق من إرسال البيانات
$message = ""; // متغير لتخزين الرسائل
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_name = $_POST['student_name'];
    $national_id = $_POST['national_id'];
    $gpa = $_POST['gpa'];
    $university_id = $_POST['university_id'];
    $major_id = $_POST['major_id'];
    $user_id = $_SESSION['user_id']; // معرف المستخدم

    // التحقق من تحميل الملف
    if (isset($_FILES['application_file']) && $_FILES['application_file']['error'] == 0) {
        $file_name = $_FILES['application_file']['name'];
        $file_tmp = $_FILES['application_file']['tmp_name'];
        $file_size = $_FILES['application_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // التحقق من نوع الملف (PDF أو صورة)
        $allowed_extensions = ["pdf", "jpg", "jpeg", "png"];
        if (!in_array($file_ext, $allowed_extensions)) {
            $message = "<div class='alert alert-danger'>❌ خطأ: يجب أن يكون الملف بصيغة PDF أو صورة (JPG, JPEG, PNG).</div>";
        } elseif ($file_size > 5242880) { // 5MB حد أقصى
            $message = "<div class='alert alert-danger'>❌ خطأ: حجم الملف يجب أن لا يتجاوز 5MB.</div>";
        } else {
            // تحديد مسار الحفظ
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // إنشاء المجلد إذا لم يكن موجودًا
            }

            // إعادة تسمية الملف ليكون فريدًا
            $new_file_name = time() . "_" . $user_id . "." . $file_ext;
            $upload_path = $upload_dir . $new_file_name;

            // رفع الملف
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // إدخال البيانات في قاعدة البيانات
                $stmt = $conn->prepare("INSERT INTO applications (user_id, university_id, major_id, student_name, national_id, gpa, application_file) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiissds", $user_id, $university_id, $major_id, $student_name, $national_id, $gpa, $upload_path);

                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>✅ تم تقديم طلبك بنجاح! سيتم مراجعته من قبل المسؤولين.</div>";
                    
                    // إرسال بريد إلكتروني تأكيدي (يمكن تفعيله لاحقاً)
                    // send_confirmation_email($_SESSION['user_email'], $student_name);
                } else {
                    $message = "<div class='alert alert-danger'>❌ حدث خطأ أثناء تقديم الطلب: " . $stmt->error . "</div>";
                }

                $stmt->close();
            } else {
                $message = "<div class='alert alert-danger'>❌ فشل في رفع الملف. يرجى المحاولة مرة أخرى.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ يجب رفع مستند التقديم المطلوب.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقديم الطلب الجامعي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }
        
        .application-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 15px;
        }
        
        .application-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .application-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #283593 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: none;
        }
        
        .card-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .card-body {
            padding: 30px;
            background-color: white;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 35, 126, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px 25px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #0d1a6b;
            transform: translateY(-2px);
        }
        
        .btn-outline-secondary {
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 500;
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .steps-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .steps-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: #e0e0e0;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 2;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: #4caf50;
            color: white;
        }
        
        .step-text {
            font-size: 0.9rem;
            color: #999;
        }
        
        .step.active .step-text,
        .step.completed .step-text {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .application-container {
                padding: 0 10px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .steps-indicator {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .step {
                margin: 0 10px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="application-container">
        <div class="card application-card">
            <div class="card-header">
                <h2><i class="bi bi-file-earmark-text"></i> تقديم طلب القبول الجامعي</h2>
            </div>
            
            <div class="card-body">
                <!-- مؤشر الخطوات -->
                <div class="steps-indicator">
                    <div class="step completed">
                        <div class="step-number">1</div>
                        <div class="step-text">اختيار الجامعة</div>
                    </div>
                    <div class="step completed">
                        <div class="step-number">2</div>
                        <div class="step-text">اختيار التخصص</div>
                    </div>
                    <div class="step active">
                        <div class="step-number">3</div>
                        <div class="step-text">إدخال البيانات</div>
                    </div>
                    <div class="step">
                        <div class="step-number">4</div>
                        <div class="step-text">إكمال الطلب</div>
                    </div>
                </div>
                
                <?= $message; ?>
                
                <div class="text-center mb-4">
                    <h4 class="text-primary">تم إرسال طلبك بنجاح!</h4>
                    <p class="text-muted">سيتم مراجعة طلبك من قبل المسؤولين وسيتم إعلامك بالنتيجة عبر البريد الإلكتروني.</p>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                </div>
                
                <div class="d-grid gap-3">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="bi bi-house-door"></i> العودة للوحة التحكم
                    </a>
                    <a href="universities.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> العودة لقائمة الجامعات
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>