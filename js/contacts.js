const form = document.getElementById('contactForm');
const errorEl = document.getElementById('contactError');
const successEl = document.getElementById('contactSuccess');

if (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const name = document.getElementById('contactName').value.trim();
        const email = document.getElementById('contactEmail').value.trim();
        const topic = document.getElementById('contactTopic').value;
        const message = document.getElementById('contactMessage').value.trim();

        errorEl.hidden = true;
        successEl.hidden = true;
        errorEl.textContent = '';

        if (name.length < 2) {
            errorEl.textContent = 'Введите имя — минимум 2 символа.';
            errorEl.hidden = false;
            return;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errorEl.textContent = 'Введите корректный e-mail.';
            errorEl.hidden = false;
            return;
        }

        if (!topic) {
            errorEl.textContent = 'Выберите тему сообщения.';
            errorEl.hidden = false;
            return;
        }

        if (message.length < 10) {
            errorEl.textContent = 'Сообщение должно быть не короче 10 символов.';
            errorEl.hidden = false;
            return;
        }

        const messages = JSON.parse(localStorage.getItem('contactMessages') || '[]');
        messages.push({
            name,
            email,
            topic,
            message,
            createdAt: new Date().toISOString(),
        });
        localStorage.setItem('contactMessages', JSON.stringify(messages));

        form.reset();
        successEl.hidden = false;
    });
}
