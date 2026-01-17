<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Anon Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- À inclure dans includes/header.php (après Bootstrap CSS) -->
    <link rel="stylesheet" href="assets/css/custom.css">

    <style>
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }
        .badge-cart {
            font-size: 0.75rem;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-bag-heart-fill text-primary"></i> AnonShop
        </a>

        <!-- Mobile button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house"></i> Accueil
                    </a>
                </li>

                 <li class="nav-item">
                    <a class="nav-link" href="contact.php">
                        <i class="bi bi-envelope"></i> Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="my-orders.php">
                        <i class="bi bi-clipboard2-check"></i> Mes commandes
                    </a>
                </li>
                <li class="nav-item position-relative">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger badge-cart">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person-circle"></i> Mon compte
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Connexion
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm" href="register.php">
                            <i class="bi bi-person-plus"></i> Inscription
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
