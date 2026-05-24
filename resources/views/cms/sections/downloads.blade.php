<section class="bw-card bw-downloads" data-download-section>
    <div class="bw-card-topline"></div>
    <div class="bw-card-body">
        <span class="bw-pill">Downloads</span>
        @if($section['model']->title)<h2 class="bw-section-title">{{ $section['model']->title }}</h2>@endif

        @if($section['intro_html'])
            <div class="bw-prose bw-downloads-intro">{!! $section['intro_html'] !!}</div>
        @endif

        <div class="bw-download-categories">
            @foreach($section['downloads']['categories'] as $category)
                <div class="bw-download-category" id="downloads-{{ $category['slug'] }}">
                    <div class="bw-download-category-heading">
                        <h3>{{ $category['title'] }}</h3>
                        <span>{{ count($category['items']) }} downloads</span>
                    </div>

                    @if($section['downloads']['show_category_intro'] && $category['intro_html'])
                        <div class="bw-prose bw-download-category-intro">{!! $category['intro_html'] !!}</div>
                    @endif

                    <div class="bw-download-grid">
                        @foreach($category['items'] as $item)
                            <article class="bw-download-card">
                                @if($item['image_url'])
                                    <img
                                        src="{{ $item['image_url'] }}"
                                        alt="{{ $item['image_alt'] }}"
                                        loading="lazy"
                                        @if($item['image_focus']) style="object-position: {{ $item['image_focus'] }}" @endif
                                    >
                                @endif

                                <div class="bw-download-card-body">
                                    <h4>{{ $item['title'] }}</h4>
                                    @if($item['preview_html'])
                                        <div class="bw-prose bw-download-preview">{!! $item['preview_html'] !!}</div>
                                    @endif

                                    @if($item['primary_format'])
                                        <div class="bw-download-actions">
                                            @include('cms.sections.partials.download-format-button', [
                                                'category' => $category,
                                                'item' => $item,
                                                'format' => $item['primary_format'],
                                                'downloads' => $section['downloads'],
                                                'primary' => true,
                                            ])

                                            @if($item['alternative_formats'])
                                                <details class="bw-download-alternatives">
                                                    <summary>Andere formats</summary>
                                                    <div>
                                                        @foreach($item['alternative_formats'] as $format)
                                                            @include('cms.sections.partials.download-format-button', [
                                                                'category' => $category,
                                                                'item' => $item,
                                                                'format' => $format,
                                                                'downloads' => $section['downloads'],
                                                                'primary' => false,
                                                            ])
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <dialog class="bw-download-modal" data-download-modal>
            <form method="dialog" class="bw-download-modal-close-form">
                <button type="submit" aria-label="Sluiten">x</button>
            </form>
            <form class="bw-download-form" data-download-form action="{{ $section['downloads']['secure_request_url'] }}">
                <h3>Ontvang de download per e-mail</h3>
                <input type="hidden" name="category_id" data-download-category-id>
                <input type="hidden" name="item_id" data-download-item-id>
                <input type="hidden" name="format_id" data-download-format-id>
                <input type="hidden" name="form_token" value="{{ $section['downloads']['secure_form_token'] }}">
                <label>
                    <span>Voornaam</span>
                    <input type="text" name="first_name" required autocomplete="given-name">
                </label>
                <label>
                    <span>E-mailadres</span>
                    <input type="email" name="email" required autocomplete="email">
                </label>
                <label class="bw-download-hp" aria-hidden="true" tabindex="-1">
                    <span>Website</span>
                    <input type="text" name="{{ $section['downloads']['honeypot_field'] }}" tabindex="-1" autocomplete="off">
                </label>
                <button type="submit" class="bw-button">Stuur downloadlink</button>
                <p data-download-message role="status"></p>
            </form>
        </dialog>
    </div>
</section>
