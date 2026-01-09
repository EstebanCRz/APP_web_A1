// Script pour l'accordéon FAQ
// FAQ Accordion Script with localStorage persistence

document.addEventListener('DOMContentLoaded', function () {

    const faqQuestions = document.querySelectorAll('.faq-question');
    const STORAGE_KEY = 'amigo_last_faq';

    function closeAllExcept(currentQuestion) {
        faqQuestions.forEach(q => {
            if (q !== currentQuestion) {
                q.setAttribute('aria-expanded', 'false');
                q.nextElementSibling.classList.remove('active');
            }
        });
    }

    function toggleQuestion(question) {
        const answer = question.nextElementSibling;
        const isExpanded = question.getAttribute('aria-expanded') === 'true';

        closeAllExcept(question);

        question.setAttribute('aria-expanded', String(!isExpanded));
        answer.classList.toggle('active');

        // Sauvegarde dans le localStorage si la question est ouverte
        if (!isExpanded && question.id) {
            localStorage.setItem(STORAGE_KEY, question.id);
        }
    }

    faqQuestions.forEach(question => {
        question.addEventListener('click', function () {
            toggleQuestion(this);
        });
    });

    // Restauration de l’état au chargement de la page
    const lastFaqId = localStorage.getItem(STORAGE_KEY);
    if (lastFaqId) {
        const lastQuestion = document.getElementById(lastFaqId);
        if (lastQuestion) {
            toggleQuestion(lastQuestion);
        }
    }
});

