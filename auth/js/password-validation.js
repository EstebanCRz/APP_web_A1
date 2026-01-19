document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitButton = document.querySelector('button[type="submit"]');
    
    if (!passwordInput) return;

    /* --- 1. CRÉATION DE L'INDICATEUR --- */
    const strengthIndicator = document.createElement('div');
    strengthIndicator.className = 'password-strength-container';
    strengthIndicator.innerHTML = `
        <div class="strength-bar"><div class="strength-bar-fill"></div></div>
        <span class="strength-text"></span>
    `;
    
    // Insertion SOUS le bloc (après le wrapper de l'œil s'il existe)
    const wrapper = passwordInput.closest('.password-input-wrapper') || passwordInput;
    wrapper.after(strengthIndicator);

    const barFill = strengthIndicator.querySelector('.strength-bar-fill');
    const msgText = strengthIndicator.querySelector('.strength-text');

    /* --- 2. LOGIQUE DE CALCUL --- */
    function checkStrength(pwd) {
        let score = 0;
        if (pwd.length >= 8) score += 25;
        if (/[A-Z]/.test(pwd)) score += 25;
        if (/[0-9]/.test(pwd)) score += 25;
        if (/[^A-Za-z0-9]/.test(pwd)) score += 25;

        if (score >= 75) return { score, text: 'Fort ', color: '#66bb6a' };
        if (score >= 50) return { score, text: 'Moyen ', color: '#ffa726' };
        return { score, text: 'Faible ', color: '#ef5350' };
    }

    /* --- 3. MISES À JOUR --- */
    function updateUI() {
        const val = passwordInput.value;
        if (val.length === 0) {
            strengthIndicator.style.display = 'none';
        } else {
            const res = checkStrength(val);
            strengthIndicator.style.display = 'block';
            msgText.textContent = res.text;
            msgText.style.color = res.color;
            barFill.style.width = res.score + '%';
            barFill.style.backgroundColor = res.color;
        }
        validateMatch();
    }

    function validateMatch() {
        if (!confirmPasswordInput) return;
        const match = passwordInput.value === confirmPasswordInput.value && passwordInput.value !== "";
        confirmPasswordInput.style.borderColor = match ? '#66bb6a' : (confirmPasswordInput.value ? '#ef5350' : '#e1e8ed');
        
        if (submitButton) {
            const isCguChecked = document.querySelector('input[type="checkbox"]')?.checked ?? true;
            submitButton.disabled = !(match && passwordInput.value.length >= 6 && isCguChecked);
            submitButton.style.opacity = submitButton.disabled ? "0.6" : "1";
        }
    }

    passwordInput.addEventListener('input', updateUI);
    if (confirmPasswordInput) confirmPasswordInput.addEventListener('input', validateMatch);
    document.querySelector('input[type="checkbox"]')?.addEventListener('change', validateMatch);
});