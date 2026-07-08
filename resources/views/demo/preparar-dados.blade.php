<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados de demonstracao - NOTVIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 px-6 py-10 font-sans text-slate-900">
    <main class="mx-auto max-w-3xl rounded-lg border border-slate-200 bg-white p-8 shadow-sm">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-blue-600">Sistema Notvis</p>
        <h1 class="mt-2 text-3xl font-black uppercase">Dados de demonstracao criados</h1>
        <p class="mt-2 text-sm font-semibold text-slate-500">A base foi preparada para a apresentacao.</p>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            @foreach($resumo as $label => $valor)
                <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase text-slate-500">{{ str_replace('_', ' ', $label) }}</p>
                    <p class="mt-1 text-xl font-black">{{ $valor }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="rounded-md bg-slate-900 px-4 py-2 text-xs font-black uppercase text-white">Tela inicial</a>
            <a href="{{ route('os.nova') }}" class="rounded-md bg-blue-600 px-4 py-2 text-xs font-black uppercase text-white">Ordens de servico</a>
            <a href="{{ route('relatorios') }}" class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-black uppercase text-white">Relatorios</a>
            <a href="{{ route('produtos.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-black uppercase text-slate-700">Produtos</a>
        </div>
    </main>
</body>
</html>
