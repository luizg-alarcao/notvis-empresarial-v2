<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Clientes Cadastrados</h2>
        <a href="{{ route('clientes.criar') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition shadow-sm text-sm">
            + Novo Cliente
        </a>
    </div>

    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
        @if (session()->has('success'))
            <div id="success-alert"
                class="mb-4 p-4 bg-green-100 text-green-800 rounded-md border border-green-200 shadow-sm flex justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()"
                    class="text-green-600 font-bold">&times;</button>
            </div>
        @endif
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <input type="text" wire:model.live="search" placeholder="Pesquisar por nome ou documento..."
                class="w-full md:w-1/3 border-gray-300 rounded-lg p-2.5 border focus:ring-2 focus:ring-blue-500 outline-none">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="p-4 text-sm font-semibold uppercase tracking-wider">Nome</th>
                        <th class="p-4 text-sm font-semibold uppercase tracking-wider">Documento</th>
                        <th class="p-4 text-sm font-semibold uppercase tracking-wider">WhatsApp</th>
                        <th class="p-4 text-sm font-semibold uppercase tracking-wider">Cidade/UF</th>
                        <th class="p-4 text-sm font-semibold uppercase tracking-wider text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 uppercase">
                                    {{ $cliente->nome }}
                                </td>
                            <td class="p-4 text-sm text-gray-600">{{ $cliente->cpf_cnpj }}</td>
                            <td class="p-4 text-sm text-gray-600">{{ $cliente->whatsapp }}</td>
                            <td class="p-4 text-sm text-gray-600">{{ $cliente->cidade }} / {{ $cliente->estado }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('clientes.show', $cliente->id) }}"
                                   class="text-green-600 hover:text-green-900 mx-2 text-xs font-bold uppercase transition">
                                    Ver
                                </a>
                                <a href="{{ route('clientes.editar', $cliente->id) }}"
                                    class="text-blue-600 hover:text-blue-900 mx-2 text-xs font-bold uppercase transition">Editar</a>
                                <button wire:click="excluir({{ $cliente->id }})"
                                    wire:confirm="Tem certeza que deseja excluir e deletar todos os dados deste cliente? Esta ação não pode ser desfeita."
                                    class="text-red-600 hover:text-red-900 mx-2 text-xs font-bold uppercase transition">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500 italic">
                                Nenhum cliente encontrado para "{{ $search }}".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-gray-50 border-t border-gray-200">
            {{ $clientes->links() }}
        </div>
    </div>
</div>

<script>
    @if (session()->has('success'))
        setTimeout(function() {
            var alert = document.getElementById('success-alert');
            if (alert) {
                alert.remove();
            }
        }, 5000); // 5 segundos
    @endif
</script>
