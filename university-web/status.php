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

// جلب حالة الطلب من جدول "applications" مع التخصص والجامعة
$sql = "
    SELECT a.*, s.name AS specialization, u.name AS university
    FROM applications a
    LEFT JOIN majors s ON a.major_id = s.id
    LEFT JOIN universities u ON a.university_id = u.id
    WHERE a.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();

// تحديد حالة الطلب مع القيم الافتراضية
$order_status = $order ? $order['status'] : 'لا توجد حالة طلب حالياً.';
$order_specialization = $order ? $order['specialization'] : 'غير محدد';
$order_university = $order ? $order['university'] : 'غير محددة';

// دالة لتحديد لون حالة الطلب
function getStatusColor($status) {
    switch($status) {
        case 'مقبول': return 'success';
        case 'مرفوض': return 'danger';
        case 'قيد المراجعة': return 'warning';
        case 'قيد الانتظار': return 'info';
        default: return 'secondary';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl"> <!-- إضافة dir="rtl" -->
<head>
    <meta charset="UTF-8">
    <title>حالة الطلب - بيانات الطالب</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- إضافة روابط Bootstrap وأيقونات -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f7fa;
            text-align: right; /* محاذاة النصوص لليمين */
        }
        .status-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-header {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
        }
        /* تأكد من أن كل العناصر ستكون بمحاذاة اليمين */
        .card, .alert, .row, .col-md-6, .d-flex {
            text-align: right;
            direction: rtl;
        }
        /* تعديلات للأيقونات لتكون على اليمين */
        .bi {
            margin-left: 5px;
            margin-right: 0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card status-card">
            <div class="card-header status-header text-white">
                <h3 class="text-center"><i class="bi bi-file-earmark-text"></i> حالة الطلب الجامعي</h3>
            </div>
            <div class="card-body">
                <!-- معلومات الطالب -->
                <div class="mb-4">
                    <h4><i class="bi bi-person-circle"></i> معلومات الطالب</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>الاسم:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                        </div>
                    </div>
                </div>
                
               
                <!-- حالة الطلب -->
                <div class="alert alert-<?php echo getStatusColor($order_status); ?>">
                    <h4><i class="bi bi-info-circle"></i> حالة الطلب الحالية:</h4>
                    <p class="mb-1"><strong>الحالة:</strong> <?php echo htmlspecialchars($order_status); ?></p>
                    <p class="mb-1"><strong>الجامعة:</strong> <?php echo htmlspecialchars($order_university); ?></p>
                    <p class="mb-1"><strong>التخصص:</strong> <?php echo htmlspecialchars($order_specialization); ?></p>
                    <?php if ($order && isset($order['created_at'])): ?>
                        <p class="mb-0"><small>تاريخ التقديم: <?php echo date('Y-m-d', strtotime($order['created_at'])); ?></small></p>
                    <?php endif; ?>
                </div>
                
                <!-- رسالة إضافية حسب الحالة -->
                <?php if ($order_status == 'مقبول'): ?>
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle"></i> تهانينا!</h5>
                        <p>تم قبول طلبك في <?php echo htmlspecialchars($order_university); ?>.</p>
                    </div>
                <?php elseif ($order_status == 'مرفوض'): ?>
                    <div class="alert alert-danger">
                        <h5><i class="bi bi-x-circle"></i> نأسف لإبلاغك</h5>
                        <p>تم رفض طلبك للالتحاق ب<?php echo htmlspecialchars($order_university); ?>.</p>
                    </div>
                <?php elseif (!$order): ?>
                    <div class="alert alert-warning">
                        <h5><i class="bi bi-exclamation-triangle"></i> لا يوجد طلب مسجل</h5>
                        <p>لم تقم بتقديم أي طلب للقبول الجامعي بعد.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h5><i class="bi bi-hourglass"></i> جاري المراجعة</h5>
                        <p>طلبك قيد المراجعة من قبل <?php echo htmlspecialchars($order_university); ?>.</p>
                    </div>
                <?php endif; ?>
                
                <!-- أزرار التنقل -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="bi bi-house-door"></i> لوحة التحكم
                    </a>
                   
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>