<?php
session_start();
require_once '../config.php';

// Protection admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'ID de commande invalide.'];
    header('Location: dashboard.php');
    exit;
}

$order_id = (int)$_GET['id'];

// Récupération de la commande avec infos client
$stmt = $pdo->prepare("
    SELECT o.*, u.name AS user_name, u.email AS user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Commande non trouvée.'];
    header('Location: dashboard.php');
    exit;
}

// Récupération des items de la commande
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $validStatuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

    if (in_array($newStatus, $validStatuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $order_id]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => "Statut de la commande mis à jour : $newStatus"];
        
        // Optionnel : actions spécifiques (ex: remboursement si cancelled, mais à implémenter plus tard)
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Statut invalide.'];
    }

    header("Location: view_order.php?id=$order_id");
    exit;
}

// Message flash
if (isset($_SESSION['flash'])) {
    echo "<script>showToast('" . addslashes($_SESSION['flash']['message']) . "', '" . $_SESSION['flash']['type'] . "');</script>";
    unset($_SESSION['flash']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation Commande #<?= $order_id ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .order-status {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-delivered { background: #c3e6cb; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-receipt"></i> Commande #<?= $order_id ?></h4>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Informations client</h5>
                        <hr>
                        <p><strong>Nom :</strong> <?= htmlspecialchars($order['customer_name'] ?? $order['user_name'] ?? 'Anonyme') ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($order['customer_email'] ?? $order['user_email'] ?? 'N/A') ?></p>
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></p>
                        <p><strong>Adresse :</strong> <?= nl2br(htmlspecialchars($order['customer_address'] ?? 'N/A')) ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5>Statut actuel</h5>
                        <hr>
                        <span class="order-status status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                        <p class="mt-3"><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
                        <p><strong>Total :</strong> <?= number_format($order['total'], 2, '', ' ') ?> CFA</p>
                    </div>
                </div>

                <h5>Articles commandés</h5>
                <hr>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produit</th>
                                <th class="text-center">Quantité</th>
                                <th class="text-end">Prix unitaire</th>
                                <th class="text-end">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../<?= htmlspecialchars($item['image'] ?? 'uploads/placeholder.jpg') ?>"
                                                 class="rounded shadow-sm me-3"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <span><?= htmlspecialchars($item['name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= number_format($item['price'], 2, '', ' ') ?> CFA</td>
                                    <td class="text-end fw-bold"><?= number_format($subtotal, 2, '', ' ') ?> CFA</td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary">
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end"><?= number_format($order['total'], 2, '', ' ') ?> CFA</th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h5 class="mt-5">Validation / Mise à jour du statut</h5>
                <hr>
                <form method="POST" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nouveau statut</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                            <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Payée</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expédiée</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Livrée</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <button type="submit" name="update_status" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Mettre à jour le statut
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>