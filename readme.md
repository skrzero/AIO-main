# 🌟 AIO-main - Application Web Complète

**AIO-main** est une application web complète développée en PHP MVC from scratch, combinant un frontend moderne avec Bootstrap 5 et un backend sécurisé avec authentification, gestion de calendrier, mémos et widget météo.

---

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Structure du projet](#-structure-du-projet)
- [Routes disponibles](#-routes-disponibles)
- [Sécurité](#-sécurité)
- [Composants](#-composants)
- [Développement](#-développement)
- [Déploiement](#-déploiement)

---

## ✨ Fonctionnalités

### 🔐 Authentification
- **Inscription** : Création de compte avec validation
- **Connexion** : Authentification sécurisée avec sessions
- **Déconnexion** : Gestion sécurisée des sessions
- **Protection CSRF** : Tokens sur tous les formulaires
- **Sessions sécurisées** : Régénération d'ID, cookies httponly

### 📅 Calendrier
- **Vue mensuelle** : Affichage du calendrier par mois
- **Gestion d'événements** : Création, modification, suppression
- **Catégories** : Travail, personnel, autre
- **Navigation** : Mois précédent/suivant
- **Affichage détaillé** : Modal avec événements du jour

### 📝 Mémos
- **Création de notes** : Titre et contenu
- **Drag & Drop** : Réorganisation par glisser-déposer
- **Édition** : Modification des mémos existants
- **Suppression** : Gestion des mémos

### 🌤️ Widget Météo
- **Géolocalisation automatique** : Détection de la position GPS
- **Fallback** : Ville par défaut si géolocalisation refusée
- **Informations complètes** : Température, ressenti, humidité, vent, pression
- **Design adaptatif** : Couleurs selon les conditions météo
- **Animations** : Effets visuels modernes

### 🎨 Interface
- **Menu burger persistant** : Navigation sur toutes les pages
- **Design responsive** : Mobile-first avec Bootstrap 5
- **Icônes Font Awesome** : Interface moderne
- **Thème sombre** : Menu avec fond sombre

---

## 📦 Prérequis

### Backend (PHP)
- **PHP** >= 8.0
- **Composer** (gestionnaire de dépendances PHP)
- **MariaDB/MySQL** >= 10.3
- **Extensions PHP** : PDO, PDO_MySQL, mbstring

### Frontend (Optionnel pour développement)
- **Node.js** >= 16.0
- **npm** (gestionnaire de paquets Node)

### Serveur Web
- **Apache** avec mod_rewrite (recommandé)
- **Nginx** (configuration fournie)
- **PHP intégré** (pour développement)

---

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone <url-du-repo>
cd AIO-main
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances Node.js (optionnel)

```bash
npm install
```

### 4. Configurer la base de données

#### A. Créer la base de données

Ouvrez DBeaver, phpMyAdmin ou votre client MySQL et exécutez :

```sql
CREATE DATABASE aio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### B. Exécuter le script SQL

Exécutez le fichier `database.sql` dans votre base de données :

**Via DBeaver :**
1. Ouvrez DBeaver
2. Connectez-vous à votre base MariaDB/MySQL
3. Sélectionnez la base `aio_db`
4. Ouvrez un éditeur SQL (Ctrl+\)
5. Ouvrez le fichier `database.sql`
6. Exécutez le script (Ctrl+Enter)

**Via ligne de commande :**
```bash
mysql -u root -p aio_db < database.sql
```

#### C. Migration (si table users existe déjà)

Si la table `users` existe déjà sans le champ `name`, exécutez :

```sql
ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT 'Utilisateur' AFTER id;
```

Ou utilisez le fichier `migration_add_name.sql` fourni.

### 5. Configurer les identifiants de base de données

Éditez le fichier `src/config/db_config.php` :

```php
return [
    'host' => 'localhost',
    'dbname' => 'aio_db',
    'username' => 'root',
    'password' => 'votre_mot_de_passe',
    'charset' => 'utf8mb4',
    // ...
];
```

### 6. Configuration du serveur web

#### Apache

Assurez-vous que `mod_rewrite` est activé. Le fichier `.htaccess` est déjà présent.

#### Nginx

Ajoutez dans votre bloc `server` :

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## ⚙️ Configuration

### Variables d'environnement

Pour la production, créez un fichier `.env` (non inclus dans le repo) :

```env
DB_HOST=localhost
DB_NAME=aio_db
DB_USER=root
DB_PASS=votre_mot_de_passe
```

### Configuration Twig

Dans les contrôleurs, le cache Twig est désactivé en développement. Pour la production, modifiez :

```php
$this->twig = new Environment($loader, [
    'cache' => __DIR__ . '/../../cache/twig', // Active le cache
    'debug' => false
]);
```

### API Météo

Le widget météo utilise l'API OpenWeatherMap. La clé API est dans `js/Accueil.js`. Pour utiliser votre propre clé :

1. Créez un compte sur [OpenWeatherMap](https://openweathermap.org/api)
2. Obtenez votre clé API
3. Remplacez dans `js/Accueil.js` :

```javascript
const apiKey = "VOTRE_CLE_API";
```

---

## 📁 Structure du projet

```
AIO-main/
├── src/                          # Code source PHP (MVC)
│   ├── Config/                   # Configuration
│   │   ├── Database.php         # Singleton PDO
│   │   └── db_config.php        # Config BDD
│   │
│   ├── Controllers/              # Contrôleurs MVC
│   │   ├── BaseController.php   # Contrôleur de base
│   │   ├── AuthController.php   # Authentification
│   │   ├── HomeController.php   # Page d'accueil
│   │   ├── DashboardController.php
│   │   ├── CalendarController.php
│   │   └── MemoController.php
│   │
│   ├── Models/                   # Modèles (accès BDD)
│   │   ├── UserModel.php
│   │   ├── MessageModel.php
│   │   ├── SessionModel.php
│   │   ├── EventModel.php
│   │   └── MemoModel.php
│   │
│   ├── Middleware/               # Middlewares
│   │   └── AuthMiddleware.php   # Protection des routes
│   │
│   └── Views/                    # Vues Twig
│       ├── layout.twig          # Layout commun (menu)
│       ├── home.twig
│       ├── dashboard.twig
│       ├── calendar.twig
│       ├── memos.twig
│       └── auth/
│           ├── login.twig
│           └── register.twig
│
├── css/                          # Styles CSS
│   ├── style.css                # Styles principaux
│   └── auth.css                 # Styles authentification
│
├── js/                           # JavaScript
│   ├── script.js                # Script principal (menu)
│   ├── auth.js                  # Validation formulaires
│   ├── Accueil.js               # Widget météo
│   ├── Timer.js                 # Minuteur
│   ├── Agenda.js                # Calendrier (ancien)
│   ├── calendar.js              # Calendrier (nouveau)
│   ├── memos.js                 # Drag & drop mémos
│   └── Bloc-note.js             # Bloc-notes (ancien)
│
├── images/                       # Images et assets
│
├── assets/                       # Assets source (Webpack)
│   ├── scripts/
│   ├── styles/
│   └── images/
│
├── dist/                         # Fichiers compilés (Webpack)
│   ├── js/
│   └── css/
│
├── index.php                     # Point d'entrée (router)
├── index.html                    # Page HTML statique
├── agenda.html                   # Page agenda HTML
├── bloc-note.html                # Page bloc-notes HTML
│
├── database.sql                  # Script SQL complet
├── migration_add_name.sql        # Migration champ name
├── composer.json                 # Dépendances PHP
├── package.json                  # Dépendances Node.js
├── webpack.config.cjs            # Configuration Webpack
├── .htaccess                     # Configuration Apache
├── .gitignore
└── README.md                     # Ce fichier
```

---

## 🎯 Routes disponibles

### Routes publiques

| Route | Méthode | Description |
|-------|---------|-------------|
| `/` | GET | Redirige vers `/login` ou `/home` |
| `/login` | GET | Formulaire de connexion |
| `/login` | POST | Traitement de la connexion |
| `/register` | GET | Formulaire d'inscription |
| `/register` | POST | Traitement de l'inscription |
| `/logout` | GET | Déconnexion |

### Routes protégées (nécessitent authentification)

| Route | Méthode | Description |
|-------|---------|-------------|
| `/home` | GET | Page d'accueil |
| `/dashboard` | GET | Tableau de bord |
| `/dashboard/message/add` | POST | Ajouter un message |
| `/dashboard/message/delete` | POST | Supprimer un message |
| `/calendar` | GET | Calendrier mensuel |
| `/calendar/day-events` | GET | Événements d'un jour (AJAX) |
| `/calendar/event/create` | POST | Créer un événement |
| `/calendar/event/delete` | POST | Supprimer un événement |
| `/memos` | GET | Liste des mémos |
| `/memos/create` | POST | Créer un mémo |
| `/memos/update` | POST | Modifier un mémo |
| `/memos/update-positions` | POST | Mettre à jour positions (AJAX) |
| `/memos/delete` | POST | Supprimer un mémo |

---

## 🔐 Sécurité

### Mesures implémentées

- ✅ **Protection SQL Injection** : Toutes les requêtes utilisent PDO avec requêtes préparées
- ✅ **Protection CSRF** : Tokens CSRF sur tous les formulaires
- ✅ **Sessions sécurisées** :
  - `session_regenerate_id()` après connexion
  - Cookies `httponly` et `samesite=Strict`
  - Stockage des sessions en base de données
- ✅ **Hash des mots de passe** : `password_hash()` avec `PASSWORD_DEFAULT` (bcrypt)
- ✅ **Validation des données** : Filtrage et validation côté serveur
- ✅ **Gestion d'erreurs** : Try/catch sur toutes les requêtes PDO
- ✅ **Middleware d'authentification** : Protection automatique des routes

### Bonnes pratiques

- Ne jamais exposer les mots de passe en clair
- Toujours utiliser des requêtes préparées
- Valider toutes les entrées utilisateur
- Logger les erreurs sans exposer les détails aux utilisateurs
- Utiliser HTTPS en production

---

## 🧩 Composants

### 1. Widget Météo

**Fichier :** `js/Accueil.js`

**Fonctionnalités :**
- Géolocalisation automatique
- Fallback sur ville par défaut
- Affichage des conditions météo
- Design adaptatif selon le temps

**Configuration :**
```javascript
const apiKey = "VOTRE_CLE_OPENWEATHERMAP";
const defaultCity = "Dax";
```

### 2. Calendrier

**Fichiers :** `src/Views/calendar.twig`, `js/calendar.js`

**Fonctionnalités :**
- Vue mensuelle interactive
- Création/modification d'événements
- Catégories (travail, personnel, autre)
- Navigation mois précédent/suivant
- Modal avec détails des événements

### 3. Mémos

**Fichiers :** `src/Views/memos.twig`, `js/memos.js`

**Fonctionnalités :**
- Création de notes
- Drag & drop avec SortableJS
- Édition et suppression
- Persistance des positions

**Dépendance :** SortableJS (inclus via CDN)

### 4. Menu Burger

**Fichier :** `src/Views/layout.twig`, `js/script.js`

**Fonctionnalités :**
- Menu responsive (burger sur mobile)
- Navigation persistante
- Mise en surbrillance du lien actif
- Affichage conditionnel (connecté/non connecté)

---

## 💻 Développement

### Démarrer le serveur de développement

#### Option 1 : Serveur PHP intégré

```bash
php -S localhost:8000 -t .
```

Puis ouvrez `http://localhost:8000` dans votre navigateur.

#### Option 2 : Script PowerShell (Windows)

Double-cliquez sur `start-server.bat` ou exécutez :

```powershell
powershell -ExecutionPolicy Bypass -File .\start-server.ps1
```

Le serveur démarre sur `http://localhost:8080`

#### Option 3 : Apache/Nginx

Configurez votre serveur web pour pointer vers le dossier du projet.

### Compiler les assets (Webpack)

```bash
# Mode développement (watch)
npm run watch

# Mode production (build)
npm run build
```

### Linting

```bash
# Linter SCSS
npm run scss-lint
npm run scss-fix

# Linter JavaScript (via ESLint)
npx eslint js/
```

### Structure MVC

#### Contrôleur

```php
class MonController extends BaseController
{
    public function index(): void
    {
        $this->render('ma-vue.twig', [
            'data' => $data
        ]);
    }
}
```

#### Modèle

```php
class MonModel
{
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function maMethode(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM table");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
```

#### Vue Twig

```twig
{% extends 'layout.twig' %}

{% block title %}Ma Page{% endblock %}

{% block content %}
    <h1>{{ titre }}</h1>
{% endblock %}
```

---

## 🗄️ Base de données

### Tables

#### `users`
- `id` : Identifiant unique
- `name` : Nom de l'utilisateur
- `email` : Email (unique)
- `password` : Mot de passe haché
- `created_at` : Date de création

#### `sessions`
- `session_id` : ID de session PHP
- `user_id` : Référence à l'utilisateur
- `expires_at` : Date d'expiration
- `created_at` : Date de création

#### `messages`
- `id` : Identifiant unique
- `user_id` : Référence à l'utilisateur
- `content` : Contenu du message
- `created_at` : Date de création

#### `events`
- `id` : Identifiant unique
- `user_id` : Référence à l'utilisateur
- `title` : Titre de l'événement
- `description` : Description
- `start_datetime` : Date/heure de début
- `end_datetime` : Date/heure de fin
- `category` : Catégorie (travail, personnel, autre)
- `created_at` : Date de création

#### `memos`
- `id` : Identifiant unique
- `user_id` : Référence à l'utilisateur
- `title` : Titre du mémo
- `content` : Contenu
- `position` : Position pour drag & drop
- `created_at` : Date de création
- `updated_at` : Date de mise à jour

---

## 🚢 Déploiement

### Checklist de production

- [ ] Configurer les variables d'environnement
- [ ] Activer le cache Twig
- [ ] Désactiver le mode debug
- [ ] Configurer HTTPS
- [ ] Mettre à jour la clé API météo
- [ ] Configurer les logs d'erreurs
- [ ] Optimiser les assets (Webpack build)
- [ ] Configurer la base de données de production
- [ ] Tester toutes les fonctionnalités
- [ ] Configurer les sauvegardes automatiques

### Configuration Apache (.htaccess)

Le fichier `.htaccess` est déjà configuré pour :
- Le routing vers `index.php`
- La protection des fichiers sensibles
- Les sessions sécurisées

### Configuration Nginx

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /chemin/vers/AIO-main;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 📚 Technologies utilisées

### Backend
- **PHP 8.0+** : Langage serveur
- **PDO** : Accès base de données
- **Twig 3.8** : Moteur de templates
- **Composer** : Gestionnaire de dépendances

### Frontend
- **Bootstrap 5.3** : Framework CSS
- **Font Awesome 6.4** : Icônes
- **JavaScript ES6+** : Interactivité
- **SortableJS** : Drag & drop
- **FullCalendar** : Calendrier (ancien système)

### Outils
- **Webpack** : Bundler
- **Sass** : Préprocesseur CSS
- **Babel** : Transpilation JavaScript
- **ESLint** : Linter JavaScript
- **Stylelint** : Linter CSS/SCSS

---

## 🐛 Dépannage

### Problème : Erreur de connexion à la base de données

**Solution :**
1. Vérifiez les identifiants dans `src/config/db_config.php`
2. Vérifiez que MariaDB/MySQL est démarré
3. Vérifiez que la base `aio_db` existe

### Problème : Page 404 sur les routes

**Solution :**
1. Vérifiez que `mod_rewrite` est activé (Apache)
2. Vérifiez la configuration Nginx
3. Vérifiez que le fichier `.htaccess` est présent

### Problème : Menu burger ne s'affiche pas

**Solution :**
1. Vérifiez que Bootstrap JS est chargé
2. Vérifiez la console du navigateur pour les erreurs
3. Vérifiez que `js/script.js` est inclus

### Problème : Widget météo ne fonctionne pas

**Solution :**
1. Vérifiez votre clé API OpenWeatherMap
2. Autorisez la géolocalisation dans le navigateur
3. Vérifiez la console pour les erreurs

---

## 📝 Licence

Ce projet est un projet éducatif. Libre d'utilisation et de modification.

---

## 👥 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
- Signaler des bugs
- Proposer des améliorations
- Soumettre des pull requests

---

## 📞 Support

Pour toute question ou problème :
1. Consultez la documentation ci-dessus
2. Vérifiez les fichiers de configuration
3. Consultez les logs d'erreurs PHP

---

**Développé avec ❤️ en PHP MVC from scratch**
