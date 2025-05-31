<?php
session_start();
require 'db_config.php';

$step = $_GET['step'] ?? 1;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

// 清理会话数据
if ($step == 1) {
    unset($_SESSION['reset_user']);
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['step']) {
        case 1:
            $username = trim($_POST['username']);
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if ($user) {
                $_SESSION['reset_user'] = $user;
                header('Location: forgot_password.php?step=2');
                exit;
            } else {
                $_SESSION['error'] = "用户名不存在";
                header('Location: forgot_password.php');
                exit;
            }
            break;

        case 2:
            $user = $_SESSION['reset_user'];
            $answer = trim($_POST['answer']);

            if (password_verify($answer, $user['security_answer'])) {
                header('Location: forgot_password.php?step=3');
                exit;
            } else {
                $_SESSION['error'] = "安全答案错误";
                header('Location: forgot_password.php?step=2');
                exit;
            }
            break;

        case 3:
            $user = $_SESSION['reset_user'];
            $new_pw = $_POST['password'];
            
            if (strlen($new_pw) < 8 || !preg_match('/[A-Za-z0-9]/', $new_pw)) {
                $_SESSION['error'] = "密码需至少8位且包含字母数字";
                header('Location: forgot_password.php?step=3');
                exit;
            }

            $hashed_pw = password_hash($new_pw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_pw, $user['id']);
            
            if ($stmt->execute()) {
                session_destroy();
                header('Location: login.php?reset=success');
                exit;
            } else {
                $_SESSION['error'] = "密码更新失败";
                header('Location: forgot_password.php?step=3');
                exit;
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>重置密码</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
            <div class="card-body">
                <h3 class="card-title mb-4">重置密码</h3>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($step == 1): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="1">
                    <div class="mb-3">
                        <label class="form-label">用户名</label>
                        <input type="text" 
                               class="form-control"
                               name="username"
                               required
                               autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">下一步</button>
                </form>

                <?php elseif ($step == 2): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="2">
                    <div class="mb-3">
                        <label class="form-label">安全问题</label>
                        <p class="form-control-plaintext bg-light p-2 rounded">
                            <?= htmlspecialchars($_SESSION['reset_user']['security_question']) ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">请输入答案</label>
                        <input type="text" 
                               class="form-control"
                               name="answer"
                               required
                               autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">验证答案</button>
                </form>

                <?php elseif ($step == 3): ?>
                <form method="POST">
                    <input type="hidden" name="step" value="3">
                    <div class="mb-3">
                        <label class="form-label">新密码</label>
                        <input type="password" 
                               class="form-control"
                               name="password"
                               pattern="(?=.*\d)(?=.*[a-zA-Z]).{8,}"
                               required>
                        <small class="form-text">需至少8位，包含字母和数字</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">确认密码</label>
                        <input type="password" 
                               class="form-control"
                               name="confirm_password"
                               required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">确认修改</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>