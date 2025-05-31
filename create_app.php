<?php
session_start();
require 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin'])) {
    http_response_code(403);
    die(json_encode([
        'success' => false,
        'message' => '未授权访问'
    ]));
}

try {
    // 获取并验证输入
    $data = json_decode(file_get_contents('php://input'), true);
    $appName = trim($data['name'] ?? '');

    if (empty($appName)) {
        throw new Exception('应用名称不能为空');
    }

    if (strlen($appName) > 50) {
        throw new Exception('应用名称不能超过50个字符');
    }

    // 生成唯一App Key
    $appKey = bin2hex(random_bytes(16));

    // 插入数据库
    $stmt = $conn->prepare("INSERT INTO applications (name, app_key) VALUES (?, ?)");
    $stmt->bind_param("ss", $appName, $appKey);
    
    if (!$stmt->execute()) {
        throw new Exception($conn->error ?: '创建应用失败');
    }

    // 返回创建结果
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $stmt->insert_id,
            'name' => $appName,
            'app_key' => $appKey
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}