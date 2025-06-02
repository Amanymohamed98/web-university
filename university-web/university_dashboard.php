<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['university_id'])) {
    header("Location: login.php");
    exit();
}

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// حساب إجمالي الطلبات
$sql_total = "SELECT COUNT(*) as total FROM applications";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_data = $result_total->fetch_assoc();
$total_applications = $total_data['total'];
$stmt_total->close();

// جلب جميع الطلبات
$sql = "SELECT 
            a.id AS application_id,
            a.status,
            a.gpa,
            a.submission_date,
            a.application_file,
            u.id AS student_id,
            u.name AS student_name,
            u.email AS student_email,
            m.id AS major_id,
            m.name AS major_name
        FROM applications a
        INNER JOIN users u ON a.user_id = u.id
        INNER JOIN majors m ON a.major_id = m.id
        ORDER BY a.submission_date DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("خطأ في إعداد الاستعلام: " . $conn->error);
}

if (!$stmt->execute()) {
    die("خطأ في تنفيذ الاستعلام: " . $stmt->error);
}

$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطلبات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #ffc107;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #fd7e14;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #283593 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .application-card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }
        
        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }
        
        .status-accepted {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-rejected {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        
        .btn-accept {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-reject {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-view {
            background-color: var(--primary-color);
            color: white;
        }
        
        .info-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .application-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- رأس الصفحة -->
    <div class="header text-center">
        <div class="container">
            <h1><i class="bi bi-building"></i> لوحة تحكم الطلبات</h1>
            <p class="lead">عرض جميع طلبات القبول</p>
        </div>
    </div>

    <div class="container mb-5">
        <!-- زر تسجيل الخروج -->
        <div class="text-end mb-3">
            <a href="index.html" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-left"></i> تسجيل الخروج
            </a>
        </div>
      
        <!-- قسم تصحيح الأخطاء -->
        <div class="alert alert-info mt-3">
            
            <p>عدد الطلبات المسترجعة: <?= count($applications) ?></p>
        </div>

        <!-- عرض الطلبات -->
        <?php if (!empty($applications)): ?>
            <div class="row">
                <?php foreach ($applications as $app): ?>
                    <div class="col-lg-6">
                        <div class="card application-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>طلب #<?= $app['application_id'] ?></span>
                                <span class="status-badge 
                                    <?= $app['status'] == 'مقبول' ? 'status-accepted' : 
                                       ($app['status'] == 'مرفوض' ? 'status-rejected' : 'status-pending') ?>">
                                    <?= $app['status'] ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="info-label">اسم الطالب:</p>
                                        <p><?= htmlspecialchars($app['student_name']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="info-label">البريد الإلكتروني:</p>
                                        <p><?= htmlspecialchars($app['student_email']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="info-label">التخصص المطلوب:</p>
                                        <p><?= htmlspecialchars($app['major_name']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="info-label">المعدل التراكمي:</p>
                                        <p><?= htmlspecialchars($app['gpa']) ?></p>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="info-label">تاريخ التقديم:</p>
                                        <p><?= date('Y-m-d H:i', strtotime($app['submission_date'])) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="info-label">المستندات:</p>
                                        <?php if (!empty($app['application_file'])): ?>
                                            <a href="uploads/<?= htmlspecialchars($app['application_file']) ?>" 
                                               target="_blank" 
                                               class="btn btn-view btn-sm">
                                               <i class="bi bi-file-earmark-text"></i> عرض الملف
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">لا يوجد ملف مرفق</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                            <!-- أزرار الإجراءات -->
<?php if ($app['status'] == 'قيد المراجعة'): ?>
    <div class="d-flex justify-content-between mt-3">
        <form method="post" action="process_application.php" class="d-inline">
            <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
            <input type="hidden" name="action" value="accept">
            <button type="submit" class="btn-action btn-accept">
                <i class="bi bi-check-circle"></i> قبول الطلب
            </button>
        </form>
        
        <form method="post" action="process_application.php" class="d-inline">
            <input type="hidden" name="application_id" value="<?= $app['application_id'] ?>">
            <input type="hidden" name="action" value="reject">
            <button type="submit" class="btn-action btn-reject">
                <i class="bi bi-x-circle"></i> رفض الطلب
            </button>
        </form>
    </div>
<?php else: ?>
    <div class="alert alert-secondary mt-3 mb-0 text-center">
        هذا الطلب <?= $app['status'] ?> 
    </div>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center py-4">
                <i class="bi bi-exclamation-triangle display-4"></i>
                <h3 class="mt-3">لا توجد طلبات حالياً</h3>
                <p class="mt-2">لم يتم تقديم أي طلبات بعد.</p>
            </div>
        <?php endif; ?>
    </div>
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <a href="messages.php" class="btn btn-primary btn-lg">
                <i class="bi bi-envelope-fill"></i> الذهاب إلى الرسائل
            </a>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // معالجة جميع النماذج
    document.querySelectorAll('form[action="process_application.php"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const card = form.closest('.application-card');
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            // عرض تحميل
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-arrow-repeat"></i> جاري المعالجة...';
            
            // إرسال البيانات باستخدام Fetch API
            fetch('process_application.php', {
                method: 'POST',
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // تحديث واجهة المستخدم
                    const statusBadge = card.querySelector('.status-badge');
                    statusBadge.textContent = data.new_status;
                    statusBadge.className = 'status-badge ' + data.status_class;
                    
                    // استبدال أزرار الإجراءات برسالة
                    const actionsDiv = form.closest('.d-flex');
                    actionsDiv.innerHTML = `
                        <div class="alert alert-secondary w-100 text-center mb-0">
                            هذا الطلب ${data.new_status}
                        </div>
                    `;
                    
                    // عرض رسالة نجاح
                    showAlert('تم تحديث حالة الطلب بنجاح', 'success');
                } else {
                    showAlert(data.message || 'حدث خطأ أثناء المعالجة', 'danger');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                showAlert('حدث خطأ في الاتصال', 'danger');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    });
    
    // دالة لعرض التنبيهات
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '1000';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // إزالة التنبيه بعد 5 ثواني
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});
</script>
</body>
</html>

<?php
$conn->close();
?>