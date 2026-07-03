<div class="p-6 min-h-full">
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Configuracoes</h1>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Ajustes principais do NOTVIS</p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-bold uppercase text-slate-600 shadow-sm hover:bg-slate-50">
            Voltar ao inicio
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('info') }}
        </div>
    @endif

    <div class="mb-5 flex flex-wrap gap-2 border-b border-slate-200 pb-3">
        <button type="button" wire:click="setAba('funcionarios')" class="rounded-md px-4 py-2 text-xs font-black uppercase transition {{ $abaAtiva === 'funcionarios' ? 'bg-slate-900 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Funcionarios
        </button>
        <button type="button" wire:click="setAba('usuarios')" class="rounded-md px-4 py-2 text-xs font-black uppercase transition {{ $abaAtiva === 'usuarios' ? 'bg-slate-900 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Usuarios
        </button>
        <button type="button" wire:click="setAba('empresa')" class="rounded-md px-4 py-2 text-xs font-black uppercase transition {{ $abaAtiva === 'empresa' ? 'bg-slate-900 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Empresa
        </button>
        <button type="button" wire:click="setAba('comercial')" class="rounded-md px-4 py-2 text-xs font-black uppercase transition {{ $abaAtiva === 'comercial' ? 'bg-slate-900 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Comercial
        </button>
        <button type="button" wire:click="setAba('aparencia')" class="rounded-md px-4 py-2 text-xs font-black uppercase transition {{ $abaAtiva === 'aparencia' ? 'bg-slate-900 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Aparencia
        </button>
        <button type="button" wire:click="setAba('auditoria')" class="rounded-md px-4 py-2 text-xs font-black uppercase transition {{ $abaAtiva === 'auditoria' ? 'bg-slate-900 text-white shadow' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Auditoria
        </button>
    </div>

    @if($abaAtiva === 'funcionarios')
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[380px_1fr]">
            <form wire:submit.prevent="salvarFuncionario" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-black uppercase text-slate-800">{{ $funcionario_id ? 'Editar funcionario' : 'Novo funcionario' }}</h2>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Atendentes e mecanicos usados na OS</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nome</label>
                        <input type="text" wire:model="nome_funcionario" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold uppercase outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('nome_funcionario') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Cargo</label>
                        <select wire:model="cargo_funcionario" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="ATENDENTE">Atendente</option>
                            <option value="MECANICO">Mecanico</option>
                            <option value="GERENTE">Gerente</option>
                        </select>
                        @error('cargo_funcionario') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                        <input type="checkbox" wire:model="ativo_funcionario" class="rounded border-slate-300">
                        Funcionario ativo
                    </label>
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-xs font-black uppercase text-white shadow hover:bg-blue-700">
                        Salvar
                    </button>
                    <button type="button" wire:click="limparFuncionario" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-50">
                        Limpar
                    </button>
                </div>
            </form>

            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-black uppercase text-slate-800">Equipe cadastrada</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Nome</th>
                                <th class="px-5 py-3">Cargo</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($funcionarios as $funcionario)
                                <tr>
                                    <td class="px-5 py-3 font-bold uppercase text-slate-700">{{ $funcionario->nome }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $funcionario->cargo }}</td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $funcionario->ativo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $funcionario->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <button type="button" wire:click="editarFuncionario({{ $funcionario->id }})" class="rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase text-blue-700 hover:bg-blue-600 hover:text-white">Editar</button>
                                        <button type="button" wire:click="alternarFuncionario({{ $funcionario->id }})" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-black uppercase text-slate-600 hover:bg-slate-100">{{ $funcionario->ativo ? 'Desativar' : 'Ativar' }}</button>
                                        <button type="button" wire:click="excluirFuncionario({{ $funcionario->id }})" class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-[10px] font-black uppercase text-red-700 hover:bg-red-600 hover:text-white">Excluir</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm font-semibold uppercase tracking-wide text-slate-400">Nenhum funcionario cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($abaAtiva === 'usuarios')
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-[420px_1fr]">
            <form wire:submit.prevent="salvarUsuario" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-black uppercase text-slate-800">{{ $usuario_id ? 'Editar usuario' : 'Novo usuario' }}</h2>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Login e nivel de acesso ao sistema</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nome</label>
                        <input type="text" wire:model="nome_usuario" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold uppercase outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('nome_usuario') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">E-mail</label>
                        <input type="email" wire:model="email_usuario" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('email_usuario') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Perfil</label>
                        <select wire:model="perfil_usuario" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            @foreach($perfisUsuario as $valor => $nome)
                                <option value="{{ $valor }}">{{ $nome }}</option>
                            @endforeach
                        </select>
                        @error('perfil_usuario') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase text-slate-500">{{ $usuario_id ? 'Nova senha' : 'Senha' }}</label>
                        <input type="password" wire:model="senha_usuario" placeholder="{{ $usuario_id ? 'Deixe em branco para manter' : 'Minimo 8 caracteres' }}" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('senha_usuario') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm font-bold text-slate-600">
                        <input type="checkbox" wire:model="ativo_usuario" class="rounded border-slate-300">
                        Usuario ativo
                    </label>
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="flex-1 rounded-md bg-blue-600 px-4 py-2 text-xs font-black uppercase text-white shadow hover:bg-blue-700">
                        Salvar
                    </button>
                    <button type="button" wire:click="limparUsuario" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-50">
                        Limpar
                    </button>
                </div>
            </form>

            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-black uppercase text-slate-800">Usuarios cadastrados</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Nome</th>
                                <th class="px-5 py-3">E-mail</th>
                                <th class="px-5 py-3">Perfil</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Ultimo login</th>
                                <th class="px-5 py-3 text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td class="px-5 py-3 font-bold uppercase text-slate-700">{{ $usuario->name }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $usuario->email }}</td>
                                    <td class="px-5 py-3 font-bold uppercase text-slate-600">{{ $usuario->perfilNome() }}</td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2 py-1 text-[10px] font-black uppercase {{ $usuario->ativo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-xs font-semibold uppercase text-slate-500">
                                        {{ $usuario->ultimo_login_em ? $usuario->ultimo_login_em->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <button type="button" wire:click="editarUsuario({{ $usuario->id }})" class="rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-[10px] font-black uppercase text-blue-700 hover:bg-blue-600 hover:text-white">Editar</button>
                                        <button type="button" wire:click="alternarUsuario({{ $usuario->id }})" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-black uppercase text-slate-600 hover:bg-slate-100">{{ $usuario->ativo ? 'Desativar' : 'Ativar' }}</button>
                                        <button type="button" wire:click="excluirUsuario({{ $usuario->id }})" class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-[10px] font-black uppercase text-red-700 hover:bg-red-600 hover:text-white">Excluir</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm font-semibold uppercase tracking-wide text-slate-400">Nenhum usuario cadastrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($abaAtiva === 'empresa')
        <form wire:submit.prevent="salvarEmpresa" class="max-w-5xl rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-black uppercase text-slate-800">Dados da empresa</h2>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Usado futuramente em OS, impressao e relatorios</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Nome fantasia *</label>
                    <input type="text" wire:model="nome_fantasia" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold uppercase outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('nome_fantasia') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Razao social *</label>
                    <input type="text" wire:model="razao_social" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold uppercase outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('razao_social') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">CNPJ *</label>
                    <div class="flex gap-2">
                        <input type="text"
                               wire:model.live.debounce.250ms="cnpj"
                               inputmode="numeric"
                               maxlength="18"
                               placeholder="00.000.000/0000-00"
                               oninput="let v=this.value.replace(/\D/g,'').slice(0,14); v=v.replace(/^(\d{2})(\d)/,'$1.$2').replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3').replace(/\.(\d{3})(\d)/,'.$1/$2').replace(/(\d{4})(\d)/,'$1-$2'); this.value=v;"
                               class="min-w-0 flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <button type="button"
                                wire:click="buscarDadosCnpj"
                                wire:loading.attr="disabled"
                                wire:target="buscarDadosCnpj"
                                class="rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-[10px] font-black uppercase text-blue-700 hover:bg-blue-600 hover:text-white disabled:opacity-60">
                            Buscar
                        </button>
                    </div>
                    <div wire:loading wire:target="buscarDadosCnpj" class="mt-1 text-xs font-semibold uppercase text-blue-600">
                        Consultando CNPJ...
                    </div>
                    @error('cnpj') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Telefone *</label>
                    <input type="text" wire:model="telefone" inputmode="numeric" maxlength="11" placeholder="Somente numeros" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 11)" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('telefone') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">E-mail</label>
                    <input type="email" wire:model="email" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('email') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Endereco *</label>
                    <input type="text" wire:model="endereco" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold uppercase outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('endereco') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-between">
                <button type="button"
                        wire:click="excluirEmpresa"
                        class="rounded-md border border-red-200 bg-red-50 px-6 py-2 text-xs font-black uppercase text-red-700 hover:bg-red-600 hover:text-white">
                    Excluir empresa
                </button>

                <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-xs font-black uppercase text-white shadow hover:bg-blue-700">
                    Salvar empresa
                </button>
            </div>
        </form>
    @endif

    @if($abaAtiva === 'comercial')
        <form wire:submit.prevent="salvarComercial" class="max-w-3xl rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-black uppercase text-slate-800">Regras comerciais</h2>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Padroes usados no cadastro de produtos e na OS</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Desconto padrao do preco a vista (%)</label>
                    <input type="text" wire:model="desconto_vista_padrao" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('desconto_vista_padrao') <span class="text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-md bg-blue-600 px-6 py-2 text-xs font-black uppercase text-white shadow hover:bg-blue-700">
                    Salvar regras
                </button>
            </div>
        </form>
    @endif

    @if($abaAtiva === 'aparencia')
        <div class="max-w-4xl rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-black uppercase text-slate-800">Aparencia</h2>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Escolha como o sistema deve aparecer neste computador</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <button type="button" onclick="window.notvisSetTheme('light')" class="rounded-lg border-2 border-slate-200 bg-white p-5 text-left shadow-sm hover:border-blue-500">
                    <span class="mb-3 block h-20 rounded-md border border-slate-200 bg-slate-50"></span>
                    <span class="block text-sm font-black uppercase text-slate-800">Tema claro</span>
                    <span class="text-xs font-semibold text-slate-500">Visual padrao, limpo e direto.</span>
                </button>

                <button type="button" onclick="window.notvisSetTheme('dark')" class="rounded-lg border-2 border-slate-700 bg-slate-800 p-5 text-left shadow-sm hover:border-blue-400">
                    <span class="mb-3 block h-20 rounded-md border border-slate-600 bg-slate-700"></span>
                    <span class="block text-sm font-black uppercase text-white">Tema escuro</span>
                    <span class="text-xs font-semibold text-slate-300">Cinza escuro, sem ficar preto pesado.</span>
                </button>
            </div>
        </div>
    @endif

    @if($abaAtiva === 'auditoria')
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-black uppercase text-slate-800">Auditoria do sistema</h2>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Registro das principais acoes feitas pelos usuarios</p>
            </div>

            <div class="grid grid-cols-1 gap-3 border-b border-slate-200 bg-slate-50 p-4 lg:grid-cols-[1fr_.65fr_.65fr_.65fr_.6fr_.6fr_auto]">
                <input type="text" wire:model.live.debounce.350ms="auditoria_busca" placeholder="Buscar descricao, modulo ou acao" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                <select wire:model.live="auditoria_modulo" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                    <option value="">Modulo</option>
                    @foreach($modulosAuditoria as $modulo)
                        <option value="{{ $modulo }}">{{ $modulo }}</option>
                    @endforeach
                </select>
                <select wire:model.live="auditoria_acao" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                    <option value="">Acao</option>
                    @foreach($acoesAuditoria as $acao)
                        <option value="{{ $acao }}">{{ $acao }}</option>
                    @endforeach
                </select>
                <select wire:model.live="auditoria_usuario_id" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold uppercase shadow-sm outline-none focus:border-blue-500">
                    <option value="">Usuario</option>
                    @foreach($usuarios as $usuarioFiltro)
                        <option value="{{ $usuarioFiltro->id }}">{{ $usuarioFiltro->name }}</option>
                    @endforeach
                </select>
                <input type="date" wire:model.live="auditoria_data_inicio" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold shadow-sm outline-none focus:border-blue-500">
                <input type="date" wire:model.live="auditoria_data_fim" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-bold shadow-sm outline-none focus:border-blue-500">
                <button type="button" wire:click="limparFiltrosAuditoria" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 shadow-sm hover:bg-slate-100">
                    Limpar
                </button>
            </div>

            <div class="max-h-[62vh] overflow-y-auto">
                <table class="w-full min-w-[1120px] text-left text-xs">
                    <thead class="sticky top-0 z-10 bg-slate-100 text-[10px] font-black uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Data</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">Modulo</th>
                            <th class="px-4 py-3">Acao</th>
                            <th class="px-4 py-3">Descricao</th>
                            <th class="px-4 py-3">Origem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logsAuditoria as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-mono font-bold text-slate-600">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-black uppercase text-slate-700">{{ $log->usuario->name ?? 'SISTEMA' }}</div>
                                    <div class="text-[10px] font-bold uppercase text-slate-400">{{ $log->usuario?->perfilNome() ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 font-black uppercase text-slate-700">{{ $log->modulo }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-blue-50 px-2 py-1 text-[10px] font-black uppercase text-blue-700">{{ $log->acao }}</span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">
                                    {{ $log->descricao }}
                                    @if($log->dados)
                                        <div class="mt-1 max-w-xl truncate font-mono text-[10px] text-slate-400">{{ json_encode($log->dados, JSON_UNESCAPED_UNICODE) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-mono text-[10px] font-bold text-slate-500">{{ $log->ip ?: '-' }}</div>
                                    <div class="max-w-[240px] truncate text-[10px] font-semibold text-slate-400">{{ $log->user_agent ?: '-' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-xs font-black uppercase text-slate-400">Nenhum log encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($acao_admin_pendente)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4">
            <form wire:submit.prevent="confirmarAcaoAdmin" class="w-full max-w-md rounded-lg border border-slate-200 bg-white p-5 shadow-xl">
                <div class="mb-4">
                    <h2 class="text-lg font-black uppercase text-slate-800">{{ $acao_admin_titulo }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $acao_admin_descricao }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Senha do administrador</label>
                    <input type="password"
                           wire:model="senha_admin"
                           autofocus
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('senha_admin') <span class="mt-1 block text-xs font-semibold text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="cancelarConfirmacaoAdmin" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-black uppercase text-slate-600 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-xs font-black uppercase text-white shadow hover:bg-slate-800">
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
