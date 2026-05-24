@if($format['is_secure'] && $downloads['secure_enabled'])
    <button
        type="button"
        @class(['bw-download-button', 'is-primary' => $primary])
        data-download-secure
        data-download-category="{{ $category['id'] }}"
        data-download-item="{{ $item['id'] }}"
        data-download-format="{{ $format['id'] }}"
    >
        {{ $primary ? 'Download' : $format['label'] }} per e-mail
    </button>
@else
    <a
        @class(['bw-download-button', 'is-primary' => $primary])
        href="{{ $format['href'] }}"
        @if($format['target']) target="{{ $format['target'] }}" rel="noopener noreferrer" @endif
        @if($format['download']) download @endif
    >
        {{ $primary ? 'Download '.$format['label'] : $format['label'] }}
    </a>
@endif
