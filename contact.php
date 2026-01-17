<?php
session_start();
include 'config.php';
include 'includes/header.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Contact Attirant</title>
     <link rel="stylesheet" href="assets/css/style.css">
    <script src="assrts/js/scripts.js"></script>
    <meta name="description" content="Un formulaire de contact élégant et réactif pour votre site web.">
    <meta name="keywords" content="formulaire de contact, design élégant, réactif, HTML, CSS, JavaScript">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
        
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg,rgb(34, 20, 9),rgb(30, 60, 125));
            color:rgb(37, 51, 81);

        }
  
 
        .contact-container {
            position: relative;
            max-width: 640px;
            width: 100%;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
        
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .contact-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #f97316);
        }

        .contact-container h2 {
            font-size: 2.25rem;
            font-weight: 700;
            color:white; 
            margin-bottom: 1rem;
        }

        .contact-container p {
            font-size: 1rem;
            color:rgb(141, 149, 165);
            margin-bottom: 2rem;
        }

        .contact-options {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .contact-option {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s, transform 0.2s, box-shadow 0.3s;
            animation: fadeInUp 0.5s ease-out forwards;
            animation-delay: calc(0.1s * var(--i));
            opacity: 0;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .contact-option.email {
            background: #2563eb;
            color: #ffffff;
        }

        .contact-option.phone {
            background: #f97316;
            color: #ffffff;
        }

        .contact-option.chat {
            background: #10b981;
            color: #ffffff;
        }

        .contact-option:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .contact-option i {
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .contact-container {
                padding: 2rem;
            }

            .contact-container h2 {
                font-size: 1.75rem;
            }

            .contact-container p {
                font-size: 0.9rem;
            }

            .contact-option {
                font-size: 0.9rem;
                padding: 0.85rem;
            }
        }
        section {
            padding: 2rem;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 2rem;
        }
        section p {
            margin-bottom: 1rem;
            font-size: 1rem;
            color: #333;
        }
    </style>
</head>
<body>
     
   
    <!-- Bannière promotionnelle -->
    <div class="banner">
        <p>🎉 Surprise ! 15% de réduction sur votre première commande avec le code <strong>Anon15</strong> !</p>
        <a href="product.php">Découvrir maintenant</a>
    </div>
    <!-- Formulaire de contact -->
    <div class="contact-container">
        <h2>Dites-nous tout !</h2>
        <p>Une question, une idée, ou juste envie de discuter ? Contactez-nous, et on vous répond avec le sourire sous 24h !</p>
        <div class="contact-options">
            <a href="mailto:contact@AnonShop.fr " class="contact-option email" style="--i: 1">
                <i class="fas fa-envelope"></i> Envoyez-nous un e-mail
            </a>
            <a href="tel:+228 93 81 46 45" class="contact-option phone" style="--i: 2">
                <i class="fas fa-phone"></i> Appelez-nous : +228  93 81 46 45
            </a>
            <a href="https://wa.me/+22893814645" class="contact-option chat" style="--i: 3">
                <i class="fab fa-whatsapp"></i> Discutez via WhatsApp
            </a>

        </div>
  
    </div>
 <section>
     <p>
    Salut ! Nous sommes là pour vous aider. Si vous avez des questions ou des préoccupations, n'hésitez pas à nous contacter. Nous nous engageons à vous répondre dans les plus brefs délais. Merci de votre confiance !
    </p>
        <p>
            <strong>Note :</strong> Nous respectons votre vie privée. Vos informations ne seront jamais partagées avec des tiers
   </p>
        
            <strong>Politique de confidentialité :</strong> Consultez notre <a href="politique_confidentialite.html">Politique de Confidentialité</a> pour en savoir plus sur la manière dont nous protégeons vos données.
        </p>
 </section>
    <footer>
        <div class="container">
            <div class="footer-nav">
                <h3>Navigation</h3>
                <ul>
                    <li><a href="#home">Accueil</a></li>
                    <li><a href="#about">A propos</a></li>
                    <li><a href="products.php">Produits</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="contact">
                <h3>Contact</h3>
                <p>E-mail : <a href="mailto:contact@AnonShop.fr">contact@AnonShop.fr</a></p>
                <p>Téléphone : +228  93 81 46 45</p>
                <p>Adresse : 123 Rue Tech, 75001 lomé</p>
            </div>
            <div class="social">
                <h3>Suivez-nous</h3>
                <div class="social-links">
                    <a href="https://x.com/Anon-shop" target="_blank" aria-label="Twitter/X">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </a>
                    <a href="https://linkedin.com/company/Anon-shop" target="_blank" aria-label="LinkedIn">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.024-3.037-1.852-3.037-1.852 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.048c.477-.9 1.637-1.852 3.37-1.852 3.602 0 4.267 2.37 4.267 5.455v6.288zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0z"/>
                        </svg>
                    </a>
                    <a href="https://instagram.com/Anon-shop" target="_blank" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.936 3.236.63 4.025c-.297.764-.498 1.634-.558 2.912C.015 8.217 0 8.624 0 11.884v.232c0 3.26.015 3.667.072 4.947.06 1.278.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126s1.337 1.078 2.126 1.384c.765.297 1.635.498 2.913.558 1.28.057 1.687.072 4.947.072s3.667-.015 4.947-.072c1.278-.06 2.148-.261 2.913-.558.788-.306 1.459-.717 2.126-1.384s1.078-1.337 1.384-2.126c.297-.765.498-1.635.558-2.913.057-1.28.072-1.687.072-4.947v-.232c0-3.26-.015-3.667-.072-4.947-.06-1.278-.261-2.148-.558-2.912-.306-.789-.717-1.459-1.384-2.126S22.764.936 21.975.63c-.765-.297-1.635-.498-2.913-.558C17.782.015 17.375 0 14.116 0h-.232zM12 2.163c3.204 0 3.584.012 4.849.07 1.366.062 2.633.326 3.608 1.301.975.975 1.24 2.242 1.301 3.608.058 1.265.07 1.645.07 4.849s-.012 3.584-.07 4.849c-.062 1.366-.326 2.633-1.301 3.608-.975.975-2.242 1.24-3.608 1.301-1.265.058-1.645.07-4.849.07s-3.584-.012-4.849-.07c-1.366-.062-2.633-.326-3.608-1.301-.975-.975-1.24-2.242-1.301-3.608-.058-1.265-.07-1.645-.07-4.849s.012-3.584.07-4.849c.062-1.366.326-2.633 1.301-3.608.975-.975 2.242-1.24 3.608-1.301 1.265-.058 1.645-.07 4.849-.07zM12 5.838c-3.403 0-6.162 2.759-6.162 6.162S8.597 18.162 12 18.162s6.162-2.759 6.162-6.162S15.403 5.838 12 5.838zm0 10.324c-2.297 0-4.162-1.865-4.162-4.162S9.703 7.838 12 7.838s4.162 1.865 4.162 4.162S14.297 16.162 12 16.162zm6.406-11.845c-.796 0-1.441.645-1.441 1.441s.645 1.441 1.441 1.441 1.441-.645 1.441-1.441-.645-1.441-1.441-1.441z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <p class="copyright">© 2026 AnonShop. Tous droits réservés.</p>
        </div>
    </footer>
     <script src="assets/js/script.js"></script>
</body>
</html>