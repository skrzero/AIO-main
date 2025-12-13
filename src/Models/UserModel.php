<?php

namespace App\Models;

use PDO;
use PDOException;

class UserModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Trouve un utilisateur par son email
     * @return array|null Retourne l'utilisateur ou null si non trouvé
     */
    public function findByEmail(string $email): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans findByEmail: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la recherche d'utilisateur");
        }
    }

    /**
     * Crée un nouvel utilisateur avec mot de passe haché
     * @return bool True si l'insertion réussit
     */
    public function create(array $userData): bool
    {
        try {
            // Hash du mot de passe
            if (isset($userData['password'])) {
                $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }

            // Vérifie si le champ name existe dans la table
            $hasName = isset($userData['name']);
            
            if ($hasName) {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO users (name, email, password, created_at) 
                     VALUES (:name, :email, :password, NOW())"
                );
                return $stmt->execute([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ]);
            } else {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO users (email, password, created_at) 
                     VALUES (:email, :password, NOW())"
                );
                return $stmt->execute([
                    'email' => $userData['email'],
                    'password' => $userData['password']
                ]);
            }
        } catch (PDOException $e) {
            error_log("Erreur SQL dans create: " . $e->getMessage());
            
            // Vérifie si c'est une erreur de contrainte unique (email déjà utilisé)
            if ($e->getCode() === '23000') {
                throw new \RuntimeException("Cet email est déjà utilisé");
            }
            
            throw new \RuntimeException("Erreur de base de données lors de la création d'utilisateur");
        }
    }

    /**
     * Valide les identifiants de l'utilisateur.
     * @param string $email L'adresse email de l'utilisateur
     * @param string $password Le mot de passe en clair à vérifier
     * @return array|null Retourne les données utilisateur (sans le mot de passe) ou null si échec.
     * @throws \RuntimeException En cas d'erreur BDD.
     */
    public function validateLogin(string $email, string $password): ?array
    {
        try {
            $user = $this->findByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                // Retourne l'utilisateur sans le mot de passe
                unset($user['password']);
                return $user;
            }
            
            return null;
        } catch (\RuntimeException $e) {
            error_log("Erreur dans validateLogin: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Trouve un utilisateur par son ID
     * @return array|null Retourne l'utilisateur ou null si non trouvé
     */
    public function findById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans findById: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la recherche d'utilisateur");
        }
    }
}

