<div class="bw-youtube" data-youtube-embed data-youtube-src="{{ $media['embed_url'] }}" data-youtube-title="{{ $media['label'] }}">
    <button class="bw-youtube__button" type="button" aria-label="{{ $media['label'] }}">
        <span class="bw-youtube__play" aria-hidden="true"></span>
        <span class="bw-youtube__copy">{{ $media['label'] }}</span>
    </button>
    <noscript>
        <a href="https://www.youtube-nocookie.com/embed/{{ $media['youtube_id'] }}" rel="noopener noreferrer">Bekijk video</a>
    </noscript>
</div>
