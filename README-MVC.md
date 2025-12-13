# Application MVC PHP - AIO

Application PHP MVC from scratch avec authentification, base de données MariaDB et sécurité.

## 📋 Prérequis

- PHP >= 8.0
- Composer
- MariaDB/MySQL
- Serveur web (Apache avec mod_rewrite ou Nginx)

## 🚀 Installation

### 1. Installer les dépendances Composer

```bash
composer install
```

### 2. Configurer la base de données

1. Créez une base de données MariaDB nommée `aio_db` (ou modifiez le nom dans `src/config/db_config.php`)
2. Exécutez le script SQL `database.sql` dans votre base de données (via DBeaver, phpMyAdmin, ou ligne de commande)

```bash
mysql -u root -p aio_db < database.sql
```

### 3. Configurer les identifiants de base de données

Éditez le fichier `src/config/db_config.php` et modifiez les valeurs :

```php
'host' => 'localhost',
'dbname' => 'aio_db',
'username' => 'root',
'password' => 'votre_mot_de_passe',
```

### 4. Configuration du serveur web

#### Apache
Assurez-vous que `mod_rewrite` est activé et que le fichier `.htaccess` est présent.

#### Nginx
Ajoutez cette configuration dans votre bloc `server` :

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 📁 Structure du projet

```
AIO-main/
├── src/
│   ├── Config/          # Configuration (base de données)
│   ├── Controllers/     # Contrôleurs MVC
│   │   ├── AuthController.php
│   │   └── DashboardController.php
│   ├── Models/          # Modèles (accès BDD)
│   │   ├── UserModel.php
│   │   ├── MessageModel.php
│   │   └── SessionModel.php
│   ├── Middleware/      # Middlewares (authentification)
│   │   └── AuthMiddleware.php
│   └── Views/           # Vues Twig
│       ├── base.twig
│       ├── auth/
│       │   ├── login.twig
│       │   └── register.twig
│       └── dashboard.twig
├── index.php            # Point d'entrée (router)
├── database.sql         # Script de création des tables
├── composer.json        # Dépendances PHP
└── .htaccess           # Configuration Apache
```

## 🔐 Sécurité implémentée

- ✅ **Protection SQL Injection** : Requêtes préparées PDO
- ✅ **CSRF Protection** : Tokens CSRF sur tous les formulaires
- ✅ **Sessions sécurisées** : `session_regenerate_id`, cookies `httponly`
- ✅ **Hash des mots de passe** : `password_hash()` avec PASSWORD_DEFAULT
- ✅ **Validation des données** : Filtrage et validation des entrées
- ✅ **Gestion d'erreurs** : Try/catch sur toutes les requêtes PDO

## 🎯 Routes disponibles

- `GET /` → Redirige vers `/login` ou `/dashboard`
- `GET /login` → Formulaire de connexion
- `POST /login` → Traitement de la connexion
- `GET /register` → Formulaire d'inscription
- `POST /register` → Traitement de l'inscription
- `GET /logout` → Déconnexion
- `GET /dashboard` → Tableau de bord (protégé)
- `POST /dashboard/message/add` → Ajouter un message (protégé)
- `POST /dashboard/message/delete` → Supprimer un message (protégé)

## 🧪 Test de l'application

1. Démarrez votre serveur web (Apache/Nginx) ou utilisez le serveur PHP intégré :
   ```bash
   php -S localhost:8000 -t .
   ```

2. Accédez à `http://localhost:8000` dans votre navigateur

3. Créez un compte via `/register`

4. Connectez-vous via `/login`

5. Accédez au dashboard pour voir vos messages

## 📝 Notes

- Les sessions sont stockées dans la table `sessions` pour un meilleur contrôle
- Les mots de passe sont hachés avec `password_hash()` (algorithme bcrypt par défaut)
- Les erreurs sont loggées dans les logs PHP (configurez `error_log` dans `php.ini`)
- En production, activez le cache Twig dans `AuthController.php` et `DashboardController.php`

