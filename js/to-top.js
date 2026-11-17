(function () {
    const button = document.getElementById('toTop');

    if (!button) {
        return;
    }

    const showAfter = 420;

    function updateVisibility() {
        const shouldShow = window.scrollY > showAfter;
        button.hidden = !shouldShow;
        button.classList.toggle('to-top--visible', shouldShow);
    }

    button.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', updateVisibility, { passive: true });
    updateVisibility();
})();
