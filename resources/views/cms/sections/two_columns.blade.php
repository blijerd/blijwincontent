<section class="px-6 py-12">
    <div class="mx-auto grid max-w-6xl gap-8 md:grid-cols-2">
        @foreach($section['blocks'] as $block)
            <div class="prose">
                @if($block['model']->heading)<h2>{{ $block['model']->heading }}</h2>@endif
                {!! $block['body_html'] !!}
            </div>
        @endforeach
    </div>
</section>
