<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die('拒绝访问');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('方法不允许');
}

$app_id = intval($_POST['app_id'] ?? 0);

// 验证应用是否存在
$stmt = $conn->prepare("SELECT id FROM applications WHERE id = ?");
$stmt->bind_param("i", $app_id);
$stmt->execute();

if (!$stmt->get_result()->num_rows) {
    $_SESSION['error'] = '应用不存在';
    header('Location: admin.php');
    exit;
}

// 删除应用（依赖外键自动删除配置）
$stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
$stmt->bind_param("i", $app_id);

if ($stmt->execute()) {
    $_SESSION['success'] = '应用删除成功';
} else {
    $_SESSION['error'] = '删除失败: ' . $conn->error;
}

header('Location: admin.php');



