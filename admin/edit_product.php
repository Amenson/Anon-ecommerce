<?php
session_start();
require_once '../config.php'; // Connexion PDO

// Protection admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Messages flash
$success = '';
$error   = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error = 'ID du produit invalide.';
} else {
    $id = (int)$_GET['id'];

    // Récupération du produit
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $error = 'Produit non trouvé.';
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $category    = $_POST['category'] ?? '';

    // Validation
    $errors = [];
    if (empty($name)) $errors[] = 'Le nom est obligatoire.';
    if (empty($description)) $errors[] = 'La description est obligatoire.';
    if ($price <= 0) $errors[] = 'Le prix doit être supérieur à 0.';
    if ($stock < 0) $errors[] = 'Le stock ne peut pas être négatif.';
    if (empty($category)) $errors[] = 'Sélectionnez une catégorie.';

    $imagePath = $product['image']; // Garder l'ancienne image par défaut

    // Gestion upload nouvelle image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['image']['tmp_name'];
        $fileName      = $_FILES['image']['name'];
        $fileSize      = $_FILES['image']['size'];
        $fileExt       = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize    = 5 * 1024 * 1024; // 5 Mo

        if (!in_array($fileExt, $allowedExt)) {
            $errors[] = 'Format d\'image non autorisé (JPG, PNG, GIF, WEBP uniquement).';
        } elseif ($fileSize > $maxSize) {
            $errors[] = 'L\'image dépasse 5 Mo.';
        } else {
            // Suppression ancienne image (si pas placeholder)
            $oldImage = '../' . $product['image'];
            $placeholder = '../uploads/placeholder.jpg';
            if ($product['image'] && file_exists($oldImage) && $oldImage !== $placeholder) {
                @unlink($oldImage);
            }

            // Nouveau nom unique
            $newFileName = uniqid('prod_', true) . '.' . $fileExt;
            $uploadDir   = '../uploads/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $imagePath = 'uploads/products/' . $newFileName;
            } else {
                $errors[] = 'Erreur lors de l\'enregistrement de l\'image.';
            }
        }
    }

    // Mise à jour si pas d'erreurs
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?, stock = ?, category = ?, image = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $price, $stock, $category, $imagePath, $id]);

            $success = 'Produit modifié avec succès !';
            // Recharger le produit mis à jour
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = 'Erreur base de données.';
            error_log($e->getMessage());
        }
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}

// Catégories (adaptez selon votre table)
$categories = [
    'electronique' => 'Électronique',
    'mode'         => 'Mode',
    'maison'       => 'Maison',
    'loisirs'      => 'Loisirs',
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier Produit - Admin AnonShop</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .preview-img {
            max-height: 300px;
            object-fit: contain;
            border: 1px dashed #ccc;
            border-radius: 12px;
            background: #f8f9fa;
        }
        .card {
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-warning text-white text-center">
                        <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Modifier le produit #<?= $id ?></h4>
                    </div>
                    <div class="card-body p-4">

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

                        <?php if ($product): ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom du produit <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Choisir...</option>
                                        <?php foreach ($categories as $slug => $nom): ?>
                                            <option value="<?= $slug ?>" <?= ($product['category'] ?? '') === $slug ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nom) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Prix (CFA) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Stock <span class="text-danger">*</span></label>
                                    <input type="number" min="0" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" rows="6" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Image actuelle</label>
                                    <div class="text-center mb-3">
                                        <img src="../<?= htmlspecialchars($product['image'] ?? 'uploads/placeholder.jpg') ?>"
                                             class="preview-img" alt="Image actuelle">
                                    </div>
                                    <label class="form-label fw-bold">Nouvelle image (optionnel, max 5 Mo)</label>
                                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                                    <small class="text-muted">Formats : JPG, PNG, GIF, WEBP</small>
                                    <div class="mt-3 text-center">
                                        <img id="imagePreview" class="preview-img d-none" alt="Prévisualisation">
                                    </div>
                                </div>

                                <div class="col-12 text-center mt-4">
                                    <button type="submit" name="edit" class="btn btn-warning btn-lg px-5 me-3">
                                        <i class="bi bi-check-circle"></i> Enregistrer les modifications
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary btn-lg px-5">
                                        <i class="bi bi-arrow-left"></i> Retour
                                    </a>
                                </div>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prévisualisation nouvelle image
        document.getElementById('imageInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.src = ev.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else if (preview) {
                preview.classList.add('d-none');
            }
        });
    </script>
</body>
</html>