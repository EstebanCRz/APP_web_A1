/**
 * Filtrage des caractères spéciaux dans les champs nom et prénom
 * Accepte uniquement les lettres, accents, tirets, apostrophes et espaces
 */

document.addEventListener('DOMContentLoaded', function() {
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    
    /**
     * Filtre les caractères spéciaux
     * @param {string} input - Texte à filtrer
     * @returns {string} Texte filtré
     */
    function filterSpecialChars(input) {
        // Accepte : lettres (a-z, A-Z), accents, tirets, apostrophes, espaces
        return input.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, '');
    }
    
    /**
     * Limite la longueur d'une chaîne
     * @param {string} input - Texte
     * @param {number} maxLength - Longueur maximale
     * @returns {string} Texte limité
     */
    function limitLength(input, maxLength) {
        return input.substring(0, maxLength);
    }
    
    /**
     * Attache les écouteurs à un champ
     * @param {HTMLElement} field - Champ input
     * @param {number} maxLength - Longueur maximale
     */
    function attachFilterListener(field, maxLength = 50) {
        if (!field) return;
        
        field.addEventListener('input', function(e) {
            // Filtrer les caractères spéciaux
            let filtered = filterSpecialChars(this.value);
            
            // Limiter la longueur
            filtered = limitLength(filtered, maxLength);
            
            // Mettre à jour la valeur du champ
            if (this.value !== filtered) {
                this.value = filtered;
            }
        });
        
        // Valider au blur
        field.addEventListener('blur', function(e) {
            this.value = filterSpecialChars(this.value).trim();
        });
    }
    
    // Appliquer le filtrage aux champs nom et prénom
    attachFilterListener(nomInput);
    attachFilterListener(prenomInput);
    
    // Afficher un message d'aide à l'utilisateur
    function addHelpText(field, message) {
        if (!field) return;
        
        const helpText = document.createElement('small');
        helpText.style.display = 'block';
        helpText.style.color = '#7f8c8d';
        helpText.style.marginTop = '4px';
        helpText.style.fontSize = '12px';
        helpText.textContent = message;
        
        field.parentNode.appendChild(helpText);
    }
    
    addHelpText(nomInput, 'Pas de caractères spéciaux');
    addHelpText(prenomInput, 'Pas de caractères spéciaux');
});
