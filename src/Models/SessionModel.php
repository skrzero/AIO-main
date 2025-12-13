<?php

namespace App\Models;

use PDO;
use PDOException;

class SessionModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crée une session dans la base de données
     * @return bool True si l'insertion réussit
     */
    public function create(string $sessionId, int $userId, int $expiresIn = 3600): bool
    {
        try {
            $expiresAt = date('Y-m-d H:i:s', time() + $expiresIn);
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO sessions (session_id, user_id, expires_at) 
                 VALUES (:session_id, :user_id, :expires_at)
                 ON DUPLICATE KEY UPDATE expires_at = :expires_at"
            );

            return $stmt->execute([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'expires_at' => $expiresAt
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans create: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la création de session");
        }
    }

    /**
     * Vérifie si une session est valide
     * @return array|null Retourne les données de session ou null si invalide
     */
    public function findValid(string $sessionId): ?array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM sessions 
                 WHERE session_id = :session_id 
                 AND expires_at > NOW() 
                 LIMIT 1"
            );
            $stmt->execute(['session_id' => $sessionId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log("Erreur SQL dans findValid: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la vérification de session");
        }
    }

    /**
     * Supprime une session
     * @return bool True si la suppression réussit
     */
    public function delete(string $sessionId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE session_id = :session_id");
            return $stmt->execute(['session_id' => $sessionId]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans delete: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la suppression de session");
        }
    }

    /**
     * Nettoie les sessions expirées
     * @return int Nombre de sessions supprimées
     */
    public function cleanExpired(): int
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Erreur SQL dans cleanExpired: " . $e->getMessage());
            return 0;
        }
    }
}

