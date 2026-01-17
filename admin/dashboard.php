<?php
session_start();
require_once '../config.php';

// Protection admin obligatoire
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
// Comptage commandes par statut pour le graphique
$statusCounts = [
    'pending'   => 0,
    'paid'      => 0,
    'shipped'   => 0,
    'delivered' => 0,
    'cancelled' => 0,
];
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['status']] = (int)$row['count'];
}

// Récupérer tous les produits
$stmt = $pdo->query("SELECT id, name, price, stock, image, category FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les commandes avec infos client
$stmt = $pdo->prepare("
    SELECT o.id, o.total, o.status, o.created_at, o.user_id,
           u.name AS client_name, u.email AS client_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion AJAX pour mise à jour du statut (via JS plus bas)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)$_POST['order_id'];
    $newStatus = $_POST['new_status'];

    $validStatuses = ['pending', 'paid', 'shipped', 'cancelled'];
    if (in_array($newStatus, $validStatuses)) {
        $updateStmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $orderId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - AnonShop</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .table thead {
            background: #2c3e50;
            color: white;
        }
        .table th {
            cursor: pointer;
            user-select: none;
        }
        .table th:hover {
            background: #34495e;
        }
        .badge-status {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-shipped { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            z-index: 1050;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.4s ease;
        }
        .toast-notification.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
    <?php
 if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show mx-4 mt-3" role="alert">
        <?= htmlspecialchars($_SESSION['flash']['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    <?php unset($_SESSION['flash']); // Efface après affichage ?>
<?php endif; ?>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0"><i class="bi bi-speedometer2"></i> Dashboard Admin</h1>
                <div>
                    <a href="add_product.php" class="btn btn-light me-2">
                        <i class="bi bi-plus-circle"></i> Ajouter Produit
                    </a>
                    <a href="manage_users.php" class="btn btn-light me-2">
                    <i class="bi bi-people"></i> Gérer Utilisateurs
                    </a>
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>

        <div class="p-4">
            <!-- Résumé rapide -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center p-4 bg-primary text-white">
                        <h4><?= count($products) ?></h4>
                        <p class="mb-0">Produits</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-4 bg-success text-white">
                        <h4><?= count($orders) ?></h4>
                        <p class="mb-0">Commandes</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-4 bg-info text-white">
                        <h4><?= array_reduce($orders, fn($c, $o) => $c + ($o['status'] === 'paid' ? 1 : 0), 0) ?></h4>
                        <p class="mb-0">Payées</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-4 bg-warning text-white">
                        <h4><?= array_reduce($orders, fn($c, $o) => $c + ($o['status'] === 'pending' ? 1 : 0), 0) ?></h4>
                        <p class="mb-0">En attente</p>
                    </div>
                </div>
            </div>

            <!-- Produits -->
            <div class="card mb-5">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-box-seam"></i> Gestion des Produits</h4>
                    <input type="text" id="searchProduct" class="form-control w-25" placeholder="Rechercher un produit...">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="productsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td>
                                        <img src="../<?= htmlspecialchars($p['image'] ?? 'uploads/placeholder.jpg') ?>"
                                             alt="Produit" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    </td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($p['category'] ?? 'Non classé')) ?></td>
                                    <td><?= number_format($p['price'], 0, ',', ' ') ?> CFA</td>
                                    <td>
                                        <span class="badge <?= $p['stock'] <= 5 ? 'bg-danger' : 'bg-secondary' ?>">
                                            <?= $p['stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce produit ?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Commandes -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-cart-check"></i> Gestion des Commandes</h4>
                    <select id="statusFilter" class="form-select w-auto">
                        <option value="all">Tous les statuts</option>
                        <option value="pending">En attente</option>
                        <option value="paid">Payée</option>
                        <option value="shipped">Expédiée</option>
                        <option value="cancelled">Annulée</option>
                    </select>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                <tr data-status="<?= strtolower($o['status']) ?>">
                                    <td>#<?= $o['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($o['client_name'] ?? 'Anonyme') ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($o['client_email'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= number_format($o['total'], 2, '', ' ') ?> CFA</td>
                                    <td>
                                        <select class="form-select form-select-sm badge-status status-<?= strtolower($o['status']) ?> status-select"
                                                data-order-id="<?= $o['id'] ?>"
                                                data-current="<?= strtolower($o['status']) ?>">
                                            <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                            <option value="paid" <?= $o['status'] === 'paid' ? 'selected' : '' ?>>Payée</option>
                                            <option value="shipped" <?= $o['status'] === 'shipped' ? 'selected' : '' ?>>Expédiée</option>
                                            <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
                                        </select>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                                    <td>
                                        <a href="view_order.php?id=<?= $o['id'] ?>" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

 <!-- Graphique statuts commandes -->
<div class="card mb-5">
    <div class="card-header bg-info text-white text-center">
        <h4 class="mb-0"><i class="bi bi-pie-chart"></i> Répartition des commandes par statut</h4>
    </div>
    <div class="card-body">
        <canvas id="statusChart"></canvas>
    </div>
</div>

<!-- Inclusion de Chart.js (à placer une seule fois dans ton layout, idéalement dans <head> ou avant la fermeture </body>) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('statusChart').getContext('2d');

    const totalCommands = <?= array_sum($statusCounts ?? []) ?>;

    const statusData = {
        labels: ['En attente', 'Payées', 'Expédiées', 'Livrées', 'Annulées'],
        datasets: [{
            data: [
                <?= $statusCounts['pending'] ?? 0 ?>,
                <?= $statusCounts['paid'] ?? 0 ?>,
                <?= $statusCounts['shipped'] ?? 0 ?>,
                <?= $statusCounts['delivered'] ?? 0 ?>,
                <?= $statusCounts['cancelled'] ?? 0 ?>
            ],
            backgroundColor: [
                '#ffc107', // pending (jaune)
                '#28a745', // paid (vert)
                '#17a2b8', // shipped (bleu)
                '#6f42c1', // delivered (violet)
                '#dc3545'  // cancelled (rouge)
            ],
            borderColor: '#fff',
            borderWidth: 2,
            hoverBorderWidth: 3
        }]
    };

    new Chart(ctx, {
        type: 'doughnut',
        data: statusData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%', // Pour un doughnut plus aéré (optionnel, tu peux ajuster)
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                title: {
                    display: true,
                    text: 'Nombre total de commandes : ' + totalCommands,
                    font: {
                        size: 16
                    },
                    padding: 20
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ' : ';
                            }
                            label += context.parsed + ' commande(s)';
                            const percent = totalCommands > 0 
                                ? Math.round((context.parsed / totalCommands) * 100) + '%'
                                : '0%';
                            return label + ' (' + percent + ')';
                        }
                    }
                }
            }
        }
    });
