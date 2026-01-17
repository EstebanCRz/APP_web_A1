// Validation formulaire d'avis avec confirmation avant quitter
// Attend que la page soit complètement chargée
document.addEventListener('DOMContentLoaded', function() {
    // Recherche le formulaire d'avis dans la page
    const form = document.querySelector('.review-form');
    const comment = form.querySelector('#comment');
    const btn = form.querySelector('.btn-primary');
    // Créer un élément pour afficher le compteur de caractères
    const counter = document.createElement('small');
    counter.className = 'review-counter';
    // Insérer le compteur juste après la zone de texte
    comment.parentNode.insertBefore(counter, comment.nextSibling);
    // Désactive le bouton par défaut
    btn.disabled = true;
    // Message d'erreur inline
    let errorMsg = form.querySelector('.review-error');
    if (!errorMsg) {
        errorMsg = document.createElement('div');
        errorMsg.className = 'review-error';
        errorMsg.style.display = 'none';
        errorMsg.style.color = '#c21313';
        errorMsg.style.marginTop = '0.5rem';
        comment.parentNode.insertBefore(errorMsg, counter.nextSibling);
    }
    let modified = false;
    // Fonction pour échapper les caractères spéciaux
    function escapeHtml(text) {
        return text.replace(/[&<>"]/g, function(c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];
        });
    }
    // Fonction de validation du formulaire
    function validate() {
        const len = comment.value.trim().length;
        const ratingChecked = form.querySelector('input[name="rating"]:checked');
        // Interdit tout caractère non lettre, chiffre, espace, ponctuation simple
        const forbidden = /[^a-zA-Z0-9 .,;:!?()\[\]{}'"\-\n\r]/.test(comment.value);
        const ok = len >= 10 && ratingChecked && !forbidden;
        counter.textContent = len + '/1000';
        counter.style.display = 'block';
        counter.style.marginTop = '0.5rem';
        counter.style.fontSize = '0.95rem';
        counter.style.fontWeight = '600';
        counter.style.color = len < 10 ? '#a34747' : len > 900 ? '#c32c1b' : '#3ab5c8';
        btn.disabled = !ok;
        btn.style.opacity = ok ? '1' : '0.5';
        btn.style.pointerEvents = ok ? 'auto' : 'none';
        comment.style.borderColor = len === 0 ? '#ddd' : len < 10 || len >= 900 ? '#c21313' : '#55D5E0';
        if (forbidden) {
            errorMsg.textContent = 'Caractères spéciaux interdits';
            errorMsg.style.display = 'block';
        } else if (len > 0 && len < 10) {
            errorMsg.textContent = '10 caractères minimum requis et une note comprise entre 1 et 5';
            errorMsg.style.display = 'block';
        } else {
            errorMsg.textContent = '';
            errorMsg.style.display = 'none';
        }
    }
    
    comment.addEventListener('input', function() {
        modified = true;
        validate();
    });
    form.querySelectorAll('input[name="rating"]').forEach(i => i.addEventListener('change', function() {
        modified = true;
        validate();
    }));
    // Validation initiale
    validate();
    // Message d'avertissement avant de quitter si modifié
    window.addEventListener('beforeunload', function(e) {
        if (modified && comment.value.trim().length > 0) {
            e.preventDefault();
        }
    });
    // Validation finale avant envoi
    form.addEventListener('submit', function(e) {
        const len = comment.value.trim().length;
        const ratingChecked = form.querySelector('input[name="rating"]:checked');
        const forbidden = /[^a-zA-Z0-9 .,;:!?()\[\]{}'"\-\n\r]/.test(comment.value);
        if (!ratingChecked || len < 10 || forbidden) {
            e.preventDefault();
        } else {
            errorMsg.textContent = '';
            errorMsg.style.display = 'none';
            modified = false;
        }
    });
});