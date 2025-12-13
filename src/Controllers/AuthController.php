<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\SessionModel;

class AuthController extends BaseController
{
    private UserModel $userModel;
    private SessionModel $sessionModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel($this->pdo);
        $this->sessionModel = new SessionModel($this->pdo);
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function showLogin(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('auth/login.twig', [
            'csrf_token' => $csrfToken,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['error']);
    }

    /**
     * Traite la soumission du formulaire de connexion
     */
    public function login(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            $this->redirect('/login');
            return;
        }

        // Validation des données
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $_SESSION['error'] = "Email ou mot de passe invalide";
            $this->redirect('/login');
            return;
        }

        try {
            $user = $this->userModel->validateLogin($email, $password);

            if ($user) {
                // Régénère l'ID de session pour la sécurité
                session_regenerate_id(true);

                // Stocke les données utilisateur en session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'] ?? $user['email'];

                // Crée une entrée dans la table sessions
                $this->sessionModel->create(session_id(), $user['id']);

                // Redirige vers home
                $this->redirect('/home');
            } else {
                $_SESSION['error'] = "Email ou mot de passe incorrect";
                $this->redirect('/login');
            }
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la connexion: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue. Réessayez plus tard.";
            $this->redirect('/login');
        }
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegister(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('auth/register.twig', [
            'csrf_token' => $csrfToken,
            'error' => $_SESSION['error'] ?? null
        ]);

        unset($_SESSION['error']);
    }

    /**
     * Traite la soumission du formulaire d'inscription
     */
    public function register(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            $this->redirect('/register');
            return;
        }

        // Validation des données
        $name = trim($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($name)) {
            $_SESSION['error'] = "Le nom est obligatoire";
            $this->redirect('/register');
            return;
        }

        if (!$email) {
            $_SESSION['error'] = "Email invalide";
            $this->redirect('/register');
            return;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
            $this->redirect('/register');
            return;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas";
            $this->redirect('/register');
            return;
        }

        try {
            $success = $this->userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]);

            if ($success) {
                $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                $this->redirect('/login');
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription";
                $this->redirect('/register');
            }
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/register');
        }
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(): void
    {
        try {
            // Supprime la session de la base de données
            if (isset($_SESSION['user_id'])) {
                $this->sessionModel->delete(session_id());
            }
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la déconnexion: " . $e->getMessage());
        }

        // Détruit la session PHP
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        $this->redirect('/login');
    }
}
