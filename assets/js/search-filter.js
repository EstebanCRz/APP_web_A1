document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Script de recherche actif');
    
    const searchBar = document.getElementById('searchBar');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const activityItems = document.querySelectorAll('.activity-item');

    let currentSearchTerm = '';
    let currentFilter = 'all';

    function filterActivities() {
        activityItems.forEach(item => {
            // Extraction des textes pour la recherche
            const title = item.querySelector('h4').textContent.toLowerCase();
            const metaInfo = item.querySelector('.card-meta').textContent.toLowerCase();
            const activityType = item.getAttribute('data-type');

            // Logique de correspondance
            const matchesSearch = title.includes(currentSearchTerm) || metaInfo.includes(currentSearchTerm);
            const matchesFilter = currentFilter === 'all' || activityType === currentFilter;

            // Affichage conditionnel
            if (matchesSearch && matchesFilter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Gestion de la saisie clavier
    if (searchBar) {
        searchBar.addEventListener('input', function(e) {
            currentSearchTerm = e.target.value.toLowerCase().trim();
            filterActivities();
        });
    }

    // Gestion des boutons de catégories
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentFilter = this.getAttribute('data-filter');
            filterActivities();
        });
    });
});