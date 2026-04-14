<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vaulted</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .safe-top { padding-top: env(safe-area-inset-top, 20px); }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 safe-top safe-bottom">
    <div class="max-w-md mx-auto min-h-screen flex flex-col">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
