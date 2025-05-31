<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// 获取数据库版本
$mysql_version = $conn->server_version;
$mysql_version = implode('.', str_split($mysql_version, 1));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>关于系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .about-card { max-width: 800px; margin: 2rem auto; }
        .info-table td { padding: 0.75rem; border-top: none; }
        .info-table tr:not(:last-child) td { border-bottom: 1px solid #dee2e6; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="admin.php">ZERO - 应用配置管理中心</a>
            <div class="d-flex">
                <a href="admin.php" class="btn btn-outline-light me-2">返回管理</a>
                <a href="logout.php" class="btn btn-outline-light">退出</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="about-card card">
            <div class="card-header bg-white">
                <h5 class="mb-0">系统信息</h5>
            </div>
            <div class="card-body">
                <table class="table info-table">
                    <tbody>
                        <tr>
                            <td width="30%"><strong>系统版本</strong></td>
                            <td>ZERO - 应用配置管理中心 v1.0</td>
                        </tr>
                        <tr>
                            <td><strong>PHP 版本</strong></td>
                            <td><?= phpversion() ?></td>
                        </tr>
                        <tr>
                            <td><strong>MySQL 版本</strong></td>
                            <td><?= $mysql_version ?></td>
                        </tr>
                        <tr>
                            <td><strong>服务器软件</strong></td>
                            <td><?= $_SERVER['SERVER_SOFTWARE'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>开发者信息</strong></td>
                            <td>
                                <p class="mb-1">技术支持：Yi-Zero</p>
                                <p class="mb-1">联系方式：https://yizero.top/</p>
                                <p class="mb-0">最后更新：2025-05-9</p>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>系统依赖</strong></td>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    <li>Bootstrap 5.1.3</li>
                                    <li>MySQL 5.7+</li>
                                    <li>PHP 7.4+</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>



