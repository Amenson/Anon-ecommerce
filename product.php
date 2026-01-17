<?php
session_start();
require_once 'config.php'; // Connexion PDO
include 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php');
    exit;
}

// Gestion ajout au panier
$successMessage = '';
$errorMessage   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = max(1, (int)$_POST['quantity']);

    if ($product['stock'] <= 0) {
        $errorMessage = 'Ce produit est en rupture de stock.';
    } elseif ($quantity > $product['stock']) {
        $errorMessage = "Quantité demandée supérieure au stock disponible ({$product['stock']}).";
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $quantity;
        } else {
            $_SESSION['cart'][$id] = $quantity;
        }

        // Redirection pour éviter re-post et mettre à jour le badge panier (header inclus après)
        header("Location: product.php?id=$id&added=1");
        exit;
    }
}

// Message de succès après redirection
if (isset($_GET['added']) && $_GET['added'] == 1) {
    $successMessage = 'Produit ajouté au panier avec succès !';
}

// Produits similaires (même catégorie, exclure le courant, 4 aléatoires)
$related = [];
if (!empty($product['category'])) {
    $stmtRelated = $pdo->prepare("
        SELECT * FROM products 
        WHERE category = ? AND id != ? AND stock > 0 
        ORDER BY RAND() 
        LIMIT 4
    ");
    $stmtRelated->execute([$product['category'], $id]);
    $related = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);
}

// Calcul du stock status
$stockStatus = $product['stock'] > 0 ? 'En stock' : 'Rupture de stock';
$stockBadgeClass = $product['stock'] > 0 ? 'bg-success' : 'bg-danger';
$stockWarning = ($product['stock'] > 0 && $product['stock'] <= 5) ? "Plus que {$product['stock']} en stock !" : '';
$disableAddToCart = $product['stock'] <= 0;
?>

<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="container my-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
        <?php if (!empty($product['category'])): ?>
            <li class="breadcrumb-item"><a href="products.php?category=<?= htmlspecialchars($product['category']) ?>">
                <?= htmlspecialchars(ucfirst($product['category'])) ?>
            </a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
    </ol>
</nav>

<div class="container my-5">
    <!-- Messages -->
    <?php if ($successMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $successMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $errorMessage ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <div class="row g-5">
        <!-- Image -->
        <div class="col-lg-6">
            <div class="product-image rounded overflow-hidden shadow-sm">
                <img src="<?= htmlspecialchars($product['image'] ?? '/img/placeholder.jpg') ?>"
                     class="img-fluid w-100"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     id="mainProductImage">
            </div>
        </div>

        <!-- Infos -->
        <div class="col-lg-6">
            <h1 class="display-6 fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>

            <div class="d-flex align-items-center mb-3">
                <span class="badge <?= $stockBadgeClass ?> fs-6 px-3 py-2"><?= $stockStatus ?></span>
                <?php if ($stockWarning): ?>
                    <span class="ms-3 text-warning fw-bold"><?= $stockWarning ?></span>
                <?php endif; ?>
            </div>

            <p class="lead fs-3 text-primary fw-bold mb-4">
                <?= number_format($product['price'], 0, '', ' ') ?> CFA
            </p>

            <p class="text-muted mb-4">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>

            <!-- Formulaire ajout panier -->
            <form method="post" id="addToCartForm">
                <div class="row align-items-end g-3 mb-4">
                    <div class="col-auto">
                        <label for="quantity" class="form-label fw-bold">Quantité</label>
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" id="decrement">-</button>
                            <input type="number"
                                   name="quantity"
                                   id="quantity"
                                   class="form-control text-center"
                                   value="1"
                                   min="1"
                                   max="<?= $product['stock'] ?>"
                                   <?= $disableAddToCart ? 'disabled' : '' ?>
                                   readonly>
                            <button class="btn btn-outline-secondary" type="button" id="increment">+</button>
                        </div>
                    </div>

                    <div class="col-auto">
                        <button type="submit"
                                name="add_to_cart"
                                class="btn btn-primary btn-lg px-5"
                                <?= $disableAddToCart ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus"></i> Ajouter au panier
                        </button>
                    </div>
                </div>
            </form>

            <!-- Infos supplémentaires -->
            <div class="border-top pt-4">
                <small class="text-muted">
                    <i class="bi bi-truck"></i> Livraison rapide partout au Togo<br>
                    <i class="bi bi-shield-check"></i> Paiement sécurisé • Retour sous 7 jours
                </small>
            </div>
        </div>
    </div>

    <!-- Produits similaires -->
    <?php if (!empty($related)): ?>
        <section class="my-5 py-5 border-top">
            <h3 class="fw-bold mb-4 text-center">Produits similaires</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach ($related as $rel): ?>
                    <div class="col">
                        <div class="card h-100 product-card shadow-sm border-0 hover-shadow">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($rel['image'] ?? '/img/placeholder.jpg') ?>"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($rel['name']) ?>"
                                     loading="lazy">
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($rel['name']) ?></h5>
                                <p class="fw-bold fs-5 text-primary mb-3">
                                    <?= number_format($rel['price'], 0, '', ' ') ?> CFA
                                </p>
                                <a href="product.php?id=<?= $rel['id'] ?>" class="btn btn-outline-primary mt-auto">
                                    Voir le produit
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<!-- JavaScript fonctionnel -->
<script>
    // Gestion des boutons + / - pour la quantité
    const quantityInput = document.getElementById('quantity');
    const incrementBtn  = document.getElementById('increment');
    const decrementBtn  = document.getElementById('decrement');
    const maxStock      = <?= $product['stock'] ?>;

    incrementBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value);
        if (val < maxStock) {
            quantityInput.value = val + 1;
        }
    });

    decrementBtn.addEventListener('click', () => {
        let val = parseInt(quantityInput.value);
        if (val > 1) {
            quantityInput.value = val - 1;
        }
    });

    // Zoom simple sur l'image au survol
    const mainImage = document.getElementById('mainProductImage');
    const productImageContainer = document.querySelector('.product-image');

    productImageContainer.addEventListener('mousemove', (e) => {
        const { left, top, width, height } = productImageContainer.getBoundingClientRect();
        const x = (e.clientX - left) / width * 100;
        const y = (e.clientY - top) / height * 100;
        mainImage.style.transformOrigin = `${x}% ${y}%`;
    });

    productImageContainer.addEventListener('mouseenter', () => {
        mainImage.style.transform = 'scale(2)';
        productImageContainer.style.cursor = 'zoom-in';
    });

    productImageContainer.addEventListener('mouseleave', () => {
        mainImage.style.transform = 'scale(1)';
    });
</script>

<style>
    .product-image {
        overflow: hidden;
        cursor: zoom-in;
    }
    .product-image img {
        transition: transform 0.4s ease;
        transform-origin: center center;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.12) !important;
    }
</style>

<?php include 'includes/footer.php'; ?>