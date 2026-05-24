<section class="px-6 py-12">
    <div class="mx-auto max-w-4xl">
        @if($section['model']->title)<h2 class="text-3xl font-semibold">{{ $section['model']->title }}</h2>@endif
        <div class="prose mt-4">{!! $section['intro_html'] !!}</div>
    </div>
</section>
