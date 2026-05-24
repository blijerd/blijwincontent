<section class="bw-card">
    <div class="bw-card-topline"></div>
    <div class="bw-card-body">
        <span class="bw-pill">Verhaal</span>
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif
        <div class="bw-prose">
        {!! $section['intro_html'] !!}
        @foreach($section['blocks'] as $block)
            @if($block['model']->heading)<h3>{{ $block['model']->heading }}</h3>@endif
            {!! $block['body_html'] !!}
        @endforeach
        </div>
    </div>
</section>
