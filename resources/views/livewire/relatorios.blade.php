<div class="min-h-screen bg-slate-50 p-6 text-slate-800">
    <div class="mb-5 flex flex-col gap-3 border-b border-slate-200 pb-5 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-3xl font-black uppercase text-slate-900">Relatórios</h1>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Análise operacional, financeira, comercial e estoque</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="window.print()" class="rounded-md bg-slate-900 px-4 py-2 text-xs font-black uppercase text-white shadow hover:bg-slate-800">
                Imprimir
            </button>
            <a href="{{ route('home') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
                Voltar
            </a>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-2 xl:grid-cols-[1fr_.65fr_.65fr_.8fr_.8fr_.8fr_.75fr_.8fr_auto]">
        <input type="text" wire:model.live.debounce.350ms="busca" placeholder="Busca geral" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
        <input type="date" wire:model.live="data_inicio" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold shadow-sm outline-none focus:border-blue-500">
        <input type="date" wire:model.live="data_fim" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold shadow-sm outline-none focus:border-blue-500">
        <input type="text" wire:model.live.debounce.350ms="cliente" placeholder="Cliente" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
        <input type="text" wire:model.live.debounce.350ms="placa" placeholder="Placa" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
        <input type="text" wire:model.live.debounce.350ms="produto" placeholder="Produto/serviço" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
        <select wire:model.live="status" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
            <option value="TODOS">Todos</option>
            <option value="FINALIZADO">Finalizados</option>
            <option value="CANCELADO">Cancelados</option>
        </select>
        <select wire:model.live="forma_pagamento" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
            <option value="">Pagamento</option>
            <option value="DINHEIRO">Dinheiro</option>
            <option value="PIX">Pix</option>
            <option value="CARTAO_DEBITO">Débito</option>
            <option value="CARTAO_CREDITO">Crédito</option>
            <option value="BOLETO">Boleto</option>
            <option value="PRAZO">Prazo</option>
        </select>
        <button type="button" wire:click="limparFiltros" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
            Limpar
        </button>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach([
            'fechamentos' => 'Cartões fechados',
            'faturamento' => 'Faturamento',
            'produtos' => 'Produtos',
            'clientes' => 'Clientes',
            'estoque' => 'Estoque',
        ] as $chave => $titulo)
            <button type="button"
                    wire:click="setAba('{{ $chave }}')"
                    class="rounded-md border px-4 py-2 text-xs font-black uppercase shadow-sm transition {{ $aba === $chave ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-100' }}">
                {{ $titulo }}
            </button>
        @endforeach
    </div>

    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Cartões</span>
            <strong class="font-mono text-xl text-slate-900">{{ $resumoFechamentos['quantidade'] }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Finalizados</span>
            <strong class="font-mono text-xl text-emerald-700">{{ $resumoFechamentos['finalizadas'] }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Total vendido</span>
            <strong class="font-mono text-xl text-emerald-700">R$ {{ number_format($resumoFechamentos['total'], 2, ',', '.') }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Em aberto</span>
            <strong class="font-mono text-xl text-red-600">R$ {{ number_format($resumoFechamentos['pendente'], 2, ',', '.') }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Ticket médio</span>
            <strong class="font-mono text-xl text-slate-900">R$ {{ number_format($resumoFechamentos['ticket_medio'], 2, ',', '.') }}</strong>
        </div>
    </div>

    @if($aba === 'fechamentos')
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <div>
                    <h2 class="text-sm font-black uppercase text-slate-800">Cartões fechados</h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Lista completa com cliente, veículo, pagamento, itens e comprovante</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase text-slate-500">{{ $resumoFechamentos['canceladas'] }} cancelados</span>
            </div>

            <div class="max-h-[58vh] overflow-y-auto">
                <table class="w-full min-w-[1180px] text-left text-xs">
                    <thead class="sticky top-0 z-10 bg-slate-100 text-[10px] font-black uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">OS</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Veículo</th>
                            <th class="px-4 py-3">Atendente</th>
                            <th class="px-4 py-3">Pagamento</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3">Fechamento</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($fechamentos as $ordem)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono font-black text-slate-700">#{{ $ordem->id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-black uppercase text-slate-700">{{ $ordem->cliente->nome ?? 'CONSUMIDOR' }}</div>
                                    <div class="text-[10px] font-bold uppercase text-slate-400">{{ $ordem->cliente->cpf_cnpj ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold uppercase text-slate-700">{{ $ordem->marca_modelo_veiculo ?: '-' }}</div>
                                    <div class="font-mono text-[10px] font-black uppercase text-slate-400">{{ $ordem->placa_veiculo ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3 font-bold uppercase text-slate-600">{{ $ordem->atendente->nome ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-black uppercase text-slate-700">{{ $ordem->forma_pagamento ?: '-' }}</div>
                                    <div class="text-[10px] font-bold uppercase {{ $ordem->status_pagamento === 'PAGO' ? 'text-emerald-600' : 'text-red-600' }}">{{ $ordem->status_pagamento ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-[9px] font-black uppercase {{ $ordem->status === 'CANCELADO' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $ordem->status }}</span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-black text-slate-900">R$ {{ number_format($ordem->valor_total_liquido ?? 0, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">{{ $ordem->finalizado_em ? \Carbon\Carbon::parse($ordem->finalizado_em)->format('d/m/Y H:i') : $ordem->updated_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex gap-2">
                                        <a href="{{ route('os.editar', $ordem->id) }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-[10px] font-black uppercase text-slate-600 hover:bg-slate-100">Abrir</a>
                                        <a href="{{ route('os.comprovante', ['ordemServico' => $ordem->id, 'tipo' => 'comprovante']) }}" target="_blank" class="rounded-md bg-slate-900 px-3 py-1.5 text-[10px] font-black uppercase text-white hover:bg-slate-800">Comprovante</a>
                                    </div>
                                </td>
                            </tr>
                            <tr class="bg-slate-50/60">
                                <td></td>
                                <td colspan="8" class="px-4 pb-3 text-[10px] font-semibold uppercase text-slate-500">
                                    Itens:
                                    @forelse($ordem->itens as $item)
                                        <span class="mr-2 inline-block rounded bg-white px-2 py-1 shadow-sm">{{ $item->descricao }} | qtd {{ number_format($item->quantidade, 3, ',', '.') }} | R$ {{ number_format($item->valor_total, 2, ',', '.') }}</span>
                                    @empty
                                        <span class="text-slate-400">Sem itens</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Nenhum fechamento encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($aba === 'faturamento')
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 class="text-sm font-black uppercase text-slate-800">Faturamento por pagamento</h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Quanto entrou em cada forma de pagamento</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($porPagamento as $linha)
                        <div class="grid grid-cols-[1fr_90px_140px] gap-3 px-4 py-3 text-xs">
                            <div class="font-black uppercase text-slate-700">{{ $linha['forma'] }}</div>
                            <div class="text-center font-mono font-bold text-slate-600">{{ $linha['quantidade'] }}</div>
                            <div class="text-right font-mono font-black text-emerald-700">R$ {{ number_format($linha['total'], 2, ',', '.') }}</div>
                        </div>
                    @empty
                        <div class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Sem faturamento no período.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 class="text-sm font-black uppercase text-slate-800">Faturamento por dia</h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Movimento diário de OS finalizadas</p>
                </div>
                <div class="max-h-[58vh] divide-y divide-slate-100 overflow-y-auto">
                    @forelse($porDia as $linha)
                        <div class="grid grid-cols-[1fr_90px_140px] gap-3 px-4 py-3 text-xs">
                            <div class="font-black uppercase text-slate-700">{{ $linha['data'] }}</div>
                            <div class="text-center font-mono font-bold text-slate-600">{{ $linha['quantidade'] }}</div>
                            <div class="text-right font-mono font-black text-emerald-700">R$ {{ number_format($linha['total'], 2, ',', '.') }}</div>
                        </div>
                    @empty
                        <div class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Sem movimento diário.</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif

    @if($aba === 'produtos')
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="text-sm font-black uppercase text-slate-800">Produtos e serviços</h2>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Mais vendidos, valor vendido, descontos e lucro estimado</p>
            </div>
            <div class="max-h-[64vh] overflow-y-auto">
                <table class="w-full min-w-[980px] text-left text-xs">
                    <thead class="sticky top-0 z-10 bg-slate-100 text-[10px] font-black uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3 text-right">Qtd</th>
                            <th class="px-4 py-3 text-right">Valor médio</th>
                            <th class="px-4 py-3 text-right">Descontos</th>
                            <th class="px-4 py-3 text-right">Total vendido</th>
                            <th class="px-4 py-3 text-right">Lucro estimado</th>
                            <th class="px-4 py-3 text-right">Margem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($produtosRelatorio as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-black uppercase text-slate-700">{{ $item->descricao }}</div>
                                    <div class="text-[10px] font-bold uppercase text-slate-400">{{ $item->codigo_interno ?: $item->codigo_barras ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3 font-black uppercase {{ $item->tipo === 'SERVICO' ? 'text-purple-700' : 'text-orange-700' }}">{{ $item->tipo }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold">{{ number_format($item->quantidade, 3, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold">R$ {{ number_format($item->valor_medio, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-red-600">R$ {{ number_format($item->descontos, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-black text-slate-900">R$ {{ number_format($item->total, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-black text-emerald-700">R$ {{ number_format($item->lucro_estimado, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-black text-slate-700">{{ number_format($item->margem_estimada, 1, ',', '.') }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Nenhum produto ou serviço encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($aba === 'clientes')
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="text-sm font-black uppercase text-slate-800">Clientes</h2>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Ranking de clientes, total comprado e pendências financeiras</p>
            </div>
            <div class="max-h-[64vh] overflow-y-auto">
                <table class="w-full min-w-[860px] text-left text-xs">
                    <thead class="sticky top-0 z-10 bg-slate-100 text-[10px] font-black uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Contato</th>
                            <th class="px-4 py-3 text-center">OS</th>
                            <th class="px-4 py-3 text-right">Total comprado</th>
                            <th class="px-4 py-3 text-right">Em aberto</th>
                            <th class="px-4 py-3">Última OS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($clientesRelatorio as $linha)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-black uppercase text-slate-700">{{ $linha->cliente->nome ?? 'CONSUMIDOR' }}</div>
                                    <div class="text-[10px] font-bold uppercase text-slate-400">{{ $linha->cliente->cpf_cnpj ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-700">{{ $linha->cliente->whatsapp ?? '-' }}</div>
                                    <div class="text-[10px] font-bold text-slate-400">{{ $linha->cliente->email ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-black text-slate-700">{{ $linha->quantidade_os }}</td>
                                <td class="px-4 py-3 text-right font-mono font-black text-emerald-700">R$ {{ number_format($linha->total, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-black {{ $linha->pendente > 0 ? 'text-red-600' : 'text-slate-400' }}">R$ {{ number_format($linha->pendente, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-[10px] font-bold uppercase text-slate-500">{{ $linha->ultima_os ? \Carbon\Carbon::parse($linha->ultima_os)->format('d/m/Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Nenhum cliente encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if($aba === 'estoque')
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_.9fr]">
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 class="text-sm font-black uppercase text-slate-800">Estoque baixo</h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Produtos que chegaram no mínimo ou abaixo dele</p>
                </div>
                <div class="max-h-[58vh] overflow-y-auto">
                    <table class="w-full min-w-[760px] text-left text-xs">
                        <thead class="sticky top-0 z-10 bg-slate-100 text-[10px] font-black uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Produto</th>
                                <th class="px-4 py-3">Código</th>
                                <th class="px-4 py-3 text-right">Atual</th>
                                <th class="px-4 py-3 text-right">Mínimo</th>
                                <th class="px-4 py-3">Localização</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($estoqueRelatorio['baixo'] as $produtoBaixo)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-black uppercase text-slate-700">{{ $produtoBaixo->nome }}</td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-500">{{ $produtoBaixo->codigo_interno ?: $produtoBaixo->codigo_barras ?: '-' }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-black text-red-600">{{ number_format($produtoBaixo->estoque_atual ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-600">{{ number_format($produtoBaixo->estoque_minimo ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-4 py-3 font-bold uppercase text-slate-500">{{ $produtoBaixo->localizacao ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Nenhum produto em estoque baixo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="block text-[10px] font-black uppercase text-slate-400">Produtos controlados</span>
                        <strong class="font-mono text-xl text-slate-900">{{ $estoqueRelatorio['produtos_controlados'] }}</strong>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="block text-[10px] font-black uppercase text-slate-400">Sem estoque</span>
                        <strong class="font-mono text-xl text-red-600">{{ $estoqueRelatorio['sem_estoque'] }}</strong>
                    </div>
                    <div class="col-span-2 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                        <span class="block text-[10px] font-black uppercase text-slate-400">Valor de custo em estoque</span>
                        <strong class="font-mono text-xl text-emerald-700">R$ {{ number_format($estoqueRelatorio['valor_estoque'], 2, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-4 py-3">
                        <h3 class="text-sm font-black uppercase text-slate-800">Maior valor parado</h3>
                    </div>
                    <div class="max-h-[35vh] divide-y divide-slate-100 overflow-y-auto">
                        @forelse($estoqueRelatorio['maior_valor'] as $itemEstoque)
                            <div class="grid grid-cols-[1fr_110px] gap-3 px-4 py-3 text-xs">
                                <div class="min-w-0">
                                    <div class="truncate font-black uppercase text-slate-700">{{ $itemEstoque->nome }}</div>
                                    <div class="font-mono text-[10px] font-bold text-slate-400">{{ $itemEstoque->codigo_interno ?: '-' }} | qtd {{ number_format($itemEstoque->estoque_atual ?? 0, 3, ',', '.') }}</div>
                                </div>
                                <div class="text-right font-mono font-black text-slate-800">R$ {{ number_format($itemEstoque->valor_estoque ?? 0, 2, ',', '.') }}</div>
                            </div>
                        @empty
                            <div class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Sem produtos controlados.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
