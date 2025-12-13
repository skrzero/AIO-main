<?php

/**
 * Point d'entrée de l'application MVC
 */

// Démarre la session avec des paramètres sécurisés
session_start([
    'cookie_lifetime' => 3600,
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']), // Active seulement en HTTPS
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Charge l'autoloader Composer
require __DIR__ . '/vendor/autoload.php';

// Router simple
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    // Routes publiques (pas de protection)
    $publicRoutes = ['/login', '/register'];
    
    // Routes protégées (nécessitent une authentification)
    $protectedRoutes = ['/home', '/dashboard', '/calendar', '/memos'];

    // Route: Page d'accueil -> redirige vers login ou home
    if ($requestUri === '/' || $requestUri === '/index.php') {
        if (isset($_SESSION['user_id'])) {
            header('Location: /home');
        } else {
            header('Location: /login');
        }
        exit;
    }

    // Vérifie si la route est protégée
    $isProtected = false;
    foreach ($protectedRoutes as $protectedRoute) {
        if (strpos($requestUri, $protectedRoute) === 0) {
            $isProtected = true;
            break;
        }
    }

    // Applique le middleware d'authentification pour les routes protégées
    if ($isProtected) {
        \App\Middleware\AuthMiddleware::check();
    }

    // Route: Login (GET)
    if ($requestUri === '/login' && $requestMethod === 'GET') {
        (new \App\Controllers\AuthController())->showLogin();
        exit;
    }

    // Route: Login (POST)
    if ($requestUri === '/login' && $requestMethod === 'POST') {
        (new \App\Controllers\AuthController())->login();
        exit;
    }

    // Route: Register (GET)
    if ($requestUri === '/register' && $requestMethod === 'GET') {
        (new \App\Controllers\AuthController())->showRegister();
        exit;
    }

    // Route: Register (POST)
    if ($requestUri === '/register' && $requestMethod === 'POST') {
        (new \App\Controllers\AuthController())->register();
        exit;
    }

    // Route: Home (protégée)
    if ($requestUri === '/home') {
        (new \App\Controllers\HomeController())->index();
        exit;
    }

    // Route: Logout
    if ($requestUri === '/logout') {
        (new \App\Controllers\AuthController())->logout();
        exit;
    }

    // Route: Dashboard (protégée)
    if ($requestUri === '/dashboard') {
        (new \App\Controllers\DashboardController())->index();
        exit;
    }

    // Route: Ajouter un message (protégée)
    if ($requestUri === '/dashboard/message/add' && $requestMethod === 'POST') {
        (new \App\Controllers\DashboardController())->addMessage();
        exit;
    }

    // Route: Supprimer un message (protégée)
    if ($requestUri === '/dashboard/message/delete' && $requestMethod === 'POST') {
        (new \App\Controllers\DashboardController())->deleteMessage();
        exit;
    }

    // Route: Calendrier (protégée)
    if ($requestUri === '/calendar' && $requestMethod === 'GET') {
        $yearMonth = $_GET['month'] ?? null;
        (new \App\Controllers\CalendarController())->showMonth($yearMonth);
        exit;
    }

    // Route: Événements d'un jour (AJAX, protégée)
    if ($requestUri === '/calendar/day-events' && $requestMethod === 'GET') {
        (new \App\Controllers\CalendarController())->getDayEvents();
        exit;
    }

    // Route: Créer un événement (protégée)
    if ($requestUri === '/calendar/event/create' && $requestMethod === 'POST') {
        (new \App\Controllers\CalendarController())->createEvent();
        exit;
    }

    // Route: Supprimer un événement (protégée)
    if ($requestUri === '/calendar/event/delete' && $requestMethod === 'POST') {
        (new \App\Controllers\CalendarController())->deleteEvent();
        exit;
    }

    // Route: Mémos (protégée)
    if ($requestUri === '/memos' && $requestMethod === 'GET') {
        (new \App\Controllers\MemoController())->index();
        exit;
    }

    // Route: Créer un mémo (protégée)
    if ($requestUri === '/memos/create' && $requestMethod === 'POST') {
        (new \App\Controllers\MemoController())->create();
        exit;
    }

    // Route: Mettre à jour un mémo (protégée)
    if ($requestUri === '/memos/update' && $requestMethod === 'POST') {
        (new \App\Controllers\MemoController())->update();
        exit;
    }

    // Route: Mettre à jour les positions des mémos (AJAX, protégée)
    if ($requestUri === '/memos/update-positions' && $requestMethod === 'POST') {
        (new \App\Controllers\MemoController())->updatePositions();
        exit;
    }

    // Route: Supprimer un mémo (protégée)
    if ($requestUri === '/memos/delete' && $requestMethod === 'POST') {
        (new \App\Controllers\MemoController())->delete();
        exit;
    }

    // Route: Fichiers statiques (JS, CSS, images)
    if (preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico)$/i', $requestUri)) {
        $filePath = __DIR__ . $requestUri;
        if (file_exists($filePath) && is_file($filePath)) {
            $mimeTypes = [
                'js' => 'application/javascript',
                'css' => 'text/css',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon'
            ];
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }

    // Route non trouvée
    http_response_code(404);
    echo "Page non trouvée (404)";
    
} catch (\Throwable $e) {
    error_log("Erreur fatale: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo "Une erreur est survenue. Veuillez réessayer plus tard.";
}

