<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$app_id = intval($_GET['app_id'] ?? 0);

// 验证应用是否存在
$stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();

if (!$app) {
    header('Location: admin.php');
    exit;
}

// 获取或初始化配置
$stmt = $conn->prepare("SELECT * FROM software_config WHERE app_id = ?");
$stmt->bind_param("i", $app_id);
$stmt->execute();
$config = $stmt->get_result()->fetch_assoc();

// 默认配置值
$default_config = [
    'announcement' => '',
    'update_content' => '',
    'version' => '',
    'update_url' => '',
    'force_update' => 0
];
$config = $config ?: $default_config;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑配置 - <?= htmlspecialchars($app['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .config-card {
            max-width: 800px;
            margin: 2rem auto;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .version-input { max-width: 150px; }
        /* 优化导航栏样式 */
        .nav-custom {
            min-height: 56px;
            padding: 0.5rem 0;
        }
        .nav-label-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.1);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
        }
        .edit-label {
            color: rgba(255,255,255,0.8);
            font-size: 0.9em;
        }
        .app-name-display {
            color: #fff;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-light">
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary nav-custom">
        <div class="container">
            <a class="navbar-brand" href="admin.php">ZERO - 应用配置管理中心</a>
            <div class="d-flex align-items-center gap-3">
                <div class="nav-label-group">
                    <span class="edit-label">正在编辑：</span>
                    <span class="app-name-display"><?= htmlspecialchars($app['name']) ?></span>
                </div>
                <a href="admin.php" class="btn btn-outline-light">返回列表</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="config-card card">
            <div class="card-header bg-white">
                <h5 class="mb-0">版本配置管理</h5>
            </div>
            <div class="card-body">
                <form id="configForm" action="save_config.php" method="POST" novalidate>
                    <input type="hidden" name="app_id" value="<?= $app_id ?>">
                   <!-- 新增应用名称字段 -->
                    <div class="mb-4">
                        <label for="app_name" class="form-label">应用名称</label>
                        <input type="text" class="form-control" id="app_name" name="app_name"
                               value="<?= htmlspecialchars($app['name']) ?>" 
                               pattern=".{2,50}" 
                               required>
                        <div class="invalid-feedback">应用名称需为2-50个字符</div>
                    </div>
                    <!-- 系统公告 -->
                    <div class="mb-4">
                        <label for="announcement" class="form-label">系统公告</label>
                        <textarea class="form-control" id="announcement" name="announcement"
                                  rows="3" placeholder="请输入公告内容..."required><?= htmlspecialchars($config['announcement']) ?></textarea>
                        <div class="invalid-feedback">公告内容不能为空</div>
                    </div>

                    <!-- 新增更新内容 -->
                    <div class="mb-4">
                        <label for="update_content" class="form-label">更新内容</label>
                        <textarea class="form-control" id="update_content" name="update_content"
                                  rows="5" placeholder="请输入更新说明..." required><?= htmlspecialchars($config['update_content']) ?></textarea>
                        <div class="invalid-feedback">更新内容不能为空</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="version" class="form-label">版本号</label>
                            <input type="text" class="form-control version-input" id="version"
                                   name="version" pattern="\d+\.\d+\.\d+" 
                                   value="<?= htmlspecialchars($config['version']) ?>" 
                                   placeholder="例如：1.0.0" required>
                            <div class="invalid-feedback">请输入有效的版本号（格式：x.x.x）</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">强制更新</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" 
                                       name="force_update" value="1" id="forceUpdate"
                                       <?= $config['force_update'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="forceUpdate">
                                    启用强制更新
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="update_url" class="form-label">更新包地址</label>
                        <input type="url" class="form-control" id="update_url" 
                               name="update_url" 
                               value="<?= htmlspecialchars($config['update_url']) ?>"
                               placeholder="https://example.com/update.zip" required>
                        <div class="invalid-feedback">请输入有效的URL地址</div>
                    </div>

                    <div class="d-flex justify-content-between border-top pt-3">
                        <a href="admin.php" class="btn btn-secondary">取消</a>
                        <button type="submit" class="btn btn-primary px-4">保存配置</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        // 实时验证应用名称
        document.getElementById('app_name').addEventListener('input', function(e) {
            const isValid = this.value.length >= 2 && this.value.length <= 50;
            this.classList.toggle('is-invalid', !isValid);
            this.classList.toggle('is-valid', isValid);
        });

        // 实时版本号验证
        (() => {
            const versionInput = document.getElementById('version');
            const versionPattern = /^\d+\.\d+\.\d+$/;
            
            versionInput.addEventListener('input', function(e) {
                const isValid = versionPattern.test(e.target.value);
                versionInput.classList.toggle('is-invalid', !isValid);
                versionInput.classList.toggle('is-valid', isValid);
            });

            // 表单提交验证
            document.getElementById('configForm').addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');
            }, false);
        })();
    </script>
</body>
</html>