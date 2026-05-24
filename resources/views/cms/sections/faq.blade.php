<section class="bw-card bw-faq" data-faq-section data-faq-allow-multiple="{{ $section['faq']['allow_multiple_open'] ? '1' : '0' }}">
    <div class="bw-card-topline"></div>
    <div class="bw-card-body">
        <span class="bw-pill">FAQ</span>
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif

        @if($section['intro_html'])
            <div class="bw-prose bw-faq-intro">{!! $section['intro_html'] !!}</div>
        @endif

        @if($section['faq']['searchable'] || $section['faq']['categories_enabled'])
            <div class="bw-faq-toolbar">
                @if($section['faq']['searchable'])
                    <label class="bw-faq-search">
                        <span class="sr-only">Zoek in veelgestelde vragen</span>
                        <input type="search" placeholder="Zoek een vraag" data-faq-search>
                    </label>
                @endif

                @if($section['faq']['categories_enabled'])
                    <div class="bw-faq-filters" aria-label="FAQ categorieen">
                        <button type="button" class="is-active" data-faq-filter="all">Alles</button>
                        @foreach($section['faq']['categories'] as $category)
                            <button type="button" data-faq-filter="{{ $category['slug'] }}">{{ $category['title'] }}</button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="bw-faq-list" data-faq-list data-faq-initial-limit="{{ $section['faq']['initial_limit'] }}">
        @foreach($section['faq']['items'] as $index => $item)
            <details
                class="bw-faq-item"
                id="{{ $item['anchor'] }}"
                data-faq-item
                data-faq-category="{{ $item['category'] }}"
                data-faq-text="{{ \Illuminate\Support\Str::lower($item['question'].' '.$item['answer_text']) }}"
                @if($section['faq']['expand_first'] && $index === 0) open @endif
            >
                <summary aria-controls="{{ $item['panel_id'] }}">
                    <span>{{ $item['question'] }}</span>
                    @if($item['category_title'])
                        <small>{{ $item['category_title'] }}</small>
                    @endif
                </summary>
                <div id="{{ $item['panel_id'] }}" class="bw-prose mt-3">{!! $item['answer_html'] !!}</div>
            </details>
        @endforeach
        </div>

        <p class="bw-faq-empty" data-faq-empty hidden>Geen veelgestelde vragen gevonden.</p>

        @if($section['faq']['initial_limit'] > 0 && count($section['faq']['items']) > $section['faq']['initial_limit'])
            <button type="button" class="bw-faq-more" data-faq-more>Toon meer vragen</button>
        @endif

        @if($section['faq']['cta_label'] && $section['faq']['cta_url'])
            <a class="bw-button bw-faq-cta" href="{{ $section['faq']['cta_url'] }}">{{ $section['faq']['cta_label'] }}</a>
        @endif
    </div>

    @if($section['faq']['schema_enabled'] && $section['faq']['schema'])
        <script type="application/ld+json">{!! $section['faq']['schema'] !!}</script>
    @endif
</section>
