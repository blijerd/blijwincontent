(function () {
    const cfg = window.TrackingWriterConfig || {};
    const consentEndpoint = cfg.consentEndpoint || '/tracking-consent';
    const collectEndpoint = cfg.collectEndpoint || '/tracking-collect';
    const fieldName = cfg.fieldName || 'data[blijwin_t_info]';
    const heartbeatSeconds = Math.max(Number(cfg.heartbeatSeconds || 30), 5);
    const state = {
        consent: cfg.consentDefaults || { hasDecision: false, categories: { necessary: true, analytics: false, marketing: false } },
        identifiers: { visitor_id: '', session_id: '', storage_mode: 'server_session' },
        pageVisitId: 'pv_' + Math.random().toString(36).slice(2) + Date.now().toString(36),
        pageviewSent: false,
    };

    function csrfHeaders() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        return token ? { 'X-CSRF-TOKEN': token } : {};
    }

    function postJson(url, data, keepalive) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: !!keepalive,
            headers: Object.assign({ 'Content-Type': 'application/json', Accept: 'application/json' }, csrfHeaders()),
            body: JSON.stringify(data),
        }).then((response) => response.json()).catch(() => null);
    }

    function hasAnalyticsConsent() {
        return !!(state.consent && state.consent.categories && state.consent.categories.analytics);
    }

    function sourceParams() {
        const params = new URLSearchParams(window.location.search);
        const keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'gclid', 'fbclid'];
        const source = {};
        keys.forEach((key) => {
            const value = params.get(key);
            if (value) source[key] = value;
        });
        return source;
    }

    function deviceType() {
        if (window.matchMedia && window.matchMedia('(max-width: 767px)').matches) return 'mobile';
        if (window.matchMedia && window.matchMedia('(max-width: 1023px)').matches) return 'tablet';
        return 'desktop';
    }

    function basePayload(eventType) {
        return {
            event_type: eventType,
            visitor_id: state.identifiers.visitor_id,
            session_id: state.identifiers.session_id,
            page_visit_id: state.pageVisitId,
            at: new Date().toISOString(),
            slug: cfg.pageSlug || 'home',
            path: window.location.pathname,
            url: window.location.href,
            title: document.title,
            referrer: document.referrer,
            device: deviceType(),
            landing: !sessionStorage.getItem('tracking_writer_seen'),
            source: sourceParams(),
        };
    }

    function syncHiddenFields() {
        const value = JSON.stringify({
            visitor_id: state.identifiers.visitor_id,
            session_id: state.identifiers.session_id,
            page_visit_id: state.pageVisitId,
        });

        document.querySelectorAll('form').forEach((form) => {
            let input = form.querySelector('input[name="' + fieldName.replace(/"/g, '\\"') + '"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = fieldName;
                form.appendChild(input);
            }
            input.value = value;
        });
    }

    function sendEvent(eventType, extra, keepalive) {
        if (!hasAnalyticsConsent() || !state.identifiers.visitor_id || !state.identifiers.session_id) return Promise.resolve(null);
        return postJson(collectEndpoint, Object.assign(basePayload(eventType), extra || {}), keepalive);
    }

    function sendPageview() {
        if (state.pageviewSent) return;
        state.pageviewSent = true;
        sessionStorage.setItem('tracking_writer_seen', '1');
        sendEvent('pageview');
    }

    function createConsentBanner() {
        if (state.consent.hasDecision || document.getElementById('tracking-writer-consent-banner')) return;

        const banner = document.createElement('section');
        banner.id = 'tracking-writer-consent-banner';
        banner.className = 'tw-consent-banner';
        banner.innerHTML = [
            '<div class="tw-consent-banner__body">',
            '<p class="tw-consent-banner__eyebrow">Cookiekeuze</p>',
            '<h2 class="tw-consent-banner__title">Mogen we je bezoek meten?</h2>',
            '<p class="tw-consent-banner__intro">We gebruiken noodzakelijke cookies altijd. Met statistiek help je deze website beter te maken.</p>',
            '<label class="tw-consent-option"><input type="checkbox" data-tw-category="analytics"> Statistiek / analytics</label>',
            '<label class="tw-consent-option"><input type="checkbox" data-tw-category="marketing"> Marketing en externe inhoud</label>',
            '</div>',
            '<div class="tw-consent-banner__actions">',
            '<button type="button" data-tw-consent="necessary">Alleen nodig</button>',
            '<button type="button" data-tw-consent="preferences">Voorkeuren opslaan</button>',
            '<button type="button" data-tw-consent="all">Alles toestaan</button>',
            '</div>',
        ].join('');
        document.body.appendChild(banner);

        banner.addEventListener('click', (event) => {
            const button = event.target.closest('[data-tw-consent]');
            if (!button) return;

            const mode = button.getAttribute('data-tw-consent');
            const categories = {
                necessary: true,
                analytics: mode === 'all' || (mode === 'preferences' && banner.querySelector('[data-tw-category="analytics"]').checked),
                marketing: mode === 'all' || (mode === 'preferences' && banner.querySelector('[data-tw-category="marketing"]').checked),
            };

            postJson(consentEndpoint, { categories, source: mode === 'all' ? 'initial_accept_all' : 'initial_preferences' })
                .then(applyConsentResponse)
                .then(() => banner.remove());
        });
    }

    function applyConsentResponse(response) {
        if (!response || !response.ok) return;
        state.consent = response.consent || state.consent;
        state.identifiers = response.identifiers || state.identifiers;
        syncHiddenFields();
        sendPageview();
    }

    function bindContactAttempts() {
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link) return;

            const href = link.getAttribute('href') || '';
            const type = href.indexOf('mailto:') === 0 ? 'email' : (href.indexOf('tel:') === 0 ? 'phone' : '');
            if (!type) return;

            sendEvent('contact_attempt', {
                contact_type: type,
                href: href,
                link_text: (link.textContent || '').trim(),
            }, true);
        }, { capture: true });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            syncHiddenFields();
            sendEvent('form_submit', {
                contact_type: 'form',
                form_action: form.action || window.location.href,
                form_id: form.id || '',
                form_name: form.getAttribute('name') || '',
                form_method: form.method || 'get',
            }, true);
        }, { capture: true });
    }

    function boot() {
        fetch(consentEndpoint, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then(applyConsentResponse)
            .then(createConsentBanner);

        bindContactAttempts();
        window.setInterval(() => sendEvent('heartbeat'), heartbeatSeconds * 1000);
        window.addEventListener('pagehide', () => sendEvent('page_end', {}, true));
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
