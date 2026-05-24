function showScreen(container, screen) {
    container.querySelectorAll('[data-status-screen]').forEach((item) => {
        item.hidden = item.dataset.statusScreen !== screen;
    });
}

document.querySelectorAll('[data-booking-request-form]').forEach((root) => {
    const form = root.querySelector('form');
    const status = root.querySelector('[data-booking-request-status]');
    const error = root.querySelector('[data-booking-request-error]');

    if (!form || !status || !error) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        error.textContent = '';
        status.hidden = false;
        form.hidden = true;
        showScreen(status, 'submitting');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json();

            if (!response.ok && response.status !== 202) {
                const message = data.message || Object.values(data.errors || {}).flat()[0] || 'De aanvraag kon niet worden verwerkt.';
                throw new Error(message);
            }

            showScreen(status, data.screen || 'confirm_email');
        } catch (caught) {
            status.hidden = true;
            form.hidden = false;
            error.textContent = caught.message || 'De aanvraag kon niet worden verwerkt.';
        }
    });
});
