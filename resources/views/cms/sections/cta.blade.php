<section class="bw-card">
    <div class="bw-card-topline"></div>
    <div class="bw-card-body text-center items-center">
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif
        @if($section['intro_html'])<div class="bw-section-copy bw-prose max-w-2xl">{!! $section['intro_html'] !!}</div>@endif
        @foreach($section['blocks'] as $block)
            @if($block['model']->button_url && $block['model']->button_label)
                <a href="{{ $block['model']->button_url }}" class="bw-button-primary">{{ $block['model']->button_label }}</a>
            @endif
        @endforeach
    </div>
</section>
