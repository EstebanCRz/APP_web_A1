/**
 * Ajoute un bouton œil personnalisé pour chaque champ password
 * Permet de basculer entre afficher/masquer le mot de passe
 */

document.addEventListener('DOMContentLoaded', function() {
    // SVG pour l'œil ouvert (affichage)
    const eyeOpenSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
    </svg>`;
    
    // SVG pour l'œil fermé (masquage)
    const eyeClosedSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
        <line x1="1" y1="1" x2="23" y2="23"></line>
    </svg>`;
    
    // Trouver tous les champs password
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    
    passwordInputs.forEach((input) => {
        // Ignorer les champs de confirmation
        if (input.name === 'confirm_password') {
            return;
        }
        
        // Créer le wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'password-input-wrapper';
        
        // Créer le bouton œil
        const eyeBtn = document.createElement('button');
        eyeBtn.type = 'button';
        eyeBtn.className = 'password-eye-btn';
        eyeBtn.innerHTML = eyeOpenSVG;
        eyeBtn.setAttribute('aria-label', 'Afficher/masquer le mot de passe');
        
        // Insérer le wrapper avant le champ
        input.parentNode.insertBefore(wrapper, input);
        
        // Ajouter le champ et le bouton au wrapper
        wrapper.appendChild(input);
        wrapper.appendChild(eyeBtn);
        
        // Gestion du clic sur le bouton œil
        eyeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (input.type === 'password') {
                // Afficher le mot de passe
                input.type = 'text';
                eyeBtn.innerHTML = eyeOpenSVG;
                eyeBtn.setAttribute('title', 'Masquer');
            } else {
                // Masquer le mot de passe
                input.type = 'password';
                eyeBtn.innerHTML = eyeClosedSVG;
                eyeBtn.setAttribute('title', 'Afficher');
            }
        });
    });
});
