<form wire:submit.prevent="criarAdministrador" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5">
        <h2 class="text-xl font-black uppercase text-slate-900">Primeiro acesso</h2>
        <p class="mt-1 text-sm font-semibold text-slate-500">Crie o administrador principal do sistema.</p>
    </div>

    <div class="space-y-4">
        <div>
            <label class="mb-1 block text-xs font-black uppercase text-slate-500">Nome</label>
            <input type="text" wire:model="name" autofocus class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-bold uppercase outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            @error('name') <span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-black uppercase text-slate-500">E-mail</label>
            <input type="email" wire:model="email" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-bold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            @error('email') <span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-black uppercase text-slate-500">Senha</label>
            <input type="password" wire:model="password" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-bold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            @error('password') <span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-black uppercase text-slate-500">Confirmar senha</label>
            <input type="password" wire:model="password_confirmation" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-bold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
        </div>
    </div>

    <button type="submit" class="mt-6 w-full rounded-md bg-blue-600 px-4 py-2.5 text-xs font-black uppercase text-white shadow hover:bg-blue-700">
        Criar administrador
    </button>
</form>
