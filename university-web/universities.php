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
    die("<div class='alert alert-danger'>فشل الاتصال بقاعدة البيانات: " . $conn->connect_error . "</div>");
}

// استعلام لجلب جميع الجامعات (الاسم فقط)
$sql = "SELECT id, name FROM universities ORDER BY name";
$result = $conn->query($sql);

// تعريف مصفوفة لتخزين الجامعات
$universities = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $universities[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختر الجامعة</title>
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
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        
        .university-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #283593 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .university-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 25px;
            height: 100%;
        }
        
        .university-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .university-img {
            height: 180px;
            object-fit: contain;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 15px 15px 0 0;
        }
        
        .university-body {
            padding: 20px;
            text-align: center;
        }
        
        .university-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .btn-choose {
            background-color: var(--primary-color);
            border: none;
            padding: 8px 25px;
            font-weight: 500;
        }
        
        .btn-choose:hover {
            background-color: #0d1a6b;
        }
        
        .btn-dashboard {
            background-color: #28a745;
            border: none;
            padding: 10px 30px;
            font-weight: 500;
            margin-top: 30px;
        }
        
        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .university-img {
                height: 150px;
            }
            
            .university-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- رأس الصفحة -->
    <div class="university-header text-center">
        <h1><i class="bi bi-building"></i> اختر الجامعة</h1>
        <p class="lead">الجامعات المتاحة للتقديم</p>
    </div>
    
    <!-- بطاقات الجامعات -->
    <div class="container">
        <div class="row">
            <?php if (!empty($universities)): ?>
                <!-- جامعة الملك سعود -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card university-card">
                        <img src="images\1.png" class="card-img-top university-img" alt="جامعة الملك سعود">
                        <div class="card-body university-body">
                            <h3 class="card-title university-title">جامعة الملك سعود</h3>
                            <a href="majors.php?university_id=1" class="btn btn-primary btn-choose">
                                <i class="bi bi-bookmark-check"></i> اختر التخصص
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- جامعة الملك عبدالعزيز -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card university-card">
                        <img src="images\2.png" class="card-img-top university-img" alt="جامعة الملك عبدالعزيز">
                        <div class="card-body university-body">
                            <h3 class="card-title university-title">جامعة الملك عبدالعزيز</h3>
                            <a href="majors.php?university_id=2" class="btn btn-primary btn-choose">
                                <i class="bi bi-bookmark-check"></i> اختر التخصص
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- جامعة الإمام محمد بن سعود -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card university-card">
                        <img src="images\3.jpg" class="card-img-top university-img" alt="جامعة الإمام محمد بن سعود">
                        <div class="card-body university-body">
                            <h3 class="card-title university-title">جامعة الإمام محمد بن سعود</h3>
                            <a href="majors.php?university_id=3" class="btn btn-primary btn-choose">
                                <i class="bi bi-bookmark-check"></i> اختر التخصص
                            </a>
                        </div>
                    </div>
                </div>
                
               
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card university-card">
                        <img src="images\4.jpg" class="card-img-top university-img" alt="جامعة الإمام محمد بن سعود">
                        <div class="card-body university-body">
                            <h3 class="card-title university-title">جامعة الأمير سلطان</h3>
                            <a href="majors.php?university_id=4" class="btn btn-primary btn-choose">
                                <i class="bi bi-bookmark-check"></i> اختر التخصص
                            </a>
                        </div>
                    </div>
                </div>
                
                 <!-- جامعة الإمام محمد بن سعود -->
                 <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card university-card">
                        <img src="images\5.png" class="card-img-top university-img" alt="جامعة الإمام محمد بن سعود">
                        <div class="card-body university-body">
                            <h3 class="card-title university-title">جامعة الملك فهد للبترول والمعادن
                            </h3>
                            <a href="majors.php?university_id=5" class="btn btn-primary btn-choose">
                                <i class="bi bi-bookmark-check"></i> اختر التخصص
                            </a>
                        </div>
                    </div>
                </div>
                
                 <!-- جامعة الإمام محمد بن سعود -->
                 <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card university-card">
                        <img src="images\6.jpg" class="card-img-top university-img" alt="جامعة الإمام محمد بن سعود">
                        <div class="card-body university-body">
                            <h3 class="card-title university-title">جامعة الدمام</h3>
                            <a href="majors.php?university_id=6" class="btn btn-primary btn-choose">
                                <i class="bi bi-bookmark-check"></i> اختر التخصص
                            </a>
                        </div>
                    </div>
                </div>
                
                
                
                
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> لا توجد جامعات متاحة حالياً
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> العودة للرئيسية
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- زر متابعة حالة الطلب -->
        <div class="text-center mb-5">
            <a href="dashboard.php" class="btn btn-success btn-dashboard">
                <i class="bi bi-file-earmark-text"></i> متابعة حالة الطلب
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // يمكنك إضافة أي تفاعلات JavaScript هنا
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثيرات عند التمرير
            const cards = document.querySelectorAll('.university-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transitionDelay = `${index * 0.1}s`;
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body>
</html>