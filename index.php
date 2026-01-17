<?php
session_start();
require_once 'config.php'; // votre connexion PDO
include 'includes/header.php';

// Paramètres de pagination
$perPage = 12;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

// Paramètres de tri (whitelist pour sécurité)
$sortOptions = [
    'newest'     => 'created_at DESC',
    'price_asc'  => 'price ASC',
    'price_desc' => 'price DESC',
    'name'       => 'name ASC',
];
$sort = $_GET['sort'] ?? 'newest';
$orderBy = $sortOptions[$sort] ?? 'created_at DESC';

// Catégories autorisées (slug => nom affiché)
$categoryMap = [
    'electronique' => 'Électronique',
    'mode'        => 'Mode',
    'maison'      => 'Maison',
    'loisirs'     => 'Loisirs',
];
$categoryIcons = [
    'electronique' => 'phone',
    'mode'        => 'shirt',
    'maison'      => 'house',
    'loisirs'     => 'controller',
];
$categorySlug = $_GET['category'] ?? '';
$categoryName = $categoryMap[$categorySlug] ?? '';

// Recherche
$search = trim($_GET['search'] ?? '');

// Construction sécurisée de la clause WHERE et des paramètres
// Construction sécurisée de la clause WHERE et des paramètres (PARAMÈTRES NOMMÉS)
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE (name LIKE :search OR description LIKE :search2)";
    $params['search']  = "%$search%";
    $params['search2'] = "%$search%";
}

if ($categorySlug !== '' && $categoryName !== '') {
    $where .= $where ? " AND category = :category" : "WHERE category = :category";
    $params['category'] = $categorySlug;
}


// Requête COUNT pour pagination
$countSql = "SELECT COUNT(*) FROM products " . $where;
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalProducts = $stmtCount->fetchColumn();
$totalPages = max(1, ceil($totalProducts / $perPage));

// Requête produits principaux (liste paginée)
$sql = "SELECT * FROM products 
        $where 
        ORDER BY $orderBy 
        LIMIT :offset, :perPage";

$stmt = $pdo->prepare($sql);

// Bind des paramètres dynamiques (search, category)
foreach ($params as $key => $value) {
    $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
}

// Bind pagination
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);

