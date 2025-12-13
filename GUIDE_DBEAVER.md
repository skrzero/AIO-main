# Guide étape par étape : Migration SQL dans DBeaver

## 📋 Étape 1 : Ouvrir DBeaver

1. Lancez **DBeaver** sur votre ordinateur
2. Si vous avez déjà une connexion à votre base de données, passez à l'étape 2
3. Si vous n'avez pas encore de connexion :
   - Cliquez sur l'icône **"Nouvelle connexion"** (prise électrique) dans la barre d'outils
   - OU allez dans **Fichier → Nouveau → Connexion à la base de données**
   - Sélectionnez **"MariaDB"** ou **"MySQL"**
   - Cliquez sur **"Suivant"**

## 🔌 Étape 2 : Configurer la connexion

Dans la fenêtre de configuration :

1. **Onglet "Principal"** :
   - **Host** : `localhost` (ou l'adresse de votre serveur)
   - **Port** : `3306` (port par défaut)
   - **Database** : `aio_db`
   - **Username** : `root` (ou votre nom d'utilisateur)
   - **Password** : Votre mot de passe

2. Cliquez sur **"Tester la connexion"**
   - Si c'est la première fois, DBeaver peut vous demander de télécharger le driver MariaDB/MySQL → Cliquez sur **"Télécharger"**

3. Si le test réussit, cliquez sur **"Terminer"**

## 📂 Étape 3 : Sélectionner la base de données

Dans l'arborescence à gauche (panneau "Navigateur de base de données") :

1. Développez votre connexion (cliquez sur la flèche ▶)
2. Développez **"Databases"**
3. Vous devriez voir **`aio_db`** dans la liste
4. **Cliquez droit** sur **`aio_db`**
5. Dans le menu contextuel, sélectionnez :
   - **SQL Editor → New SQL Script**
   - OU utilisez le raccourci clavier : **Ctrl+\**

Une nouvelle fenêtre d'éditeur SQL s'ouvre.

## 🔍 Étape 4 : Vérifier la structure actuelle (optionnel)

Dans l'éditeur SQL qui vient de s'ouvrir, tapez cette commande :

```sql
DESCRIBE users;
```

1. **Sélectionnez** la commande (surlignez-la)
2. Cliquez sur le bouton **"Exécuter le script SQL"** (icône ▶) dans la barre d'outils
   - OU utilisez le raccourci : **Ctrl+Enter**
   - OU **Alt+X**

3. Regardez le résultat dans le panneau en bas :
   - Si vous voyez une colonne `name`, la migration a déjà été faite
   - Si vous ne voyez pas `name`, continuez à l'étape 5

## ✏️ Étape 5 : Exécuter la migration

### Option A : Commande simple (recommandée)

Dans l'éditeur SQL, **effacez** tout le contenu et collez cette commande :

```sql
ALTER TABLE users ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT 'Utilisateur' AFTER id;
```

### Option B : Script avec vérification (plus sûr)

Ouvrez le fichier `migration_add_name.sql` que j'ai créé et copiez tout son contenu dans l'éditeur SQL.

## ▶️ Étape 6 : Exécuter la commande

1. **Sélectionnez** toute la commande SQL (Ctrl+A)
2. Cliquez sur le bouton **"Exécuter le script SQL"** (▶) dans la barre d'outils
   - OU utilisez **Ctrl+Enter**
   - OU **Alt+X**

3. Attendez quelques secondes...

## ✅ Étape 7 : Vérifier le résultat

Vous devriez voir dans le panneau "Log" en bas :

- **Succès** : Message vert "SQL script executed successfully" ou "Query executed successfully"
- **Erreur** : Message rouge avec le détail de l'erreur

### Si succès :

Réexécutez la commande de vérification :

```sql
DESCRIBE users;
```

Vous devriez maintenant voir la colonne `name` dans la liste avec :
- **Field** : `name`
- **Type** : `varchar(100)`
- **Null** : `NO`
- **Default** : `Utilisateur`

### Si erreur :

**Erreur : "Duplicate column name 'name'"**
→ La colonne existe déjà, pas besoin de migration !

**Erreur de connexion**
→ Vérifiez vos identifiants dans la configuration de connexion

**Erreur "Table doesn't exist"**
→ La table `users` n'existe pas encore. Exécutez d'abord le fichier `database.sql` complet.

## 🎉 C'est terminé !

Votre table `users` a maintenant le champ `name` et votre application PHP peut fonctionner correctement.

---

## 📸 Aide visuelle

### Où trouver l'éditeur SQL :
- **Méthode 1** : Clic droit sur `aio_db` → SQL Editor → New SQL Script
- **Méthode 2** : Menu → SQL Editor → New SQL Script
- **Méthode 3** : Raccourci **Ctrl+\**

### Bouton d'exécution :
- Icône **▶** (flèche vers la droite) dans la barre d'outils
- Raccourci : **Ctrl+Enter** ou **Alt+X**

### Panneau de résultats :
- En bas de l'écran, onglet **"Log"** ou **"Résultats"**
- Affiche les messages de succès/erreur

