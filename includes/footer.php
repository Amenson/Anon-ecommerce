

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<style>
.footer {
    background: #111;
    color: #ccc;
    padding: 60px 0 20px;
    position: relative;
}

.footer-logo {
    color: #fff;
    font-weight: 700;
}

.footer-text {
    font-size: 0.9rem;
    color: #aaa;
}

.footer-title {
    color: #fff;
    font-size: 0.95rem;
    margin-bottom: 15px;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 8px;
}

.footer-links a {
    color: #aaa;
    text-decoration: none;
    transition: 0.3s;
}

.footer-links a:hover {
    color: #0d6efd;
}

.newsletter {
    display: flex;
    background: #222;
    border-radius: 6px;
    overflow: hidden;
}

.newsletter input {
    flex: 1;
    background: transparent;
    border: none;
    padding: 10px;
    color: #fff;
    outline: none;
}

.newsletter button {
    background: #0d6efd;
    border: none;
    color: #fff;
    padding: 0 16px;
    cursor: pointer;
}

.social-links a {
    color: #ccc;
    font-size: 1.3rem;
    margin-right: 10px;
    transition: 0.3s;
}

.social-links a:hover {
    color: #0d6efd;
}

.footer-bottom {
    text-align: center;
    font-size: 0.85rem;
    color: #777;
}

/* Scroll to top */
#scrollTopBtn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: #0d6efd;
    color: #fff;
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 999;
}
</style>
<footer class="footer">
    <div class="container">
        <div class="row">

            <!-- Logo & description -->
            <div class="col-md-4 mb-4">
                <h4 class="footer-logo">
                    <i class="bi bi-bag-heart-fill"></i> AnonShop
                </h4>
                <p class="footer-text">
                    Votre boutique en ligne moderne.
                    Achetez en toute sécurité avec livraison rapide.
                </p>
            </div>

            <!-- Liens rapides -->
            <div class="col-md-2 mb-4">
                <h6 class="footer-title">Boutique</h6>
                <ul class="footer-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="product.php">Produits</a></li>
                    <li><a href="cart.php">Panier</a></li>
                    <li><a href="login.php">Connexion</a></li>
                </ul>
            </div>

            <!-- Aide -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-title">Aide</h6>
                <ul class="footer-links">
                    <li><a href="#">Livraison</a></li>
                    <li><a href="#">Retours</a></li>
                    <li><a href="#">Conditions</a></li>
                    <li><a href="#">Confidentialité</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-title">Newsletter</h6>
                <p class="footer-text small">
                    Recevez nos offres exclusives
                </p>
                <form id="newsletterForm" class="newsletter">
                    <input type="email" id="newsletterEmail"
                           placeholder="Votre email" required>
                    <button type="submit">
                        <i class="bi bi-send"></i>
                    </button>
                </form>

                <div class="social-links mt-3">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="https://wa.me/+22893814645"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>

        </div>

        <hr>

        <div class="footer-bottom">
            <p>&copy; 2026 AnonShop — Tous droits réservés</p>
        </div>
    </div>

    <!-- Bouton retour en haut -->
    <button id="scrollTopBtn">
        <i class="bi bi-arrow-up"></i>
    </button>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Footer JS -->
<script>// Newsletter fake submit
document.getElementById("newsletterForm").addEventListener("submit", e => {
    e.preventDefault();
    const email = document.getElementById("newsletterEmail").value;
    alert("Merci pour votre inscription : " + email);
    e.target.reset();
});

// Scroll to top
const btn = document.getElementById("scrollTopBtn");

window.addEventListener("scroll", () => {
    btn.style.display = window.scrollY > 300 ? "flex" : "none";
});

btn.addEventListener("click", () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
