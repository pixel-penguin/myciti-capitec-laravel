<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shuttle Tracking</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="max-w-4xl mx-auto px-6 py-16">
        <h1 class="text-2xl font-semibold mb-4">Live Shuttle Tracking</h1>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-slate-600 mb-4">Tracking data will render here from the real-time device feed.</p>
            <div class="h-72 rounded border border-dashed border-slate-300 flex items-center justify-center text-slate-400">
                Map Placeholder
            </div>
        </div>
    </div>
</body>
</html>
