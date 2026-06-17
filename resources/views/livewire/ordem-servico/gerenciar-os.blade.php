<div class="flex flex-col w-full h-full bg-slate-100 p-3 gap-3 font-sans text-slate-800"
     x-data="{
        init() {
            window.addEventListener('keydown', e => {
                if (e.key === 'F10') { e.preventDefault(); $refs.inputCliente.focus(); }
                if (e.key === 'F6') { e.preventDefault(); $refs.inputBuscaItem.focus(); }
            });
        }
     }">

    <div class="bg-white p-2 rounded shadow-sm border border-slate-200 flex items-center justify-between border-l-4 border-l-slate-800">
        <div class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-2">Cartão (F4):</label>
            <div class="flex items-center gap-1">
                <select wire:change="alternarCartao($event.target.value)" class="text-sm border border-slate-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-slate-500 outline-none w-64 shadow-sm font-semibold text-slate-700">
                    @foreach($listaCartoes as $cartao)
                        <option value="{{ $cartao->id }}" {{ $cartao->id == $os->id ? 'selected' : '' }}>
                            OS #{{ $cartao->id }} {{ $cartao->nome_cartao ? ' - ' . $cartao->nome_cartao : '' }}
                        </option>
                    @endforeach
                </select>
                <button type="button" onclick="let novoNome = prompt('Digite o apelido para a OS #{{ $os->id }}:', '{{ $nome_cartao }}'); if(novoNome !== null) { @this.set('nome_cartao', novoNome); @this.call('salvarCampo', 'nome_cartao'); }" class="bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded px-2.5 py-1.5 text-xs shadow-sm transition-colors">✏️</button>
            </div>
            <button type="button" onclick="let nome = prompt('Digite o nome do cartão:'); if(nome !== null) { @this.call('novoAtendimento', nome); }" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-1.5 rounded text-xs font-bold shadow-sm transition-colors ml-2">
                + Novo Atendimento
            </button>
            <button type="button" wire:click="excluirCartao" wire:confirm="Deseja excluir este cartão?" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded text-xs font-bold shadow-sm transition-colors">Excluir Cartão</button>
        </div>
        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mr-2">SISTEMA NOTVIS • ORDEM DE SERVIÇO</div>
    </div>

    <div class="bg-white p-3 rounded shadow-sm border border-slate-200 flex items-center gap-6">
        <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase block">OS Nº</span>
            <span class="text-lg font-black text-slate-800">#{{ $os->id }}</span>
        </div>
        <div class="h-8 w-px bg-slate-200"></div>
        <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Data Abertura</span>
            <span class="text-sm font-semibold text-slate-700">{{ $os->created_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span>
        </div>
        <div class="h-8 w-px bg-slate-200"></div>

        <div class="w-64">
            <label class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Atendente</label>
            <div class="flex gap-1">
                <input type="text" wire:model="atendente_id" wire:blur="salvarCampo('atendente_id')" placeholder="ID" class="w-12 text-sm border border-slate-300 rounded px-2 py-1.5 text-center font-semibold shadow-sm">
                <select wire:model="atendente_id" wire:change="salvarCampo('atendente_id')" class="flex-1 text-sm border border-slate-300 rounded px-2 py-1.5 outline-none shadow-sm">
                    <option value="">Selecione...</option>
                    <option value="1">Gustavo Henrique</option>
                </select>
            </div>
        </div>
        <div class="ml-auto">
            <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded border border-slate-300 uppercase shadow-sm">{{ $os->status }}</span>
        </div>
    </div>

    <div class="bg-white p-3 rounded shadow-sm border border-slate-200 flex items-start gap-3">
        <div class="flex-1 relative">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Cliente (F10)</label>
            <div class="flex gap-1">
                <input type="number"
                       wire:model.live="cliente_id"
                       placeholder="ID"
                       class="w-20 text-sm border border-slate-300 rounded px-2 py-1.5 text-center shadow-sm font-bold bg-white text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">

                <input type="text"
                       x-ref="inputCliente"
                       wire:model.live="nome_cliente"
                       placeholder="Escreva o nome do cliente para pesquisar..."
                       class="flex-1 text-sm border border-slate-300 rounded px-2 py-1.5 shadow-sm uppercase font-semibold text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">

                <button type="button" wire:click="abrirModalCliente" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 rounded shadow-md transition-all flex items-center justify-center text-sm">
                    🔍
                </button>
            </div>

            @if(!empty($resultadosFiltradosInline))
                <div class="absolute left-24 right-14 mt-1 bg-white border border-slate-200 rounded shadow-xl z-50 max-h-48 overflow-y-auto divide-y divide-slate-100">
                    @foreach($resultadosFiltradosInline as $itemCli)
                        <button type="button" wire:click="selecionarClienteDirect({{ $itemCli['id'] }}, '{{ $itemCli['nome'] }}')" class="w-full text-left px-3 py-2 text-xs font-bold hover:bg-blue-50 text-slate-700 block uppercase">
                            [{{ $itemCli['id'] }}] - {{ $itemCli['nome'] }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="w-48">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Veículo (Opcional)</label>
            <input type="text" wire:model="marca_modelo_veiculo" wire:blur="salvarCampo('marca_modelo_veiculo')" placeholder="MARCA / MODELO" class="w-full text-sm border border-slate-300 rounded px-2 py-1.5 shadow-sm uppercase">
        </div>
        <div class="w-32"> <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Placa (Opcional)</label>
            <input type="text" wire:model="placa_veiculo" wire:blur="salvarCampo('placa_veiculo')" placeholder="ABC-1234" class="w-full text-sm border border-slate-300 rounded px-2 py-1.5 text-center font-bold shadow-sm uppercase">
        </div>
    </div>

    <div class="bg-white rounded shadow-sm border border-slate-200 flex-1 flex flex-col min-h-0 overflow-hidden">

        <div class="p-3 border-b border-slate-100 flex items-center gap-2">
            <input type="text"
                   x-ref="inputBuscaItem"
                   wire:model="buscaProdutoOuCodigo"
                   wire:keydown.enter="processarInsercaoRapida"
                   placeholder="Bipe o código de barras ou digite o ID do item e pressione Enter (F6)..."
                   class="flex-1 text-sm bg-white border border-slate-300 rounded px-3 py-1.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm uppercase font-medium">

            <button type="button" wire:click="abrirModalProduto" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-1.5 rounded text-sm font-bold shadow-md transition-all">
                🔍
            </button>
        </div>

        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm m-3">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-2 text-center w-16">Cód</th>
                        <th class="py-3 px-4">Descrição do Item</th>
                        <th class="py-3 px-2 text-center w-20">Data</th>
                        <th class="py-3 px-2 text-center w-16">Hora</th>
                        <th class="py-3 px-2 text-center w-20">Qtd</th>
                        <th class="py-3 px-2 text-center w-28">Val. Unit</th>
                        <th class="py-3 px-2 text-center w-28">Desconto</th>
                        <th class="py-3 px-4 text-right w-32">Subtotal</th>
                        <th class="py-3 px-2 text-center w-20">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 text-xs bg-white">
                    @forelse($this->os->itens ?? [] as $item)
                        <tr wire:key="item-os-row-{{ $item->id }}"
                            wire:click="selecionarItem({{ $item->id }})"
                            class="transition-all cursor-pointer select-none {{ $itemSelecionadoId == $item->id ? 'bg-blue-50 border-l-4 border-blue-600 font-medium' : 'hover:bg-slate-50' }}">
                            <td class="py-2.5 px-2 text-center font-mono text-slate-400">#{{ $item->produto_id ?? $item->servico_id }}</td>
                            <td class="py-2.5 px-4 text-slate-700 uppercase" wire:dblclick.stop="iniciarEdicao({{ $item->id }}, '{{ $item->descricao }}')">
                                @if($editandoItemId == $item->id)
                                    <input type="text"
                                           wire:model="novoNomeItem"
                                           wire:keydown.enter="salvarNome"
                                           wire:blur="salvarNome"
                                           class="w-full p-1 border border-blue-500 rounded bg-white text-xs uppercase text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 cursor-text"
                                           autofocus />
                                @else
                                    {{ $item->descricao }}
                                @endif
                            </td>
                            <td class="py-2.5 px-2 text-center text-slate-500 font-mono">{{ $item->created_at?->format('d/m/y') }}</td>
                            <td class="py-2.5 px-2 text-center text-slate-500 font-mono">{{ $item->created_at?->format('H:i') }}</td>
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                <input type="number"
                                       value="{{ $item->quantidade }}"
                                       wire:change="atualizarCampo({{ $item->id }}, 'quantidade', $event.target.value)"
                                       class="w-full bg-transparent border-none p-0 text-center font-mono text-xs font-bold text-slate-800 focus:bg-white focus:ring-1 focus:ring-blue-500 rounded cursor-text" />
                            </td>
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                <input type="text"
                                       value="{{ number_format($item->valor_unitario, 2, ',', '') }}"
                                       wire:change="atualizarCampo({{ $item->id }}, 'valor_unitario', $event.target.value)"
                                       class="w-full bg-transparent border-none p-0 text-center font-mono text-xs text-slate-700 focus:bg-white focus:ring-1 focus:ring-blue-500 rounded cursor-text" />
                            </td>
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                <input type="text"
                                       value="{{ $item->valor_desconto > 0 ? number_format($item->valor_desconto, 2, ',', '') : '' }}"
                                       wire:change="atualizarCampo({{ $item->id }}, 'valor_desconto', $event.target.value)"
                                       placeholder="0,00"
                                       class="w-full bg-transparent border-none p-0 text-center font-mono text-xs text-red-500 placeholder-slate-300 focus:bg-white focus:ring-1 focus:ring-blue-500 rounded cursor-text" />
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">R$ {{ number_format($item->valor_total ?? $item->subtotal ?? 0, 2, ',', '.') }}</td>
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                <button type="button"
                                        wire:click="excluirItem({{ $item->id }})"
                                        class="text-red-500 hover:text-red-700 font-bold uppercase text-[10px] tracking-wider transition-colors cursor-pointer p-1 rounded hover:bg-red-50">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-400 font-medium uppercase tracking-wide">Nenhum registro encontrado nesta Ordem de Serviço.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex gap-3 shrink-0 items-stretch">
        <div class="flex-1 bg-white p-3 rounded shadow-sm border border-slate-200 flex flex-col">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Observações Internas / Defeito</label>
            <textarea wire:model="sintoma_reclamacao" wire:blur="salvarCampo('sintoma_reclamacao')" class="w-full h-full text-sm border border-slate-300 rounded focus:border-slate-500 outline-none p-2 shadow-sm resize-none uppercase" placeholder="Adicione os sintomas ou observações do veículo..."></textarea>
        </div>

        <div class="w-64 bg-white p-3 rounded shadow-sm border border-slate-200 flex flex-col gap-2 justify-between">
            <label class="block text-[10px] font-bold text-slate-500 uppercase">Aplicar Desconto</label>
            <select class="w-full text-xs border border-slate-300 rounded px-2 py-1.5 focus:border-slate-500 outline-none shadow-sm">
                <option value="item">No Item Selecionado</option>
            </select>
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">%</span>
                    <input type="number" wire:model.live="desconto_porcento" wire:keyup="aplicarDescontoGeral" class="w-full text-xs border border-slate-300 rounded pl-6 pr-2 py-1.5 text-right outline-none shadow-sm">
                </div>
                <div class="flex-1 relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">R$</span>
                    <input type="number" wire:model.live="desconto_reais" wire:keyup="aplicarDescontoGeral" class="w-full text-xs border border-slate-300 rounded pl-7 pr-2 py-1.5 text-right outline-none shadow-sm">
                </div>
            </div>
            <button type="button" wire:click="aplicarDescontoGeral" class="w-full bg-slate-900 text-white font-bold py-1.5 rounded shadow-sm text-xs">APLICAR</button>
        </div>

        <div class="w-80 bg-white p-4 rounded shadow-sm border border-slate-200 flex flex-col justify-between shrink-0">
            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 text-right space-y-1.5 text-xs">
                <div class="text-slate-600">Itens Adicionados: <span class="font-mono font-bold text-slate-800">{{ $this->os->itens ? $this->os->itens->sum('quantidade') : 0 }}</span></div>
                <div class="text-slate-600">Subtotal: <span class="font-mono font-bold text-slate-800">R$ {{ number_format($this->os->subtotal ?? 0, 2, ',', '.') }}</span></div>
                <div class="text-slate-500">Desconto: <span class="font-mono font-bold text-red-600">- R$ {{ number_format($this->os->desconto ?? 0, 2, ',', '.') }}</span></div>
                <div class="text-sm font-bold text-slate-900 border-t border-slate-200 pt-2 mt-1">TOTAL GERAL: <span class="text-lg text-slate-950 font-mono">R$ {{ number_format($this->os->total ?? $this->os->valor_total ?? 0, 2, ',', '.') }}</span></div>
            </div>
            <div class="flex gap-2 mt-3">
                <button type="button" class="flex-1 bg-slate-900 text-white font-bold py-2 rounded text-xs shadow hover:bg-slate-800 transition-all">IMPRIMIR</button>
                <button type="button" class="flex-1 bg-slate-900 text-white font-bold py-2 rounded text-xs shadow hover:bg-slate-800 transition-all">FINALIZAR (F2)</button>
            </div>
        </div>
    </div>

    @if($modalClienteAberto)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden border border-slate-200">
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center border-b border-slate-800">
                    <div class="flex items-center gap-2 text-white">
                        <span class="text-lg">👥</span>
                        <h3 class="text-sm font-bold uppercase tracking-wider">Localizar Cliente</h3>
                    </div>
                    <button type="button" wire:click="$set('modalClienteAberto', false)" class="text-slate-400 hover:text-white font-medium text-2xl transition-colors outline-none">&times;</button>
                </div>
                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔍</span>
                        <input type="text" wire:model.live="pesquisaCliente" placeholder="DIGITE O NOME DO CLIENTE PARA FILTRAR..." class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-lg pl-9 pr-4 py-2.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm uppercase tracking-wide">
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto min-h-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider sticky top-0 border-b border-slate-200 z-10">
                            <tr>
                                <th class="py-3 px-6 w-24">Código</th>
                                <th class="py-3 px-4">Nome do Cliente</th>
                                <th class="py-3 px-6 text-center w-32">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($listaClientesModal as $c)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-6 font-mono font-bold text-slate-500">#{{ $c->id }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-700 uppercase tracking-wide">{{ $c->nome }}</td>
                                    <td class="py-3.5 px-6 text-center">
                                        <button type="button" wire:click="selecionarClienteDirect({{ $c->id }}, '{{ $c->nome }}')" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 font-bold px-4 py-1.5 rounded-md transition-all uppercase text-[10px] tracking-wider shadow-sm">
                                            Selecionar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-12 text-center text-slate-400 font-medium uppercase tracking-wide text-xs">
                                        Nenhum cliente localizado com este nome.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($modalProdutoAberto)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden border border-slate-200">
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center border-b border-slate-800">
                    <div class="flex items-center gap-2 text-white">
                        <span class="text-lg">📦</span>
                        <h3 class="text-sm font-bold uppercase tracking-wider">Pesquisa de Catálogo (Produtos & Serviços)</h3>
                    </div>
                    <button type="button" wire:click="$set('modalProdutoAberto', false)" class="text-slate-400 hover:text-white font-medium text-2xl transition-colors outline-none">&times;</button>
                </div>
                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">🔍</span>
                        <input type="text" wire:model.live="pesquisaProduto" placeholder="DIGITE O NOME OU PARTE DA DESCRIÇÃO PARA PROCURAR NO ESTOQUE..." class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-lg pl-9 pr-4 py-2.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm uppercase tracking-wide">
                    </div>
                </div>
                <div class="w-full max-h-[55vh] overflow-y-auto block border-t border-slate-200" style="scrollbar-width: auto !important;">
                    <table class="w-full text-left border-collapse table-auto">
                        <thead class="bg-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider sticky top-0 border-b border-slate-200 z-10">
                            <tr>
                                <th class="py-3 px-4 w-24 text-center">Tipo</th>
                                <th class="py-3 px-2 w-16 text-center">Cód.</th>
                                <th class="py-3 px-4">Descrição do Item / Marca</th>
                                <th class="py-3 px-2 text-center w-16">Un.</th>
                                <th class="py-3 px-3 text-center w-20">Estoque</th>
                                <th class="py-3 px-4 text-right w-28">P. À Vista</th>
                                <th class="py-3 px-4 text-right w-28">P. a Prazo</th>
                                <th class="py-3 px-4 text-center w-28">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs bg-white">
                            @foreach($produtosModal as $p)
                                <tr wire:key="prod-modal-{{ $p->id }}"
                                    wire:dblclick="adicionarItem({{ $p->id }}, 'produto', true)"
                                    class="hover:bg-slate-50 transition-colors cursor-pointer select-none group">
                                    <td class="py-2.5 px-4 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black rounded bg-blue-50 text-blue-700 border border-blue-200 block">PRODUTO</span>
                                    </td>
                                    <td class="py-2.5 px-2 font-mono font-bold text-slate-500 text-center">#{{ $p->id }}</td>
                                    <td class="py-2.5 px-4 font-semibold text-slate-700 uppercase tracking-wide group-hover:text-blue-600">
                                        {{ $p->nome }}
                                        @if($p->marca) <span class="text-[10px] font-normal text-slate-400 block font-mono">MARCA: {{ $p->marca }}</span> @endif
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono text-slate-500 uppercase">{{ $p->unidade ?? 'UN' }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-600">{{ number_format($p->estoque_current ?? $p->estoque_atual ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-emerald-600 bg-emerald-50/20">
                                        R$ {{ number_format($p->preco_venda_vista ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-blue-600 bg-blue-50/20">
                                        R$ {{ number_format($p->preco_venda_prazo ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        <button type="button" wire:click="adicionarItem({{ $p->id }}, 'produto', false)" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 font-bold px-3 py-1.5 rounded-md text-[10px] w-full">
                                            + Incluir
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            @foreach($servicosModal as $s)
                                <tr wire:key="serv-modal-{{ $s->id }}" wire:dblclick="adicionarItem({{ $s->id }}, 'servico', true)" class="hover:bg-slate-50 transition-colors cursor-pointer select-none group">
                                    <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 text-[9px] font-black rounded bg-purple-50 text-purple-700 border border-purple-200 block">SERVIÇO</span></td>
                                    <td class="py-2.5 px-2 font-mono font-bold text-slate-500 text-center">#{{ $s->id }}</td>
                                    <td class="py-2.5 px-4 font-semibold text-slate-700 uppercase tracking-wide group-hover:text-purple-600">{{ $s->descricao }}</td>
                                    <td class="py-2.5 px-2 text-center text-slate-300">-</td>
                                    <td class="py-2.5 px-3 text-center text-slate-300">-</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-purple-600 bg-purple-50/20">R$ {{ number_format($s->preco ?? $s->valor ?? 0, 2, ',', '.') }}</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-purple-600 bg-purple-50/20">R$ {{ number_format($s->preco ?? $s->valor ?? 0, 2, ',', '.') }}</td>
                                    <td class="py-2.5 px-4 text-center">
                                        <button type="button" wire:click="adicionarItem({{ $s->id }}, 'servico', false)" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 font-bold px-3 py-1.5 rounded-md text-[10px] w-full">
                                            + Incluir
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
