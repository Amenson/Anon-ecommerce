<?php
session_start();
require_once '../config.php';

// Protection admin obligatoire
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Messages flash
$success = '';
$error = '';

// Traitement suppression utilisateur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = (int)$_GET['delete'];

    // Vérifier si l'utilisateur a des commandes (optionnel : empêcher suppression)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Impossible de supprimer : cet utilisateur a des commandes.';
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$userId])) {
            $success = 'Utilisateur supprimé avec succès.';
        } else {
            $error = 'Erreur lors de la suppression.';
        }
    }
}

// Recherche
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like];
}

// Récupération utilisateurs avec pagination
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users $where");
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalUsers / $perPage));

$stmt = $pdo->prepare("SELECT id, name, email, phone, address, created_at FROM users $where ORDER BY created_at DESC LIMIT :offset, :perPage");
foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion Utilisateurs - Admin AnonShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        .admin-container { max-width: 1400px; margin: 30px auto; background: white; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .admin-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px 30px; }
        .table th { cursor: pointer; user-select: none; }
        .toast-notification { position: fixed; top: 20px; right: 20px; min-width: 300px; z-index: 1055; opacity: 0; transform: translateY(-30px); transition: all 0.4s ease; }
        .toast-notification.show { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="bi bi-people"></i> Gestion des Utilisateurs</h1>
                <a href="dashboard.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Retour Dashboard
                </a>
            </div>
        </div>

        <div class="p-4">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Barre de recherche -->
            <form method="GET" class="mb-4">
                <div class="input-group w-50">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, email ou téléphone..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Rechercher</button>
                </div>
            </form>

            <!-- Tableau utilisateurs -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Adresse</th>
                            <th>Inscrit le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">Aucun utilisateur trouvé.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['phone'] ?? 'Non renseigné') ?></td>
                                    <td><?= htmlspecialchars(substr($u['address'] ?? 'Non renseignée', 0, 50)) ?>...</td>
                                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                    <td>
                                        <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i> Modifier
                                        </a>
                                        <a href="?delete=<?= $u['id'] ?>" class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Supprimer cet utilisateur ? (Irreversible si pas de commandes)')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Pagination utilisateurs">
                    <ul class="pagination justify-content-center">
                        <?php
                        $queryString = $search ? '&search=' . urlencode($search) : '';
                        ?>
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page-1 ?><?= $queryString ?>">Précédent</a>
                        </li>
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?><?= $queryString ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page+1 ?><?= $queryString ?>">Suivant</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <div id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} toast-notification shadow-lg`;
            toast.innerHTML = `<strong>${type === 'success' ? '✓' : '✗'}</strong> ${message}`;
            document.getElementById('toastContainer').appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 500); }, 4000);
        }
    </script>
</body>
</html>
