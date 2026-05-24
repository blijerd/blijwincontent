<section class="bw-card">
    <div class="bw-card-topline"></div>
    <div class="bw-card-body">
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif
        @if($section['intro_html'])<div class="bw-section-copy bw-prose">{!! $section['intro_html'] !!}</div>@endif
        <div class="bw-grid-3">
        @foreach($section['blocks'] as $block)
            <article class="bw-mini-card">
                @if($block['model']->heading)<h3 class="bw-section-title text-2xl">{{ $block['model']->heading }}</h3>@endif
                <div class="bw-prose">{!! $block['body_html'] !!}</div>
            </article>
        @endforeach
        </div>
    </div>
</section>
