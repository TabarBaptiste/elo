# 💇‍♀️ Gestion de Réservations – Salon de Coiffure

Bienvenue dans ce projet de **gestion de réservations en ligne pour un salon de coiffure**.
Il permet aux utilisateurs de réserver des prestations en ligne et aux coiffeurs de gérer leurs **disponibilités** et **services** proposés.

---

## ✨ Fonctionnalités principales

1. **Réservations en ligne** : prise de rendez-vous par date et heure.
2. **Disponibilités des coiffeurs** : chaque coiffeur peut définir ses créneaux horaires.
3. **Gestion des prestations** : ajout, modification, suppression des services.
4. **Comptes utilisateurs et coiffeurs** : gérés par des administrateurs.
5. **Historique des réservations** : consultation des rendez-vous passés.

---

## 📁 Structure du projet

```bash
.
├── src/
│   ├── Controller/       # Contrôleurs Symfony
│   ├── Entity/           # Entités Doctrine (Réservation, Prestation, Utilisateur...)
│   ├── Repository/       # Accès aux données
│   └── Templates/        # Fichiers Twig pour les vues
│
├── assets/
│   ├── styles/           # Feuilles de style CSS
│   └── scripts/          # Scripts JavaScript
│
├── config/               # Configuration Symfony
├── tests/                # Tests unitaires et fonctionnels
```

---

## 🔍 Fonctionnalités détaillées

### Réservations

* Sélection d’une date/heure et prestation souhaitée.
<!-- * Notifications possibles pour les nouvelles réservations côté coiffeur. -->

### Disponibilités

* Gestion des créneaux disponibles par chaque coiffeur.
* Visualisation du planning avant réservation.

### Prestations

* Ajout/modification/suppression de prestations (coupe, couleur, soin, etc.).
* Consultation de l’offre par les utilisateurs.

### Comptes utilisateurs

* Gestion par les administrateurs.
* Accès à l’historique de rendez-vous pour chaque utilisateur.

---

## 🛠️ Technologies utilisées

* Symfony 5
* Twig
* Bootstrap (pour la mise en page)
* JavaScript
* CSS

---

## 🚀 Installation

```bash
# 1. Cloner le dépôt
git clone https://github.com/TabarBaptiste/elo.git
cd elo

# 2. Installer les dépendances
composer install

# 3. Configurer la base de données
# Modifier le fichier .env.local ou configurer un parameters.yml (selon version Symfony)

# 4. Créer la base
php bin/console doctrine:database:create

# 5. Générer le schéma
php bin/console doctrine:schema:update --force
```

---

## 🧪 Lancement du serveur

```bash
php bin/console server:run
```

Ensuite, ouvrez [http://localhost:8000](http://localhost:8000) dans votre navigateur.

---

## 📌 Remarques

* Ce projet est un **prototype** pouvant être étendu avec des fonctionnalités comme le paiement en ligne, les avis clients, ou les rappels par email.
* Pour toute contribution, ouvrez une **issue** ou proposez une **pull request**.
