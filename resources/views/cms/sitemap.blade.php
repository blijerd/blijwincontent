<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($pages as $page)
    <url>
        <loc>{{ url($page->full_path) }}</loc>
        <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
    </url>
@endforeach
</urlset>
