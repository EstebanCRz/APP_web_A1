// Validation formulaire d'avis avec confirmation avant quitter
// Attend que la page soit complètement chargée
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.review-form').forEach(function(form) {
        const comment = form.querySelector('#comment');
        const btn = form.querySelector('.btn-primary');
        // Créer un élément pour afficher le compteur de caractères
        let counter = form.querySelector('.review-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'review-counter';
            comment.parentNode.insertBefore(counter, comment.nextSibling);
        }
        btn.disabled = true;
        // Message d'erreur inline
        let errorMsg = form.querySelector('.review-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'review-error';
            comment.parentNode.insertBefore(errorMsg, counter.nextSibling);
        }
        let modified = false;
        function enforceCharLimit() {
            if (comment.value.length > 1000) {
                comment.value = comment.value.slice(0, 1000);
            }
            counter.textContent = comment.value.length + '/1000';
            validate();
        }
        function validate() {
            let val = comment.value;
            if (val.length > 1000) {
                val = val.slice(0, 1000);
                comment.value = val;
            }
            const len = val.trim().length;
            const ratingChecked = form.querySelector('input[name="rating"]:checked');
            const forbidden = /[^\p{L}0-9 .,;:!?()\[\]{}'"\-\n\r]/u.test(val);
            const ok = len >= 10 && len <= 1000 && ratingChecked && !forbidden && parseInt(ratingChecked?.value || 0) >= 1;
            counter.textContent = len + '/1000';
            counter.style.display = 'block';
            counter.style.marginTop = '0.5rem';
            counter.style.fontSize = '0.95rem';
            counter.style.fontWeight = '600';
            counter.style.color = len < 10 ? '#a34747' : len > 1000 ? '#c21313' : len > 900 ? '#c32c1b' : '#3ab5c8';
            btn.disabled = !ok;
            btn.style.opacity = ok ? '1' : '0.5';
            btn.style.pointerEvents = ok ? 'auto' : 'none';
            comment.style.borderColor = len === 0 ? '#ddd' : len < 10 || len > 1000 ? '#c21313' : len > 900 ? '#c32c1b' : '#55D5E0';
            if (forbidden) {
                errorMsg.textContent = 'Caractères spéciaux interdits';
                errorMsg.style.display = 'block';
            } else if (len > 1000) {
                errorMsg.textContent = '1000 caractères maximum.';
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
        comment.addEventListener('input', enforceCharLimit);
        comment.addEventListener('blur', enforceCharLimit);
        form.querySelectorAll('input[name="rating"]').forEach(i => i.addEventListener('change', function() {
            modified = true;
            validate();
        }));
        validate();
        window.addEventListener('beforeunload', function(e) {
            if (modified && comment.value.trim().length > 0) {
                e.preventDefault();
            }
        });
        form.addEventListener('submit', function(e) {
            let val = comment.value;
            if (val.length > 1000) {
                val = val.slice(0, 1000);
                comment.value = val;
            }
            const len = val.trim().length;
            const ratingChecked = form.querySelector('input[name="rating"]:checked');
            const forbidden = /[^\p{L}0-9 .,;:!?()\[\]{}'"\-\n\r]/u.test(val);
            if (!ratingChecked || len < 10 || len > 1000 || forbidden) {
                e.preventDefault();
            } else {
                errorMsg.textContent = '';
                errorMsg.style.display = 'none';
                modified = false;
                setTimeout(function() {
                    comment.value = '';
                    form.querySelectorAll('input[name="rating"]').forEach(i => i.checked = false);
                    validate();
                }, 100);
            }
        });
    });
});
