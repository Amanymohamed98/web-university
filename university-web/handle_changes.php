<?php
session_start();

// تحقق من تسجيل الدخول كجامعة
if (!isset($_SESSION['university_id'])) {
    echo "<script>alert('❌ يرجى تسجيل الدخول أولاً كجامعة'); window.location.href='login.php';</script>";
    exit();
}

$university_id = $_SESSION['university_id'];

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// تحقق من أن الطلبات والإجراءات تم إرسالها
if (isset($_POST['action'])) {
    // بداية المعاملة لضمان الأمان
    $conn->begin_transaction();

    try {
        // معالجة كل طلب
        foreach ($_POST['action'] as $app_id => $action) {
            // التحقق من إذا كان الإجراء هو قبول أو رفض
            if ($action === 'accepted' || $action === 'rejected' || $action === 'pending') {
                // تحديث حالة الطلب في قاعدة البيانات
                $sql = "UPDATE applications SET status = ? WHERE id = ? AND university_id = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    throw new Exception("فشل في تحضير الاستعلام: " . $conn->error);
                }

                $stmt->bind_param("sii", $action, $app_id, $university_id);

                if (!$stmt->execute()) {
                    throw new Exception("فشل في تحديث الطلب: " . $stmt->error);
                }
            }
        }

        // إذا تم تنفيذ كل شيء بنجاح، يتم التثبيت (commit) للمعاملة
        $conn->commit();
        echo "<script>alert('✅ تم حفظ التغييرات بنجاح'); window.location.href='university_dashboard.php';</script>";

    } catch (Exception $e) {
        // في حال حدوث خطأ، نقوم بالتراجع (rollback) عن المعاملة
        $conn->rollback();
        echo "<script>alert('❌ حدث خطأ أثناء حفظ التغييرات: " . $e->getMessage() . "'); window.location.href='university_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('❌ لم يتم إرسال البيانات بشكل صحيح'); window.location.href='university_dashboard.php';</script>";
}

$conn->close();
?>
