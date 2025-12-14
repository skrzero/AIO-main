# 👥 Utilisateurs de Test

Ce document liste les utilisateurs de test créés pour tester l'application AIO-main.

## 📋 Liste des Utilisateurs

| # | Nom | Email | Mot de passe |
|---|-----|-------|--------------|
| 1 | Alice Martin | `alice.martin@test.com` | `password123` |
| 2 | Bob Dupont | `bob.dupont@test.com` | `password123` |
| 3 | Claire Bernard | `claire.bernard@test.com` | `password123` |
| 4 | David Leroy | `david.leroy@test.com` | `password123` |
| 5 | Emma Petit | `emma.petit@test.com` | `password123` |

## 🔑 Informations de Connexion

**Mot de passe pour tous les utilisateurs :** `password123`

## 🚀 Comment Créer les Utilisateurs

### Méthode 1 : Via DBeaver (Recommandé)

1. Ouvrez DBeaver
2. Connectez-vous à votre base de données `aio_db`
3. Ouvrez un éditeur SQL (Ctrl+\)
4. Ouvrez le fichier `insert_test_users.sql`
5. Exécutez le script (Ctrl+Enter)

### Méthode 2 : Via Script PHP

```bash
php create_test_users.php
```

### Méthode 3 : Via Ligne de Commande MySQL

```bash
mysql -u root -p aio_db < insert_test_users.sql
```

## ✅ Vérification

Pour vérifier que les utilisateurs ont été créés, exécutez dans DBeaver :

```sql
SELECT id, name, email, created_at FROM users ORDER BY id;
```

Vous devriez voir les 5 utilisateurs listés ci-dessus.

## 🧪 Tests à Effectuer

Avec ces utilisateurs, vous pouvez tester :

1. **Connexion** : Connectez-vous avec n'importe quel email et le mot de passe `password123`
2. **Calendrier** : Créez des événements pour chaque utilisateur
3. **Mémos** : Créez des mémos et testez le drag & drop
4. **Messages** : Ajoutez des messages sur le dashboard
5. **Réinitialisation de mot de passe** : Testez le flux complet

## 🔒 Sécurité

⚠️ **Important** : Ces utilisateurs sont uniquement pour le développement et les tests. 
Ne les utilisez jamais en production !

En production :
- Supprimez ces utilisateurs de test
- Utilisez des mots de passe forts et uniques
- Activez l'authentification à deux facteurs si possible

