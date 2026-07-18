<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--
        Métadonnées de partage rendues côté serveur : les robots de WhatsApp,
        Facebook et Telegram n'exécutent pas le JavaScript, ils ne lisent que
        ce HTML. $meta est fourni par les contrôleurs (withViewData).
    --}}
    @php($meta = ($meta ?? []) + \App\Support\PageMeta::make(\App\Support\PageMeta::SITE_NAME))

    <title inertia>{{ $meta['title'] }}</title>
    <meta name="description" content="{{ $meta['description'] }}">

    <meta property="og:site_name" content="{{ $meta['site_name'] }}">
    <meta property="og:type" content="{{ $meta['type'] }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    <meta property="og:description" content="{{ $meta['description'] }}">
    <meta property="og:url" content="{{ $meta['url'] }}">
    <meta property="og:locale" content="fr_FR">
    @if ($meta['image'])
        <meta property="og:image" content="{{ $meta['image'] }}">
        <meta property="og:image:secure_url" content="{{ $meta['image'] }}">
        @if (! empty($meta['image_size']))
            <meta property="og:image:width" content="{{ $meta['image_size']['width'] }}">
            <meta property="og:image:height" content="{{ $meta['image_size']['height'] }}">
        @endif
        <meta property="og:image:alt" content="{{ $meta['title'] }}">
    @endif

    <meta name="twitter:card" content="{{ $meta['image'] ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    <meta name="twitter:description" content="{{ $meta['description'] }}">
    @if ($meta['image'])
        <meta name="twitter:image" content="{{ $meta['image'] }}">
    @endif

    {{-- Modern Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @viteReactRefresh
    @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-stone-50 text-zinc-900">
    @inertia
</body>
</html>