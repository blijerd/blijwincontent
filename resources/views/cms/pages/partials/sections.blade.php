@foreach($viewModel->sections() as $section)
    @include(\App\Support\Sections\SectionConfig::partial($section['model']->type), ['section' => $section])
@endforeach
