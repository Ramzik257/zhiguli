(function () {
    const buttons = Array.from(document.querySelectorAll('.projects-filter__btn'));
    const items = Array.from(document.querySelectorAll('.projects__item'));
    const empty = document.getElementById('projectsEmpty');

    if (!buttons.length || !items.length) {
        return;
    }

    function applyFilter(filter) {
        let visible = 0;

        items.forEach((item) => {
            const categories = (item.dataset.category || '').split(/\s+/);
            const show = filter === 'all' || categories.includes(filter);
            item.hidden = !show;
            if (show) {
                visible += 1;
            }
        });

        if (empty) {
            empty.hidden = visible > 0;
        }
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter || 'all';

            buttons.forEach((btn) => {
                const active = btn === button;
                btn.classList.toggle('projects-filter__btn--active', active);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            applyFilter(filter);
        });
    });
})();
