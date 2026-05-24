<section class="px-6 py-16 text-center">
    @if($section['model']->title)<h2 class="text-3xl font-semibold">{{ $section['model']->title }}</h2>@endif
    @if($section['intro_html'])<div class="prose mx-auto mt-4 max-w-2xl">{!! $section['intro_html'] !!}</div>@endif
</section>
