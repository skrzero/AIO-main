<?php
/**
 * Script PHP pour créer 5 utilisateurs de test
 * Exécutez ce script via : php create_test_users.php
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/config/db_config.php';

use App\Config\Database;
use App\Models\UserModel;

$pdo = Database::getInstance();
$userModel = new UserModel($pdo);

$testUsers = [
    [
        'name' => 'Alice Martin',
        'email' => 'alice.martin@test.com',
        'password' => 'password123'
    ],
    [
        'name' => 'Bob Dupont',
        'email' => 'bob.dupont@test.com',
        'password' => 'password123'
    ],
    [
        'name' => 'Claire Bernard',
        'email' => 'claire.bernard@test.com',
        'password' => 'password123'
    ],
    [
        'name' => 'David Leroy',
        'email' => 'david.leroy@test.com',
        'password' => 'password123'
    ],
    [
        'name' => 'Emma Petit',
        'email' => 'emma.petit@test.com',
        'password' => 'password123'
    ]
];

echo "Création de 5 utilisateurs de test...\n\n";

foreach ($testUsers as $userData) {
    try {
        // Vérifie si l'utilisateur existe déjà
        $existingUser = $userModel->findByEmail($userData['email']);
        
        if ($existingUser) {
            echo "⚠️  Utilisateur {$userData['email']} existe déjà. Ignoré.\n";
            continue;
        }

        $success = $userModel->create($userData);
        
        if ($success) {
            echo "✅ Utilisateur créé : {$userData['name']} ({$userData['email']})\n";
        } else {
            echo "❌ Erreur lors de la création de {$userData['email']}\n";
        }
    } catch (\RuntimeException $e) {
        echo "❌ Erreur pour {$userData['email']} : {$e->getMessage()}\n";
    }
}

echo "\n✅ Terminé !\n";
echo "\n📋 Informations de connexion :\n";
echo "   Email : n'importe quel email ci-dessus\n";
echo "   Mot de passe : password123\n\n";

