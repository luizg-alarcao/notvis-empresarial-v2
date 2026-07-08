<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOTVIS - Sistema Empresarial</title>

    <script>
        (function () {
            const theme = localStorage.getItem('notvis-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);

            window.notvisSetTheme = function (nextTheme) {
                localStorage.setItem('notvis-theme', nextTheme);
                document.documentElement.setAttribute('data-theme', nextTheme);
            };
        })();
    </script>
    <style>
        html[data-theme="light"] body {
            background: #f3f6fb;
        }

        html[data-theme="light"] main {
            background: #f4f7fb !important;
        }

        html[data-theme="light"] .bg-white {
            border-color: #d7e0ec;
        }

        html[data-theme="light"] input,
        html[data-theme="light"] select,
        html[data-theme="light"] textarea {
            background-color: #ffffff;
            border-color: #b8c7da !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        html[data-theme="light"] input:focus,
        html[data-theme="light"] select:focus,
        html[data-theme="light"] textarea:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.14), 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        html[data-theme="light"] .shadow-sm {
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.10), 0 1px 2px rgba(15, 23, 42, 0.06) !important;
        }

        html[data-theme="light"] .border-slate-200,
        html[data-theme="light"] .border-gray-200 {
            border-color: #d7e0ec !important;
        }

        html[data-theme="light"] table thead,
        html[data-theme="light"] .bg-slate-100,
        html[data-theme="light"] .bg-slate-50 {
            background-color: #eef3f9;
        }

        .os-workspace {
            min-width: 0;
        }

        .os-items-panel {
            min-height: clamp(260px, 32vh, 360px);
        }

        .os-items-table-wrap {
            overflow-x: hidden;
            overflow-y: auto;
            scrollbar-width: auto;
            scrollbar-gutter: stable;
            overscroll-behavior: contain;
        }

        .os-items-table-wrap thead {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .os-bottom-panel {
            min-height: 150px;
        }

        .os-notes-panel textarea {
            min-height: 105px;
        }

        @media (min-height: 960px) {
            .os-items-panel {
                min-height: 380px;
            }
        }

        @media (max-height: 820px) {
            .os-workspace {
                padding: 0.6rem;
                gap: 0.6rem;
            }

            .os-items-panel {
                min-height: 250px;
            }

            .os-bottom-panel {
                min-height: 140px;
            }

            .os-notes-panel textarea {
                min-height: 90px;
            }
        }

        @media (max-width: 1280px) {
            .os-bottom-panel {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 270px 300px;
            }

            .os-discount-panel,
            .os-total-panel {
                width: auto !important;
            }
        }

        @media (max-width: 1100px) {
            .os-bottom-panel {
                grid-template-columns: 1fr;
            }
        }

        html[data-theme="dark"] body {
            background: #1f2937 !important;
            color: #dbe4ef !important;
            color-scheme: dark;
        }

        html[data-theme="dark"] main,
        html[data-theme="dark"] .bg-slate-50,
        html[data-theme="dark"] .bg-gray-50,
        html[data-theme="dark"] .bg-slate-100 {
            background-color: #273244 !important;
        }

        html[data-theme="dark"] .bg-slate-50\/60,
        html[data-theme="dark"] .bg-white\/60 {
            background-color: rgba(39, 50, 68, 0.72) !important;
        }

        html[data-theme="dark"] .bg-white {
            background-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        html[data-theme="dark"] .bg-gray-100,
        html[data-theme="dark"] .bg-gray-200,
        html[data-theme="dark"] .bg-blue-100,
        html[data-theme="dark"] .bg-emerald-100,
        html[data-theme="dark"] .bg-red-100,
        html[data-theme="dark"] .bg-amber-100,
        html[data-theme="dark"] .bg-yellow-100,
        html[data-theme="dark"] .bg-slate-200 {
            background-color: #3b495f !important;
            color: #e2e8f0 !important;
        }

        html[data-theme="dark"] .bg-slate-900,
        html[data-theme="dark"] aside {
            background-color: #111827 !important;
        }

        html[data-theme="dark"] header {
            background-color: #263244 !important;
            border-color: #475569 !important;
        }

        html[data-theme="dark"] .text-slate-800,
        html[data-theme="dark"] .text-slate-900,
        html[data-theme="dark"] .text-gray-700,
        html[data-theme="dark"] .text-gray-800,
        html[data-theme="dark"] .text-gray-900,
        html[data-theme="dark"] .text-slate-700 {
            color: #e2e8f0 !important;
        }

        html[data-theme="dark"] .text-slate-600,
        html[data-theme="dark"] .text-slate-500,
        html[data-theme="dark"] .text-gray-500,
        html[data-theme="dark"] .text-gray-600 {
            color: #cbd5e1 !important;
        }

        html[data-theme="dark"] .text-slate-400,
        html[data-theme="dark"] .text-gray-400,
        html[data-theme="dark"] .text-gray-300 {
            color: #9fb0c6 !important;
        }

        html[data-theme="dark"] input,
        html[data-theme="dark"] select,
        html[data-theme="dark"] textarea {
            background-color: #1f2937 !important;
            border-color: #64748b !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] input::placeholder,
        html[data-theme="dark"] textarea::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        html[data-theme="dark"] option {
            background-color: #1f2937 !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] table thead,
        html[data-theme="dark"] .bg-slate-100,
        html[data-theme="dark"] .bg-slate-50 {
            background-color: #263244 !important;
        }

        html[data-theme="dark"] .border-slate-200,
        html[data-theme="dark"] .border-slate-100,
        html[data-theme="dark"] .border-gray-100,
        html[data-theme="dark"] .border-gray-200,
        html[data-theme="dark"] .border-gray-300,
        html[data-theme="dark"] .border-slate-300 {
            border-color: #475569 !important;
        }

        html[data-theme="dark"] .divide-y > :not([hidden]) ~ :not([hidden]) {
            border-color: #475569 !important;
        }

        html[data-theme="dark"] .hover\:bg-white:hover,
        html[data-theme="dark"] .hover\:bg-slate-50:hover,
        html[data-theme="dark"] .hover\:bg-slate-100:hover,
        html[data-theme="dark"] .hover\:bg-slate-200:hover,
        html[data-theme="dark"] .hover\:bg-gray-50:hover,
        html[data-theme="dark"] .hover\:bg-gray-100:hover,
        html[data-theme="dark"] tbody tr:hover {
            background-color: #3b495f !important;
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .hover\:bg-blue-50:hover {
            background-color: #1e3a5f !important;
        }

        html[data-theme="dark"] .hover\:bg-red-50:hover {
            background-color: #4b1d2a !important;
        }

        html[data-theme="dark"] .hover\:bg-amber-100:hover,
        html[data-theme="dark"] .hover\:bg-yellow-100:hover {
            background-color: #5a3d15 !important;
        }

        html[data-theme="dark"] .hover\:bg-purple-50:hover,
        html[data-theme="dark"] .hover\:bg-indigo-50:hover {
            background-color: #2f2a5f !important;
        }

        html[data-theme="dark"] .hover\:text-gray-700:hover,
        html[data-theme="dark"] .hover\:text-slate-700:hover,
        html[data-theme="dark"] .hover\:text-slate-900:hover {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .hover\:text-white:hover {
            color: #ffffff !important;
        }

        html[data-theme="dark"] .bg-blue-50 {
            background-color: #17345f !important;
        }

        html[data-theme="dark"] .bg-emerald-50,
        html[data-theme="dark"] .bg-green-50 {
            background-color: #123f35 !important;
        }

        html[data-theme="dark"] .bg-red-50 {
            background-color: #4b1d2a !important;
        }

        html[data-theme="dark"] .bg-orange-50,
        html[data-theme="dark"] .bg-amber-50,
        html[data-theme="dark"] .bg-yellow-50 {
            background-color: #4a3416 !important;
        }

        html[data-theme="dark"] .bg-purple-50,
        html[data-theme="dark"] .bg-indigo-50 {
            background-color: #2f2a5f !important;
        }

        html[data-theme="dark"] .text-blue-700,
        html[data-theme="dark"] .text-blue-600,
        html[data-theme="dark"] .text-blue-500,
        html[data-theme="dark"] .text-blue-400,
        html[data-theme="dark"] .text-blue-300,
        html[data-theme="dark"] .text-blue-900,
        html[data-theme="dark"] .text-blue-800 {
            color: #93c5fd !important;
        }

        html[data-theme="dark"] .text-emerald-700,
        html[data-theme="dark"] .text-emerald-600,
        html[data-theme="dark"] .text-green-700,
        html[data-theme="dark"] .text-green-600,
        html[data-theme="dark"] .text-green-900,
        html[data-theme="dark"] .text-green-800 {
            color: #6ee7b7 !important;
        }

        html[data-theme="dark"] .text-red-700,
        html[data-theme="dark"] .text-red-600,
        html[data-theme="dark"] .text-red-500,
        html[data-theme="dark"] .text-red-400,
        html[data-theme="dark"] .text-red-900,
        html[data-theme="dark"] .text-red-800 {
            color: #fca5a5 !important;
        }

        html[data-theme="dark"] .text-orange-700,
        html[data-theme="dark"] .text-orange-600,
        html[data-theme="dark"] .text-orange-500,
        html[data-theme="dark"] .text-amber-700,
        html[data-theme="dark"] .text-yellow-700 {
            color: #fbbf24 !important;
        }

        html[data-theme="dark"] .text-cyan-700,
        html[data-theme="dark"] .text-cyan-600 {
            color: #67e8f9 !important;
        }

        html[data-theme="dark"] .text-slate-950 {
            color: #f8fafc !important;
        }

        html[data-theme="dark"] .text-purple-700,
        html[data-theme="dark"] .text-indigo-700,
        html[data-theme="dark"] .text-indigo-600 {
            color: #c4b5fd !important;
        }

        html[data-theme="dark"] .border-blue-200,
        html[data-theme="dark"] .border-emerald-200,
        html[data-theme="dark"] .border-green-200,
        html[data-theme="dark"] .border-red-200,
        html[data-theme="dark"] .border-orange-200,
        html[data-theme="dark"] .border-amber-200,
        html[data-theme="dark"] .border-yellow-200,
        html[data-theme="dark"] .border-purple-200,
        html[data-theme="dark"] .border-indigo-200 {
            border-color: #64748b !important;
        }

        html[data-theme="dark"] .shadow-sm,
        html[data-theme="dark"] .shadow,
        html[data-theme="dark"] .shadow-md,
        html[data-theme="dark"] .shadow-lg,
        html[data-theme="dark"] .shadow-xl,
        html[data-theme="dark"] .shadow-2xl {
            box-shadow: 0 10px 24px rgba(2, 6, 23, 0.24), 0 1px 2px rgba(2, 6, 23, 0.28) !important;
        }

        html[data-theme="dark"] ::selection {
            background: #2563eb;
            color: #ffffff;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">

        <aside class="w-64 bg-slate-900 text-white flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6">
                <h1 class="text-2xl font-bold tracking-widest text-blue-400">
                    <a href="/" class="hover:text-white">NOTVIS</a>
                </h1>
                <p class="text-xs text-slate-400 uppercase">Sistemas de Gestão</p>
            </div>

            @php($usuarioAtual = auth()->user())

            <nav class="flex-1 px-4 space-y-2 py-4">
                <a href="/" class="flex items-center space-x-3 p-3 rounded-lg transition {{ request()->routeIs('home') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
                    <span class="font-medium">Tela Inicial</span>
                </a>
                @if($usuarioAtual?->podeAcessar('clientes'))
                    <a href="{{ route('clientes.index') }}" class="flex items-center space-x-3 p-3 rounded-lg transition {{ request()->routeIs('clientes.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
                        <span class="font-medium">Clientes</span>
                    </a>
                @endif
                @if($usuarioAtual?->podeAcessar('os'))
                <a href="{{ route('os.nova') }}" class="flex items-center space-x-3 p-3 rounded-lg transition {{ request()->routeIs('os.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
                    <span class="font-medium">Ordens de Serviço</span>
                </a>
                @endif
                @if($usuarioAtual?->podeAcessar('produtos'))
                    <a href="{{ route('produtos.index') }}" class="flex items-center space-x-3 p-3 rounded-lg transition {{ request()->routeIs('produtos.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
                        <span class="font-medium">Produtos</span>
                    </a>
                @endif
                @if($usuarioAtual?->podeAcessar('estoque'))
                    <a href="{{ route('estoque.movimentacoes') }}" class="flex items-center space-x-3 p-3 rounded-lg transition {{ request()->routeIs('estoque.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
                        <span class="font-medium">Movimentação de Estoque</span>
                    </a>
                @endif
                @if($usuarioAtual?->podeAcessar('relatorios'))
                    <a href="{{ route('relatorios') }}" class="flex items-center space-x-3 p-3 rounded-lg transition {{ request()->routeIs('relatorios') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
                        <span class="font-medium">Relatórios</span>
                    </a>
                @endif
            </nav>

            <div class="p-4 border-t border-slate-800 space-y-3">
                @if($usuarioAtual?->podeAcessar('configuracoes'))
                <a href="{{ route('configuracoes') }}" class="flex items-center space-x-3 p-3 rounded-lg transition {{ request()->routeIs('configuracoes') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 text-slate-300 hover:text-white' }}">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.607 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
                    </svg>
                    <span class="font-medium">Configurações</span>
                </a>
                @endif
                <div class="flex items-center space-x-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center font-bold">{{ mb_substr($usuarioAtual?->name ?? 'U', 0, 1, 'UTF-8') }}</div>
                    <div class="min-w-0">
                        <span class="block truncate font-semibold">{{ $usuarioAtual?->name ?? 'Usuario' }}</span>
                        <span class="block text-[10px] font-bold uppercase text-slate-400">{{ $usuarioAtual?->perfilNome() ?? '' }}</span>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg border border-slate-700 px-3 py-2 text-left text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                        Sair do sistema
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">

            <header class="bg-white shadow-sm border-b border-gray-200 py-3 px-4 flex justify-between items-center shrink-0">
                <h2 class="text-lg font-semibold text-gray-700">Sistema de Gerenciamento</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ date('d/m/Y') }}</span>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto bg-slate-50 relative p-3">
                {{ $slot }}
            </main>

        </div>
    </div>

    @livewireScripts
</body>
</html>
