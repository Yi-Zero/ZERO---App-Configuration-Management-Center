<?php
session_start();
require 'db_config.php';

// 未登录且无令牌时重定向
if (!isset($_SESSION['loggedin']) && !isset($_GET['token'])) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = null;

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? null;

    // 验证密码复杂度
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "密码必须包含大小写字母和数字，且至少8位";
    } elseif ($password !== $confirm_password) {
        $error = "两次输入的密码不一致";
    } else {
        // 根据登录状态或令牌处理
        if (isset($_SESSION['loggedin'])) {
            // 登录用户修改密码
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bind_param("si", $password_hash, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = "密码修改成功！";
            } else {
                $error = "密码修改失败，请稍后再试";
            }
        } elseif ($token) {
            // 令牌验证修改密码
            $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expire > NOW()");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $update_stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, token_expire = NULL WHERE id = ?");
                $update_stmt->bind_param("si", $password_hash, $user['id']);
                
                if ($update_stmt->execute()) {
                    $success = "密码重置成功！";
                } else {
                    $error = "密码重置失败，请重试";
                }
            } else {
                $error = "无效或过期的重置令牌";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>修改密码</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .password-card { max-width: 500px; margin: 5rem auto; }
        .password-strength { height: 3px; transition: all 0.3s; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="password-card card">
            <div class="card-body">
                <h4 class="card-title mb-4"><?= isset($_SESSION['loggedin']) ? '修改密码' : '重置密码' ?></h4>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php else: ?>
                <form method="POST">
                    <?php if (isset($_GET['token'])): ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">新密码</label>
                        <input type="password" name="password" class="form-control" 
                               required pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$">
                        <div class="form-text">必须包含大小写字母和数字，至少8位</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">确认密码</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">提交修改</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // 实时密码强度检测
        document.querySelector('input[name="password"]').addEventListener('input', function(e) {
            const strength = calculateStrength(e.target.value);
            // 可根据强度值更新UI
        });

        function calculateStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            return strength;
        }
    </script>
</body>
</html>



