<?php
session_start();
require 'db_config.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// 获取所有应用及配置
$stmt = $conn->prepare("
    SELECT a.*, sc.version, sc.last_updated 
    FROM applications a
    LEFT JOIN software_config sc ON a.id = sc.app_id
");
$stmt->execute();
$result = $stmt->get_result();
$apps = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>ZERO - 应用配置管理中心</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
        .dashboard-card { max-width: 1200px; margin: 2rem auto; }
        .search-box { 
            max-width: 300px; 
            height: 38px;  /* 固定高度方便居中 */
            display: flex;
            align-items: center;  /* 垂直居中 */
        }
        .card-header {
            padding: 0.75rem 1.5rem;  /* 调整内边距 */
            min-height: 60px;  /* 保证最小高度 */
        }
        .header-title {
            line-height: 38px;  /* 与搜索框高度一致 */
            margin: 0;
        }
        .highlight { background-color: #ffec99; }
        .delete-btn { transition: transform 0.2s; }
        .delete-btn:hover { transform: scale(1.05); }
    </style>
</head>
<body class="bg-light">
    <!-- 删除确认模态框 -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">确认删除</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>确定要删除应用 <strong id="appName"></strong> 吗？此操作不可恢复！</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <form id="deleteForm" method="POST" action="delete_app.php">
                        <input type="hidden" name="app_id" id="deleteAppId">
                        <button type="submit" class="btn btn-danger">确认删除</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">ZERO - 应用配置管理中心</a>
            <div class="d-flex">
                            <button class="btn btn-light me-2" data-bs-toggle="modal" data-bs-target="#createAppModal">
                + 新建应用
            </button>
                <a href="api_docs.php" class="btn btn-outline-light me-2">接口文档</a>
                <a href="about.php" class="btn btn-outline-light me-2">关于</a>
                <a href="logout.php" class="btn btn-outline-light">退出</a>
            </div>
        </div>
    </nav>

        <div class="container">
        <div class="dashboard-card card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="header-title">应用列表</h5>
                <div class="search-box">
                    <input type="text" 
                           id="searchInput" 
                           class="form-control h-100"  /* 继承父容器高度 */
                           placeholder="搜索应用名称..." 
                           autocomplete="off">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="appTable">
                        <thead>
                            <tr>
                                <th>应用名称</th>
                                <th>App Key</th>
                                <th>当前版本</th>
                                <th>访问次数</th>
                                <th>最后更新</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apps as $app): ?>
                            <tr data-appname="<?= strtolower(htmlspecialchars($app['name'])) ?>">
                                <td class="app-name"><?= htmlspecialchars($app['name']) ?></td>
                                <td><code class="text-primary"><?= $app['app_key'] ?></code></td>
                                <td><?= $app['version'] ?? '未配置' ?></td>
                                <td><?= number_format($app['access_count']) ?></td>
                                <td><?= $app['last_updated'] ?? '-' ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="edit_config.php?app_id=<?= $app['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary">配置</a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger delete-btn"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal"
                                                data-app-id="<?= $app['id'] ?>"
                                                data-app-name="<?= htmlspecialchars($app['name']) ?>">
                                            删除
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- 在页面底部添加新建应用模态框 -->
<div class="modal fade" id="createAppModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">新建应用</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createAppForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">应用名称</label>
                        <input type="text" 
                               class="form-control"
                               name="name"
                               required
                               placeholder="请输入应用名称"
                               maxlength="50">
                        <div class="invalid-feedback">应用名称不能为空且不超过50字符</div>
                    </div>
                    <div id="createAppError" class="alert alert-danger d-none mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="submit-text">创建</span>
                        <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // 删除确认对话框处理
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const appId = button.data('app-id');
            const appName = button.data('app-name');
            
            $(this).find('#appName').text(appName);
            $(this).find('#deleteAppId').val(appId);
        });

        // 实时搜索功能
        $('#searchInput').on('input', function() {
            const searchTerm = $(this).val().trim().toLowerCase();
            
            $('#appTable tbody tr').each(function() {
                const appName = $(this).find('.app-name').text().toLowerCase();
                const row = $(this);
                
                if (appName.includes(searchTerm)) {
                    row.show();
                    // 高亮匹配内容
                    if (searchTerm) {
                        const regex = new RegExp(`(${searchTerm})`, 'gi');
                        const highlighted = row.find('.app-name').html()
                            .replace(regex, '<span class="highlight">$1</span>');
                        row.find('.app-name').html(highlighted);
                    }
                } else {
                    row.hide();
                }
            });
        });

        // 清除高亮
        $('#searchInput').on('blur', function() {
            $('.app-name').each(function() {
                const text = $(this).text();
                $(this).html(text);
            });
        });

// 新建应用表单提交
document.getElementById('createAppForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const errorDiv = document.getElementById('createAppError');
    const appNameInput = form.querySelector('input[name="name"]');

    // 清除状态
    errorDiv.classList.add('d-none');
    appNameInput.classList.remove('is-invalid');

    // 验证输入
    if (!appNameInput.value.trim()) {
        appNameInput.classList.add('is-invalid');
        return;
    }

    // 显示加载状态
    submitBtn.disabled = true;
    submitBtn.querySelector('.submit-text').classList.add('d-none');
    submitBtn.querySelector('.spinner-border').classList.remove('d-none');

    try {
        const response = await fetch('create_app.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: appNameInput.value.trim()
            })
        });

        const result = await response.json();

        if (result.success) {
            // 关闭模态框
            bootstrap.Modal.getInstance(document.getElementById('createAppModal')).hide();
            
            // 动态插入新应用行
            const tbody = document.querySelector('#appTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${result.data.name}</td>
                <td><code class="text-primary">${result.data.app_key}</code></td>
                <td>未配置</td>
                <td>0</td>
                <td>-</td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="edit_config.php?app_id=${result.data.id}" 
                           class="btn btn-sm btn-outline-primary">配置</a>
                        <form method="POST" action="delete_app.php"
                              onsubmit="return confirm('确定要删除该应用吗？此操作不可恢复！')">
                            <input type="hidden" name="app_id" value="${result.data.id}">
                            <button type="submit" class="btn btn-sm btn-danger">删除</button>
                        </form>
                    </div>
                </td>
            `;
            tbody.insertBefore(newRow, tbody.firstChild);
            
            // 清空表单
            form.reset();
        } else {
            errorDiv.textContent = result.message || '创建失败，请稍后重试';
            errorDiv.classList.remove('d-none');
        }
    } catch (error) {
        errorDiv.textContent = '网络请求失败，请检查连接';
        errorDiv.classList.remove('d-none');
    } finally {
        submitBtn.disabled = false;
        submitBtn.querySelector('.submit-text').classList.remove('d-none');
        submitBtn.querySelector('.spinner-border').classList.add('d-none');
    }
});

    </script>
</body>
</html>