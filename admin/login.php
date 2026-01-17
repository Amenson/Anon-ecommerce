<?php
session_start();
require_once '../config.php';

// Redirection si déjà connecté
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$messageType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {

                session_regenerate_id(true);

                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                header('Location: dashboard.php');
                exit;
            } else {
                $message = 'Nom d’utilisateur ou mot de passe incorrect.';
            }
        } catch (PDOException $e) {
            $message = 'Erreur de connexion. Réessayez plus tard.';
            error_log('Erreur login admin : ' . $e->getMessage());
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur - AnonShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #fd7e14;
            --bg-gradient-start: #667eea;
            --bg-gradient-end: #764ba2;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-start) 0%, var(--bg-gradient-end) 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 35px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 440px;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-8px);
        }

        .login-card h2 {
            text-align: center;
            color: var(--primary);
            margin-bottom: 35px;
            font-weight: 700;
            font-size: 28px;
        }

        .form-control {
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            font-size: 16px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.3);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            font-size: 17px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: var(--secondary);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(253, 126, 20, 0.4);
        }

        .alert {
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo i {
            font-size: 60px;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2>Connexion Administrateur</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="mb-3">
              <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" 
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-4">
                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">© <?= date('Y') ?> AnonShop - Panel Administration</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>