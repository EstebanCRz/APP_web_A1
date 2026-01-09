document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.event-form');
    if (!form) return;
    const title = form.querySelector('#title');
    const description = form.querySelector('#description');
    const category = form.querySelector('#category');
    const location = form.querySelector('#location');
    const city = form.querySelector('#city');
    const date = form.querySelector('#date');
    const time = form.querySelector('#time');
    const capacity = form.querySelector('#capacity');
    const image = form.querySelector('#image');
    const descMax = 500;
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const minDate = `${yyyy}-${mm}-${dd}`;
    if (date) date.setAttribute('min', minDate);
    let descCounter = document.createElement('div');
    descCounter.className = 'desc-counter';
    description.parentNode.appendChild(descCounter);
    function updateDescCounter() {
        const len = description.value.length;
        descCounter.textContent = `${len}/${descMax} caractères`;
        if (len > descMax) {
            descCounter.style.color = 'var(--accent-energetic, #f26619)';
        } else {
            descCounter.style.color = '#888';
        }
    }
    description.addEventListener('input', updateDescCounter);
    updateDescCounter();
    form.addEventListener('submit', function (e) {
        let valid = true;
        form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
        [title, description, category, location, city, date, time, capacity].forEach(field => {
            if (!field.value || (field === category && field.value === '')) {
                field.classList.add('input-error');
                valid = false;
            }
        });
        if (description.value.length > descMax) {
            description.classList.add('input-error');
            valid = false;
        }
        if (date.value < minDate) {
            date.classList.add('input-error');
            valid = false;
        }
        if (parseInt(capacity.value) < 1) {
            capacity.classList.add('input-error');
            valid = false;
        }
        if (image.value && !/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|svg)$/i.test(image.value)) {
            image.classList.add('input-error');
            valid = false;
        }
        if (!valid) {
            e.preventDefault();
            showFormError('Merci de corriger les champs en rouge.');
        }
    });
    function showFormError(msg) {
        let alert = form.querySelector('.alert-error');
        if (!alert) {
            alert = document.createElement('div');
            alert.className = 'alert alert-error';
            form.insertBefore(alert, form.firstChild);
        }
        alert.textContent = msg;
    }
    const alertSuccess = document.querySelector('.alert-success');
    if (alertSuccess) {
        let countdown = 2;
        let timer = document.createElement('span');
        timer.style.marginLeft = '1em';
        alertSuccess.appendChild(timer);
        function tick() {
            timer.textContent = `Redirection dans ${countdown}...`;
            if (countdown > 0) {
                countdown--;
                setTimeout(tick, 1000);
            }
        }
        tick();
    }
});
