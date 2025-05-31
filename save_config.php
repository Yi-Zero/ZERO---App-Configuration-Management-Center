<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['loggedin'])) {
    $_SESSION['error'] = '请先登录';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = '无效的请求方法';
    header('Location: admin.php');
    exit;
}

$app_id = intval($_POST['app_id'] ?? 0);
$app_name = trim($_POST['app_name'] ?? '');

// 基础验证
if (empty($app_name) || strlen($app_name) < 2 || strlen($app_name) > 50) {
    $_SESSION['error'] = '应用名称需为2-50个字符';
    header("Location: edit_config.php?app_id=$app_id");
    exit;
}

// 检查应用名称是否重复
$check_stmt = $conn->prepare("SELECT id FROM applications WHERE name = ? AND id != ?");
$check_stmt->bind_param("si", $app_name, $app_id);
$check_stmt->execute();

if ($check_stmt->get_result()->num_rows > 0) {
    $_SESSION['error'] = '应用名称已存在';
    header("Location: edit_config.php?app_id=$app_id");
    exit;
}

// 开启事务
$conn->begin_transaction();

try {
    // 更新应用名称
    $app_stmt = $conn->prepare("UPDATE applications SET name = ? WHERE id = ?");
    $app_stmt->bind_param("si", $app_name, $app_id);
    if (!$app_stmt->execute()) {
        throw new Exception('更新应用名称失败');
    }

    // 更新配置信息（原有逻辑）
    $announcement = htmlspecialchars($_POST['announcement']);
    $update_content = htmlspecialchars($_POST['update_content']);
    $version = preg_match('/^\d+\.\d+\.\d+$/', $_POST['version']) ? $_POST['version'] : '';
    $update_url = filter_var($_POST['update_url'], FILTER_VALIDATE_URL);
    $force_update = isset($_POST['force_update']) ? 1 : 0;

    $config_stmt = $conn->prepare("
        INSERT INTO software_config 
        (app_id, announcement, update_content, version, update_url, force_update) 
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        announcement = VALUES(announcement),
        update_content = VALUES(update_content),
        version = VALUES(version),
        update_url = VALUES(update_url),
        force_update = VALUES(force_update)
    ");
    $config_stmt->bind_param("issssi", $app_id, $announcement, $update_content, $version, $update_url, $force_update);
    
    if (!$config_stmt->execute()) {
        throw new Exception('更新配置失败');
    }

    $conn->commit();
    header("Location: admin.php");
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header("Location: edit_config.php?app_id=$app_id");
    exit;
}