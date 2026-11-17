(function () {
    const searchInput = document.getElementById('modelsSearch');
    const bodySelect = document.getElementById('modelsBody');
    const eraSelect = document.getElementById('modelsEra');
    const empty = document.getElementById('modelsEmpty');
    const specs = Array.from(document.querySelectorAll('article.spec'));
    const indexLinks = Array.from(document.querySelectorAll('.catalog-index__link'));
    const families = Array.from(document.querySelectorAll('.catalog-family'));

    if (!searchInput || !bodySelect || !eraSelect || !specs.length) {
        return;
    }

    const eras = {
        '70': [1970, 1979],
        '80': [1980, 1989],
        '90': [1990, 1999],
        '00': [2000, 2100],
    };

    function normalize(value) {
        return (value || '')
            .toLowerCase()
            .replace(/ё/g, 'е')
            .replace(/[«»"']/g, '')
            .trim();
    }

    function specField(spec, label) {
        const rows = spec.querySelectorAll('.spec__row');
        for (const row of rows) {
            const dt = row.querySelector('dt');
            const dd = row.querySelector('dd');
            if (dt && dd && normalize(dt.textContent) === normalize(label)) {
                return dd.textContent.trim();
            }
        }
        return '';
    }

    function parseYears(raw) {
        const digits = (raw.match(/\d{4}/g) || []).map(Number);
        if (!digits.length) {
            return null;
        }
        return {
            start: digits[0],
            end: digits[1] || digits[0],
        };
    }

    const catalog = specs.map((spec) => {
        const yearsRaw = specField(spec, 'Годы');
        const bodyRaw = specField(spec, 'Кузов');
        const name = spec.querySelector('.spec__name')?.textContent || '';
        const tag = spec.querySelector('.spec__tag')?.textContent || '';
        const text = spec.querySelector('.spec__text')?.textContent || '';

        return {
            el: spec,
            id: spec.id,
            body: normalize(bodyRaw),
            years: parseYears(yearsRaw),
            haystack: normalize([name, tag, text, yearsRaw, bodyRaw, spec.id].join(' ')),
        };
    });

    function matchesEra(years, eraKey) {
        if (eraKey === 'all' || !years) {
            return true;
        }
        const range = eras[eraKey];
        if (!range) {
            return true;
        }
        return years.start <= range[1] && years.end >= range[0];
    }

    function applyFilters() {
        const query = normalize(searchInput.value);
        const body = bodySelect.value;
        const era = eraSelect.value;
        let visible = 0;

        catalog.forEach((item) => {
            const byQuery = !query || item.haystack.includes(query);
            const byBody = body === 'all' || item.body.includes(body);
            const byEra = matchesEra(item.years, era);
            const show = byQuery && byBody && byEra;
            item.el.hidden = !show;
            if (show) {
                visible += 1;
            }
        });

        indexLinks.forEach((link) => {
            const id = (link.getAttribute('href') || '').replace(/^#/, '');
            const item = catalog.find((entry) => entry.id === id);
            const hide = !!(item && item.el.hidden);
            link.hidden = hide;
            if (link.parentElement) {
                link.parentElement.hidden = hide;
            }
        });

        families.forEach((family) => {
            const hasVisible = family.querySelectorAll('article.spec:not([hidden])').length > 0;
            family.hidden = !hasVisible;
        });

        if (empty) {
            empty.hidden = visible > 0;
        }
    }

    searchInput.addEventListener('input', applyFilters);
    bodySelect.addEventListener('change', applyFilters);
    eraSelect.addEventListener('change', applyFilters);
})();
