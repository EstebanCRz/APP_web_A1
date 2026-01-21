/**
 * Gestion de la barre de recherche et de la recherche en temps réel
 * Fichier dédié à la fonctionnalité de recherche d'événements
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        searchDelay: 3000, // Délai avant la recherche (ms)
        minSearchLength: 2 // Nombre minimum de caractères
    };

    let searchTimeout = null;

    /**
     * Initialise la fonctionnalité de recherche
     */
    function initRecherche() {
        const searchInput = document.querySelector('.search-form input[type="search"]');
        if (!searchInput) return;

        // Événement de saisie avec debounce
        searchInput.addEventListener('input', handleSearchInput);
        
        // Événement de soumission du formulaire
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', handleSearchSubmit);
        }

        // Auto-focus si paramètre search dans l'URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('search')) {
            searchInput.focus();
        }
    }

    /**
     * Gère la saisie dans le champ de recherche (avec debounce)
     * @param {Event} event
     */
    function handleSearchInput(event) {
        const searchTerm = event.target.value.trim();

        // Annuler le timeout précédent
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Si la recherche est vide, ne rien faire
        if (searchTerm.length === 0) {
            return;
        }

        // Si la recherche est trop courte, attendre
        if (searchTerm.length < CONFIG.minSearchLength) {
            return;
        }

        // Lancer la recherche après un délai
        searchTimeout = setTimeout(() => {
            effectuerRecherche(searchTerm);
        }, CONFIG.searchDelay);
    }

    /**
     * Gère la soumission du formulaire de recherche
     * @param {Event} event
     */
    function handleSearchSubmit(event) {
        // Le formulaire se soumet normalement via GET
        // Mais on peut ajouter de la validation ici si nécessaire
        const searchInput = event.target.querySelector('input[type="search"]');
        const searchTerm = searchInput.value.trim();

        if (searchTerm.length === 0) {
            event.preventDefault();
            searchInput.focus();
            return;
        }
    }

    /**
     * Effectue la recherche (recharge la page avec les paramètres)
     * @param {string} searchTerm Terme de recherche
     */
    function effectuerRecherche(searchTerm) {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('search', searchTerm);
        
        // Conserver les autres filtres
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.location.href = newUrl;
    }

    /**
     * Met en surbrillance les termes de recherche dans les résultats
     * @param {string} searchTerm Terme à surligner
     */
    function highlightSearchTerms(searchTerm) {
        if (!searchTerm || searchTerm.length < CONFIG.minSearchLength) return;

        const eventsGrid = document.querySelector('.events-grid');
        if (!eventsGrid) return;

        const eventCards = eventsGrid.querySelectorAll('.event-card');
        
        eventCards.forEach(card => {
            const title = card.querySelector('.card-title');
            const excerpt = card.querySelector('.card-excerpt');
            const location = card.querySelector('.card-meta .meta-item:first-child');

            [title, excerpt, location].forEach(element => {
                if (!element) return;
                
                const text = element.textContent;
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                
                if (regex.test(text)) {
                    element.innerHTML = text.replace(regex, '<mark class="search-highlight">$1</mark>');
                }
            });
        });
    }

    /**
     * Échappe les caractères spéciaux pour regex
     * @param {string} str Chaîne à échapper
     * @return {string}
     */
    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Ajoute les styles pour la surbrillance
     */
    function addHighlightStyles() {
        if (document.getElementById('search-highlight-styles')) return;

        const style = document.createElement('style');
        style.id = 'search-highlight-styles';
        style.textContent = `
            .search-highlight {
                background-color: rgba(246, 177, 45, 0.3);
                color: inherit;
                padding: 0.1em 0.2em;
                border-radius: 3px;
                font-weight: 600;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Affiche un indicateur de chargement
     */
    function showLoadingIndicator() {
        const searchForm = document.querySelector('.search-form');
        if (!searchForm) return;

        const existingIndicator = document.querySelector('.search-loading');
        if (existingIndicator) return;

        const indicator = document.createElement('div');
        indicator.className = 'search-loading';
        indicator.innerHTML = '🔍 Recherche en cours...';
        searchForm.appendChild(indicator);
    }

    /**
     * Masque l'indicateur de chargement
     */
    function hideLoadingIndicator() {
        const indicator = document.querySelector('.search-loading');
        if (indicator) {
            indicator.remove();
        }
    }

    // Initialisation au chargement du DOM
    document.addEventListener('DOMContentLoaded', function() {
        initRecherche();
        addHighlightStyles();

        // Surligner les termes de recherche si présents
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get('search');
        if (searchTerm) {
            highlightSearchTerms(searchTerm);
        }
    });

})();
