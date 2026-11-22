<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #E9EDEF; color: #000;
               font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 10; display: flex; flex-wrap: wrap; gap: .5rem;
                   justify-content: center; align-items: center; padding: .75rem; background: #1F4E5F; }
        .toolbar button, .toolbar a { border: 0; border-radius: 999px; padding: .55rem 1.2rem; font-weight: 600;
                   font-size: .9rem; cursor: pointer; text-decoration: none; background: #fff; color: #1F4E5F; }
        .toolbar .primary { background: #F28C28; color: #1B1B1B; }
        .toolbar .info { color: #fff; font-size: .9rem; }
        @media print { .no-print { display: none !important; } body { background: #fff; } }
    </style>
    @stack('css')
</head>
<body>
    @yield('toolbar')
    @yield('content')
</body>
</html>