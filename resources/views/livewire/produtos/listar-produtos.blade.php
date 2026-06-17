<div class="max-w-7xl mx-auto py-8 px-4">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Estoque de Produtos</h1>
            <p class="text-slate-500 text-sm">Gerencie suas peças e serviços cadastrados.</p>
        </div>

        <div class="flex w-full md:w-auto gap-3">
            <input type="text" wire:model.live="busca" placeholder="Buscar por nome ou código..."
                class="w-full md:w-80 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 uppercase font-semibold text-sm">

            <a href="{{ route('produtos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-black uppercase text-xs shadow-md transition">
                + Novo Produto
            </a>
        </div>
    </div>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest">ID</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest">Cód. Fabric.</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest">Produto</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest">Preço Vista</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest">Preço Prazo</th>
                    <th class="px-6 py-4 text-left text-xs font-black text-slate-500 uppercase tracking-widest">Estoque</th>
                    <th class="px-6 py-4 text-right text-xs font-black text-slate-500 uppercase tracking-widest">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($produtos as $produto)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                        {{ $produto->id }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-600 font-mono">
                        {{ strtoupper($produto->codigo_interno) ?: '---' }}
                    </td>

                    <td class="px-6 py-4">
                        <div class="text-sm font-black text-slate-800 uppercase">{{ $produto->nome }}</div>
                        @if($produto->codigo_barras)
                            <div class="text-[9px] text-slate-500 font-mono tracking-tighter leading-none mt-1">
                                {{ strtoupper($produto->codigo_barras) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-700">R$ {{ number_format($produto->preco_venda_vista, 2, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-700">R$ {{ number_format($produto->preco_venda_prazo, 2, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <span class="text-sm font-black {{ $produto->estoque_atual <= $produto->estoque_minimo ? 'text-orange-500' : 'text-slate-700' }}">
                                @php
                                    $valorEstoque = $produto->estoque_atual == floor($produto->estoque_atual)
                                        ? number_format($produto->estoque_atual, 0, ',', '.')
                                        : number_format($produto->estoque_atual, 2, ',', '.');
                                @endphp
                                {{ $valorEstoque }} {{ $produto->unidade }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-black">
                        <a href="#" class="text-green-600 hover:text-green-900 mx-2 uppercase">Ver</a>
                        <a href="{{ route('produtos.edit', $produto->id) }}" class="text-blue-600 hover:text-blue-900 mx-2 uppercase">Editar</a>
                        <button wire:click="excluir({{ $produto->id }})" wire:confirm="Excluir este produto?" class="text-red-400 hover:text-red-600 mx-2 uppercase">
                            Excluir
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-4 border-t bg-slate-50">
            {{ $produtos->links() }}
        </div>
    </div>
</div>
