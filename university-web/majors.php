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

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // الحصول على معرف الجامعة مع التحقق من الأمان
    $university_id = isset($_GET['university_id']) ? (int)$_GET['university_id'] : 0;

    // استرجاع معلومات الجامعة
    $university_stmt = $conn->prepare("SELECT id, name FROM universities WHERE id = :university_id");
    $university_stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
    $university_stmt->execute();
    
    $university = $university_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$university) {
        header("Location: universities.php"); // توجيه المستخدم إذا كانت الجامعة غير موجودة
        exit();
    }

    // استرجاع جميع التخصصات للجامعة المحددة
    $majors_stmt = $conn->prepare("
        SELECT m.id, m.name, m.required_gpa, m.available_seats, 
               (SELECT COUNT(*) FROM applications WHERE major_id = m.id) AS applicants_count
        FROM majors m 
        WHERE m.university_id = :university_id
        ORDER BY m.name
    ");
    $majors_stmt->bindParam(':university_id', $university_id, PDO::PARAM_INT);
    $majors_stmt->execute();
    
    $majors = $majors_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقديم طلب القبول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .application-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 25px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .form-title {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .university-info {
            background-color: #f1f8ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .major-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .major-details {
            font-size: 0.9rem;
            color: #666;
        }
        .seats-info {
            font-weight: bold;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="application-container">
            <h2 class="form-title"><i class="bi bi-file-earmark-text"></i> تقديم طلب القبول الجامعي</h2>
            
            <!-- معلومات الجامعة -->
            <div class="university-info">
                <h4><i class="bi bi-building"></i> <?php echo htmlspecialchars($university['name']); ?></h4>
            </div>
            
            <form action="submit_application.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="university_id" value="<?php echo $university['id']; ?>">
                
                <!-- معلومات الطالب -->
                <div class="mb-3">
                    <label for="student_name" class="form-label"><i class="bi bi-person"></i> الاسم الكامل:</label>
                    <input type="text" class="form-control" id="student_name" name="student_name" required>
                </div>
                
                <div class="mb-3">
                    <label for="national_id" class="form-label"><i class="bi bi-credit-card"></i> رقم الهوية:</label>
                    <input type="text" class="form-control" id="national_id" name="national_id" required>
                </div>
                
                <div class="mb-3">
                    <label for="gpa" class="form-label"><i class="bi bi-graph-up"></i> المعدل التراكمي:</label>
                    <input type="number" step="0.01" min="0" max="5" class="form-control" id="gpa" name="gpa" required>
                </div>
                
                <!-- قائمة التخصصات -->
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-book"></i> التخصصات المتاحة:</label>
                    
                    <?php if (count($majors) > 0): ?>
                        <select class="form-select" name="major_id" required>
                            <option value="" selected disabled>اختر التخصص المناسب</option>
                            <?php foreach ($majors as $major): ?>
                                <option value="<?php echo $major['id']; ?>">
                                    <?php echo htmlspecialchars($major['name']); ?> 
                                    (المعدل المطلوب: <?php echo $major['required_gpa']; ?>%)
                                    - المقاعد: <?php echo $major['available_seats'] - $major['applicants_count']; ?> / <?php echo $major['available_seats']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <!-- عرض تفاصيل التخصصات -->
                        <div class="mt-3">
                            <h5>تفاصيل التخصصات:</h5>
                            <?php foreach ($majors as $major): ?>
                                <div class="major-option">
                                    <div>
                                        <strong><?php echo htmlspecialchars($major['name']); ?></strong>
                                        <div class="major-details">
                                            المعدل المطلوب: <?php echo $major['required_gpa']; ?>% | 
                                            المقاعد المتاحة: <span class="seats-info"><?php echo $major['available_seats'] - $major['applicants_count']; ?></span> من <?php echo $major['available_seats']; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            لا توجد تخصصات متاحة حالياً في هذه الجامعة.
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- رفع المستندات -->
                <div class="mb-3">
                    <label for="application_file" class="form-label"><i class="bi bi-file-earmark-arrow-up"></i> رفع المستندات المطلوبة:</label>
                    <input class="form-control" type="file" id="application_file" name="application_file" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-muted">(PDF, JPG, PNG - الحد الأقصى 5MB)</small>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> تقديم الطلب</button>
                    <a href="universities.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> العودة للجامعات</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>