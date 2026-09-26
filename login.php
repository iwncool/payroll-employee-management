<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === APP_ADMIN_USER && $password === APP_ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        $_SESSION['success'] = 'Login berhasil.';
        header('Location: index.php?page=dashboard');
        exit;
    }

    $_SESSION['error'] = 'Username atau password salah.';
}

if (is_logged_in()) {
    header('Location: index.php?page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #1d4ed8 0%, #4338ca 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            width: min(420px, calc(100% - 30px));
            background: #fff;
            border-radius: 16px;
            padding: 32px 26px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
        }

        .login-box h2 {
            margin: 0 0 20px;
            text-align: center;
            color: #1e3a8a;
        }

        .login-box .field {
            margin-bottom: 16px;
        }

        .login-box button {
            margin-top: 4px;
        }

        .badge-login {
            display: inline-block;
            width: 100%;
            background: #eff6ff;
            color: #1e3a8a;
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login Admin</h2>
        <div class="badge-login">Username: admin | Password: admin123</div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert danger"><?= e($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Masuk</button>
        </form>
    </div>
</body>
</html>
