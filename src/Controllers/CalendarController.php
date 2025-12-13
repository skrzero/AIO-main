<?php

namespace App\Controllers;

use App\Models\EventModel;

class CalendarController extends BaseController
{
    private EventModel $eventModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventModel = new EventModel($this->pdo);
    }

    /**
     * Affiche le calendrier pour un mois donné
     */
    public function showMonth(?string $yearMonth = null): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Si pas de mois spécifié, utilise le mois actuel
        if (!$yearMonth) {
            $yearMonth = date('Y-m');
        }

        // Validation du format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            $yearMonth = date('Y-m');
        }

        try {
            $events = $this->eventModel->getEventsByMonth($userId, $yearMonth);
            
            $csrfToken = $this->generateCsrfToken();

            $this->render('calendar.twig', [
                'yearMonth' => $yearMonth,
                'events' => $events,
                'csrf_token' => $csrfToken,
                'success' => $_SESSION['success'] ?? null,
                'error' => $_SESSION['error'] ?? null
            ]);

            unset($_SESSION['success'], $_SESSION['error']);
        } catch (\RuntimeException $e) {
            error_log("Erreur dans CalendarController: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors du chargement du calendrier";
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Récupère les événements d'un jour (AJAX)
     */
    public function getDayEvents(): void
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            exit;
        }

        $date = $_GET['date'] ?? '';
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400);
            echo json_encode(['error' => 'Format de date invalide']);
            exit;
        }

        try {
            $events = $this->eventModel->getEventsByDate($_SESSION['user_id'], $date);
            echo json_encode(['events' => $events]);
        } catch (\RuntimeException $e) {
            error_log("Erreur dans getDayEvents: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }

    /**
     * Crée un nouvel événement
     */
    public function createEvent(): void
    {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            header('Location: /calendar');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $startDatetime = $_POST['start_datetime'] ?? '';
        $endDatetime = $_POST['end_datetime'] ?? '';
        $category = $_POST['category'] ?? 'personnel';

        // Validation
        if (empty($title) || empty($startDatetime) || empty($endDatetime)) {
            $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis";
            header('Location: /calendar');
            exit;
        }

        // Validation des dates
        if (!strtotime($startDatetime) || !strtotime($endDatetime)) {
            $_SESSION['error'] = "Format de date invalide";
            header('Location: /calendar');
            exit;
        }

        if (strtotime($endDatetime) < strtotime($startDatetime)) {
            $_SESSION['error'] = "La date de fin doit être après la date de début";
            header('Location: /calendar');
            exit;
        }

        try {
            $this->eventModel->createEvent([
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'description' => $description,
                'start_datetime' => $startDatetime,
                'end_datetime' => $endDatetime,
                'category' => $category
            ]);

            $_SESSION['success'] = "Événement créé avec succès";
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la création de l'événement: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la création de l'événement";
        }

        header('Location: /calendar');
        exit;
    }

    /**
     * Supprime un événement
     */
    public function deleteEvent(): void
    {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            header('Location: /calendar');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);

        if (!$eventId) {
            $_SESSION['error'] = "ID d'événement invalide";
            header('Location: /calendar');
            exit;
        }

        try {
            $this->eventModel->deleteEvent($eventId, $_SESSION['user_id']);
            $_SESSION['success'] = "Événement supprimé avec succès";
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la suppression de l'événement: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la suppression de l'événement";
        }

        header('Location: /calendar');
        exit;
    }
}

