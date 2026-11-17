(function () {
    const buttons = Array.from(document.querySelectorAll('.projects-filter__btn'));
    const items = Array.from(document.querySelectorAll('.projects__item'));
    const empty = document.getElementById('projectsEmpty');
    const searchInput = document.getElementById('projectsSearch');

    if (!buttons.length || !items.length) {
        return;
    }

    let activeFilter = 'all';

    function normalize(value) {
        return (value || '')
            .toLowerCase()
            .replace(/ё/g, 'е')
            .replace(/[«»"']/g, '')
            .trim();
    }

    function itemHaystack(item) {
        const parts = [
            item.dataset.category || '',
            item.querySelector('.project__title')?.textContent || '',
            item.querySelector('.project__tag')?.textContent || '',
            item.querySelector('.project__meta')?.textContent || '',
            item.querySelector('.project__text')?.textContent || '',
        ];
        return normalize(parts.join(' '));
    }

    const catalog = items.map((item) => ({
        el: item,
        categories: (item.dataset.category || '').split(/\s+/).filter(Boolean),
        haystack: itemHaystack(item),
    }));

    function applyFilters() {
        const query = normalize(searchInput ? searchInput.value : '');
        let visible = 0;

        catalog.forEach((item) => {
            const byCategory = activeFilter === 'all' || item.categories.includes(activeFilter);
            const byQuery = !query || item.haystack.includes(query);
            const show = byCategory && byQuery;
            item.el.hidden = !show;
            if (show) {
                visible += 1;
            }
        });

        if (empty) {
            empty.hidden = visible > 0;
            if (visible === 0) {
                empty.textContent = query
                    ? 'По запросу ничего не нашлось — попробуй другое слово или сбрось фильтр.'
                    : 'В этой категории пока нет проектов.';
            }
        }
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.filter || 'all';

            buttons.forEach((btn) => {
                const active = btn === button;
                btn.classList.toggle('projects-filter__btn--active', active);
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            });

            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    applyFilters();
})();
