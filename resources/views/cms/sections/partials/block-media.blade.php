@if(($block['media']['type'] ?? null) === 'youtube')
    @include('cms.sections.partials.privacy-youtube', ['media' => $block['media']])
@elseif(($block['media']['type'] ?? null) === 'image')
    <figure class="bw-story-media">
        <img src="{{ $block['media']['url'] }}" alt="{{ $block['media']['alt'] }}" loading="lazy">
    </figure>
@endif
