<div class="min-h-screen bg-slate-50 p-4 text-slate-800">
    <div class="mb-4 flex flex-col gap-3 border-b border-slate-200 pb-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-3xl font-black uppercase text-slate-900">Movimentação de Estoque</h1>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Entradas, saídas, ajustes e histórico operacional</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('produtos.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
                Produtos
            </a>
            <a href="{{ route('home') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
                Voltar
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Entradas</span>
            <strong class="font-mono text-xl text-emerald-700">{{ number_format($resumo['entradas'], 3, ',', '.') }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Saídas</span>
            <strong class="font-mono text-xl text-red-600">{{ number_format($resumo['saidas'], 3, ',', '.') }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Ajustes</span>
            <strong class="font-mono text-xl text-blue-700">{{ $resumo['ajustes'] }}</strong>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <span class="block text-[10px] font-black uppercase text-slate-400">Movimentos</span>
            <strong class="font-mono text-xl text-slate-900">{{ $resumo['total'] }}</strong>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_340px]">
        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="text-sm font-black uppercase text-slate-800">Nova movimentação</h2>
            </div>

            <form wire:submit.prevent="registrarMovimentacao" class="grid grid-cols-1 gap-3 p-4 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Produto</label>
                    <select wire:model.live="produto_id" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                        <option value="">Selecione o produto</option>
                        @foreach($produtos as $produto)
                            <option value="{{ $produto->id }}">
                                #{{ $produto->id }} - {{ $produto->nome }} | Estoque {{ number_format((float) $produto->estoque_atual, 3, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('produto_id') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="lg:col-span-5">
                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Tipo</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['ENTRADA' => 'Entrada', 'SAIDA' => 'Saída', 'AJUSTE' => 'Ajuste'] as $valor => $label)
                            <button type="button"
                                    wire:click="selecionarTipo('{{ $valor }}')"
                                    class="rounded-md border px-3 py-2 text-xs font-black uppercase shadow-sm transition {{ $tipo === $valor ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-100' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">{{ $tipo === 'AJUSTE' ? 'Estoque final' : 'Quantidade' }}</label>
                    <input type="text" wire:model.live.debounce.250ms="quantidade" placeholder="0,000" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold shadow-sm outline-none focus:border-blue-500">
                    @error('quantidade') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="lg:col-span-5">
                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Motivo</label>
                    <input type="text" wire:model="motivo" placeholder="Ex: compra, avaria, conferência" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                    @error('motivo') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="lg:col-span-4">
                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Previsão</label>
                    <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
                        <span class="block text-[10px] font-black uppercase text-slate-400">Estoque após movimento</span>
                        <strong class="font-mono text-sm {{ $previsaoEstoque !== null && $previsaoEstoque < 0 ? 'text-red-600' : 'text-slate-900' }}">
                            {{ $previsaoEstoque === null ? '-' : number_format($previsaoEstoque, 3, ',', '.') }}
                        </strong>
                    </div>
                </div>

                <div class="lg:col-span-12">
                    <label class="mb-1 block text-[10px] font-black uppercase text-slate-500">Observação</label>
                    <textarea wire:model="observacao" rows="3" placeholder="Detalhes da movimentação" class="w-full resize-none rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-semibold uppercase shadow-sm outline-none focus:border-blue-500"></textarea>
                    @error('observacao') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-wrap justify-end gap-2 lg:col-span-12">
                    <button type="button" wire:click="limparFormulario" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
                        Limpar
                    </button>
                    <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-xs font-black uppercase text-white shadow hover:bg-blue-700">
                        Registrar
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-4">
            <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-black uppercase text-slate-800">Produto selecionado</h2>
                @if($produtoSelecionado)
                    <div class="space-y-3">
                        <div>
                            <span class="block text-[10px] font-black uppercase text-slate-400">Descrição</span>
                            <strong class="text-sm font-black uppercase text-slate-800">{{ $produtoSelecionado->nome }}</strong>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-md bg-slate-50 p-3">
                                <span class="block text-[10px] font-black uppercase text-slate-400">Atual</span>
                                <strong class="font-mono text-lg text-slate-900">{{ number_format((float) $produtoSelecionado->estoque_atual, 3, ',', '.') }}</strong>
                            </div>
                            <div class="rounded-md bg-slate-50 p-3">
                                <span class="block text-[10px] font-black uppercase text-slate-400">Mínimo</span>
                                <strong class="font-mono text-lg text-slate-900">{{ number_format((float) $produtoSelecionado->estoque_minimo, 3, ',', '.') }}</strong>
                            </div>
                            <div class="rounded-md bg-slate-50 p-3">
                                <span class="block text-[10px] font-black uppercase text-slate-400">Custo</span>
                                <strong class="font-mono text-sm text-slate-900">R$ {{ number_format((float) $produtoSelecionado->preco_custo, 2, ',', '.') }}</strong>
                            </div>
                            <div class="rounded-md bg-slate-50 p-3">
                                <span class="block text-[10px] font-black uppercase text-slate-400">Local</span>
                                <strong class="text-xs font-black uppercase text-slate-900">{{ $produtoSelecionado->localizacao ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="rounded-md bg-slate-50 px-4 py-8 text-center text-xs font-black uppercase text-slate-400">
                        Nenhum produto selecionado.
                    </div>
                @endif
            </section>

            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3">
                    <h2 class="text-sm font-black uppercase text-slate-800">Estoque baixo</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($produtosBaixo as $produto)
                        <button type="button" wire:click="$set('produto_id', '{{ $produto->id }}')" class="grid w-full grid-cols-[1fr_auto] gap-3 px-4 py-3 text-left text-xs hover:bg-slate-50">
                            <span>
                                <strong class="block font-black uppercase text-slate-700">{{ $produto->nome }}</strong>
                                <span class="font-semibold uppercase text-slate-400">Min. {{ number_format((float) $produto->estoque_minimo, 3, ',', '.') }}</span>
                            </span>
                            <strong class="font-mono text-red-600">{{ number_format((float) $produto->estoque_atual, 3, ',', '.') }}</strong>
                        </button>
                    @empty
                        <div class="px-4 py-8 text-center text-xs font-black uppercase text-slate-400">Sem alerta de estoque.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-4 py-3">
            <h2 class="text-sm font-black uppercase text-slate-800">Histórico de movimentações</h2>
        </div>

        <div class="grid grid-cols-1 gap-2 border-b border-slate-200 p-4 lg:grid-cols-[1fr_170px_220px_150px_150px_auto]">
            <input type="text" wire:model.live.debounce.350ms="busca" placeholder="Buscar produto, motivo ou observação" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
            <select wire:model.live="filtro_tipo" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                <option value="">Todos os tipos</option>
                <option value="ENTRADA">Entrada</option>
                <option value="SAIDA">Saída</option>
                <option value="AJUSTE">Ajuste</option>
            </select>
            <select wire:model.live="filtro_produto" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                <option value="">Todos os produtos</option>
                @foreach($produtos as $produto)
                    <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="data_inicio" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold shadow-sm outline-none focus:border-blue-500">
            <input type="date" wire:model.live="data_fim" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold shadow-sm outline-none focus:border-blue-500">
            <button type="button" wire:click="limparFiltros" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
                Limpar
            </button>
        </div>

        <div class="max-h-[42vh] overflow-y-auto">
            <table class="w-full text-left text-xs">
                <thead class="sticky top-0 z-10 bg-slate-100 text-[10px] font-black uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Produto</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3 text-right">Qtd</th>
                        <th class="px-4 py-3 text-right">Antes</th>
                        <th class="px-4 py-3 text-right">Depois</th>
                        <th class="px-4 py-3">Motivo</th>
                        <th class="px-4 py-3">Responsável</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movimentacoes as $movimento)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono font-bold text-slate-600">{{ $movimento->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="font-black uppercase text-slate-700">{{ $movimento->produto->nome ?? 'Produto removido' }}</div>
                                <div class="text-[10px] font-bold uppercase text-slate-400">#{{ $movimento->produto_id ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-[9px] font-black uppercase {{
                                    $movimento->tipo === 'ENTRADA' ? 'bg-emerald-50 text-emerald-700' : (
                                    $movimento->tipo === 'SAIDA' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700')
                                }}">
                                    {{ $movimento->tipoNome() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-black text-slate-700">{{ number_format((float) $movimento->quantidade, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-500">{{ number_format((float) $movimento->estoque_anterior, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-black text-slate-900">{{ number_format((float) $movimento->estoque_posterior, 3, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="font-bold uppercase text-slate-700">{{ $movimento->motivo }}</div>
                                @if($movimento->observacao)
                                    <div class="text-[10px] font-semibold uppercase text-slate-400">{{ $movimento->observacao }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold uppercase text-slate-600">{{ $movimento->usuario->name ?? 'Sistema' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">
                                Nenhuma movimentação encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
