<div class="max-w-5xl mx-auto py-10 px-4">
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 uppercase font-bold text-xs">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-gray-200">
        <div class="bg-[#1e293b] p-4 flex justify-between items-center">
            <h2 class="text-white font-bold text-lg uppercase">Editar Produto #{{ $produtoId }}</h2>
            <a href="{{ route('produtos.index') }}" class="text-black hover:text-gray-800 text-sm flex items-center transition-colors font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                DESCARTAR ALTERAÇÕES E VOLTAR
            </a>
        </div>

        <div class="flex border-b border-gray-200 bg-gray-50">
            <button wire:click="setAba('geral')" class="px-6 py-3 text-xs font-bold uppercase tracking-wider {{ $abaAtiva == 'geral' ? 'bg-white border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                1. Identificação e Comercial
            </button>
            <button wire:click="setAba('fiscal')" class="px-6 py-3 text-xs font-bold uppercase tracking-wider {{ $abaAtiva == 'fiscal' ? 'bg-white border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                2. Dados para Impressão NF-e
            </button>
        </div>

        <form wire:submit.prevent="atualizar" class="p-8">
            @if($abaAtiva == 'geral')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Nome do Produto *</label>
                            <input type="text" wire:model.blur="nome" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none uppercase">
                            @error('nome') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Marca do Produto</label>
                            <input type="text" wire:model.blur="marca" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none uppercase">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Categoria *</label>
                            <select wire:model="categoria" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none bg-white uppercase">
                                <option value="ELÉTRICA">ELÉTRICA</option>
                                <option value="SUSPENSÃO">SUSPENSÃO</option>
                                <option value="MOTOR">MOTOR</option>
                                <option value="FREIOS">FREIOS</option>
                                <option value="CÂMBIO">CÂMBIO</option>
                                <option value="LUBRIFICANTES">LUBRIFICANTES</option>
                                <option value="ACESSÓRIOS">ACESSÓRIOS</option>
                                <option value="OUTROS">OUTROS</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Cód. Fabricante (SKU)</label>
                            <input type="text" wire:model="codigo_interno" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none uppercase">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Cód. Barras (EAN)</label>
                            <input type="text"
                                   wire:model="codigo_barras"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   placeholder=""
                                   class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none">
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Unidade</label>
                            <select wire:model="unidade" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none uppercase font-bold">
                                <option value="UN">UNIDADE</option>
                                <option value="PC">PEÇA</option>
                                <option value="MT">METRO</option>
                                <option value="KG">KILOGRAMA</option>
                            </select>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Tipo</label>
                            <select wire:model="tipo" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none uppercase font-bold">
                                <option value="P">PRODUTO</option>
                                <option value="S">SERVIÇO</option>
                            </select>
                        </div>
                        <div class="md:col-span-12">
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Descrição Detalhada do Produto ou Serviço</label>
                            <textarea wire:model="descricao_detalhada" rows="2" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none uppercase"></textarea>
                        </div>
                    </div>

                    <hr class="border-gray-200">

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <div class="md:col-span-6 grid grid-cols-2 gap-4 border-r border-gray-100 pr-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Qtd em Estoque</label>
                                <input type="number" step="0.001" wire:model="estoque_atual" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Estoque Mínimo</label>
                                <input type="number" step="0.001" wire:model="estoque_minimo" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Localização</label>
                                <input type="text" wire:model.blur="localizacao" placeholder="EX: PRATELEIRA A1" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none uppercase">
                            </div>
                        </div>

                        <div class="md:col-span-6 grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Custo Unitário (R$) *</label>
                                <input type="number" step="0.01" wire:model.live="preco_custo" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none text-blue-700">
                                @error('preco_custo') <span class="text-red-500 text-xs font-bold uppercase">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Margem Lucro (%)</label>
                                <input type="number" step="0.01" wire:model.live="margem_lucro" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Venda à Vista (R$)</label>
                                <input type="number"
                                       step="0.01"
                                       wire:model="preco_venda_vista"
                                       class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none text-green-600 bg-white uppercase font-bold">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Venda à Prazo (R$)</label>
                                <input type="number"
                                       step="0.01"
                                       wire:model.live="preco_venda_prazo"
                                       class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none text-gray-700 bg-white uppercase font-bold">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($abaAtiva == 'fiscal')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">NCM (8 dígitos)</label>
                            <input type="text" wire:model="ncm" maxlength="8" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">CFOP *</label>
                            <input type="text" wire:model="cfop" maxlength="4" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">CST / CSOSN *</label>
                            <input type="text"
                                   wire:model="cst_csosn"
                                   maxlength="3"
                                   placeholder="EX: 102"
                                   class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none">
                            @error('cst_csosn') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1 uppercase">Origem</label>
                            <select wire:model="origem" class="w-full border border-gray-300 rounded-md p-2 shadow-sm outline-none bg-white">
                                <option value="0">0 - NACIONAL</option>
                                <option value="1">1 - ESTRANGEIRA (IMPORTAÇÃO DIRETA)</option>
                                <option value="2">2 - ESTRANGEIRA (MERCADO INTERNO)</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-10 pt-6 border-t border-gray-100 flex justify-between items-center">
                <div class="flex items-center">
                    <input type="checkbox" wire:model="ativo" id="chk_ativo" class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                    <label for="chk_ativo" class="ml-2 text-sm text-gray-600 font-bold uppercase cursor-pointer">Produto ativo para venda</label>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-md font-bold shadow-md transition-all active:scale-95 flex items-center">
                        <span wire:loading.remove>Salvar Alterações</span>
                        <span wire:loading>PROCESSANDO...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
