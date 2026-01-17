<?php
session_start();
require_once 'config.php';
include 'includes/header.php';

// Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Veuillez vous connecter pour voir vos commandes.'];
    header('Location: login.php'); // Créez une page login si pas encore fait
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupération des commandes de l'utilisateur
$stmt = $pdo->prepare("
    SELECT id, total, status, created_at 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($orders)) {
    $noOrders = true;
} else {
    $noOrders = false;
}

// Message flash
if (isset($_SESSION['flash'])) {
    echo "<script>showToast('" . addslashes($_SESSION['flash']['message']) . "', '" . $_SESSION['flash']['type'] . "');</script>";
    unset($_SESSION['flash']);
}
?>

<div class="container my-5">
    <h1 class="display-6 fw-bold text-center mb-5">
        <i class="bi bi-list-check me-2"></i> Mes Commandes
    </h1>

    <?php if ($noOrders): ?>
        <div class="text-center py-5 bg-light rounded-3 shadow-sm">
            <i class="bi bi-bag-x display-1 text-muted mb-4"></i>
            <h4 class="text-muted">Aucune commande pour le moment</h4>
            <p class="text-muted mb-4">Parcourez nos produits et passez votre première commande !</p>
            <a href="products.php" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-bag-heart"></i> Découvrir les produits
            </a>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th># Commande</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong>#<?= $order['id'] ?></strong></td>
                                    <td><?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="fw-bold"><?= number_format($order['total'], 0, ',', ' ') ?> CFA</td>
                                    <td>
                                        <span class="badge order-status status-<?= strtolower($order['status']) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="order_confirmation.php?id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="bi bi-eye"></i> Voir détails
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="product.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Continuer mes achats
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .order-status {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-paid { background: #d4edda; color: #155724; }
    .status-shipped { background: #d1ecf1; color: #0c5460; }
    .status-delivered { background: #c3e6cb; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
</style>

<?php include 'includes/footer.php'; ?>