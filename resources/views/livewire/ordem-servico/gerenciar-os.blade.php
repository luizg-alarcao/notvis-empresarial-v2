<div class="os-workspace flex flex-col w-full h-full bg-slate-100 p-3 gap-3 font-sans text-slate-800"
     x-data="{
        init() {
            window.addEventListener('keydown', e => {
                 if (e.key === 'F10') { e.preventDefault(); $refs.inputCliente.focus(); }
                 if (e.key === 'F6') { e.preventDefault(); $refs.inputBuscaItem.focus(); }
                 if (e.key === 'F2') { e.preventDefault(); $wire.abrirFinalizacao(); }
             });
         }
      }">

    <div class="bg-white p-2 rounded shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-2 border-l-4 border-l-slate-800">
        <div class="flex flex-wrap items-center gap-2 min-w-0">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-2">Cartão (F4):</label>
            <div class="flex items-center gap-1">
                <select wire:change="alternarCartao($event.target.value)" class="text-sm border border-slate-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-slate-500 outline-none w-64 shadow-sm font-semibold text-slate-700">
                    @if(!$os)
                        <option value="">Nova OS ainda não salva</option>
                    @endif
                    @foreach($listaCartoes as $cartao)
                        <option value="{{ $cartao->id }}" {{ $os && $cartao->id == $os->id ? 'selected' : '' }}>
                            OS #{{ $cartao->id }} {{ $cartao->nome_cartao ? ' - ' . $cartao->nome_cartao : '' }}
                        </option>
                    @endforeach
                </select>
                <button type="button" onclick="let novoNome = prompt('Digite o apelido para a OS {{ $os ? '#' . $os->id : 'nova' }}:', '{{ $nome_cartao }}'); if(novoNome !== null) { @this.set('nome_cartao', novoNome); @this.call('salvarCampo', 'nome_cartao'); }" class="bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded px-2.5 py-1.5 text-xs shadow-sm transition-colors">✏️</button>
            </div>
            <button type="button" onclick="let nome = prompt('Digite o nome do cartão:'); if(nome !== null) { @this.call('novoAtendimento', nome); }" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-1.5 rounded text-xs font-bold shadow-sm transition-colors ml-2">
                + Novo Atendimento
            </button>
            <button type="button" wire:click="excluirCartao" wire:confirm="Deseja excluir este cartão?" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded text-xs font-bold shadow-sm transition-colors">Excluir Cartão</button>
            <a href="{{ route('relatorios') }}" class="relative inline-flex items-center bg-blue-700 hover:bg-blue-800 text-transparent px-4 py-1.5 rounded text-xs font-bold shadow-sm transition-colors">
                <span class="absolute inset-0 flex items-center justify-center text-white">Relatorios</span>
                Cartões Fechados
            </a>
        </div>
        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mr-2">SISTEMA NOTVIS • ORDEM DE SERVIÇO</div>
    </div>

    <div class="bg-white p-3 rounded shadow-sm border border-slate-200 flex flex-wrap items-center gap-6">
        <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase block">OS Nº</span>
            <span class="text-lg font-black text-slate-800">{{ $os ? '#' . $os->id : 'NOVA' }}</span>
        </div>
        <div class="h-8 w-px bg-slate-200"></div>
        <div>
            <span class="text-[10px] text-slate-400 font-bold uppercase block">Data Abertura</span>
            <span class="text-sm font-semibold text-slate-700">{{ $os?->created_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span>
        </div>
        <div class="h-8 w-px bg-slate-200"></div>

        <div class="w-64">
            <label class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Atendente</label>
            <div class="flex gap-1">
                <input type="text" wire:model="atendente_id" wire:focus="garantirOsCriada" wire:blur="salvarCampo('atendente_id')" placeholder="ID" class="w-12 text-sm border border-slate-300 rounded px-2 py-1.5 text-center font-semibold shadow-sm">
                <select wire:model="atendente_id" wire:focus="garantirOsCriada" wire:change="salvarCampo('atendente_id')" class="flex-1 text-sm border border-slate-300 rounded px-2 py-1.5 outline-none shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach($atendentes as $atendente)
                        <option value="{{ $atendente->id }}">{{ $atendente->nome }}</option>
                    @endforeach
                    @if($atendentes->isEmpty())
                        <option value="" disabled>Nenhum atendente cadastrado</option>
                    @endif
                </select>
            </div>
        </div>
        <div class="ml-auto">
            <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded border border-slate-300 uppercase shadow-sm">{{ $os->status ?? 'NOVA' }}</span>
        </div>
    </div>

    <div class="bg-white p-3 rounded shadow-sm border border-slate-200 flex flex-wrap items-start gap-3">
        <div class="flex-1 min-w-[420px] relative">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Cliente (F10)</label>
            <div class="flex gap-1">
                <input type="number"
                       wire:model.live="cliente_id"
                       wire:focus="garantirOsCriada"
                       placeholder="ID"
                       class="w-20 text-sm border border-slate-300 rounded px-2 py-1.5 text-center shadow-sm font-bold bg-white text-slate-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">

                <input type="text"
                       x-ref="inputCliente"
                       wire:model.live="nome_cliente"
                       wire:focus="garantirOsCriada"
                       placeholder="Escreva o nome do cliente para pesquisar..."
                       class="flex-1 text-sm border border-slate-300 rounded px-2 py-1.5 shadow-sm uppercase font-semibold text-slate-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">

                <button type="button" wire:click="abrirModalCliente" title="Buscar cliente" class="inline-flex h-[34px] w-12 items-center justify-center rounded-md border border-blue-200 bg-white text-blue-700 shadow-sm transition hover:border-blue-500 hover:bg-blue-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="m21 21-4.35-4.35m1.1-5.15a6.25 6.25 0 1 1-12.5 0 6.25 6.25 0 0 1 12.5 0z"></path>
                    </svg>
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

        <div class="w-48 min-w-[180px]">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Veículo (Opcional)</label>
            <input type="text" wire:model="marca_modelo_veiculo" wire:focus="garantirOsCriada" wire:blur="salvarCampo('marca_modelo_veiculo')" placeholder="MARCA / MODELO" class="w-full text-sm border border-slate-300 rounded px-2 py-1.5 shadow-sm uppercase">
        </div>
        <div class="w-32 min-w-[130px]"> <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Placa (Opcional)</label>
            <input type="text" wire:model="placa_veiculo" wire:focus="garantirOsCriada" wire:blur="salvarCampo('placa_veiculo')" placeholder="ABC-1234" class="w-full text-sm border border-slate-300 rounded px-2 py-1.5 text-center font-bold shadow-sm uppercase">
        </div>
    </div>

    <div class="os-items-panel bg-white rounded shadow-sm border border-slate-200 flex-1 flex flex-col min-h-0 overflow-hidden">

        <div class="p-3 border-b border-slate-100 flex items-center gap-2">
            <input type="text"
                   x-ref="inputBuscaItem"
                   wire:model="buscaProdutoOuCodigo"
                   wire:focus="garantirOsCriada"
                   wire:keydown.enter="processarInsercaoRapida"
                   placeholder="Bipe o código de barras, digite o ID do produto ou 2 para serviço avulso (F6)..."
                   class="flex-1 text-sm bg-white border border-slate-300 rounded px-3 py-1.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm uppercase font-medium">

            <button type="button" wire:click="abrirModalServicoManual" title="Adicionar serviço avulso" class="inline-flex h-[34px] items-center justify-center rounded-md border border-purple-200 bg-white px-3 text-[10px] font-black uppercase text-purple-700 shadow-sm transition hover:border-purple-500 hover:bg-purple-50">
                Serviço
            </button>

            <button type="button" wire:click="abrirModalProduto" title="Buscar produto ou serviço" class="inline-flex h-[34px] w-14 items-center justify-center rounded-md border border-blue-200 bg-white text-blue-700 shadow-sm transition hover:border-blue-500 hover:bg-blue-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="m21 21-4.35-4.35m1.1-5.15a6.25 6.25 0 1 1-12.5 0 6.25 6.25 0 0 1 12.5 0z"></path>
                </svg>
            </button>
        </div>

        @if (session('error'))
            <div class="mx-3 mt-2 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold uppercase text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="os-items-table-wrap flex-1 min-h-0 bg-white rounded-lg border border-slate-200 overflow-y-auto overflow-x-hidden shadow-sm m-3">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-2 text-center w-16">Cód</th>
                        <th class="py-3 px-4">Descrição do Item</th>
                        <th class="py-3 px-2 text-center w-24">Preço</th>
                        <th class="py-3 px-2 text-center w-20">Qtd</th>
                        <th class="py-3 px-2 text-center w-28">Val. Unit</th>
                        <th class="py-3 px-2 text-center w-28">Desconto</th>
                        <th class="py-3 px-4 text-right w-32">Subtotal</th>
                        <th class="py-3 px-2 text-center w-20">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 text-xs bg-white">
                    @forelse($itensDaOs as $item)
                        @php
                            $valorBrutoItem = (float) $item->quantidade * (float) $item->valor_unitario;
                            $descontoInformado = (float) ($item->desconto_valor ?? 0);
                            $descontoAplicado = 0;

                            if ($descontoInformado > 0) {
                                $descontoAplicado = ($item->desconto_tipo ?? 'VALOR') === 'PORCENTAGEM'
                                    ? $valorBrutoItem * ($descontoInformado / 100)
                                    : $descontoInformado;
                            }

                            $descontoAplicado = min($valorBrutoItem, $descontoAplicado);
                            $subtotalItem = max(0, $valorBrutoItem - $descontoAplicado);
                            $produtoItem = $item->produto;
                            $tipoPrecoAtual = 'prazo';
                            if ($produtoItem && (float) ($produtoItem->preco_venda_vista ?? 0) > 0 && abs((float) $item->valor_unitario - (float) $produtoItem->preco_venda_vista) <= 0.01) {
                                $tipoPrecoAtual = 'vista';
                            }
                        @endphp
                        <tr wire:key="item-os-row-{{ $item->id }}"
                            wire:click="selecionarItem({{ $item->id }})"
                            class="transition-all cursor-pointer select-none {{ $itemSelecionadoId == $item->id ? 'bg-blue-50 border-l-4 border-blue-600 font-medium' : 'hover:bg-slate-50' }}">
                            <td class="py-2.5 px-2 text-center font-mono text-slate-400">#{{ $item->produto_id ?? '-' }}</td>
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
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                @if($produtoItem)
                                    <select wire:change="alterarTipoPrecoItem({{ $item->id }}, $event.target.value)" class="w-full rounded border border-slate-200 bg-white px-1 py-1 text-[10px] font-bold uppercase text-slate-600 outline-none focus:border-blue-500">
                                        <option value="prazo" {{ $tipoPrecoAtual === 'prazo' ? 'selected' : '' }}>A prazo</option>
                                        <option value="vista" {{ $tipoPrecoAtual === 'vista' ? 'selected' : '' }}>À vista</option>
                                    </select>
                                @else
                                    <span class="text-[10px] font-bold uppercase text-slate-400">Serviço</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                <input type="number"
                                       min="0.001"
                                       step="0.001"
                                       value="{{ $item->quantidade }}"
                                       wire:input.debounce.350ms="atualizarCampo({{ $item->id }}, 'quantidade', $event.target.value)"
                                       wire:change="atualizarCampo({{ $item->id }}, 'quantidade', $event.target.value)"
                                       class="w-full bg-transparent border-none p-0 text-center font-mono text-xs font-bold text-slate-800 focus:bg-white focus:ring-1 focus:ring-blue-500 rounded cursor-text" />
                            </td>
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                <input type="text"
                                       value="{{ number_format($item->valor_unitario, 2, ',', '') }}"
                                       wire:input.debounce.350ms="atualizarCampo({{ $item->id }}, 'valor_unitario', $event.target.value)"
                                       wire:change="atualizarCampo({{ $item->id }}, 'valor_unitario', $event.target.value)"
                                       class="w-full bg-transparent border-none p-0 text-center font-mono text-xs text-slate-700 focus:bg-white focus:ring-1 focus:ring-blue-500 rounded cursor-text" />
                            </td>
                            <td class="py-2.5 px-2 text-center" wire:click.stop>
                                <input type="text"
                                       value="{{ $descontoAplicado > 0 ? number_format($descontoAplicado, 2, ',', '') : '' }}"
                                       wire:input.debounce.350ms="atualizarCampo({{ $item->id }}, 'desconto_valor', $event.target.value)"
                                       wire:change="atualizarCampo({{ $item->id }}, 'desconto_valor', $event.target.value)"
                                       placeholder="0,00"
                                       class="w-full bg-transparent border-none p-0 text-center font-mono text-xs text-red-500 placeholder-slate-300 focus:bg-white focus:ring-1 focus:ring-blue-500 rounded cursor-text" />
                                @if(($item->desconto_tipo ?? null) === 'PORCENTAGEM' && $descontoInformado > 0)
                                    <div class="mt-0.5 text-[9px] font-bold text-red-400">{{ number_format($descontoInformado, 2, ',', '') }}%</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">R$ {{ number_format($subtotalItem, 2, ',', '.') }}</td>
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
                            <td colspan="8" class="p-8 text-center text-slate-400 font-medium uppercase tracking-wide">Nenhum registro encontrado nesta Ordem de Serviço.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="os-bottom-panel flex gap-3 shrink-0 items-stretch">
        <div class="os-notes-panel flex-1 bg-white p-2.5 rounded shadow-sm border border-slate-200 flex flex-col min-w-0">
            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Observações Internas / Defeito</label>
            <textarea wire:model="sintoma_reclamacao" wire:focus="garantirOsCriada" wire:blur="salvarCampo('sintoma_reclamacao')" class="w-full h-full text-sm border border-slate-300 rounded focus:border-slate-500 outline-none p-2 shadow-sm resize-none uppercase" placeholder="Adicione os sintomas ou observações do veículo..."></textarea>
        </div>

        <div class="os-discount-panel w-72 bg-white p-2.5 rounded shadow-sm border border-slate-200 flex flex-col gap-1.5 justify-between">
            <label class="block text-[10px] font-bold text-slate-500 uppercase">Aplicar Desconto</label>
            <select wire:model.live="desconto_alvo" class="w-full text-xs border border-slate-300 rounded px-2 py-1.5 focus:border-slate-500 outline-none shadow-sm">
                <option value="total">No total da OS</option>
                <option value="item">Somente no item selecionado</option>
            </select>
            <label class="block text-[10px] font-bold text-slate-500 uppercase">Preço padrão novos itens</label>
            <select wire:model.live="tipo_preco_produto" class="w-full text-xs border border-slate-300 rounded px-2 py-1.5 focus:border-slate-500 outline-none shadow-sm">
                <option value="prazo">A prazo / valor cheio</option>
                <option value="vista">À vista</option>
            </select>
            <div class="flex gap-2">
                <div class="flex-1 relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">%</span>
                    <input type="text" wire:model.live="desconto_porcento" wire:focus="garantirOsCriada" wire:keyup="aplicarDescontoGeral" class="w-full text-xs border border-slate-300 rounded pl-6 pr-2 py-1.5 text-right outline-none shadow-sm">
                </div>
                <div class="flex-1 relative">
                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">R$</span>
                    <input type="text" wire:model.live="desconto_reais" wire:focus="garantirOsCriada" wire:keyup="aplicarDescontoGeral" class="w-full text-xs border border-slate-300 rounded pl-7 pr-2 py-1.5 text-right outline-none shadow-sm">
                </div>
            </div>
            <button type="button" wire:click="aplicarDescontoGeral" class="w-full bg-slate-900 text-white font-bold py-1.5 rounded shadow-sm text-xs">APLICAR</button>
        </div>

        <div class="os-total-panel w-80 bg-white p-2.5 rounded shadow-sm border border-slate-200 flex flex-col justify-between shrink-0 gap-2">
            <select wire:model="forma_pagamento" wire:focus="garantirOsCriada" wire:change="salvarCampo('forma_pagamento')" class="w-full rounded border border-slate-300 bg-white px-2 py-1.5 text-xs font-bold uppercase text-slate-600 outline-none focus:border-blue-500">
                <option value="">Forma de pagamento</option>
                <option value="DINHEIRO">Dinheiro</option>
                <option value="PIX">Pix</option>
                <option value="CARTAO_DEBITO">Cartão débito</option>
                <option value="CARTAO_CREDITO">Cartão crédito</option>
                <option value="BOLETO">Boleto</option>
                <option value="PRAZO">A prazo</option>
            </select>
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-right space-y-1 text-xs">
                <div class="text-slate-600">Itens Adicionados: <span class="font-mono font-bold text-slate-800">{{ $itensDaOs->sum('quantidade') }}</span></div>
                <div class="text-slate-600">Subtotal: <span class="font-mono font-bold text-slate-800">R$ {{ number_format($subtotal, 2, ',', '.') }}</span></div>
                <div class="text-slate-500">Desconto: <span class="font-mono font-bold text-red-600">- R$ {{ number_format($desconto_reais, 2, ',', '.') }}</span></div>
                <div class="text-sm font-bold text-slate-900 border-t border-slate-200 pt-1.5 mt-1">TOTAL GERAL: <span class="text-lg text-slate-950 font-mono">R$ {{ number_format($total_geral, 2, ',', '.') }}</span></div>
            </div>
            <div class="flex gap-2">
                @if($os)
                    <a href="{{ route('os.comprovante', ['ordemServico' => $os->id, 'tipo' => 'orcamento']) }}" target="_blank" class="flex-1 bg-slate-900 text-center text-white font-bold py-2 rounded text-xs shadow hover:bg-slate-800 transition-all">IMPRIMIR</a>
                @else
                    <button type="button" disabled class="flex-1 bg-slate-300 text-white font-bold py-2 rounded text-xs shadow">IMPRIMIR</button>
                @endif
                <button type="button" wire:click="abrirFinalizacao" class="flex-1 bg-emerald-700 text-white font-bold py-2 rounded text-xs shadow hover:bg-emerald-800 transition-all">FINALIZAR (F2)</button>
            </div>
        </div>
    </div>

    @if($modalCartoesFechadosAberto)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-7xl max-h-[92vh] flex flex-col overflow-hidden border border-slate-200">
                <div class="bg-slate-900 px-5 py-4 flex justify-between items-center border-b border-slate-800">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-white">Central de Cartões Fechados</h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Consulta, comprovantes, devoluções e cancelamentos</p>
                    </div>
                    <button type="button" wire:click="$set('modalCartoesFechadosAberto', false)" class="text-slate-400 hover:text-white font-medium text-2xl transition-colors outline-none">&times;</button>
                </div>

                <div class="border-b border-slate-200 bg-slate-50 p-4">
                    <div class="grid grid-cols-1 gap-2 xl:grid-cols-[1.2fr_.9fr_.8fr_.7fr_.9fr_.55fr_.55fr_.65fr_.65fr_auto]">
                        <input type="text" wire:model.live.debounce.350ms="fechados_busca" placeholder="Busca geral: OS, cliente, placa, item..." class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                        <input type="text" wire:model.live.debounce.350ms="fechados_empresa" placeholder="Empresa" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                        <input type="text" wire:model.live.debounce.350ms="fechados_cliente" placeholder="Cliente" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                        <input type="text" wire:model.live.debounce.350ms="fechados_placa" placeholder="Placa" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                        <input type="text" wire:model.live.debounce.350ms="fechados_produto" placeholder="Produto/serviço" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                        <input type="date" wire:model.live="fechados_data_inicio" class="rounded-md border border-slate-300 px-2 py-2 text-xs font-bold outline-none focus:border-blue-500">
                        <input type="date" wire:model.live="fechados_data_fim" class="rounded-md border border-slate-300 px-2 py-2 text-xs font-bold outline-none focus:border-blue-500">
                        <select wire:model.live="fechados_status" class="rounded-md border border-slate-300 px-2 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                            <option value="FINALIZADO">Finalizados</option>
                            <option value="CANCELADO">Cancelados</option>
                            <option value="TODOS">Todos</option>
                        </select>
                        <select wire:model.live="fechados_pagamento" class="rounded-md border border-slate-300 px-2 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                            <option value="">Pagamento</option>
                            <option value="DINHEIRO">Dinheiro</option>
                            <option value="PIX">Pix</option>
                            <option value="CARTAO_DEBITO">Débito</option>
                            <option value="CARTAO_CREDITO">Crédito</option>
                            <option value="BOLETO">Boleto</option>
                            <option value="PRAZO">Prazo</option>
                        </select>
                        <button type="button" wire:click="limparFiltrosFechados" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-100">
                            Limpar
                        </button>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 lg:grid-cols-4">
                        <div class="rounded-md border border-slate-200 bg-white p-3">
                            <span class="block text-[10px] font-black uppercase text-slate-400">Cartões</span>
                            <strong class="font-mono text-lg text-slate-800">{{ $resumoFechados['quantidade'] }}</strong>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-white p-3">
                            <span class="block text-[10px] font-black uppercase text-slate-400">Total vendido</span>
                            <strong class="font-mono text-lg text-emerald-700">R$ {{ number_format($resumoFechados['total'], 2, ',', '.') }}</strong>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-white p-3">
                            <span class="block text-[10px] font-black uppercase text-slate-400">Em aberto</span>
                            <strong class="font-mono text-lg text-red-600">R$ {{ number_format($resumoFechados['pendente'], 2, ',', '.') }}</strong>
                        </div>
                        <div class="rounded-md border border-slate-200 bg-white p-3">
                            <span class="block text-[10px] font-black uppercase text-slate-400">Cancelados</span>
                            <strong class="font-mono text-lg text-slate-700">{{ $resumoFechados['cancelados'] }}</strong>
                        </div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="mx-4 mt-3 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold uppercase text-emerald-700">{{ session('success') }}</div>
                @endif
                @if (session('info'))
                    <div class="mx-4 mt-3 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold uppercase text-blue-700">{{ session('info') }}</div>
                @endif
                @if (session('error'))
                    <div class="mx-4 mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold uppercase text-red-700">{{ session('error') }}</div>
                @endif

                <div class="grid flex-1 min-h-0 grid-cols-1 xl:grid-cols-[minmax(0,1fr)_420px]">
                    <div class="min-h-0 overflow-y-auto border-r border-slate-200">
                        <div class="sticky top-0 z-10 grid grid-cols-[80px_minmax(180px,1.2fr)_120px_100px_120px_120px_110px] gap-3 border-b border-slate-200 bg-slate-100 px-4 py-3 text-[10px] font-black uppercase tracking-wide text-slate-500">
                            <div>OS</div>
                            <div>Cliente</div>
                            <div>Placa</div>
                            <div>Status</div>
                            <div>Pagamento</div>
                            <div class="text-right">Total</div>
                            <div>Fechamento</div>
                        </div>
                        <div class="divide-y divide-slate-100 text-xs">
                            @forelse($cartoesFechados as $fechado)
                                <button type="button" wire:click="selecionarCartaoFechado({{ $fechado->id }})" class="grid w-full grid-cols-[80px_minmax(180px,1.2fr)_120px_100px_120px_120px_110px] gap-3 px-4 py-3 text-left transition {{ $cartaoFechadoSelecionado?->id === $fechado->id ? 'bg-blue-50 ring-1 ring-inset ring-blue-200' : 'bg-white hover:bg-slate-50' }}">
                                    <div class="font-mono font-black text-slate-700">#{{ $fechado->id }}</div>
                                    <div class="min-w-0">
                                        <div class="truncate font-black uppercase text-slate-700">{{ $fechado->cliente->nome ?? 'CONSUMIDOR' }}</div>
                                        <div class="truncate text-[10px] font-bold uppercase text-slate-400">{{ $fechado->marca_modelo_veiculo ?: 'Sem veículo' }}</div>
                                    </div>
                                    <div class="font-mono font-bold uppercase text-slate-600">{{ $fechado->placa_veiculo ?: '-' }}</div>
                                    <div>
                                        <span class="rounded-full px-2 py-1 text-[9px] font-black uppercase {{ $fechado->status === 'CANCELADO' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $fechado->status }}</span>
                                    </div>
                                    <div class="font-bold uppercase text-slate-600">{{ $fechado->forma_pagamento ?: '-' }}</div>
                                    <div class="text-right font-mono font-black text-slate-800">R$ {{ number_format($fechado->valor_total_liquido ?? 0, 2, ',', '.') }}</div>
                                    <div class="text-[10px] font-bold text-slate-500">{{ $fechado->finalizado_em ? \Carbon\Carbon::parse($fechado->finalizado_em)->format('d/m/Y') : $fechado->updated_at?->format('d/m/Y') }}</div>
                                </button>
                            @empty
                                <div class="p-12 text-center text-xs font-bold uppercase tracking-wide text-slate-400">
                                    Nenhum cartão fechado encontrado.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="min-h-0 overflow-y-auto bg-slate-50 p-4">
                        @if($cartaoFechadoSelecionado)
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h4 class="text-lg font-black uppercase text-slate-800">OS #{{ $cartaoFechadoSelecionado->id }}</h4>
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $cartaoFechadoSelecionado->cliente->nome ?? 'CONSUMIDOR' }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $cartaoFechadoSelecionado->status === 'CANCELADO' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $cartaoFechadoSelecionado->status }}</span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                    <div class="rounded-md bg-slate-50 p-3"><span class="block font-black uppercase text-slate-400">Total</span><strong class="font-mono text-slate-800">R$ {{ number_format($cartaoFechadoSelecionado->valor_total_liquido ?? 0, 2, ',', '.') }}</strong></div>
                                    <div class="rounded-md bg-slate-50 p-3"><span class="block font-black uppercase text-slate-400">Vencimento</span><strong class="text-slate-800">{{ $cartaoFechadoSelecionado->data_vencimento ? \Carbon\Carbon::parse($cartaoFechadoSelecionado->data_vencimento)->format('d/m/Y') : '-' }}</strong></div>
                                    <div class="rounded-md bg-slate-50 p-3"><span class="block font-black uppercase text-slate-400">Pagamento</span><strong class="text-slate-800">{{ $cartaoFechadoSelecionado->forma_pagamento ?: '-' }}</strong></div>
                                    <div class="rounded-md bg-slate-50 p-3"><span class="block font-black uppercase text-slate-400">Placa</span><strong class="font-mono text-slate-800">{{ $cartaoFechadoSelecionado->placa_veiculo ?: '-' }}</strong></div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    @if($cartaoFechadoSelecionado->status === 'FINALIZADO')
                                        <button type="button" wire:click="reabrirCartaoFechado({{ $cartaoFechadoSelecionado->id }})" class="rounded-md bg-blue-600 px-3 py-2 text-xs font-black uppercase text-white shadow hover:bg-blue-700">Voltar para edição</button>
                                    @else
                                        <button type="button" disabled class="rounded-md bg-slate-200 px-3 py-2 text-xs font-black uppercase text-slate-500">Voltar para edição</button>
                                    @endif
                                    <a href="{{ route('os.comprovante', ['ordemServico' => $cartaoFechadoSelecionado->id, 'tipo' => 'comprovante']) }}" target="_blank" class="rounded-md bg-slate-900 px-3 py-2 text-center text-xs font-black uppercase text-white shadow hover:bg-slate-800">Comprovante</a>
                                    <button type="button" disabled class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-black uppercase text-slate-400">NFE</button>
                                    @if($cartaoFechadoSelecionado->status === 'FINALIZADO')
                                        <button type="button" wire:click="abrirCancelamentoVenda({{ $cartaoFechadoSelecionado->id }})" class="rounded-md bg-red-600 px-3 py-2 text-xs font-black uppercase text-white shadow hover:bg-red-700">Cancelar venda</button>
                                    @else
                                        <button type="button" disabled class="rounded-md bg-slate-200 px-3 py-2 text-xs font-black uppercase text-slate-500">Venda cancelada</button>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 rounded-lg border border-slate-200 bg-white shadow-sm">
                                <div class="border-b border-slate-200 px-4 py-3">
                                    <h5 class="text-xs font-black uppercase text-slate-600">Itens da OS</h5>
                                </div>
                                <div class="max-h-80 overflow-y-auto">
                                    @foreach($cartaoFechadoSelecionado->itens as $itemFechado)
                                        @php
                                            $devolvido = (float) ($itemFechado->quantidade_devolvida ?? 0);
                                            $disponivel = max(0, (float) $itemFechado->quantidade - $devolvido);
                                        @endphp
                                        <div class="grid grid-cols-[1fr_70px_90px] gap-2 border-b border-slate-100 px-4 py-3 text-xs">
                                            <div class="min-w-0">
                                                <div class="truncate font-black uppercase text-slate-700">{{ $itemFechado->descricao }}</div>
                                                <div class="mt-1 text-[10px] font-bold uppercase text-slate-400">
                                                    {{ $itemFechado->tipo }} • Qtd {{ number_format($itemFechado->quantidade, 3, ',', '.') }}
                                                    @if($devolvido > 0)
                                                        • Devolvido {{ number_format($devolvido, 3, ',', '.') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right font-mono font-black text-slate-800">R$ {{ number_format($itemFechado->valor_total ?? 0, 2, ',', '.') }}</div>
                                            <div class="text-right">
                                                @if($cartaoFechadoSelecionado->status === 'FINALIZADO' && $itemFechado->produto_id && $disponivel > 0)
                                                    <button type="button" wire:click="abrirDevolucaoItem({{ $itemFechado->id }})" class="rounded-md border border-amber-200 bg-amber-50 px-2 py-1.5 text-[10px] font-black uppercase text-amber-700 hover:bg-amber-100">Devolver</button>
                                                @else
                                                    <span class="text-[10px] font-bold uppercase text-slate-300">-</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @if($cartaoFechadoSelecionado->motivo_cancelamento)
                                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-xs font-bold uppercase text-red-700">
                                    Cancelamento: {{ $cartaoFechadoSelecionado->motivo_cancelamento }}
                                </div>
                            @endif
                        @else
                            <div class="flex h-full min-h-96 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center">
                                <div>
                                    <p class="text-sm font-black uppercase text-slate-700">Nenhum cartão selecionado</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">Use os filtros ou selecione uma OS na lista.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($modalClienteAberto)
        <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-fade-in">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-7xl max-h-[90vh] flex flex-col overflow-hidden border border-slate-200">
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center border-b border-slate-800">
                    <div class="flex items-center gap-2 text-white">
                        <span class="text-lg">👥</span>
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider">Central do Cliente</h3>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Busca, dados, pendencias e historico</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('modalClienteAberto', false)" class="text-slate-400 hover:text-white font-medium text-2xl transition-colors outline-none">&times;</button>
                </div>

                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_220px_120px_150px]">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="m21 21-4.35-4.35m1.1-5.15a6.25 6.25 0 1 1-12.5 0 6.25 6.25 0 0 1 12.5 0z"></path>
                            </svg>
                            <input type="text" wire:model.live.debounce.300ms="pesquisaCliente" placeholder="BUSCAR POR NOME, DOCUMENTO, WHATSAPP, E-MAIL OU CIDADE..." class="w-full text-xs font-semibold bg-white border border-slate-300 rounded-lg pl-9 pr-4 py-2.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm uppercase tracking-wide">
                        </div>

                        <select wire:model.live="filtroClienteSituacao" class="text-xs font-bold bg-white border border-slate-300 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm uppercase">
                            <option value="todos">Todos os clientes</option>
                            <option value="com_os">Com historico de OS</option>
                            <option value="com_debito">Com valor em aberto</option>
                        </select>

                        <button type="button" wire:click="limparFiltrosCliente" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
                            Limpar
                        </button>

                        <a href="{{ route('clientes.criar') }}" class="rounded-lg bg-slate-900 px-3 py-2.5 text-center text-xs font-black uppercase text-white shadow-sm hover:bg-slate-800">
                            Novo cliente
                        </a>
                    </div>
                </div>

                <div class="grid flex-1 min-h-0 grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div class="overflow-y-auto overflow-x-hidden min-h-0 border-r border-slate-200">
                        <div class="sticky top-0 z-10 grid grid-cols-[70px_minmax(190px,1.35fr)_minmax(150px,1fr)_minmax(120px,.75fr)_60px_110px_80px] gap-3 border-b border-slate-200 bg-slate-100 px-4 py-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                            <div>Cod.</div>
                            <div>Cliente</div>
                            <div>Contato</div>
                            <div>Cidade</div>
                            <div class="text-center">OS</div>
                            <div class="text-right">Em aberto</div>
                            <div class="text-right">Editar</div>
                        </div>

                        <div class="divide-y divide-slate-100 text-xs">
                            @forelse($listaClientesModal as $c)
                                @php
                                    $totalAbertoCliente = (float) ($totaisAbertosClientes[$c->id] ?? 0);
                                @endphp
                                <div wire:key="cliente-modal-{{ $c->id }}"
                                     wire:click="verHistoricoCliente({{ $c->id }})"
                                     wire:dblclick="selecionarClienteDirect({{ $c->id }}, @js($c->nome))"
                                     class="grid cursor-pointer grid-cols-[70px_minmax(190px,1.35fr)_minmax(150px,1fr)_minmax(120px,.75fr)_60px_110px_80px] gap-3 px-4 py-3 transition-colors {{ $clienteHistoricoId == $c->id ? 'bg-blue-50 ring-1 ring-inset ring-blue-200' : 'bg-white' }}">
                                    <div class="self-center font-mono font-bold text-slate-500">#{{ $c->id }}</div>
                                    <div class="min-w-0 self-center">
                                        <div class="truncate font-black text-slate-700 uppercase tracking-wide">{{ $c->nome }}</div>
                                        <div class="mt-0.5 truncate text-[10px] font-semibold text-slate-400">
                                            DOC: {{ $c->cpf_cnpj ?: 'NAO INFORMADO' }}
                                        </div>
                                    </div>
                                    <div class="min-w-0 self-center">
                                        <div class="truncate font-bold text-slate-600">{{ $c->whatsapp ?: 'Sem WhatsApp' }}</div>
                                        <div class="mt-0.5 truncate text-[10px] text-slate-400">{{ $c->email ?: 'Sem e-mail' }}</div>
                                    </div>
                                    <div class="min-w-0 self-center truncate font-semibold text-slate-600 uppercase">
                                        {{ $c->cidade ?: '-' }}{{ $c->estado ? '/' . $c->estado : '' }}
                                    </div>
                                    <div class="self-center text-center">
                                        <span class="rounded-full bg-slate-100 px-2 py-1 font-black text-slate-700">{{ $c->ordens_servico_count }}</span>
                                    </div>
                                    <div class="self-center text-right font-mono font-black {{ $totalAbertoCliente > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        R$ {{ number_format($totalAbertoCliente, 2, ',', '.') }}
                                    </div>
                                    <div class="self-center text-right" wire:click.stop>
                                        <a href="{{ route('clientes.editar', $c->id) }}" class="inline-flex rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-[10px] font-black uppercase text-amber-700 shadow-sm hover:bg-amber-100">
                                            Editar
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-12 text-center text-slate-400 font-medium uppercase tracking-wide text-xs">
                                    Nenhum cliente localizado com os filtros atuais.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 overflow-y-auto min-h-0">
                        @if($clienteHistorico)
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="mb-3">
                                    <div>
                                        <h4 class="text-sm font-black uppercase text-slate-800">{{ $clienteHistorico->nome }}</h4>
                                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Resumo do cliente</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="rounded-md bg-slate-50 p-3">
                                        <span class="block text-[10px] font-black uppercase text-slate-400">OS realizadas</span>
                                        <span class="font-mono text-lg font-black text-slate-800">{{ $clienteHistorico->ordens_servico_count }}</span>
                                    </div>
                                    <div class="rounded-md bg-slate-50 p-3">
                                        <span class="block text-[10px] font-black uppercase text-slate-400">Em aberto</span>
                                        <span class="font-mono text-lg font-black {{ $totalAbertoHistorico > 0 ? 'text-red-600' : 'text-emerald-600' }}">R$ {{ number_format($totalAbertoHistorico, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="rounded-md bg-slate-50 p-3">
                                        <span class="block text-[10px] font-black uppercase text-slate-400">Limite</span>
                                        <span class="font-mono font-black text-slate-700">R$ {{ number_format($clienteHistorico->limite_credito ?? 0, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="rounded-md bg-slate-50 p-3">
                                        <span class="block text-[10px] font-black uppercase text-slate-400">WhatsApp</span>
                                        <span class="font-mono font-black text-slate-700">{{ $clienteHistorico->whatsapp ?: '-' }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 border-t border-slate-200 pt-3">
                                    <div class="mb-2 flex items-center justify-between">
                                        <h5 class="text-xs font-black uppercase text-slate-600">Ultimas OS</h5>
                                        <a href="{{ route('clientes.show', $clienteHistorico->id) }}" class="text-[10px] font-black uppercase text-blue-600 hover:text-blue-800">Ver cadastro</a>
                                    </div>

                                    <div class="space-y-2">
                                        @forelse($historicoOsCliente as $ordemHistorico)
                                            <a href="{{ route('os.editar', $ordemHistorico->id) }}" class="block rounded-md border border-slate-200 bg-white p-3 shadow-sm hover:border-blue-300">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-mono text-xs font-black text-slate-700">OS #{{ $ordemHistorico->id }}</span>
                                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-black uppercase text-slate-600">{{ $ordemHistorico->status }}</span>
                                                </div>
                                                <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                                                    <span>{{ $ordemHistorico->created_at?->format('d/m/Y') }}</span>
                                                    <span class="font-mono font-black text-slate-700">R$ {{ number_format($ordemHistorico->valor_total_liquido ?? 0, 2, ',', '.') }}</span>
                                                </div>
                                                <div class="mt-1 text-[10px] font-semibold uppercase text-slate-400">
                                                    {{ $ordemHistorico->marca_modelo_veiculo ?: 'Sem veiculo' }} {{ $ordemHistorico->placa_veiculo ? '- ' . $ordemHistorico->placa_veiculo : '' }}
                                                </div>
                                            </a>
                                        @empty
                                            <div class="rounded-md border border-dashed border-slate-300 p-5 text-center text-xs font-bold uppercase text-slate-400">
                                                Cliente sem historico de OS.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="flex h-full min-h-80 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center">
                                <div>
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-1a3 3 0 0 1 3-3h0a3 3 0 0 1 3 3v1m-6-8a3 3 0 1 0 6 0 3 3 0 0 0-6 0zm12 12H3V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v16z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-black uppercase text-slate-700">Clique em um cliente</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">Um clique mostra detalhes. Dois cliques selecionam para a OS.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(false && $modalClienteAberto)
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

    @if($modalDevolucaoItemAberto)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[60] p-4 animate-fade-in">
            <form wire:submit.prevent="registrarDevolucaoItem" class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                <div class="bg-amber-600 px-5 py-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-white">Registrar devolução</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-100">Estorna o produto para o estoque</p>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Quantidade devolvida</label>
                        <input type="text" wire:model="devolucao_quantidade" class="w-full rounded-md border border-slate-300 px-3 py-2 text-right text-sm font-bold outline-none focus:border-amber-500">
                        @error('devolucao_quantidade') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Motivo</label>
                        <textarea wire:model="devolucao_motivo" class="h-20 w-full resize-none rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase outline-none focus:border-amber-500" placeholder="Ex: produto retornou sem uso"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                    <button type="button" wire:click="$set('modalDevolucaoItemAberto', false)" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-100">Cancelar</button>
                    <button type="submit" class="rounded-md bg-amber-600 px-5 py-2 text-xs font-black uppercase text-white shadow hover:bg-amber-700">Confirmar devolução</button>
                </div>
            </form>
        </div>
    @endif

    @if($modalCancelamentoVendaAberto)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[60] p-4 animate-fade-in">
            <form wire:submit.prevent="cancelarVendaFechada" class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                <div class="bg-red-700 px-5 py-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-white">Cancelar venda</h3>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-red-100">Estorna produtos ainda não devolvidos para o estoque</p>
                </div>
                <div class="space-y-4 p-5">
                    <div class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold uppercase text-red-700">
                        Esta ação marca a OS como cancelada e registra auditoria do cancelamento.
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Motivo do cancelamento</label>
                        <textarea wire:model="motivo_cancelamento" class="h-24 w-full resize-none rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase outline-none focus:border-red-500" placeholder="Informe o motivo"></textarea>
                        @error('motivo_cancelamento') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                    <button type="button" wire:click="$set('modalCancelamentoVendaAberto', false)" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-100">Voltar</button>
                    <button type="submit" class="rounded-md bg-red-700 px-5 py-2 text-xs font-black uppercase text-white shadow hover:bg-red-800">Cancelar venda</button>
                </div>
            </form>
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
                            @if($produtosModal->isEmpty() && $servicosModal->isEmpty())
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-xs font-black uppercase tracking-wider text-slate-400">
                                        Nenhum item encontrado no catalogo.
                                    </td>
                                </tr>
                            @endif

                            @foreach($produtosModal as $p)
                                @php
                                    $produtoEhServico = in_array(mb_strtoupper((string) ($p->tipo ?? 'P'), 'UTF-8'), ['S', 'SERVICO', 'SERVIÇO'], true);
                                @endphp
                                <tr wire:key="prod-modal-{{ $p->id }}"
                                    wire:dblclick="adicionarItem({{ $p->id }}, 'produto', true)"
                                    class="hover:bg-slate-50 transition-colors cursor-pointer select-none group">
                                    <td class="py-2.5 px-4 text-center">
                                        <span class="px-2 py-0.5 text-[9px] font-black rounded border block {{ $produtoEhServico ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                            {{ $produtoEhServico ? 'SERVICO' : 'PRODUTO' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 font-mono font-bold text-slate-500 text-center">#{{ $p->id }}</td>
                                    <td class="py-2.5 px-4 font-semibold text-slate-700 uppercase tracking-wide group-hover:text-blue-600">
                                        {{ $p->nome }}
                                        @if($p->marca) <span class="text-[10px] font-normal text-slate-400 block font-mono">MARCA: {{ $p->marca }}</span> @endif
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono text-slate-500 uppercase">{{ $p->unidade ?? 'UN' }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold {{ $produtoEhServico ? 'text-slate-300' : 'text-slate-600' }}">
                                        {{ $produtoEhServico ? '-' : number_format($p->estoque_current ?? $p->estoque_atual ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-emerald-600 bg-emerald-50/20">
                                        R$ {{ number_format($p->preco_venda_vista ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-blue-600 bg-blue-50/20">
                                        R$ {{ number_format($p->preco_venda_prazo ?? 0, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-4 text-center">
                                        @if($produtoEhServico)
                                            <button type="button" wire:click="adicionarItem({{ $p->id }}, 'produto', false, 'prazo')" class="w-full bg-purple-50 text-purple-700 hover:bg-purple-600 hover:text-white border border-purple-200 font-bold px-3 py-1.5 rounded-md text-[10px]">
                                                + Incluir
                                            </button>
                                        @else
                                        <div class="flex gap-1">
                                            <button type="button" wire:click="adicionarItem({{ $p->id }}, 'produto', false, 'vista')" class="flex-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white border border-emerald-200 font-bold px-2 py-1.5 rounded-md text-[9px]">
                                                À Vista
                                            </button>
                                            <button type="button" wire:click="adicionarItem({{ $p->id }}, 'produto', false, 'prazo')" class="flex-1 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 font-bold px-2 py-1.5 rounded-md text-[9px]">
                                                A Prazo
                                            </button>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                            @foreach($servicosModal as $s)
                                <tr wire:key="serv-modal-{{ $s->id }}" wire:dblclick="adicionarItem({{ $s->id }}, 'servico', true)" class="hover:bg-slate-50 transition-colors cursor-pointer select-none group">
                                    <td class="py-2.5 px-4 text-center"><span class="px-2 py-0.5 text-[9px] font-black rounded bg-purple-50 text-purple-700 border border-purple-200 block">SERVIÇO</span></td>
                                    <td class="py-2.5 px-2 font-mono font-bold text-slate-500 text-center">#{{ $s->id }}</td>
                                    <td class="py-2.5 px-4 font-semibold text-slate-700 uppercase tracking-wide group-hover:text-purple-600">{{ $s->descricao ?? $s->nome }}</td>
                                    <td class="py-2.5 px-2 text-center text-slate-300">-</td>
                                    <td class="py-2.5 px-3 text-center text-slate-300">-</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-purple-600 bg-purple-50/20">R$ {{ number_format($s->preco ?? $s->valor ?? $s->valor_base ?? 0, 2, ',', '.') }}</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-purple-600 bg-purple-50/20">R$ {{ number_format($s->preco ?? $s->valor ?? $s->valor_base ?? 0, 2, ',', '.') }}</td>
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

    @if($modalServicoManualAberto)
        <div class="fixed inset-0 bg-slate-900/55 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-fade-in">
            <form wire:submit.prevent="adicionarServicoManual" class="w-full max-w-lg overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-900 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-white">Serviço avulso</h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Mão de obra livre para esta OS</p>
                    </div>
                    <button type="button" wire:click="$set('modalServicoManualAberto', false)" class="text-2xl font-medium text-slate-400 hover:text-white">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Descrição do serviço</label>
                        <input type="text"
                               wire:model="servico_manual_descricao"
                               x-on:keydown.enter.prevent="$refs.valorServicoManual.focus()"
                               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-bold uppercase outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                               placeholder="Ex: INSTALAÇÃO DE FAROL AUXILIAR"
                               autofocus>
                        @error('servico_manual_descricao') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Valor da mão de obra</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">R$</span>
                            <input type="text"
                                   x-ref="valorServicoManual"
                                   wire:model="servico_manual_valor"
                                   class="w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-right text-sm font-bold outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                   placeholder="0,00">
                        </div>
                        @error('servico_manual_valor') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                    <button type="button" wire:click="$set('modalServicoManualAberto', false)" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-md bg-purple-700 px-5 py-2 text-xs font-black uppercase text-white shadow hover:bg-purple-800">
                        Adicionar serviço
                    </button>
                </div>
            </form>
        </div>
    @endif

    @if($modalFinalizacaoAberto)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-fade-in">
            <div class="w-full max-w-2xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 bg-slate-900 px-5 py-4">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-white">Finalizar OS #{{ $os?->id }}</h3>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Fechamento, fiscal e comprovante</p>
                    </div>
                    <button type="button" wire:click="$set('modalFinalizacaoAberto', false)" class="text-2xl font-medium text-slate-400 hover:text-white">&times;</button>
                </div>

                <div class="grid grid-cols-3 border-b border-slate-200 bg-slate-50 text-center text-[10px] font-black uppercase tracking-wide">
                    <div class="py-2 {{ $etapaFinalizacao === 1 ? 'bg-blue-50 text-blue-700' : 'text-slate-400' }}">1. Financeiro</div>
                    <div class="py-2 {{ $etapaFinalizacao === 2 ? 'bg-blue-50 text-blue-700' : 'text-slate-400' }}">2. Fiscal</div>
                    <div class="py-2 {{ $etapaFinalizacao === 3 ? 'bg-blue-50 text-blue-700' : 'text-slate-400' }}">3. Comprovante</div>
                </div>

                <div class="p-5">
                    @if($etapaFinalizacao === 1)
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Forma de pagamento</label>
                                    <select wire:model.live="forma_pagamento" class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                                        <option value="">Selecione</option>
                                        <option value="DINHEIRO">Dinheiro</option>
                                        <option value="PIX">Pix</option>
                                        <option value="CARTAO_DEBITO">Cartão débito</option>
                                        <option value="CARTAO_CREDITO">Cartão crédito</option>
                                        <option value="BOLETO">Boleto</option>
                                        <option value="PRAZO">Duplicata / prazo</option>
                                    </select>
                                    @error('forma_pagamento') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Vencimento</label>
                                    <input type="date" wire:model="data_vencimento" class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs font-bold outline-none focus:border-blue-500">
                                    @error('data_vencimento') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Status</label>
                                    <select wire:model="status_pagamento" class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs font-bold uppercase outline-none focus:border-blue-500">
                                        <option value="PENDENTE">Pendente</option>
                                        <option value="PARCIAL">Parcial</option>
                                        <option value="PAGO">Pago</option>
                                    </select>
                                    @error('status_pagamento') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Observação do fechamento</label>
                                <textarea wire:model="observacao_fechamento" class="h-20 w-full resize-none rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase outline-none focus:border-blue-500"></textarea>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-right">
                                <div class="text-xs font-bold text-slate-500">Total da OS</div>
                                <div class="font-mono text-2xl font-black text-slate-900">R$ {{ number_format($total_geral, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    @endif

                    @if($etapaFinalizacao === 2)
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <span class="block text-[10px] font-black uppercase text-slate-400">Documento fiscal</span>
                                <strong class="mt-1 block text-sm font-black uppercase text-slate-800">Não emitido</strong>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <span class="block text-[10px] font-black uppercase text-slate-400">Série / Número</span>
                                <strong class="mt-1 block font-mono text-sm font-black uppercase text-slate-800">-</strong>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-right">
                                <span class="block text-[10px] font-black uppercase text-slate-400">Valor</span>
                                <strong class="mt-1 block font-mono text-xl font-black text-slate-900">R$ {{ number_format($total_geral, 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    @endif

                    @if($etapaFinalizacao === 3)
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div class="rounded-md bg-slate-50 p-3">
                                    <span class="block font-black uppercase text-slate-400">Pagamento</span>
                                    <span class="font-bold text-slate-700">{{ $forma_pagamento ?: '-' }}</span>
                                </div>
                                <div class="rounded-md bg-slate-50 p-3">
                                    <span class="block font-black uppercase text-slate-400">Vencimento</span>
                                    <span class="font-bold text-slate-700">{{ $data_vencimento ? \Carbon\Carbon::parse($data_vencimento)->format('d/m/Y') : '-' }}</span>
                                </div>
                                <div class="rounded-md bg-slate-50 p-3">
                                    <span class="block font-black uppercase text-slate-400">Status</span>
                                    <span class="font-bold text-slate-700">{{ $status_pagamento ?: '-' }}</span>
                                </div>
                                <div class="rounded-md bg-slate-50 p-3 text-right">
                                    <span class="block font-black uppercase text-slate-400">Total</span>
                                    <span class="font-mono text-lg font-black text-slate-900">R$ {{ number_format($total_geral, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                    <button type="button" wire:click="$set('modalFinalizacaoAberto', false)" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-100">
                        Cancelar
                    </button>

                    <div class="flex gap-2">
                        @if($etapaFinalizacao > 1)
                            <button type="button" wire:click="voltarFinalizacao" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-100">
                                Voltar
                            </button>
                        @endif

                        @if($etapaFinalizacao < 3)
                            <button type="button" wire:click="avancarFinalizacao" class="rounded-md bg-blue-600 px-5 py-2 text-xs font-black uppercase text-white shadow hover:bg-blue-700">
                                Prosseguir
                            </button>
                        @else
                            <button type="button" wire:click="finalizarOs(false)" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-700 hover:bg-slate-100">
                                Só finalizar
                            </button>
                            <button type="button" wire:click="finalizarOs(true)" class="rounded-md bg-emerald-700 px-5 py-2 text-xs font-black uppercase text-white shadow hover:bg-emerald-800">
                                Finalizar e abrir comprovante
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
