<section class="px-6 py-12">
    <div class="prose mx-auto max-w-3xl">
        @if($section['model']->title)<h2>{{ $section['model']->title }}</h2>@endif
        {!! $section['intro_html'] !!}
        @foreach($section['blocks'] as $block)
            @if($block['model']->heading)<h3>{{ $block['model']->heading }}</h3>@endif
            {!! $block['body_html'] !!}
        @endforeach
    </div>
</section>