</script>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Notification toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} toast-notification`;
            toast.innerHTML = `
                <strong>${type === 'success' ? '✓' : '✗'} ${type === 'success' ? 'Succès' : 'Erreur'}</strong><br>
                ${message}
            `;
            document.getElementById('toastContainer').appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        // Recherche produits
        document.getElementById('searchProduct').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#productsTable tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // Filtre statut commandes
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            document.querySelectorAll('#ordersTable tbody tr').forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                row.style.display = (status === 'all' || rowStatus === status) ? '' : 'none';
            });
        });
        

        // Mise à jour du statut de commande (AJAX)
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.dataset.orderId;
                const newStatus = this.value;
                const row = this.closest('tr');

                fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_status&order_id=${orderId}&new_status=${newStatus}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour le badge et l'attribut data-status
                        row.setAttribute('data-status', newStatus);
                        this.className = `form-select form-select-sm badge-status status-${newStatus} status-select`;

                        showToast(`Statut de la commande #${orderId} mis à jour : ${this.options[this.selectedIndex].text}`, 'success');
                    } else {
                        showToast('Erreur lors de la mise à jour du statut.', 'danger');
                        this.value = this.dataset.current; // Revenir à l'ancien
                    }
                })
                .catch(() => {
                    showToast('Erreur de connexion.', 'danger');
                    this.value = this.dataset.current;
                });
            });
        });

        // Tri des tableaux
        document.querySelectorAll('table th').forEach((th, index) => {
            th.addEventListener('click', () => {
                const table = th.closest('table');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                const isAsc = th.classList.toggle('asc');
                if (!isAsc) th.classList.add('desc');

                rows.sort((a, b) => {
                    const aText = a.children[index].textContent.trim();
                    const bText = b.children[index].textContent.trim();

                    const aNum = parseFloat(aText.replace(/[^0-9.-]+/g,""));
                    const bNum = parseFloat(bText.replace(/[^0-9.-]+/g,""));

                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return isAsc ? aNum - bNum : bNum - aNum;
                    }
                    return isAsc ? aText.localeCompare(bText) : bText.localeCompare(aText);
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });
        // Comptage commandes par statut pour le graphique
         

    </script>
</body>
</html>