(function () {
    const THEME_KEY = 'zhiguli-theme';
    const body = document.body;
    const themeBtn = document.getElementById('themeBtn');

    function applyTheme(isDark) {
        body.classList.toggle('theme--dark', isDark);
    }

    const savedTheme = localStorage.getItem(THEME_KEY);
    applyTheme(savedTheme === 'dark');

    if (!themeBtn) {
        return;
    }

    themeBtn.addEventListener('click', () => {
        const isDark = !body.classList.contains('theme--dark');
        applyTheme(isDark);
        localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    });
})();
