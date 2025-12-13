# Script PowerShell pour démarrer un serveur HTTP simple
$port = 8080
$url = "http://localhost:$port"

Write-Host "Démarrage du serveur HTTP sur le port $port..." -ForegroundColor Green
Write-Host "Ouvrez votre navigateur à l'adresse: $url/index.html" -ForegroundColor Yellow
Write-Host "Appuyez sur Ctrl+C pour arrêter le serveur" -ForegroundColor Gray
Write-Host ""

# Créer un listener HTTP
$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add($url + "/")
$listener.Start()

Write-Host "Serveur démarré avec succès!" -ForegroundColor Green
Write-Host ""

# Ouvrir le navigateur
Start-Process $url/index.html

# Boucle pour gérer les requêtes
while ($listener.IsListening) {
    $context = $listener.GetContext()
    $request = $context.Request
    $response = $context.Response
    
    $localPath = $request.Url.LocalPath
    if ($localPath -eq "/" -or $localPath -eq "") {
        $localPath = "/index.html"
    }
    
    $filePath = Join-Path $PSScriptRoot $localPath.TrimStart('/')
    
    if (Test-Path $filePath -PathType Leaf) {
        $content = [System.IO.File]::ReadAllBytes($filePath)
        $extension = [System.IO.Path]::GetExtension($filePath)
        
        # Déterminer le type MIME
        $contentType = switch ($extension) {
            ".html" { "text/html; charset=utf-8" }
            ".css" { "text/css" }
            ".js" { "application/javascript" }
            ".json" { "application/json" }
            ".png" { "image/png" }
            ".jpg" { "image/jpeg" }
            ".jpeg" { "image/jpeg" }
            ".gif" { "image/gif" }
            ".svg" { "image/svg+xml" }
            default { "application/octet-stream" }
        }
        
        $response.ContentType = $contentType
        $response.ContentLength64 = $content.Length
        $response.StatusCode = 200
        $response.OutputStream.Write($content, 0, $content.Length)
    } else {
        $response.StatusCode = 404
        $notFound = [System.Text.Encoding]::UTF8.GetBytes("404 - Fichier non trouvé")
        $response.ContentLength64 = $notFound.Length
        $response.OutputStream.Write($notFound, 0, $notFound.Length)
    }
    
    $response.Close()
}

