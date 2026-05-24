document.querySelectorAll('[data-download-section]').forEach((section) => {
    const modal = section.querySelector('[data-download-modal]');
    const form = section.querySelector('[data-download-form]');
    const message = section.querySelector('[data-download-message]');

    section.querySelectorAll('[data-download-secure]').forEach((button) => {
        button.addEventListener('click', () => {
            form.querySelector('[data-download-category-id]').value = button.dataset.downloadCategory || '';
            form.querySelector('[data-download-item-id]').value = button.dataset.downloadItem || '';
            form.querySelector('[data-download-format-id]').value = button.dataset.downloadFormat || '';
            message.textContent = '';

            if (typeof modal.showModal === 'function') {
                modal.showModal();
            }
        });
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        message.textContent = 'Bezig met versturen...';

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: new FormData(form),
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            const errors = payload.errors || {};
            message.textContent = Object.values(errors).flat()[0] || 'De aanvraag kon niet worden verwerkt.';
            return;
        }

        form.reset();
        message.textContent = payload.message || 'De downloadlink is verstuurd.';
    });
});
