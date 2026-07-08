@php($usuarioAtual = auth()->user())

<div class="min-h-screen p-8">
    <div class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-4xl font-black uppercase text-slate-800">Sistema de Gestão</h1>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Sistema empresarial NOTVIS</p>
        </div>

        @if($usuarioAtual?->podeAcessar('configuracoes'))
        <a href="{{ route('configuracoes', ['aba' => 'empresa']) }}"
           class="border-l-4 border-slate-800 bg-slate-100 px-4 py-2 transition hover:bg-slate-200"
           title="Abrir configurações da empresa">
            <span class="block text-[10px] font-black uppercase text-slate-400">Empresa ativa</span>
            <span class="text-sm font-black uppercase text-slate-700">{{ $empresaAtiva }}</span>
        </a>
        @endif
    </div>

    <div class="mb-5">
        <h2 class="text-lg font-black uppercase text-slate-800">Módulos do sistema</h2>
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Somente funções já disponíveis para uso na apresentação</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @if($usuarioAtual?->podeAcessar('os'))
        <a href="{{ route('os.nova') }}" class="group rounded-lg border-2 border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-600">
            <div class="mb-4 text-blue-600">
                <svg class="h-11 w-11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <span class="block text-sm font-black uppercase text-slate-700 group-hover:text-blue-700">Ordens de Serviço</span>
            <span class="mt-1 block text-xs font-semibold text-slate-400">Abertura, itens, descontos e finalização.</span>
        </a>
        @endif

        @if($usuarioAtual?->podeAcessar('relatorios'))
        <a href="{{ route('relatorios') }}" class="group rounded-lg border-2 border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-600">
            <div class="mb-4 text-indigo-600">
                <svg class="h-11 w-11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a2 2 0 012-2h8M9 7h10M5 7h.01M5 17h.01M5 12h.01M9 12h10M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z"/></svg>
            </div>
            <span class="block text-sm font-black uppercase text-slate-700 group-hover:text-indigo-700">Relatórios</span>
            <span class="mt-1 block text-xs font-semibold text-slate-400">Fechamentos, vendas, clientes, produtos e estoque.</span>
        </a>
        @endif

        @if($usuarioAtual?->podeAcessar('clientes'))
        <a href="{{ route('clientes.index') }}" class="group rounded-lg border-2 border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-600">
            <div class="mb-4 text-emerald-600">
                <svg class="h-11 w-11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="block text-sm font-black uppercase text-slate-700 group-hover:text-emerald-700">Clientes</span>
            <span class="mt-1 block text-xs font-semibold text-slate-400">Cadastro, edição e histórico do cliente.</span>
        </a>
        @endif

        @if($usuarioAtual?->podeAcessar('produtos'))
        <a href="{{ route('produtos.index') }}" class="group rounded-lg border-2 border-slate-200 bg-white p-5 shadow-sm transition hover:border-orange-600">
            <div class="mb-4 text-orange-600">
                <svg class="h-11 w-11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <span class="block text-sm font-black uppercase text-slate-700 group-hover:text-orange-700">Produtos</span>
            <span class="mt-1 block text-xs font-semibold text-slate-400">Preços, estoque, código de barras e serviços.</span>
        </a>
        @endif

        @if($usuarioAtual?->podeAcessar('estoque'))
        <a href="{{ route('estoque.movimentacoes') }}" class="group rounded-lg border-2 border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-600">
            <div class="mb-4 text-cyan-700">
                <svg class="h-11 w-11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M7 7v14m10-14v14M5 21h14a2 2 0 002-2V7l-3-4H6L3 7v12a2 2 0 002 2zM9 12h6"/></svg>
            </div>
            <span class="block text-sm font-black uppercase text-slate-700 group-hover:text-cyan-700">Movimentação de Estoque</span>
            <span class="mt-1 block text-xs font-semibold text-slate-400">Entrada, saída, ajuste e histórico do estoque.</span>
        </a>
        @endif

        @if($usuarioAtual?->podeAcessar('configuracoes'))
        <a href="{{ route('configuracoes') }}" class="group rounded-lg border-2 border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-800">
            <div class="mb-4 text-slate-700">
                <svg class="h-11 w-11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.607 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <span class="block text-sm font-black uppercase text-slate-700 group-hover:text-slate-900">Configurações</span>
            <span class="mt-1 block text-xs font-semibold text-slate-400">Empresa, funcionários, comercial e aparência.</span>
        </a>
        @endif
    </div>
</div>
