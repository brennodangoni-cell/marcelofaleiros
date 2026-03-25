<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuário ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel do Blog</title>
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --bg-color: #f3f4f6;
            --card-bg: #ffffff;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --border-light: #e5e7eb;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-lg: 16px;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        .login-box {
            background-color: var(--card-bg);
            padding: 50px 40px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 420px;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            color: var(--text-dark);
            margin-top: 0;
            margin-bottom: 30px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            background-color: #ffffff;
            color: var(--text-dark);
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.2s;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        button:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
        }

        .error {
            color: #ef4444;
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            background-color: #fee2e2;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h1>Painel do Blog</h1>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>

</body>

</html>