<section class="px-6 py-12">
    <div class="mx-auto max-w-3xl">
        @if($section['model']->title)<h2 class="text-3xl font-semibold">{{ $section['model']->title }}</h2>@endif
        @foreach($section['blocks'] as $block)
            <details class="border-b border-slate-200 py-4">
                <summary class="cursor-pointer font-semibold">{{ $block['model']->heading }}</summary>
                <div class="prose mt-3">{!! $block['body_html'] !!}</div>
            </details>
        @endforeach
    </div>
</section>
