// Gestion du thème sombre
(function() {
    'use strict';
    
    // Récupérer la préférence de thème depuis localStorage
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Appliquer le thème au chargement
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
    
    // Fonction pour basculer le thème
    function toggleTheme() {
        const isDark = document.body.classList.toggle('dark-theme');
        const theme = isDark ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
        
        // Mettre à jour l'icône
        updateThemeIcon(isDark);
    }
    
    // Mettre à jour l'icône du bouton
    function updateThemeIcon(isDark) {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.textContent = isDark ? '☀️' : '🌙';
            themeToggle.setAttribute('aria-label', isDark ? 'Mode clair' : 'Mode sombre');
        }
    }
    
    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Mettre à jour l'icône initiale
        updateThemeIcon(currentTheme === 'dark');
        
        // Ajouter l'événement au bouton
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }
    });
})();
