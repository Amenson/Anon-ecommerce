<?php
session_start();
require_once '../config.php'; // Connexion PDO

// Protection : Accès réservé à l'admin (adaptez selon votre système d'authentification)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error   = '';

// Catégories disponibles (ajoutez/modifiez selon vos besoins)
$categories = [
    'electronique' => 'Électronique',
    'mode'         => 'Mode',
    'maison'       => 'Maison',
    'loisirs'      => 'Loisirs',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Récupération et nettoyage des données
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $category    = $_POST['category'] ?? '';
    $imagePath   = '';

    // Validation des champs obligatoires
    $errors = [];
    if (empty($name)) $errors[] = 'Le nom du produit est obligatoire.';
    if (empty($description)) $errors[] = 'La description est obligatoire.';
    if ($price <= 0) $errors[] = 'Le prix doit être supérieur à 0.';
    if ($stock < 0) $errors[] = 'Le stock ne peut pas être négatif.';
    if (!array_key_exists($category, $categories)) $errors[] = 'Catégorie invalide.';

    // Gestion de l'upload d'image
    if (empty($errors)) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['image']['tmp_name'];
            $fileName      = $_FILES['image']['name'];
            $fileSize      = $_FILES['image']['size'];
            $fileExt       = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExt    = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxSize       = 5 * 1024 * 1024; // 5 Mo

            if (!in_array($fileExt, $allowedExt)) {
                $errors[] = 'Format d\'image non autorisé (JPG, JPEG, PNG, GIF, WEBP uniquement).';
            } elseif ($fileSize > $maxSize) {
                $errors[] = 'L\'image dépasse la taille maximale autorisée (5 Mo).';
            } else {
                // Nom unique + dossier d'upload
                $newFileName = uniqid('prod_', true) . '.' . $fileExt;
                $uploadDir   = '../uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $imagePath = 'uploads/products/' . $newFileName; // Chemin relatif en base
                } else {
                    $errors[] = 'Erreur lors de l\'enregistrement de l\'image.';
                }
            }
        } else {
            // Image facultative : placeholder par défaut
            $imagePath = 'uploads/placeholder.jpg'; // Créez ce fichier placeholder
        }
    }

    // Insertion en base si aucune erreur
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, description, price, stock, category, image, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $description, $price, $stock, $category, $imagePath]);

            $success = 'Produit ajouté avec succès !';
            // Réinitialiser le formulaire
            $_POST = [];
        } catch (PDOException $e) {
            $errors[] = 'Erreur base de données : ' . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter un Produit - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
     <script src="assets/js/admin.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .preview-img {
            max-height: 300px;
            object-fit: contain;
            border: 1px dashed #ccc;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="bi bi-plus-circle"></i> Ajouter un nouveau produit</h3>
                    </div>
                    <div class="card-body p-4">

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nom du produit <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                                    <select name="category" class="form-select" required>
                                        <option value="">Choisir une catégorie...</option>
                                        <?php foreach ($categories as $slug => $nom): ?>
                                            <option value="<?= $slug ?>" <?= (($_POST['category'] ?? '') === $slug) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nom) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Prix (CFA) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="price" class="form-control" value="<?= $_POST['price'] ?? '' ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Stock <span class="text-danger">*</span></label>
                                    <input type="number" min="0" name="stock" class="form-control" value="<?= $_POST['stock'] ?? '100' ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" rows="6" class="form-control" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Image du produit (max 5 Mo)</label>
                                    <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                                    <small class="text-muted">Formats autorisés : JPG, JPEG, PNG, GIF, WEBP</small>
                                    <div class="mt-3 text-center">
                                        <img id="imagePreview" class="preview-img d-none" alt="Prévisualisation">
                                    </div>
                                </div>

                                <div class="col-12 text-center mt-4">
                                    <button type="submit" name="add" class="btn btn-primary btn-lg px-5">
                                        <i class="bi bi-check-circle"></i> Ajouter le produit
                                    </button>
                                    <a href="dashboard.php" class="btn btn-secondary btn-lg px-5 ms-3">
                                        Retour au tableau de bord
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prévisualisation de l'image en temps réel
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('d-none');
            }
        });
    </script>
</body>
</html>