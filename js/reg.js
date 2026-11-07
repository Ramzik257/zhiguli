document.getElementById('userForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const errorEl = document.getElementById('formError');

    errorEl.hidden = true;
    errorEl.textContent = '';

    if (name.length < 2) {
        errorEl.textContent = 'Ник должен быть не короче 2 символов.';
        errorEl.hidden = false;
        return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errorEl.textContent = 'Введите корректный e-mail.';
        errorEl.hidden = false;
        return;
    }

    if (password.length < 8) {
        errorEl.textContent = 'Пароль должен быть не короче 8 символов.';
        errorEl.hidden = false;
        return;
    }

    const phoneDigits = phone.replace(/\D/g, '');
    if (phoneDigits.length < 10) {
        errorEl.textContent = 'Введите корректный номер телефона.';
        errorEl.hidden = false;
        return;
    }

    const userData = {
        name,
        email,
        password,
        phone,
    };

    localStorage.setItem('userData1', JSON.stringify(userData));
    window.location.href = './main.html';
});
