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
     * Affiche la page avec onglets (Connexion / Inscription)
     */
    public function showAuthTabs(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('auth/auth-tabs.twig', [
            'csrf_token' => $csrfToken,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ]);

        unset($_SESSION['error'], $_SESSION['success']);
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
            $this->redirect('/auth');
            return;
        }

        // Validation des données
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $_SESSION['error'] = "Email ou mot de passe invalide";
            $this->redirect('/auth');
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
                $this->redirect('/auth');
            }
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la connexion: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue. Réessayez plus tard.";
            $this->redirect('/auth');
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
            $this->redirect('/auth');
            return;
        }

        // Validation des données
        $name = trim($_POST['name'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($name)) {
            $_SESSION['error'] = "Le nom est obligatoire";
            $this->redirect('/auth');
            return;
        }

        if (!$email) {
            $_SESSION['error'] = "Email invalide";
            $this->redirect('/auth');
            return;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
            $this->redirect('/auth');
            return;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas";
            $this->redirect('/auth');
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
                // Redirige vers l'onglet connexion après inscription
                $this->redirect('/auth');
            } else {
                $_SESSION['error'] = "Erreur lors de l'inscription";
                $this->redirect('/auth');
            }
        } catch (\RuntimeException $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/auth');
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

        $this->redirect('/auth');
    }

    /**
     * Affiche le formulaire de demande de réinitialisation de mot de passe
     */
    public function showForgotPassword(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('auth/forgot-password.twig', [
            'csrf_token' => $csrfToken,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ]);

        unset($_SESSION['error'], $_SESSION['success']);
    }

    /**
     * Traite la demande de réinitialisation de mot de passe
     */
    public function forgotPassword(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            $this->redirect('/forgot-password');
            return;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $_SESSION['error'] = "Email invalide";
            $this->redirect('/forgot-password');
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if ($user) {
                // Génère un token de réinitialisation
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 heure

                // Stocke le token en base de données
                $stmt = $this->pdo->prepare(
                    "INSERT INTO password_resets (email, token, expires_at) 
                     VALUES (:email, :token, :expires_at)
                     ON DUPLICATE KEY UPDATE token = :token, expires_at = :expires_at, created_at = NOW()"
                );
                $stmt->execute([
                    'email' => $email,
                    'token' => $token,
                    'expires_at' => $expiresAt
                ]);

                // En production, vous enverriez un email ici
                // Pour le développement, on affiche le lien dans un message
                $resetLink = $_SERVER['HTTP_HOST'] . '/reset-password?token=' . $token;
                
                $_SESSION['success'] = "Un lien de réinitialisation a été généré. En production, il serait envoyé par email. Lien de test : " . $resetLink;
            } else {
                // Pour la sécurité, on affiche le même message même si l'email n'existe pas
                $_SESSION['success'] = "Si cet email existe, un lien de réinitialisation vous a été envoyé.";
            }

            $this->redirect('/forgot-password');
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la demande de réinitialisation: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue. Réessayez plus tard.";
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Affiche le formulaire de réinitialisation de mot de passe
     */
    public function showResetPassword(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = "Token invalide ou manquant";
            $this->redirect('/forgot-password');
            return;
        }

        // Vérifie si le token est valide
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM password_resets 
                 WHERE token = :token 
                 AND expires_at > NOW() 
                 LIMIT 1"
            );
            $stmt->execute(['token' => $token]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $_SESSION['error'] = "Token invalide ou expiré";
                $this->redirect('/forgot-password');
                return;
            }

            $csrfToken = $this->generateCsrfToken();
            $this->render('auth/reset-password.twig', [
                'csrf_token' => $csrfToken,
                'token' => $token,
                'error' => $_SESSION['error'] ?? null
            ]);

            unset($_SESSION['error']);
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la vérification du token: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue. Réessayez plus tard.";
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Traite la réinitialisation du mot de passe
     */
    public function resetPassword(): void
    {
        // Si déjà connecté, redirige vers home
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/home');
            return;
        }

        // Vérification CSRF
        if (!isset($_POST['csrf_token']) || !$this->verifyCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = "Token CSRF invalide";
            $this->redirect('/forgot-password');
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = "Token invalide";
            $this->redirect('/forgot-password');
            return;
        }

        if (strlen($password) < 8) {
            $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères";
            $this->redirect('/reset-password?token=' . $token);
            return;
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = "Les mots de passe ne correspondent pas";
            $this->redirect('/reset-password?token=' . $token);
            return;
        }

        try {
            // Vérifie le token
            $stmt = $this->pdo->prepare(
                "SELECT * FROM password_resets 
                 WHERE token = :token 
                 AND expires_at > NOW() 
                 LIMIT 1"
            );
            $stmt->execute(['token' => $token]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $_SESSION['error'] = "Token invalide ou expiré";
                $this->redirect('/forgot-password');
                return;
            }

            // Met à jour le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare(
                "UPDATE users SET password = :password WHERE email = :email"
            );
            $stmt->execute([
                'password' => $hashedPassword,
                'email' => $reset['email']
            ]);

            // Supprime le token utilisé
            $stmt = $this->pdo->prepare("DELETE FROM password_resets WHERE token = :token");
            $stmt->execute(['token' => $token]);

            $_SESSION['success'] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
            $this->redirect('/auth');
        } catch (\RuntimeException $e) {
            error_log("Erreur lors de la réinitialisation: " . $e->getMessage());
            $_SESSION['error'] = "Une erreur est survenue. Réessayez plus tard.";
            $this->redirect('/reset-password?token=' . $token);
        }
    }
}
