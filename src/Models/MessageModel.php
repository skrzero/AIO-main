<?php

namespace App\Models;

use PDO;
use PDOException;

class MessageModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les messages d'un utilisateur
     * @return array Liste des messages
     */
    public function findByUserId(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM messages 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC"
            );
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur SQL dans findByUserId: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la récupération des messages");
        }
    }

    /**
     * Crée un nouveau message
     * @return bool True si l'insertion réussit
     */
    public function create(array $messageData): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO messages (user_id, content, created_at) 
                 VALUES (:user_id, :content, NOW())"
            );

            return $stmt->execute([
                'user_id' => $messageData['user_id'],
                'content' => $messageData['content']
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans create: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la création du message");
        }
    }

    /**
     * Supprime un message
     * @return bool True si la suppression réussit
     */
    public function delete(int $messageId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM messages 
                 WHERE id = :id AND user_id = :user_id"
            );

            return $stmt->execute([
                'id' => $messageId,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans delete: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la suppression du message");
        }
    }
}

