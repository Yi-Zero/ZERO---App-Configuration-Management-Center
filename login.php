<?php
session_start();
require 'db_config.php';

// 如果已经登录则跳转
if (isset($_SESSION['loggedin'])) {
    header('Location: admin.php');
    exit;
}

// 登录尝试限制配置
$max_attempts = 5;
$lock_time = 300; // 5分钟

// 初始化错误信息
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF 保护
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = '无效的请求';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // 获取用户信息
        $stmt = $conn->prepare("
            SELECT id, username, password_hash, login_attempts, locked_until 
            FROM users 
            WHERE username = ?
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        // 验证流程
        if ($user) {
            // 检查账户锁定状态
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $error = '账户已锁定，请稍后再试';
            } elseif (password_verify($password, $user['password_hash'])) {
                // 登录成功
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                
                // 重置登录尝试
                $conn->query("
                    UPDATE users 
                    SET login_attempts = 0, 
                        locked_until = NULL,
                        last_login = NOW()
                    WHERE id = {$user['id']}
                ");
                
                header('Location: admin.php');
                exit;
            } else {
                // 登录失败处理
                $new_attempts = $user['login_attempts'] + 1;
                $locked_until = ($new_attempts >= $max_attempts) 
                    ? date('Y-m-d H:i:s', time() + $lock_time)
                    : null;

                $conn->query("
                    UPDATE users 
                    SET login_attempts = $new_attempts,
                        locked_until = '$locked_until'
                    WHERE id = {$user['id']}
                ");

                $remaining = $max_attempts - $new_attempts;
                $error = $remaining > 0 
                    ? "密码错误，剩余尝试次数：$remaining"
                    : "账户已锁定，请5分钟后再试";
            }
        } else {
            // 用户不存在
            $error = '用户名或密码错误';
        }
    }
}

// 生成CSRF令牌
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZERO - 应用配置管理中心</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --gradient-start: #4f46e5;
            --gradient-end: #8b5cf6;
        }

        body {
            background: linear-gradient(135deg, 
                var(--gradient-start), 
                var(--gradient-end));
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .login-header {
            background: var(--primary-color);
            padding: 2rem;
            text-align: center;
        }

        .login-title {
            color: white;
            font-weight: 600;
            margin: 0;
        }

        .input-group-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            z-index: 4;
        }

        .form-control {
            padding-left: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            height: 3rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .spinner {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card mx-auto" style="max-width: 400px;">
            <div class="login-header">
                <h2 class="login-title"><i class="fas fa-lock me-2"></i>管理员登录</h2>
            </div>
            
            <div class="card-body p-4">
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-4">
                        <label class="form-label text-muted">用户名</label>
                        <div class="input-group-icon">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   name="username" 
                                   class="form-control"
                                   required
                                   autocomplete="username"
                                   placeholder="请输入用户名">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted">密码</label>
                        <div class="input-group-icon">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   name="password" 
                                   class="form-control"
                                   required
                                   autocomplete="current-password"
                                   placeholder="请输入密码">
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" 
                                class="btn btn-login text-white"
                                id="loginBtn">
                            <span class="submit-text">立即登录</span>
                            <span class="spinner-border spinner-border-sm" 
                                  role="status"
                                  aria-hidden="true"
                                  style="display: none;"></span>
                        </button>
                    </div>
                    <div class="text-center mt-3">
                       <a href="forgot_password.php"   class="text-decoration-none">忘记密码？</a>
                    </div>
                    <div class="text-center text-muted">
                        <small>首次使用？请联系系统管理员创建账号</small>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary spinner" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 登录表单提交处理
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.querySelector('.submit-text').style.display = 'none';
            btn.querySelector('.spinner-border').style.display = 'inline-block';
            document.getElementById('loadingOverlay').style.display = 'flex';
        });

        // 输入框互动效果
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-icon').style.color = '#4f46e5';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.input-icon').style.color = '#6b7280';
            });
        });
    </script>
</body>
</html>



