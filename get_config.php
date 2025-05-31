<?php
header('Content-Type: application/xml; charset=utf-8');
require 'db_config.php';

$app_key = $_GET['app_key'] ?? '';

// 验证应用并更新访问统计
$stmt = $conn->prepare("
    UPDATE applications a 
    LEFT JOIN software_config sc ON a.id = sc.app_id 
    SET a.access_count = a.access_count + 1 
    WHERE a.app_key = ?
");
$stmt->bind_param("s", $app_key);
$stmt->execute();

// 获取配置信息
$stmt = $conn->prepare("
    SELECT sc.* 
    FROM software_config sc
    JOIN applications a ON sc.app_id = a.id
    WHERE a.app_key = ?
");
$stmt->bind_param("s", $app_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $config = $result->fetch_assoc();
    $xml = new SimpleXMLElement('<?xml version="1.0"?><software></software>');
    $xml->addChild('announcement', htmlspecialchars($config['announcement']));
    $xml->addChild('update_content', htmlspecialchars($config['update_content'])); // 新增节点
    $xml->addChild('version', $config['version']);
    $xml->addChild('update_url', $config['update_url']);
    $xml->addChild('force_update', $config['force_update'] ? 'true' : 'false');
    echo $xml->asXML();
} else {
    http_response_code(404);
    echo '<?xml version="1.0"?><error>Invalid app key</error>';
}