# AnonShop - Site E-commerce Togolais

![AnonShop Logo](assets/images/logo/logo.svg) 

**AnonShop** est une plateforme de commerce en ligne complète, développée en PHP natif, adaptée au marché togolais. Elle permet aux clients de parcourir des produits, passer commande et payer via mobile money (instructions Flooz/TMoney), avec un panel administrateur sécurisé pour gérer tout le site.

## Fonctionnalités Principales

### Côté Client
- Parcourir produits (recherche, tri, catégories)
- Détails produit avec zoom image
- Panier avec mise à jour quantité (+/-)
- Inscription/Connexion obligatoire pour commander
- Checkout avec formulaire client
- Page paiement avec instructions Flooz/TMoney
- Confirmation commande + historique (mes commandes)
- Profil utilisateur (édition infos, changement mot de passe)

### Côté Administrateur
- Connexion sécurisée
- Dashboard avec résumé (produits, commandes, statuts)
- Gestion produits (ajouter/modifier/supprimer avec upload image)
- Gestion commandes (vue détails, changement statut AJAX)
- Gestion utilisateurs (liste, édition, blocage/déblocage, réinitialisation mot de passe)

### Autres
- Notifications toast
- Envoi SMS automatique (Africa's Talking) pour confirmations
- Responsive design (Bootstrap 5)
- Sécurité renforcée (hash mot de passe, prepared statements)

## Technologies Utilisées

- **Backend** : PHP 8 + PDO
- **Frontend** : Bootstrap 5 + Bootstrap Icons + JavaScript vanilla
- **Base de données** : MySQL
- **Outils** : Chart.js (graphiques dashboard), Africa's Talking (SMS)
- **Développement local** : WAMP / XAMPP

## Installation (Local)

1. **Cloner le repository**
   ```bash
   git clone https://github.com/Amenson/Anon-ecommerce.git
   cd Anon-ecommerce/
