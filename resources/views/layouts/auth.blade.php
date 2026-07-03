<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTVIS - Acesso</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <main class="flex min-h-screen items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-black uppercase tracking-widest text-blue-700">NOTVIS</h1>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">Sistema de Gestao</p>
            </div>

            {{ $slot }}
        </div>
    </main>
    @livewireScripts
</body>
</html>
