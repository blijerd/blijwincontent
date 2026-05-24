<section class="px-6 py-20">
    <div class="mx-auto max-w-5xl">
        @if($section['model']->title)<h1 class="text-5xl font-semibold">{{ $section['model']->title }}</h1>@endif
        @if($section['intro_html'])<div class="prose mt-6 max-w-3xl">{!! $section['intro_html'] !!}</div>@endif
    </div>
</section>
