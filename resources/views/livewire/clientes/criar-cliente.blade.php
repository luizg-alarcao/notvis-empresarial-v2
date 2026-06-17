<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
        <div class="bg-gray-800 p-4 flex justify-between items-center">
            <h2 class="text-white font-bold text-xl">Novo Cadastro de Cliente</h2>
            <a href="{{ route('clientes.index') }}" class="text-gray-300 hover:text-white text-sm font-semibold flex items-center transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar
            </a>
        </div>

        <form wire:submit.prevent="salvar" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700">Nome/Razão Social *</label>
                    <input type="text" wire:model="nome"
                        class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500 uppercase">
                    @error('nome')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Data de Nascimento</label>
                    <input type="date" wire:model="data_nascimento"
                        class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">CPF ou CNPJ *</label>
                    <input type="text" wire:model.blur="cpf_cnpj"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 14)"
                        placeholder="Apenas números" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    @error('cpf_cnpj')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">RG</label>
                    <input type="text" wire:model="rg" maxlength="9"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">I.E. (Estadual)</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" wire:model="inscricao_estadual" maxlength="14"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            @if ($isentoIE) disabled @endif
                            class="w-full border-gray-300 rounded-md shadow-sm p-2 border @if ($isentoIE) bg-gray-200 @endif">
                        <label class="flex items-center text-xs">
                            <input type="checkbox" wire:model.live="isentoIE" class="mr-1"> Isento
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">I.M. (Municipal)</label>
                    <input type="text" wire:model="inscricao_municipal" maxlength="15"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">WhatsApp *</label>
                    <input type="text" wire:model="whatsapp"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                        placeholder="DDD + Número (apenas números)"
                        class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    @error('whatsapp')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">E-mail *</label>
                    <input type="email" wire:model="email" placeholder="email@exemplo.com"
                        class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="border-t pt-4 mt-4">
                <h4 class="text-gray-600 font-bold mb-3 uppercase text-xs tracking-wider">Endereço</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">CEP</label>
                        <input type="text" wire:model.live="cep"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)"
                            placeholder="Apenas números" maxlength="8"
                            class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-yellow-50">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">Rua</label>
                        <input type="text" wire:model="rua"
                            class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Número</label>
                        <input type="text" wire:model="numero"
                            class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700">Bairro</label>
                        <input type="text" wire:model="bairro"
                            class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700">Cidade</label>
                        <input type="text" wire:model="cidade" readonly
                            class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-gray-100">
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700">Estado</label>
                        <input type="text" wire:model="estado" readonly
                            class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-gray-100">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-10 rounded-lg transition shadow-md">
                    Finalizar Cadastro
                </button>
            </div>
        </form>
    </div>
</div>
