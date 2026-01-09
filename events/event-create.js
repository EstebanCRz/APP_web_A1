// Event Create Page JS
document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('.event-form');
	if (!form) return;

	// Elements
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

	// Set min date/time to today
	const today = new Date();
	const yyyy = today.getFullYear();
	const mm = String(today.getMonth() + 1).padStart(2, '0');
	const dd = String(today.getDate()).padStart(2, '0');
	const minDate = `${yyyy}-${mm}-${dd}`;
	if (date) date.setAttribute('min', minDate);

	// Description char count
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

	// Form validation
	form.addEventListener('submit', function (e) {
		let valid = true;
		// Remove previous error highlights
		form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

		// Required fields
		[title, description, category, location, city, date, time, capacity].forEach(field => {
			if (!field.value || (field === category && field.value === '')) {
				field.classList.add('input-error');
				valid = false;
			}
		});
		// Description length
		if (description.value.length > descMax) {
			description.classList.add('input-error');
			valid = false;
		}
		// Date/time not in past
		if (date.value < minDate) {
			date.classList.add('input-error');
			valid = false;
		}
		// Capacity positive
		if (parseInt(capacity.value) < 1) {
			capacity.classList.add('input-error');
			valid = false;
		}
		// Image URL (if filled)
		if (image.value && !/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|svg)$/i.test(image.value)) {
			image.classList.add('input-error');
			valid = false;
		}
		// If not valid, prevent submit
		if (!valid) {
			e.preventDefault();
			showFormError('Merci de corriger les champs en rouge.');
		} else {
			// If image is empty, set default
			if (image && !image.value) {
				image.value = 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=800';
			}
		}
	});

	// Show error message
	function showFormError(msg) {
		let alert = form.querySelector('.alert-error');
		if (!alert) {
			alert = document.createElement('div');
			alert.className = 'alert alert-error';
			form.insertBefore(alert, form.firstChild);
		}
		alert.textContent = msg;
	}

	// Button animation
	form.querySelectorAll('button, .btn').forEach(btn => {
		btn.addEventListener('mousedown', () => btn.classList.add('btn-active'));
		btn.addEventListener('mouseup', () => btn.classList.remove('btn-active'));
		btn.addEventListener('mouseleave', () => btn.classList.remove('btn-active'));
	});

	// Focus highlight
	form.querySelectorAll('input, textarea, select').forEach(field => {
		field.addEventListener('focus', () => field.classList.add('input-focus'));
		field.addEventListener('blur', () => field.classList.remove('input-focus'));
	});

	// Success message: if .alert-success exists, show countdown
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
