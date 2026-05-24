<section class="px-6 py-12">
    <div class="mx-auto grid max-w-6xl gap-6 md:grid-cols-3">
        @foreach($section['blocks'] as $block)
            <article class="border border-slate-200 p-6">
                @if($block['model']->heading)<h3 class="text-xl font-semibold">{{ $block['model']->heading }}</h3>@endif
                <div class="prose mt-3">{!! $block['body_html'] !!}</div>
            </article>
        @endforeach
    </div>
</section>
