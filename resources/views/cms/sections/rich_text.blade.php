@if($section['model']->source_template === 'storyflow' && $section['blocks']->isNotEmpty())
    <section class="bw-storyflow" aria-labelledby="section-{{ $section['model']->public_id }}">
        @if($section['model']->title)
            <h2 id="section-{{ $section['model']->public_id }}" class="sr-only">{{ $section['model']->title }}</h2>
        @endif

        @foreach($section['blocks'] as $block)
            @php($layout = $block['model']->source_payload['layout'] ?? 'media-rechts')
            <article class="bw-storyflow__item {{ $loop->even ? 'bw-storyflow__item--alternate' : '' }} {{ $layout === 'media-links' ? 'bw-storyflow__item--media-left' : '' }}">
                <div class="bw-storyflow__copy bw-prose">
                    @if($block['model']->subheading)<span class="bw-pill">{{ $block['model']->subheading }}</span>@endif
                    @if($block['model']->heading)<h3 class="bw-storyflow__title">{{ $block['model']->heading }}</h3>@endif
                    {!! $block['body_html'] !!}
                    @if($block['model']->button_url && $block['model']->button_label)
                        <a href="{{ $block['model']->button_url }}" class="bw-button-primary">{{ $block['model']->button_label }}</a>
                    @endif
                </div>

                @if($block['media'])
                    <div class="bw-storyflow__media">
                        @include('cms.sections.partials.block-media', ['block' => $block])
                    </div>
                @endif
            </article>
        @endforeach
    </section>
@else
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
                @if($block['media'])
                    @include('cms.sections.partials.block-media', ['block' => $block])
                @endif
            @endforeach
            </div>
        </div>
    </section>
@endif
