document.querySelectorAll('[data-youtube-embed]').forEach((container) => {
    const button = container.querySelector('button');

    if (! button) {
        return;
    }

    button.addEventListener('click', () => {
        const src = container.dataset.youtubeSrc;

        if (! src) {
            return;
        }

        const iframe = document.createElement('iframe');
        iframe.src = src;
        iframe.title = container.dataset.youtubeTitle || 'YouTube video';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';

        container.replaceChildren(iframe);
        container.classList.add('bw-youtube--loaded');
    });
});
