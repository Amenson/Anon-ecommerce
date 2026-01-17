<?php http_response_code(404); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page non trouvée - AnonShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .error-message {
            font-size: 28px;
            color: #2c3e50;
        }
        .error-description {
            color: #636e72;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-message">Oups ! Page non trouvée</h1>
        <p class="error-description">
            La page que vous recherchez n'existe pas ou a été déplacée.<br>
            Vérifiez l'URL ou retournez à l'accueil.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="index.php" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-house"></i> Accueil
            </a>
            <a href="products.php" class="btn btn-outline-primary btn-lg px-5">
                <i class="bi bi-bag"></i> Produits
            </a>
        </div>

        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
            <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted">
                    <strong>Admin :</strong> URL demandée : <code><?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></code>
                </small>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>