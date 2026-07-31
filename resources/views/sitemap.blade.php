{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        @if($lastModified)<lastmod>{{ \Illuminate\Support\Carbon::parse($lastModified)->toAtomString() }}</lastmod>@endif
        <changefreq>weekly</changefreq>
        <priority>1.0</priority>
    </url>
</urlset>
