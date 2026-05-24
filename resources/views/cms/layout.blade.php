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
<body class="antialiased">
    <div class="bw-site-shell">
        <header class="bw-site-header">
            <a href="{{ url('/') }}" class="bw-brand-lockup" aria-label="{{ $viewModel->page->site->name }}">
                <span class="bw-brand-mark">{{ $viewModel->page->site->name }}</span>
                <span class="bw-brand-copy">
                    <strong>{{ $viewModel->page->site->name }}</strong>
                    <span>feestelijke website</span>
                </span>
            </a>

            <nav class="bw-site-nav" aria-label="Hoofdnavigatie">
                <a href="{{ url('/') }}">Home</a>
                <a href="{{ url('/blog') }}">Blog</a>
                <a href="{{ url('/product') }}">Product</a>
            </nav>
        </header>

        <main class="bw-page-stack">
            @yield('content')
        </main>
    </div>

    <footer class="bw-site-footer">
        <div class="bw-site-footer__inner">
            <div class="bw-card-topline"></div>
            <div class="bw-site-footer__content">
                <div class="bw-brand-lockup">
                    <span class="bw-brand-mark">{{ $viewModel->page->site->name }}</span>
                    <span class="bw-brand-copy">
                        <strong>{{ $viewModel->page->site->name }}</strong>
                        <span>is een handelsnaam van Blijevent</span>
                    </span>
                </div>
                <p>Markdown-first, snel en klaar voor meertalige content.</p>
            </div>
        </div>
    </footer>
</body>
</html>
