<section class="bw-card">
    <div class="bw-card-topline"></div>
    <div class="bw-card-body">
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif
        @if($section['intro_html'])<div class="bw-section-copy bw-prose">{!! $section['intro_html'] !!}</div>@endif
        <div class="bw-grid-2">
        @foreach($section['blocks'] as $block)
            <div class="bw-mini-card bw-prose">
                @if($block['model']->heading)<h2>{{ $block['model']->heading }}</h2>@endif
                {!! $block['body_html'] !!}
                @if($block['media'])
                    @include('cms.sections.partials.block-media', ['block' => $block])
                @endif
            </div>
        @endforeach
        </div>
    </div>
</section>
