<section class="bw-card">
    <div class="bw-card-topline"></div>
    <div class="bw-card-body">
        <span class="bw-pill">Video</span>
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif
        <div class="bw-prose">{!! $section['intro_html'] !!}</div>
    </div>
</section>
