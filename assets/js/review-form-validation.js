// Validation formulaire d'avis avec confirmation avant quitter

// Attend que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {

    // Sélectionne tous les formulaires d'avis
    document.querySelectorAll('.review-form').forEach(function(form) {

        // Champ de commentaire
        const comment = form.querySelector('#comment');

        // Bouton principal (envoyer)
        const btn = form.querySelector('.btn-primary');

        // Compteur de caractères (créé si absent)
        let counter = form.querySelector('.review-counter');
        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'review-counter';
            comment.parentNode.insertBefore(counter, comment.nextSibling);
        }

        // Désactive le bouton au début
        btn.disabled = true;

        // Message d'erreur inline (créé si absent)
        let errorMsg = form.querySelector('.review-error');
        if (!errorMsg) {
            errorMsg = document.createElement('div');
            errorMsg.className = 'review-error';
            comment.parentNode.insertBefore(errorMsg, counter.nextSibling);
        }

        // Flag pour savoir si l'utilisateur a modifié
        let modified = false;

        // Limite physiquement à 1000 caractères + maj compteur
        function enforceCharLimit() {
            if (comment.value.length > 1000) {
                comment.value = comment.value.slice(0, 1000); // coupe
            }
            counter.textContent = comment.value.length + '/1000';
            validate(); // revalide après coupe
        }

        // Fonction de validation globale
        function validate() {
            let val = comment.value;

            // Sécurité supplémentaire sur les 1000 chars
            if (val.length > 1000) {
                val = val.slice(0, 1000);
                comment.value = val;
            }

            // Longueur utile (sans espaces aux extrémités)
            const len = val.trim().length;

            // Note sélectionnée
            const ratingChecked = form.querySelector('input[name="rating"]:checked');

            // Vérification caractères interdits (regex Unicode)
            const forbidden = /[^\p{L}0-9 .,;:!?()\[\]{}'"\-\n\r]/u.test(val);

            // Conditions d'envoi
            const ok = len >= 10 &&
                       len <= 1000 &&
                       ratingChecked &&
                       !forbidden &&
                       parseInt(ratingChecked?.value || 0) >= 1;

            // Mise à jour compteur
            counter.textContent = len + '/1000';
            counter.style.display = 'block';
            counter.style.marginTop = '0.5rem';
            counter.style.fontSize = '0.95rem';
            counter.style.fontWeight = '600';

            // Couleur selon progression
            counter.style.color =
                len < 10   ? '#a34747' :
                len > 1000 ? '#c21313' :
                len > 900  ? '#c32c1b' :
                             '#3ab5c8';

            // Activation/désactivation bouton
            btn.disabled = !ok;
            btn.style.opacity = ok ? '1' : '0.5';
            btn.style.pointerEvents = ok ? 'auto' : 'none';

            // Bordure colorée du champ
            comment.style.borderColor =
                len === 0          ? '#ddd' :
                len < 10 || len > 1000 ? '#c21313' :
                len > 900          ? '#c32c1b' :
                                      '#55D5E0';

            // Affichage message d'erreur APRÈS le textarea (champ commentaire)
            if (forbidden || len > 1000 || (len > 0 && len < 10)) {
                // Message d'erreur
                if (forbidden) {
                    errorMsg.textContent = 'Caractères spéciaux interdits';
                } else if (len > 1000) {
                    errorMsg.textContent = '1000 caractères maximum.';
                } else if (len > 0 && len < 10) {
                    errorMsg.textContent = '10 caractères minimum requis et une note comprise entre 1 et 5';
                }
                errorMsg.style.display = 'block';
                // Place l'erreur juste après le textarea
                if (comment.nextSibling !== errorMsg) {
                    comment.parentNode.insertBefore(errorMsg, comment.nextSibling);
                }
            } else {
                errorMsg.textContent = '';
                errorMsg.style.display = 'none';
            }
        }

        // Quand on saisit du texte → on marque comme modifié + on valide
        comment.addEventListener('input', function() {
            modified = true;
            validate();
        });

        // Limite + validation sur input
        comment.addEventListener('input', enforceCharLimit);

        // Limite + validation sur perte du focus
        comment.addEventListener('blur', enforceCharLimit);

        // Sur changement de note → modifié + validation
        form.querySelectorAll('input[name="rating"]').forEach(i =>
            i.addEventListener('change', function() {
                modified = true;
                validate();
            })
        );

        // Validation initiale
        validate();

        // Avertissement quand on quitte la page avec du texte non soumis
        window.addEventListener('beforeunload', function(e) {
            if (modified && comment.value.trim().length > 0) {
                e.preventDefault();
            }
        });

        // Validation finale à la soumission
        form.addEventListener('submit', function(e) {
            let val = comment.value;

            // Coupe à 1000 au cas où
            if (val.length > 1000) {
                val = val.slice(0, 1000);
                comment.value = val;
            }

            const len = val.trim().length;
            const ratingChecked = form.querySelector('input[name="rating"]:checked');
            const forbidden = /[^\p{L}0-9 .,;:!?()\[\]{}'"\-\n\r]/u.test(val);

            // Si invalide → bloque submit
            if (!ratingChecked || len < 10 || len > 1000 || forbidden) {
                e.preventDefault();
            } else {
                // Sinon nettoyage des erreurs + reset
                errorMsg.textContent = '';
                errorMsg.style.display = 'none';
                modified = false;

                // Vide le champ après coup
                setTimeout(function() {
                    comment.value = '';
                    // Réinitialise la sélection des étoiles
                    form.querySelectorAll('input[name="rating"]').forEach(function(star) {
                        star.checked = false;
                    });
                    validate();
                    // Force le textarea à rester vide même si le DOM est mis à jour rapidement
                    comment.value = '';
                }, 100);
            }
        });
    });
});
