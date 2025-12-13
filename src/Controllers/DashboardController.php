<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\MessageModel;

class DashboardController extends BaseController
{
    private UserModel $userModel;
    private MessageModel $messageModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel($this->pdo);
        $this->messageModel = new MessageModel($this->pdo);
    }

    /**
     * Affiche la page dashboard
     */
    public function index(): void
    {
        try {
            $userId = $_SESSION['user_id'];
            $user = $this->userModel->findById($userId);
            $messages = $this->messageModel->findByUserId($userId);

            $csrfToken = $this->generateCsrfToken();

            $this->render('dashboard.twig', [
                'user' => $user,
                'messages' => $messages,
                'csrf_token' => $csrfToken,
                'success' => $_SESSION['success'] ?? null,
                'error' => $_SESSION['error'] ?? null
            ]);

            unset($_SESSION['success'], $_SESSION['error']);
        } catch (\RuntimeException $e) {
            error_log("Erreur dans DashboardController: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue lors du chargement du dashboard";
            header('Location: /login');
            exit;
        }
    }

    /**
     * Traite l'ajout d'un message
     */
    public function addMessage(): void
    {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            $this->redirect('/dashboard');
            return;
        }

        $content = trim($_POST['content'] ?? '');

        if (empty($content)) {
            $_SESSION['error'] = "Le message ne peut pas être vide";
            $this->redirect('/dashboard');
            return;
        }

        try {
            $this->messageModel->create([
                'user_id' => $_SESSION['user_id'],
                'content' => $content
            ]);

            $_SESSION['success'] = "Message ajouté avec succès";
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de l'ajout du message: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de l'ajout du message";
        }

        header('Location: /dashboard');
        exit;
    }

    /**
     * Traite la suppression d'un message
     */
    public function deleteMessage(): void
    {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            $this->redirect('/dashboard');
            return;
        }

        $messageId = filter_input(INPUT_POST, 'message_id', FILTER_VALIDATE_INT);

        if (!$messageId) {
            $_SESSION['error'] = "ID de message invalide";
            $this->redirect('/dashboard');
            return;
        }

        try {
            $this->messageModel->delete($messageId, $_SESSION['user_id']);
            $_SESSION['success'] = "Message supprimé avec succès";
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la suppression du message: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la suppression du message";
        }

        header('Location: /dashboard');
        exit;
    }
}

