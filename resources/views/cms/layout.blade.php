<!doctype html>
<html lang="{{ $viewModel->page->locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] }}</title>
    @if($seo['description'])<meta name="description" content="{{ $seo['description'] }}">@endif
    <meta name="robots" content="{{ $seo['robots'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">
    <meta property="og:title" content="{{ $seo['og_title'] }}">
    @if($seo['og_description'])<meta property="og:description" content="{{ $seo['og_description'] }}">@endif
    @foreach($hreflang as $locale => $url)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
    @endforeach
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-950 antialiased">
    <main>
        @yield('content')
    </main>
</body>
</html>
