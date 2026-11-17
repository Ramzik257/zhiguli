(function () {
    const profileLinks = Array.from(document.querySelectorAll('[data-auth="profile"]'));
    const joinLinks = Array.from(document.querySelectorAll('[data-auth="join"]'));

    if (!profileLinks.length && !joinLinks.length) {
        return;
    }

    const sessionUrl = document.body.dataset.sessionUrl || '../backend/session.php';

    function applyGuest() {
        profileLinks.forEach((link) => {
            link.href = link.dataset.authGuest || link.href;
            if (link.dataset.authGuestLabel) {
                link.textContent = link.dataset.authGuestLabel;
            }
        });

        joinLinks.forEach((link) => {
            link.href = link.dataset.authGuest || link.href;
            if (link.dataset.authGuestLabel) {
                link.textContent = link.dataset.authGuestLabel;
            }
        });
    }

    function applyUser(name) {
        profileLinks.forEach((link) => {
            link.href = link.dataset.authUser || link.href;
            if (link.dataset.authUserLabel) {
                link.textContent = name ? name : link.dataset.authUserLabel;
            }
        });

        joinLinks.forEach((link) => {
            link.href = link.dataset.authUser || link.href;
            if (link.dataset.authUserLabel) {
                link.textContent = link.dataset.authUserLabel;
            }
        });
    }

    applyGuest();

    fetch(sessionUrl, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
    })
        .then((response) => (response.ok ? response.json() : null))
        .then((data) => {
            if (data && data.authenticated) {
                applyUser(data.name || '');
            }
        })
        .catch(() => {
            // Stay on guest links if session check fails.
        });
})();
