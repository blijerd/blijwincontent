<section class="bw-hero-panel">
    <div class="bw-hero-content">
        <span class="bw-pill bw-pill--hero">Blijwin</span>
        @if($section['model']->title)<h1 class="bw-hero-title">{{ $section['model']->title }}</h1>@endif
        @if($section['intro_html'])<div class="bw-hero-copy bw-prose">{!! $section['intro_html'] !!}</div>@endif
        @foreach($section['blocks'] as $block)
            @if($block['model']->button_url && $block['model']->button_label)
                <a href="{{ $block['model']->button_url }}" class="bw-button-primary mt-6">{{ $block['model']->button_label }}</a>
            @endif
        @endforeach
    </div>
</section>
