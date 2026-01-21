/**
 * JavaScript pour le système de recherche et filtres
 * Gère les interactions côté client
 */

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    initialiserRecherche();
    initialiserFiltres();
});

/**
 * Initialise la barre de recherche
 */
function initialiserRecherche() {
    const formulaireRecherche = document.querySelector('.search-form');
    const champRecherche = document.querySelector('.search-form input[type="search"]');
    
    if (!formulaireRecherche || !champRecherche) return;
    
    // Effacer la recherche avec Escape
    champRecherche.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.focus();
        }
    });
    
    // Debug
    champRecherche.addEventListener('input', function() {
        const longueur = this.value.length;
        if (longueur > 0 && longueur < 3) {
            console.log(`Recherche : ${longueur}/3 caractères minimum`);
        }
    });
}

/**
 * Initialise les filtres avec animations
 */
function initialiserFiltres() {
    const pucesFiltres = document.querySelectorAll('.filter-chip');
    
    if (!pucesFiltres.length) return;
    
    // Ajouter des icônes visuelles aux filtres actifs
    pucesFiltres.forEach(puce => {
        if (puce.classList.contains('active')) {
            ajouterIconeActive(puce);
        }
        
        // Animation au survol
        puce.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.paddingLeft = '1.2rem';
            }
        });
        
        puce.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.paddingLeft = '1rem';
            }
        });
    });
}

/**
 * Ajoute une icône de coche aux filtres actifs
 */
function ajouterIconeActive(element) {
    if (element.querySelector('.icone-check')) return;
    
    const icone = document.createElement('span');
    icone.className = 'icone-check';
    icone.textContent = '✓ ';
    icone.style.marginRight = '0.3rem';
    icone.style.fontWeight = 'bold';
    
    element.prepend(icone);
}

/**
 * Réinitialise tous les filtres
 */
function reinitialiserFiltres() {
    const urlBase = window.location.pathname;
    window.location.href = urlBase;
}

/**
 * Affiche le nombre de résultats trouvés
 */
function afficherNombreResultats(nombre) {
    const conteneur = document.querySelector('.search-section');
    
    if (!conteneur) return;
    
    // Supprimer l'ancien compteur s'il existe
    const ancienCompteur = document.querySelector('.compteur-resultats');
    if (ancienCompteur) {
        ancienCompteur.remove();
    }
    
    // Créer le nouveau compteur
    const compteur = document.createElement('div');
    compteur.className = 'compteur-resultats';
    compteur.style.cssText = `
        margin-top: 0.5rem;
        color: #666;
        font-size: 0.9rem;
    `;
    compteur.textContent = `${nombre} événement${nombre > 1 ? 's' : ''} trouvé${nombre > 1 ? 's' : ''}`;
    
    conteneur.appendChild(compteur);
}

/**
 * Vérifie si des filtres sont actifs
 */
function aDesFiltresActifs() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.has('search') || 
           urlParams.has('category') || 
           urlParams.has('time') || 
           urlParams.has('date');
}

/**
 * Ajoute un bouton pour réinitialiser les filtres
 */
function ajouterBoutonReinitialisation() {
    if (!aDesFiltresActifs()) return;
    
    const sidebar = document.querySelector('.filters-sidebar');
    if (!sidebar) return;
    
    // Vérifier si le bouton existe déjà
    if (document.querySelector('.btn-reinitialiser-filtres')) return;
    
    const bouton = document.createElement('button');
    bouton.className = 'btn-reinitialiser-filtres';
    bouton.textContent = '✕ Réinitialiser les filtres';
    bouton.style.cssText = `
        width: 100%;
        padding: 0.75rem;
        margin-top: 1rem;
        background: #f44336;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.2s ease;
    `;
    
    bouton.addEventListener('mouseenter', function() {
        this.style.background = '#d32f2f';
    });
    
    bouton.addEventListener('mouseleave', function() {
        this.style.background = '#f44336';
    });
    
    bouton.addEventListener('click', reinitialiserFiltres);
    
    sidebar.appendChild(bouton);
}

// Initialiser le bouton de réinitialisation
document.addEventListener('DOMContentLoaded', ajouterBoutonReinitialisation);

// Export des fonctions pour utilisation externe
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initialiserRecherche,
        initialiserFiltres,
        reinitialiserFiltres,
        afficherNombreResultats,
        aDesFiltresActifs
    };
}
