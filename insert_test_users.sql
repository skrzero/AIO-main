-- Script SQL pour insérer 5 utilisateurs de test
-- À exécuter dans DBeaver après avoir créé la table users

USE aio_db;

-- Supprime les utilisateurs de test s'ils existent déjà (optionnel)
-- DELETE FROM users WHERE email LIKE '%@test.com';

-- Insertion de 5 utilisateurs de test
-- Les mots de passe sont tous : "password123" (hachés avec password_hash)
-- Pour tester, connectez-vous avec n'importe quel email ci-dessous et le mot de passe "password123"
-- 
-- NOTE : Les hashes ci-dessous sont valides. Si vous préférez générer de nouveaux hashes,
-- utilisez le script PHP : php create_test_users.php (qui génère automatiquement les hashes)

INSERT INTO users (name, email, password) VALUES
('Alice Martin', 'alice.martin@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy'),
('Bob Dupont', 'bob.dupont@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy'),
('Claire Bernard', 'claire.bernard@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy'),
('David Leroy', 'david.leroy@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy'),
('Emma Petit', 'emma.petit@test.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Vérification : Affiche les utilisateurs créés
SELECT id, name, email, created_at FROM users ORDER BY id;

-- Note : Le mot de passe pour tous ces utilisateurs est : password123
-- Vous pouvez vous connecter avec n'importe quel email ci-dessus et ce mot de passe

