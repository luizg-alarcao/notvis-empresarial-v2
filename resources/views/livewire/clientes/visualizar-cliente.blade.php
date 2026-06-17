<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="bg-slate-800 rounded-t-2xl p-8 text-white shadow-xl flex justify-between items-end">
        <div>
            <span class="bg-blue-500 text-xs font-black px-2 py-1 rounded mb-2 inline-block uppercase tracking-widest">Cliente Ativo</span>
            <h1 class="text-4xl font-black uppercase tracking-tight">{{ $cliente->nome }}</h1>
            <p class="text-slate-400 mt-1">
                <span class="font-bold text-slate-200">CPF/CNPJ:</span> {{ $cliente->cpf_cnpj }}
                <span class="mx-2">|</span>
                <span class="font-bold text-slate-200">DESDE:</span> {{ $cliente->created_at->format('d/m/Y') }}
            </p>
        </div>
        <div class="bg-white/10 backdrop-blur-md p-5 rounded-xl border border-white/20 text-right">
            <p class="text-xs font-bold text-blue-300 uppercase mb-1">Limite de Crédito</p>
            <p class="text-3xl font-black text-white font-mono">R$ {{ number_format($cliente->limite_credito, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white border-x flex overflow-hidden">
        @foreach(['dados' => 'Dados Gerais', 'historico' => 'Histórico de Compras', 'financeiro' => 'Financeiro / Contas'] as $key => $label)
            <button wire:click="setAba('{{ $key }}')"
                class="flex-1 py-4 text-sm font-black uppercase tracking-wider transition-all
                {{ $abaAtiva == $key ? 'bg-white text-blue-600 border-b-4 border-blue-600' : 'bg-slate-50 text-slate-400 hover:bg-slate-100 border-b-4 border-transparent' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="bg-white shadow-2xl rounded-b-2xl p-8 mb-6 min-h-75 border-x border-b">
        @if($abaAtiva == 'dados')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div class="space-y-6">
                    <h3 class="text-lg font-black text-slate-800 border-l-4 border-blue-600 pl-3 uppercase">Informações de Contato</h3>
                    <div class="grid gap-4">
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase">WhatsApp / Celular</p>
                            <p class="text-lg font-bold text-slate-700">{{ $cliente->whatsapp ?: 'Não Informado' }}</p>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase">E-mail Principal</p>
                            <p class="text-lg font-bold text-slate-700">{{ $cliente->email ?: 'Não Informado' }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-lg font-black text-slate-800 border-l-4 border-green-600 pl-3 uppercase">Localização</h3>
                    <div class="bg-slate-50 p-6 rounded-lg border border-slate-100">
                        @if($cliente->cep)
                            <div class="space-y-2 text-slate-700">
                                <p class="text-lg font-bold text-slate-700">{{ $cliente->rua }}, {{ $cliente->numero }}</p>
                                <p class="text-lg font-bold text-slate-700">{{ $cliente->bairro }}</p>
                                <p class="text-lg font-bold text-slate-700">{{ $cliente->cidade }} / {{ $cliente->estado }}</p>
                                <p class="text-lg font-bold text-slate-700">CEP: {{ $cliente->cep }}</p>
                            </div>
                        @else
                            <div class="flex items-center justify-center h-full text-slate-400 italic">
                                Endereço não cadastrado.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @elseif($abaAtiva == 'historico')
            <div class="flex flex-col items-center justify-center py-20">
                <div class="bg-slate-100 p-6 rounded-full mb-4">
                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-sm">Nenhuma compra registrada ainda</p>
            </div>
        @endif
    </div>

    <div class="flex justify-between items-center bg-slate-100 p-4 rounded-xl shadow-inner">
        <a href="{{ route('clientes.index') }}" class="px-6 py-2 text-slate-600 font-black uppercase text-xs hover:bg-slate-200 rounded-lg transition">
            ← Voltar para Lista
        </a>
        <a href="{{ route('clientes.editar', $cliente->id) }}" class="bg-orange-500 hover:bg-orange-600 text-white px-8 py-3 rounded-lg font-black uppercase text-sm shadow-lg transition-transform hover:scale-105">
            Editar Cadastro
        </a>
    </div>
</div>
