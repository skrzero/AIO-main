<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    /**
     * Affiche la page d'accueil
     */
    public function index(): void
    {
        $this->render('home.twig', [
            'user' => [
                'name' => $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Utilisateur',
                'email' => $_SESSION['user_email'] ?? ''
            ]
        ]);
    }
}