$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Requête produits vedettes (8 plus récents, indépendants des filtres)
$featuredSql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 8";
$stmtFeatured = $pdo->prepare($featuredSql);
$stmtFeatured->execute();
$featuredProducts = $stmtFeatured->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour afficher une carte produit (évite la duplication de code)
function displayProductCard($product) {
    ?>
    <div class="col">
        <div class="card h-100 product-card shadow-sm border-0 hover-shadow">
            <div class="position-relative">
                <img src="<?= htmlspecialchars($product['image'] ?? '/img/placeholder.jpg') ?>" 
                     class="card-img-top" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     loading="lazy">
                <?php if (isset($product['stock']) && $product['stock'] <= 0): ?>
                    <span class="position-absolute top-0 end-0 badge bg-danger m-2">Rupture</span>
                <?php elseif (isset($product['stock']) && $product['stock'] <= 5): ?>
                    <span class="position-absolute top-0 end-0 badge bg-warning text-dark m-2">Stock faible !</span>
                <?php endif; ?>
            </div>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                <p class="card-text text-muted flex-grow-1 small">
                    <?= htmlspecialchars(substr($product['description'] ?? '', 0, 80)) ?>...
                </p>
                <div class="mt-auto">
                    <p class="fw-bold fs-5 text-primary mb-2">
                        <?= number_format($product['price'], 2, ',', ' ') ?> CFA
                    </p>
                    <a href="product.php?id=<?= (int)$product['id'] ?>" 
                       class="btn btn-outline-primary w-100">
                        Voir le produit
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Construction de la query string pour pagination (préserve les filtres)
$queryParams = array_filter([
    'search'   => $search ?: null,
    'sort'     => $sort,
    'category' => $categorySlug ?: null,
]);
$queryString = http_build_query($queryParams);
$baseUrl = $queryString ? '?' . $queryString . '&' : '?';
?>
<!-- Bannière promotionnelle -->
    <div class="banner">
        <p>🎉 Surprise ! 15% de réduction sur votre première commande avec le code <strong>Anon15</strong> !</p>
       
    </div>
<!-- HERO SECTION -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="fw-bold display-5">
                    Achetez malin sur <span class="text-primary">AnonShop</span>
                </h1>
                <p class="lead text-muted">
                    Les meilleurs produits au meilleur prix. Livraison rapide et paiement sécurisé.
                </p>
                <a href="product.php" class="btn btn-primary btn-lg me-2">
                    Voir les produits
                </a>
                <a href="register.php" class="btn btn-outline-secondary btn-lg">
                    Créer un compte
                </a>
            </div>
            <div class="col-md-6 text-center">
                <img src="assets/images/newsletter.png" class="img-fluid" alt="Ecommerce">
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center fw-bold mb-4">Nos catégories</h2>
        <div class="row g-4 text-center">
            <?php foreach ($categoryMap as $slug => $name): ?>
            <div class="col-md-3">
                <a href="?category=<?= $slug ?>" class="text-decoration-none">
                    <div class="card shadow-sm h-100 border-0 <?= $categorySlug === $slug ? 'bg-primary text-white' : '' ?>">
                        <div class="card-body">
                            <i class="bi bi-<?= $categoryIcons[$slug] ?> fs-1 <?= $categorySlug === $slug ? 'text-white' : 'text-primary' ?>"></i>
                            <h5 class="mt-3"><?= htmlspecialchars($name) ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PRODUITS VEDETTES -->
<section class="bg-light py-5">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">Produits vedettes</h2>
        <?php if (!empty($featuredProducts)): ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
            <?php foreach ($featuredProducts as $product): ?>
                <?= displayProductCard($product) ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="text-center mt-4">
            <a href="product.php" class="btn btn-primary btn-lg">
                Voir tout le catalogue
            </a>
        </div>
    </div>
</section>

<!-- AVANTAGES -->
<section class="py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <i class="bi bi-truck fs-1 text-primary"></i>
                <h5 class="mt-3">Livraison rapide</h5>
                <p class="text-muted small">Partout au Togo</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-shield-check fs-1 text-primary"></i>
                <h5 class="mt-3">Paiement sécurisé</h5>
                <p class="text-muted small">100% fiable</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-arrow-repeat fs-1 text-primary"></i>
                <h5 class="mt-3">Retour facile</h5>
                <p class="text-muted small">Sous 7 jours</p>
            </div>
            <div class="col-md-3">
                <i class="bi bi-headset fs-1 text-primary"></i>
                <h5 class="mt-3">Support client</h5>
                <p class="text-muted small">Disponible 24/7</p>
            </div>
        </div>
    </div>
</section>

<!-- LISTE DES PRODUITS -->
<div class="container my-5">
    <h1 class="mb-4 text-center fw-bold">
        <?= $categoryName ? 'Catégorie : ' . htmlspecialchars($categoryName) : 'Nos Produits' ?>
    </h1>

    <!-- Barre de recherche + tri (un seul formulaire) -->
    <form method="GET" class="row g-3 align-items-center justify-content-between mb-4">
        <?php if ($categorySlug): ?>
            <input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>">
        <?php endif; ?>
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </div>
        <div class="col-md-4">
            <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Plus récents</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nom A → Z</option>
            </select>
        </div>
    </form>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center py-5">
            <h4>Aucun produit trouvé</h4>
            <p>Essayez d'autres mots-clés ou modifiez les filtres.</p>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($products as $product): ?>
                <?= displayProductCard($product) ?>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination produits" class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>page=<?= $page-1 ?>" aria-label="Précédent">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php
                    $range = 2;
                    $start = max(1, $page - $range);
                    $end   = min($totalPages, $page + $range);
                    for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl ?>page=<?= $page+1 ?>" aria-label="Suivant">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- FAQ -->
<main>
    <h2 class="text-center">Pourquoi choisir AnonShop ?</h2>
    <div class="faq-container container">
        <div class="faq-item">
            <div class="faq-question">Quels types de produits vendez-vous ?</div>
            <div class="faq-answer">
                Nous proposons une large gamme de produits dans les catégories électronique, mode, maison et loisirs.
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Puis-je payer à la livraison ?</div>
            <div class="faq-answer">
                Oui, le paiement à la livraison est disponible partout au Togo. Sélectionnez cette option lors du paiement.
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Si le produit ne me convient pas, que faire ?</div>
            <div class="faq-answer">
                Retour gratuit sous 7 jours pour les produits non utilisés dans leur emballage d'origine. Connectez-vous à votre compte pour initier le retour.
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Comment vous contacter en cas de besoin ?</div>
            <div class="faq-answer">
                Notre service client est disponible 24/7 par téléphone au +228 93 81 46 45, par e-mail à contact@anonShop.tg ou via le chat en direct.
            </div>
        </div>
    </div>
</main>

<script>
    // Accordion FAQ (une seule section ouverte à la fois)
    document.querySelectorAll('.faq-question').forEach(item => {
        item.addEventListener('click', () => {
            const answer = item.nextElementSibling;
            const isActive = answer.classList.contains('show');

            // Fermer toutes les réponses
            document.querySelectorAll('.faq-answer').forEach(ans => ans.classList.remove('show'));
            document.querySelectorAll('.faq-question').forEach(q => q.classList.remove('active'));

            // Ouvrir la section cliquée si elle n'était pas ouverte
            if (!isActive) {
                answer.classList.add('show');
                item.classList.add('active');
            }
        });
    });
</script>

<style>
    /* Styles existants conservés et améliorés */
    .product-card {
        transition: all 0.3s ease;
    }
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 16px 32px rgba(0,0,0,0.12) !important;
    }
    .faq-container {
        background-color: #f8f9fa;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    .faq-question {
        padding: 18px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.3s;
    }
    .faq-question:hover {
        background-color: #e9ecef;
    }
    .faq-question::after {
        content: '▼';
        transition: transform 0.3s;
    }
    .faq-question.active::after {
        transform: rotate(180deg);
    }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        padding: 0 18px;
        transition: max-height 0.4s ease, padding 0.4s ease;
    }
    .faq-answer.show {
        max-height: 300px;
        padding: 18px;
    }
       /* Bannière promotionnelle */
        .banner {
            background: linear-gradient(90deg, #1d4ed8, #3b82f6);
           color: white;
            text-align: center;
            padding: 1rem;
            margin-bottom: 1rem;
            animation: slideIn 0.5s ease-out;
        }
        .banner p {     
            margin: 0;
            font-size: 1.1rem;
        }
        .banner a {
            color: #1d4ed8;
            font-weight: bold;
            text-decoration: none;
            background: #f3f4f6;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
        }
        .banner a:hover {
            background: #e0e7ff;
            color: #1e40af;

        }
        @keyframes slideIn {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
</style>

<?php include 'includes/footer.php'; ?>