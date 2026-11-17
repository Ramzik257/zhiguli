(function () {
    const THEME_KEY = 'zhiguli-theme';
    const THEMES = [
        { id: 'light', label: 'День', swatch: '#c2a045' },
        { id: 'dark', label: 'Ночь', swatch: '#d4af37' },
        { id: 'garage', label: 'Гараж', swatch: '#c4a35a' },
        { id: 'racing', label: 'Трасса', swatch: '#d64545' },
        { id: 'frost', label: 'Иней', swatch: '#7eb6d4' },
    ];

    const body = document.body;
    const themeBtn = document.getElementById('themeBtn');
    const validIds = THEMES.map((theme) => theme.id);

    function normalizeTheme(value) {
        if (value === 'dark' || value === 'light') {
            return value;
        }
        if (validIds.includes(value)) {
            return value;
        }
        return 'light';
    }

    function applyTheme(themeId) {
        const theme = normalizeTheme(themeId);
        body.setAttribute('data-theme', theme);
        THEMES.forEach((item) => {
            body.classList.toggle(`theme--${item.id}`, item.id === theme);
        });

        if (themeBtn) {
            themeBtn.setAttribute('data-current-theme', theme);
            themeBtn.setAttribute('aria-label', `Тема: ${THEMES.find((item) => item.id === theme).label}`);
        }

        document.querySelectorAll('[data-theme-option]').forEach((option) => {
            const active = option.getAttribute('data-theme-option') === theme;
            option.classList.toggle('theme-switch__option--active', active);
            option.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        return theme;
    }

    const initialTheme = normalizeTheme(localStorage.getItem(THEME_KEY));
    applyTheme(initialTheme);

    if (!themeBtn) {
        return;
    }

    let switchRoot = themeBtn.closest('.theme-switch');
    if (!switchRoot) {
        switchRoot = document.createElement('div');
        switchRoot.className = 'theme-switch';
        themeBtn.parentNode.insertBefore(switchRoot, themeBtn);
        switchRoot.appendChild(themeBtn);
    }

    let menu = switchRoot.querySelector('.theme-switch__menu');
    if (!menu) {
        menu = document.createElement('ul');
        menu.className = 'theme-switch__menu';
        menu.id = 'themeMenu';
        menu.setAttribute('role', 'listbox');
        menu.hidden = true;

        THEMES.forEach((theme) => {
            const item = document.createElement('li');
            item.setAttribute('role', 'presentation');

            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'theme-switch__option';
            option.setAttribute('role', 'option');
            option.setAttribute('data-theme-option', theme.id);
            option.innerHTML = `<span class="theme-switch__swatch" style="--swatch:${theme.swatch}"></span>${theme.label}`;
            option.addEventListener('click', () => {
                const next = applyTheme(theme.id);
                localStorage.setItem(THEME_KEY, next);
                closeMenu();
            });

            item.appendChild(option);
            menu.appendChild(item);
        });

        switchRoot.appendChild(menu);
    }

    themeBtn.setAttribute('aria-haspopup', 'listbox');
    themeBtn.setAttribute('aria-controls', 'themeMenu');
    themeBtn.setAttribute('aria-expanded', 'false');

    function openMenu() {
        menu.hidden = false;
        themeBtn.setAttribute('aria-expanded', 'true');
        switchRoot.classList.add('theme-switch--open');
    }

    function closeMenu() {
        menu.hidden = true;
        themeBtn.setAttribute('aria-expanded', 'false');
        switchRoot.classList.remove('theme-switch--open');
    }

    function toggleMenu() {
        if (menu.hidden) {
            openMenu();
        } else {
            closeMenu();
        }
    }

    themeBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        toggleMenu();
    });

    document.addEventListener('click', (event) => {
        if (!switchRoot.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    applyTheme(initialTheme);
})();
