document.addEventListener('DOMContentLoaded', function() {
    const searchBar = document.getElementById('searchBar');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const container = document.getElementById('activitiesContainer');

    let currentSearch = '';
    let currentFilter = 'all';

    // Fonction de recherche Backend
    async function fetchResults() {
        if (container) container.style.opacity = '0.5';
        try {
            const response = await fetch(`ajax_search.php?search=${encodeURIComponent(currentSearch)}&category=${encodeURIComponent(currentFilter)}`);
            const activities = await response.json();
            renderActivities(activities);
        } catch (error) {
            console.error("Erreur de recherche:", error);
        } finally {
            if (container) container.style.opacity = '1';
        }
    }

    // Affichage des cartes
    function renderActivities(activities) {
        if (!container) return;
        if (activities.length === 0) {
            container.innerHTML = '<p class="no-result">Aucune activité trouvée.</p>';
            return;
        }
        container.innerHTML = activities.map(act => `
            <a href="events/event-details.php?id=${act.id}" class="activity-item">
                <div class="card-img" style="background-image: url('${act.img}');">
                    <span class="badge" style="background: ${act.color};">${act.type}</span>
                </div>
                <div class="card-body">
                    <h4>${act.title}</h4>
                    <div class="card-meta">
                        <span>📍 ${act.loc}</span>
                        <span>👤 ${act.user}</span>
                    </div>
                </div>
            </a>
        `).join('');
    }

    // Barre de recherche
    if (searchBar) {
        let timeout;
        searchBar.addEventListener('input', (e) => {
            clearTimeout(timeout);
            currentSearch = e.target.value;
            timeout = setTimeout(fetchResults, 300);
        });
    }

    // --- LOGIQUE UNIQUE POUR LES FILTRES ---
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // 1. On nettoie ABSOLUMENT TOUS les boutons de la classe active
            filterBtns.forEach(b => {
                b.classList.remove('active');
            });
            
            // 2. On l'ajoute seulement sur celui qu'on vient de cliquer
            this.classList.add('active');
            
            // 3. On lance la recherche
            currentFilter = this.getAttribute('data-filter') || 'all';
            fetchResults();
        });
    });
});