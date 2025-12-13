<?php

namespace App\Models;

use PDO;
use PDOException;

class MemoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les mémos d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des mémos triés par position
     */
    public function getMemos(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM memos 
                 WHERE user_id = :user_id 
                 ORDER BY position ASC, created_at DESC"
            );

            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur SQL dans getMemos: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la récupération des mémos");
        }
    }

    /**
     * Crée un nouveau mémo
     * @param array $memoData Données du mémo
     * @return int ID du mémo créé
     */
    public function createMemo(array $memoData): int
    {
        try {
            // Récupère la position maximale pour placer le nouveau mémo à la fin
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(MAX(position), 0) + 1 as next_position 
                 FROM memos WHERE user_id = :user_id"
            );
            $stmt->execute(['user_id' => $memoData['user_id']]);
            $result = $stmt->fetch();
            $nextPosition = $result['next_position'] ?? 1;

            $stmt = $this->pdo->prepare(
                "INSERT INTO memos (user_id, title, content, position) 
                 VALUES (:user_id, :title, :content, :position)"
            );

            $stmt->execute([
                'user_id' => $memoData['user_id'],
                'title' => $memoData['title'],
                'content' => $memoData['content'] ?? null,
                'position' => $nextPosition
            ]);

            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur SQL dans createMemo: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la création du mémo");
        }
    }

    /**
     * Met à jour un mémo
     * @param int $memoId ID du mémo
     * @param int $userId ID de l'utilisateur (vérification de sécurité)
     * @param array $memoData Nouvelles données
     * @return bool True si la mise à jour réussit
     */
    public function updateMemo(int $memoId, int $userId, array $memoData): bool
    {
        try {
            $fields = [];
            $values = ['memo_id' => $memoId, 'user_id' => $userId];

            foreach (['title', 'content'] as $field) {
                if (isset($memoData[$field])) {
                    $fields[] = "$field = :$field";
                    $values[$field] = $memoData[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE memos SET " . implode(', ', $fields) . 
                   " WHERE id = :memo_id AND user_id = :user_id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans updateMemo: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la mise à jour du mémo");
        }
    }

    /**
     * Met à jour la position d'un mémo (pour le drag-and-drop)
     * @param int $memoId ID du mémo
     * @param int $userId ID de l'utilisateur
     * @param int $newPosition Nouvelle position
     * @return bool True si la mise à jour réussit
     */
    public function updateMemoPosition(int $memoId, int $userId, int $newPosition): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE memos SET position = :position 
                 WHERE id = :memo_id AND user_id = :user_id"
            );

            return $stmt->execute([
                'position' => $newPosition,
                'memo_id' => $memoId,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans updateMemoPosition: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la mise à jour de la position");
        }
    }

    /**
     * Met à jour les positions de plusieurs mémos (pour le drag-and-drop)
     * @param array $positions Tableau associatif [memo_id => position]
     * @param int $userId ID de l'utilisateur
     * @return bool True si toutes les mises à jour réussissent
     */
    public function updateMemosPositions(array $positions, int $userId): bool
    {
        try {
            $this->pdo->beginTransaction();

            foreach ($positions as $memoId => $position) {
                $stmt = $this->pdo->prepare(
                    "UPDATE memos SET position = :position 
                     WHERE id = :memo_id AND user_id = :user_id"
                );

                if (!$stmt->execute([
                    'position' => $position,
                    'memo_id' => $memoId,
                    'user_id' => $userId
                ])) {
                    throw new \RuntimeException("Erreur lors de la mise à jour de la position");
                }
            }

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur SQL dans updateMemosPositions: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la mise à jour des positions");
        }
    }

    /**
     * Supprime un mémo
     * @param int $memoId ID du mémo
     * @param int $userId ID de l'utilisateur (vérification de sécurité)
     * @return bool True si la suppression réussit
     */
    public function deleteMemo(int $memoId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM memos WHERE id = :memo_id AND user_id = :user_id"
            );

            return $stmt->execute([
                'memo_id' => $memoId,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans deleteMemo: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la suppression du mémo");
        }
    }
}

