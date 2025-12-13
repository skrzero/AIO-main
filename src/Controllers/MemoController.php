<?php

namespace App\Controllers;

use App\Models\MemoModel;

class MemoController extends BaseController
{
    private MemoModel $memoModel;

    public function __construct()
    {
        parent::__construct();
        $this->memoModel = new MemoModel($this->pdo);
    }

    /**
     * Affiche la page des mémos
     */
    public function index(): void
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        try {
            $memos = $this->memoModel->getMemos($_SESSION['user_id']);

            $csrfToken = $this->generateCsrfToken();

            $this->render('memos.twig', [
                'memos' => $memos,
                'csrf_token' => $csrfToken,
                'success' => $_SESSION['success'] ?? null,
                'error' => $_SESSION['error'] ?? null
            ]);

            unset($_SESSION['success'], $_SESSION['error']);
        } catch (\RuntimeException $e) {
            error_log("Erreur dans MemoController: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors du chargement des mémos";
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Crée un nouveau mémo
     */
    public function create(): void
    {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            header('Location: /memos');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title)) {
            $_SESSION['error'] = "Le titre est obligatoire";
            header('Location: /memos');
            exit;
        }

        try {
            $this->memoModel->createMemo([
                'user_id' => $_SESSION['user_id'],
                'title' => $title,
                'content' => $content
            ]);

            $_SESSION['success'] = "Mémo créé avec succès";
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la création du mémo: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la création du mémo";
        }

        header('Location: /memos');
        exit;
    }

    /**
     * Met à jour un mémo
     */
    public function update(): void
    {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            header('Location: /memos');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $memoId = filter_input(INPUT_POST, 'memo_id', FILTER_VALIDATE_INT);
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (!$memoId || empty($title)) {
            $_SESSION['error'] = "Données invalides";
            header('Location: /memos');
            exit;
        }

        try {
            $this->memoModel->updateMemo($memoId, $_SESSION['user_id'], [
                'title' => $title,
                'content' => $content
            ]);

            $_SESSION['success'] = "Mémo mis à jour avec succès";
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la mise à jour du mémo: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la mise à jour du mémo";
        }

        header('Location: /memos');
        exit;
    }

    /**
     * Met à jour les positions des mémos (drag-and-drop)
     */
    public function updatePositions(): void
    {
        header('Content-Type: application/json');

        // Vérification CSRF
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $input['csrf_token'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF invalide']);
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Non authentifié']);
            exit;
        }

        $positions = $input['positions'] ?? [];

        if (!is_array($positions) || empty($positions)) {
            http_response_code(400);
            echo json_encode(['error' => 'Données invalides']);
            exit;
        }

        try {
            $this->memoModel->updateMemosPositions($positions, $_SESSION['user_id']);
            echo json_encode(['success' => true]);
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la mise à jour des positions: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }

    /**
     * Supprime un mémo
     */
    public function delete(): void
    {
        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            header('Location: /memos');
            exit;
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $memoId = filter_input(INPUT_POST, 'memo_id', FILTER_VALIDATE_INT);

        if (!$memoId) {
            $_SESSION['error'] = "ID de mémo invalide";
            header('Location: /memos');
            exit;
        }

        try {
            $this->memoModel->deleteMemo($memoId, $_SESSION['user_id']);
            $_SESSION['success'] = "Mémo supprimé avec succès";
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la suppression du mémo: " . $e->getMessage());
            $_SESSION['error'] = "Erreur lors de la suppression du mémo";
        }

        header('Location: /memos');
        exit;
    }
}

