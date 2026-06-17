<div class="p-8 min-h-screen relative flex flex-col">
    <div class="mb-10 flex justify-between items-start">
        <div>
            <h1 class="text-4xl font-black text-slate-800 uppercase tracking-tighter">Sistema de Gestão</h1>
            <p class="text-slate-500 font-bold uppercase text-xs tracking-widest">Sistemas Empresarial Notvis</p>
        </div>
        <div class="bg-slate-100 border-l-4 border-slate-800 px-4 py-2">
            <span class="text-[10px] font-black text-slate-400 uppercase block">Empresa Ativa</span>
            <span class="text-sm font-black text-slate-700 uppercase">AUTO ELETRICA E ACESSORIOS ROSEIRA</span>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-6">

        <a href="#" class="group bg-white border-2 border-slate-200 hover:border-blue-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-blue-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-blue-600 text-center">Ordens de Serviço</span>
            </div>
        </a>

        <a href="{{ route('clientes.index') }}" class="group bg-white border-2 border-slate-200 hover:border-emerald-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-emerald-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-emerald-600 text-center">Clientes</span>
            </div>
        </a>

        <a href="#" class="group bg-white border-2 border-slate-200 hover:border-indigo-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-indigo-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-indigo-600 text-center">Fornecedores</span>
            </div>
        </a>

        <a href="{{ route('produtos.index') }}" class="group bg-white border-2 border-slate-200 hover:border-orange-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-orange-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-orange-600 text-center">Estoque</span>
            </div>
        </a>

        <a href="#" class="group bg-white border-2 border-slate-200 hover:border-cyan-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-cyan-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-cyan-600 text-center">Entrada Mercadoria</span>
            </div>
        </a>

        <a href="#" class="group bg-white border-2 border-slate-200 hover:border-red-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-red-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-red-600 text-center">Financeiro / Caixa</span>
            </div>
        </a>

        <a href="#" class="group bg-white border-2 border-slate-200 hover:border-purple-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-purple-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-purple-600 text-center">Emissão NF-e</span>
            </div>
        </a>

        <a href="#" class="group bg-white border-2 border-slate-200 hover:border-teal-600 shadow-sm rounded-2xl p-6 transition-all">
            <div class="flex flex-col items-center">
                <div class="mb-3 text-teal-600">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600 group-hover:text-teal-600 text-center">Relatórios</span>
            </div>
        </a>

    </div>
</div>
