<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['university_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit(json_encode(['success' => false, 'message' => 'غير مصرح به']));
}

// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "university_system");

if ($conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    exit(json_encode(['success' => false, 'message' => 'فشل الاتصال بقاعدة البيانات']));
}

// التحقق من البيانات المرسلة
if (!isset($_POST['application_id']) || !isset($_POST['action'])) {
    header("HTTP/1.1 400 Bad Request");
    exit(json_encode(['success' => false, 'message' => 'بيانات غير صالحة']));
}

$application_id = intval($_POST['application_id']);
$action = $_POST['action'];

// تحديد الحالة الجديدة
$new_status = ($action == 'accept') ? 'مقبول' : 'مرفوض';

// تحديث حالة الطلب
$sql = "UPDATE applications SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    header("HTTP/1.1 500 Internal Server Error");
    exit(json_encode(['success' => false, 'message' => 'خطأ في إعداد الاستعلام']));
}

$stmt->bind_param("si", $new_status, $application_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'new_status' => $new_status,
        'status_class' => ($new_status == 'مقبول') ? 'status-accepted' : 'status-rejected'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'فشل في تحديث الحالة']);
}

$stmt->close();
$conn->close();
?>