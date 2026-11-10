(function () {
    const THEME_KEY = 'zhiguli-theme';
    const body = document.body;
    const themeBtn = document.getElementById('themeBtn');

    const slideImagesMap = [
        ['../assets/2106белая.jpg', '../assets/2106черная.jpg'],
        ['../assets/2107белая.jpg', '../assets/2107черная.jpg'],
        ['../assets/2199белая.jpg', '../assets/2199черная.jpg'],
    ];

    function swapThemedImages(isDark) {
        document.querySelectorAll('.slider__slide img').forEach((img, index) => {
            if (index < slideImagesMap.length) {
                img.src = isDark ? slideImagesMap[index][1] : slideImagesMap[index][0];
            }
        });

        document.querySelectorAll('img[data-light][data-dark]').forEach((img) => {
            const lightSrc = img.dataset.light;
            const darkSrc = img.dataset.dark;

            if (lightSrc && darkSrc) {
                img.src = isDark ? darkSrc : lightSrc;
            }
        });
    }

    function applyTheme(isDark) {
        body.classList.toggle('theme--dark', isDark);
        swapThemedImages(isDark);
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
