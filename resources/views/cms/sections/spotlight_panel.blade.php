<section class="bg-slate-100 px-6 py-16">
    <div class="prose mx-auto max-w-4xl">
        @if($section['model']->title)<h2>{{ $section['model']->title }}</h2>@endif
        {!! $section['intro_html'] !!}
    </div>
</section>
