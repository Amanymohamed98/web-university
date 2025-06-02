<?php
session_start();

// تحقق من تسجيل الدخول
if (!isset($_SESSION['university_id'])) {
    echo "<script>alert('❌ يرجى تسجيل الدخول أولاً'); window.location.href='login.php';</script>";
    exit();
}

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// جلب جميع الرسائل مع معلومات الطلاب والجامعات
$sql = "SELECT 
            m.*, 
            u.name AS student_name,
            univ.name AS university_name
        FROM messages m
        JOIN users u ON m.user_id = u.id
        JOIN universities univ ON m.university_id = univ.id
        ORDER BY m.created_at DESC";  // تغيير message_date إلى created_at إذا كان هذا هو الاسم الصحيح للحقل

$result = $conn->query($sql);

// تحقق من وجود أخطاء في الاستعلام
if ($result === false) {
    die("خطأ في استعلام SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>جميع الرسائل</title>
    <style>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        .status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .pending {
            background-color: #f39c12;
            color: white;
        }

        .replied {
            background-color: #2ecc71;
            color: white;
        }

        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>

 
<a href="index.html" class="logout-btn">تسجيل الخروج</a>

<h2>📩 جميع الرسائل في النظام</h2>

<div class="table-container">
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>الجامعة</th>
                    <th>الرسالة</th>
                    <th>تاريخ الإرسال</th>
                    <th>حالة الرد</th>
                    <th>الرد</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name'] ?? 'غير متوفر') ?></td>
                        <td><?= htmlspecialchars($row['university_name'] ?? 'غير متوفر') ?></td>
                        <td><?= htmlspecialchars($row['message'] ?? 'بدون رسالة') ?></td>
                        <td>
                            <?= isset($row['message_date']) ? date('Y-m-d H:i', strtotime($row['message_date'])) : 
                               (isset($row['created_at']) ? date('Y-m-d H:i', strtotime($row['created_at'])) : 
                               'غير معروف') ?>
                        </td>
                        <td>
                            <span class="status <?= !empty($row['response']) ? 'replied' : 'pending' ?>">
                                <?= !empty($row['response']) ? 'تم الرد' : 'بانتظار الرد' ?>
                            </span>
                        </td>
                        <td>
                            <?php if (empty($row['response'])): ?>
                                <a href="reply.php?message_id=<?= $row['id'] ?>">
                                    <button class="btn reply">الرد</button>
                                </a>
                            <?php else: ?>
                                <button class="btn" disabled>تم الرد</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-messages">لا توجد رسائل في النظام حالياً</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$conn->close();
?>