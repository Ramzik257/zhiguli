const saved = localStorage.getItem('userData1');
const userInfo = document.getElementById('userInfo');
const editLink = document.getElementById('profileEdit');

if (saved) {
    const userData = JSON.parse(saved);

    document.getElementById('name').textContent = userData.name || '—';
    document.getElementById('email').textContent = userData.email || '—';
    document.getElementById('phone').textContent = userData.phone || userData.number || '—';
} else {
    userInfo.innerHTML = '<p class="profile__empty">Данных нет. Сначала пройдите регистрацию.</p>';
    if (editLink) {
        editLink.textContent = 'Зарегистрироваться';
    }
}
