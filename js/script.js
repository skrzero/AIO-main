/**
 * Script principal pour le menu burger et la navigation
 */

document.addEventListener('DOMContentLoaded', () => {
    highlightActiveLink();
    closeAlerts();
});

/**
 * Met en surbrillance le lien actif dans le menu
 */
function highlightActiveLink() {
    const currentPath = window.location.pathname;
    
    document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
        const linkPath = link.getAttribute('href');
        
        // Vérifie si le chemin correspond (exact ou commence par)
        if (linkPath === currentPath || 
            (currentPath.startsWith(linkPath) && linkPath !== '/home' && linkPath !== '/')) {
            link.classList.add('active');
        }
    });
}

/**
 * Ferme automatiquement les alertes après 5 secondes
 */
function closeAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

