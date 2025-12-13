<?php

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Vérifie si l'utilisateur est authentifié
     * Redirige vers /login si non authentifié
     * @return bool True si authentifié, false sinon (redirige automatiquement)
     */
    public static function check(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        return true;
    }

    /**
     * Méthode handle() pour compatibilité avec l'ancien code
     */
    public function handle(): void
    {
        self::check();
    }
}

