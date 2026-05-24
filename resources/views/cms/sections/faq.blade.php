<section class="bw-card">
    <div class="bw-card-topline"></div>
    <div class="bw-card-body">
        <span class="bw-pill">FAQ</span>
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif
        @foreach($section['blocks'] as $block)
            <details class="bw-faq-item">
                <summary>{{ $block['model']->heading }}</summary>
                <div class="bw-prose mt-3">{!! $block['body_html'] !!}</div>
            </details>
        @endforeach
    </div>
</section>
