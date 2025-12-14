<?php
/**
 * Script pour générer un hash de mot de passe
 * Utilisez : php generate_password_hash.php
 */

$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Mot de passe : $password\n";
echo "Hash généré : $hash\n";
echo "\n";
echo "Pour vérifier :\n";
echo "password_verify('$password', '$hash') = " . (password_verify($password, $hash) ? 'true' : 'false') . "\n";

