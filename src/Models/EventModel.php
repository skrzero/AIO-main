<?php

namespace App\Models;

use PDO;
use PDOException;

class EventModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère les événements d'un utilisateur pour un mois donné
     * @param int $userId ID de l'utilisateur
     * @param string $yearMonth Format: "YYYY-MM"
     * @return array Liste des événements
     */
    public function getEventsByMonth(int $userId, string $yearMonth): array
    {
        try {
            $startDate = $yearMonth . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

            $stmt = $this->pdo->prepare(
                "SELECT * FROM events 
                 WHERE user_id = :user_id 
                 AND start_datetime >= :start_date 
                 AND start_datetime <= :end_date
                 ORDER BY start_datetime ASC"
            );

            $stmt->execute([
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur SQL dans getEventsByMonth: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la récupération des événements");
        }
    }

    /**
     * Récupère les événements d'un jour spécifique
     * @param int $userId ID de l'utilisateur
     * @param string $date Format: "YYYY-MM-DD"
     * @return array Liste des événements du jour
     */
    public function getEventsByDate(int $userId, string $date): array
    {
        try {
            $startDate = $date . ' 00:00:00';
            $endDate = $date . ' 23:59:59';

            $stmt = $this->pdo->prepare(
                "SELECT * FROM events 
                 WHERE user_id = :user_id 
                 AND start_datetime >= :start_date 
                 AND start_datetime <= :end_date
                 ORDER BY start_datetime ASC"
            );

            $stmt->execute([
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur SQL dans getEventsByDate: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la récupération des événements");
        }
    }

    /**
     * Crée un nouvel événement
     * @param array $eventData Données de l'événement
     * @return bool True si l'insertion réussit
     */
    public function createEvent(array $eventData): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO events (user_id, title, description, start_datetime, end_datetime, category) 
                 VALUES (:user_id, :title, :description, :start_datetime, :end_datetime, :category)"
            );

            return $stmt->execute([
                'user_id' => $eventData['user_id'],
                'title' => $eventData['title'],
                'description' => $eventData['description'] ?? null,
                'start_datetime' => $eventData['start_datetime'],
                'end_datetime' => $eventData['end_datetime'],
                'category' => $eventData['category'] ?? 'personnel'
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans createEvent: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la création de l'événement");
        }
    }

    /**
     * Met à jour un événement
     * @param int $eventId ID de l'événement
     * @param int $userId ID de l'utilisateur (vérification de sécurité)
     * @param array $eventData Nouvelles données
     * @return bool True si la mise à jour réussit
     */
    public function updateEvent(int $eventId, int $userId, array $eventData): bool
    {
        try {
            $fields = [];
            $values = ['event_id' => $eventId, 'user_id' => $userId];

            foreach (['title', 'description', 'start_datetime', 'end_datetime', 'category'] as $field) {
                if (isset($eventData[$field])) {
                    $fields[] = "$field = :$field";
                    $values[$field] = $eventData[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $sql = "UPDATE events SET " . implode(', ', $fields) . 
                   " WHERE id = :event_id AND user_id = :user_id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans updateEvent: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la mise à jour de l'événement");
        }
    }

    /**
     * Supprime un événement
     * @param int $eventId ID de l'événement
     * @param int $userId ID de l'utilisateur (vérification de sécurité)
     * @return bool True si la suppression réussit
     */
    public function deleteEvent(int $eventId, int $userId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM events WHERE id = :event_id AND user_id = :user_id"
            );

            return $stmt->execute([
                'event_id' => $eventId,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SQL dans deleteEvent: " . $e->getMessage());
            throw new \RuntimeException("Erreur de base de données lors de la suppression de l'événement");
        }
    }
}

