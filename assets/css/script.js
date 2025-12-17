document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.activity-item');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            // 1. Gérer l'état actif des boutons
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // 2. Filtrer les cartes
            const filter = btn.getAttribute('data-filter');

            cards.forEach(card => {
                const type = card.getAttribute('data-type');
                if (filter === 'all' || type === filter) {
                    card.style.display = 'block';
                    setTimeout(() => card.style.opacity = "1", 10);
                } else {
                    card.style.opacity = "0";
                    card.style.display = 'none';
                }
            });
        });
    });
});