<?php

namespace App\Controllers;

use PDO;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Config\Database;

class BaseController
{
    protected PDO $pdo;
    protected Environment $twig;

    public function __construct()
    {
        $this->pdo = Database::getInstance();

        $loader = new FilesystemLoader(__DIR__ . '/../Views');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true
        ]);

        // Ajoute des variables globales à Twig
        $this->twig->addGlobal('session', $_SESSION);
    }

    /**
     * Rend une vue Twig
     * @param string $template Nom du template (ex: 'auth/login.twig')
     * @param array $data Données à passer à la vue
     */
    protected function render(string $template, array $data = []): void
    {
        echo $this->twig->render($template, $data);
    }

    /**
     * Redirige vers une URL
     * @param string $url URL de destination
     */
    protected function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    /**
     * Génère un token CSRF et le stocke en session
     * @return string Le token généré
     */
    protected function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie un token CSRF
     * @param string $token Token à vérifier
     * @return bool True si le token est valide
     */
    protected function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

