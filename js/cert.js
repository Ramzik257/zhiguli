(function () {
    const certificate = document.getElementById('certificate');
    const titleInput = document.getElementById('certTitle');
    const nameInput = document.getElementById('certName');
    const textInput = document.getElementById('certText');
    const dateInput = document.getElementById('certDate');
    const bgCustom = document.getElementById('bgCustom');
    const previewTitle = document.getElementById('previewTitle');
    const previewName = document.getElementById('previewName');
    const previewText = document.getElementById('previewText');
    const previewDate = document.getElementById('previewDate');
    const previewCars = document.getElementById('previewCars');
    const printBtn = document.getElementById('printCert');
    const resetBtn = document.getElementById('resetCert');

    if (!certificate) {
        return;
    }

    const defaults = {
        title: 'Сертификат участника',
        name: 'VAZ_Driver',
        text: 'Настоящим подтверждается, что владелец является участником клуба любителей Жигули и разделяет уважение к классике ВАЗа.',
        bg: '#f7f1e3',
        ink: '#1c1b19',
        frame: 'classic',
        photos: ['2101', '2109', '21099'],
    };

    function contrastInk(hex) {
        const value = hex.replace('#', '');
        const full = value.length === 3
            ? value.split('').map((ch) => ch + ch).join('')
            : value;
        const num = parseInt(full, 16);
        const r = (num >> 16) & 255;
        const g = (num >> 8) & 255;
        const b = num & 255;
        const luma = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luma > 0.55 ? '#1c1b19' : '#f2efe8';
    }

    function setBackground(bg, ink, fromCustom) {
        certificate.style.setProperty('--cert-bg', bg);
        certificate.style.setProperty('--cert-ink', ink || contrastInk(bg));
        bgCustom.value = bg.length === 7 ? bg : '#f7f1e3';

        let matched = false;
        document.querySelectorAll('.cert-swatch[data-bg]').forEach((swatch) => {
            const active = swatch.dataset.bg.toLowerCase() === bg.toLowerCase();
            swatch.classList.toggle('is-active', active);
            if (active) {
                matched = true;
            }
        });

        const customSwatch = document.querySelector('.cert-swatch--custom');
        if (customSwatch) {
            customSwatch.classList.toggle('is-active', Boolean(fromCustom) || !matched);
            customSwatch.style.setProperty('--swatch', bg);
        }
    }

    function setFrame(frame) {
        certificate.dataset.frame = frame;
        document.querySelectorAll('.cert-chip').forEach((chip) => {
            chip.classList.toggle('is-active', chip.dataset.frame === frame);
        });
    }

    function syncText() {
        previewTitle.textContent = titleInput.value.trim() || defaults.title;
        previewName.textContent = nameInput.value.trim() || 'Участник клуба';
        previewText.textContent = textInput.value.trim() || defaults.text;
        previewDate.textContent = dateInput.value.trim();
    }

    function syncPhotos() {
        const checked = Array.from(document.querySelectorAll('input[name="photo"]:checked')).slice(0, 3);
        previewCars.innerHTML = '';

        checked.forEach((input) => {
            const item = document.createElement('li');
            item.className = 'certificate__car';
            item.innerHTML = `
                <img src="${input.dataset.src}" alt="${input.dataset.name}">
                <span>${input.dataset.name}</span>
            `;
            previewCars.appendChild(item);
        });
    }

    function enforcePhotoLimit(changedInput) {
        const checked = document.querySelectorAll('input[name="photo"]:checked');
        if (checked.length <= 3) {
            return;
        }

        changedInput.checked = false;
    }

    function todayLabel() {
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        return `${day}.${month}.${now.getFullYear()}`;
    }

    function resetAll() {
        titleInput.value = defaults.title;
        nameInput.value = defaults.name;
        textInput.value = defaults.text;
        dateInput.value = todayLabel();
        setBackground(defaults.bg, defaults.ink);
        setFrame(defaults.frame);

        document.querySelectorAll('input[name="photo"]').forEach((input) => {
            input.checked = defaults.photos.includes(input.value);
        });

        syncText();
        syncPhotos();
    }

    document.getElementById('bgSwatches').addEventListener('click', (event) => {
        const swatch = event.target.closest('.cert-swatch');
        if (!swatch) {
            return;
        }
        setBackground(swatch.dataset.bg, swatch.dataset.ink);
    });

    bgCustom.addEventListener('input', () => {
        setBackground(bgCustom.value, null, true);
    });

    document.getElementById('frameOptions').addEventListener('click', (event) => {
        const chip = event.target.closest('.cert-chip');
        if (!chip) {
            return;
        }
        setFrame(chip.dataset.frame);
    });

    [titleInput, nameInput, textInput, dateInput].forEach((input) => {
        input.addEventListener('input', syncText);
    });

    document.getElementById('photoOptions').addEventListener('change', (event) => {
        if (event.target.matches('input[name="photo"]')) {
            enforcePhotoLimit(event.target);
            syncPhotos();
        }
    });

    printBtn.addEventListener('click', () => {
        window.print();
    });

    resetBtn.addEventListener('click', resetAll);

    if (!dateInput.value) {
        dateInput.value = todayLabel();
    }

    syncText();
    syncPhotos();
})();
