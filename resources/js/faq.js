const normalize = (value) => value.toLocaleLowerCase('nl-NL').trim();

document.querySelectorAll('[data-faq-section]').forEach((section) => {
    const search = section.querySelector('[data-faq-search]');
    const filters = Array.from(section.querySelectorAll('[data-faq-filter]'));
    const list = section.querySelector('[data-faq-list]');
    const items = Array.from(section.querySelectorAll('[data-faq-item]'));
    const empty = section.querySelector('[data-faq-empty]');
    const more = section.querySelector('[data-faq-more]');
    const initialLimit = Number.parseInt(list?.dataset.faqInitialLimit || '0', 10);
    const allowMultiple = section.dataset.faqAllowMultiple === '1';
    let activeFilter = 'all';
    let expandedLimit = initialLimit === 0 ? items.length : initialLimit;

    const applyState = () => {
        const query = normalize(search?.value || '');
        let visibleCount = 0;

        items.forEach((item) => {
            const categoryMatches = activeFilter === 'all' || item.dataset.faqCategory === activeFilter;
            const textMatches = query === '' || normalize(item.dataset.faqText || '').includes(query);
            const visible = categoryMatches && textMatches;
            visibleCount += visible ? 1 : 0;
            item.hidden = !visible || visibleCount > expandedLimit;
        });

        if (empty) {
            empty.hidden = visibleCount !== 0;
        }

        if (more) {
            more.hidden = expandedLimit >= visibleCount;
        }
    };

    search?.addEventListener('input', applyState);

    filters.forEach((filter) => {
        filter.addEventListener('click', () => {
            activeFilter = filter.dataset.faqFilter || 'all';
            expandedLimit = initialLimit === 0 ? items.length : initialLimit;
            filters.forEach((button) => button.classList.toggle('is-active', button === filter));
            applyState();
        });
    });

    more?.addEventListener('click', () => {
        expandedLimit += initialLimit || items.length;
        applyState();
    });

    if (!allowMultiple) {
        items.forEach((item) => {
            item.addEventListener('toggle', () => {
                if (!item.open) {
                    return;
                }

                items.forEach((other) => {
                    if (other !== item) {
                        other.open = false;
                    }
                });
            });
        });
    }

    applyState();
});
