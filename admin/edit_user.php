<?php
session_start();
require_once '../config.php';

// Protection admin obligatoire
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
// Messages
$success = '';
$error = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = 'ID utilisateur invalide.';
} else {
    $user_id = (int)$_GET['id'];

    // Récupération utilisateur
    $stmt = $pdo->prepare("SELECT id, name, email, phone, address, is_blocked FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = 'Utilisateur non trouvé.';
    }
}

// Traitement mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $is_blocked = isset($_POST['is_blocked']) ? 1 : 0;

    $errors = [];
    if (empty($name)) $errors[] = 'Le nom est obligatoire.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';

    // Vérification email unique (sauf pour cet utilisateur)
    if (!empty($email) && $email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET name = ?, email = ?, phone = ?, address = ?, is_blocked = ? 
            WHERE id = ?
        ");
        $stmt->execute([$name, $email, $phone, $address, $is_blocked, $user_id]);

        $success = 'Utilisateur modifié avec succès !';

        // Recharger les données
        $stmt = $pdo->prepare("SELECT id, name, email, phone, address, is_blocked FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = implode('<br>', $errors);
    }
}

// Optionnel : Réinitialisation mot de passe par admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $new_password = 'nouveau_mot_de_passe123'; // Ou générer aléatoire
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $user_id]);

    $success = "Mot de passe réinitialisé ! Nouveau mot de passe : $new_password (changez-le immédiatement)";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier Utilisateur - Admin AnonShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .admin-container { max-width: 900px; margin: 30px auto; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .admin-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header text-center">
            <h3><i class="bi bi-person-gear"></i> Modifier l'utilisateur #<?= $user_id ?></h3>
        </div>

        <div class="p-4">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($user): ?>
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nom complet</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Téléphone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Statut du compte</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_blocked" id="blockSwitch" <?= $user['is_blocked'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="blockSwitch">
                                <?= $user['is_blocked'] ? '<span class="text-danger">Bloqué</span>' : '<span class="text-success">Actif</span>' ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Adresse de livraison</label>
                        <textarea name="address" rows="4" class="form-control"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button type="submit" name="update_user" class="btn btn-primary btn-lg me-3">
                            <i class="bi bi-save"></i> Enregistrer
                        </button>
                        <a href="manage_users.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </form>

            <!-- Optionnel : Réinitialisation mot de passe par admin -->
            <hr class="my-5">
            <h5 class="text-danger"><i class="bi bi-key"></i> Réinitialiser le mot de passe</h5>
            <p class="text-muted">Attention : Cela définira un mot de passe temporaire que l'utilisateur devra changer.</p>
            <form method="POST" onsubmit="return confirm('Réinitialiser le mot de passe ?')">
                <button type="submit" name="reset_password" class="btn btn-danger">
                    <i class="bi bi-shield-lock"></i> Réinitialiser mot de passe
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>