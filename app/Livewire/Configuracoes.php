<?php

namespace App\Livewire;

use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Configuracoes extends Component
{
    public $abaAtiva = 'funcionarios';

    public $funcionario_id;
    public $nome_funcionario = '';
    public $cargo_funcionario = 'ATENDENTE';
    public $ativo_funcionario = true;

    public $usuario_id;
    public $nome_usuario = '';
    public $email_usuario = '';
    public $perfil_usuario = 'ATENDENTE';
    public $senha_usuario = '';
    public $ativo_usuario = true;
    public string $auditoria_busca = '';
    public string $auditoria_modulo = '';
    public string $auditoria_acao = '';
    public string $auditoria_usuario_id = '';
    public string $auditoria_data_inicio = '';
    public string $auditoria_data_fim = '';

    public $empresa_id;
    public $nome_fantasia = '';
    public $razao_social = '';
    public $cnpj = '';
    public $telefone = '';
    public $email = '';
    public $endereco = '';
    public $desconto_vista_padrao = 5;
    public $senha_admin = '';
    public ?string $acao_admin_pendente = null;
    public array $acao_admin_parametros = [];
    public string $acao_admin_titulo = '';
    public string $acao_admin_descricao = '';
    public ?string $ultimo_cnpj_consultado = null;

    public function mount()
    {
        $aba = request()->query('aba');
        if (in_array($aba, ['funcionarios', 'usuarios', 'empresa', 'comercial', 'aparencia', 'auditoria'], true)) {
            $this->abaAtiva = $aba;
        }

        $empresa = Empresa::first();

        if ($empresa) {
            $this->empresa_id = $empresa->id;
            $this->nome_fantasia = $empresa->nome_fantasia;
            $this->razao_social = $empresa->razao_social;
            $this->cnpj = $this->formatarCnpj($empresa->cnpj);
            $this->telefone = $empresa->telefone;
            $this->email = $empresa->email;
            $this->endereco = $empresa->endereco;
            $this->desconto_vista_padrao = $empresa->desconto_vista_padrao ?? 5;
        }
    }

    public function setAba($aba)
    {
        if (!in_array($aba, ['funcionarios', 'usuarios', 'empresa', 'comercial', 'aparencia', 'auditoria'], true)) {
            return;
        }

        $this->abaAtiva = $aba;
    }

    public function updatedCnpj($value)
    {
        $this->resetErrorBag('cnpj');
        $this->cnpj = $this->formatarCnpj($value);

        $cnpj = $this->somenteNumeros($this->cnpj);
        if (
            strlen($cnpj) === 14
            && $this->cnpjValido($cnpj)
            && $cnpj !== $this->ultimo_cnpj_consultado
            && !app()->runningUnitTests()
        ) {
            $this->buscarDadosCnpj(false);
        }
    }

    private function abrirConfirmacaoAdmin(string $acao, string $titulo, string $descricao, array $parametros = []): void
    {
        $this->acao_admin_pendente = $acao;
        $this->acao_admin_parametros = $parametros;
        $this->acao_admin_titulo = $titulo;
        $this->acao_admin_descricao = $descricao;
        $this->senha_admin = '';
        $this->resetErrorBag('senha_admin');
    }

    public function cancelarConfirmacaoAdmin()
    {
        $this->acao_admin_pendente = null;
        $this->acao_admin_parametros = [];
        $this->acao_admin_titulo = '';
        $this->acao_admin_descricao = '';
        $this->senha_admin = '';
        $this->resetErrorBag('senha_admin');
    }

    public function confirmarAcaoAdmin()
    {
        if (!$this->acao_admin_pendente) {
            $this->cancelarConfirmacaoAdmin();
            return;
        }

        $usuario = auth()->user();

        if (!$usuario || !$usuario->isAdmin() || !Hash::check((string) $this->senha_admin, $usuario->password)) {
            $this->addError('senha_admin', 'Senha de administrador incorreta.');
            return;
        }

        $acao = $this->acao_admin_pendente;
        $parametros = $this->acao_admin_parametros;
        $this->cancelarConfirmacaoAdmin();

        match ($acao) {
            'salvarFuncionario' => $this->salvarFuncionarioConfirmado(),
            'alternarFuncionario' => $this->alternarFuncionarioConfirmado((int) ($parametros['id'] ?? 0)),
            'excluirFuncionario' => $this->excluirFuncionarioConfirmado((int) ($parametros['id'] ?? 0)),
            'salvarUsuario' => $this->salvarUsuarioConfirmado(),
            'alternarUsuario' => $this->alternarUsuarioConfirmado((int) ($parametros['id'] ?? 0)),
            'excluirUsuario' => $this->excluirUsuarioConfirmado((int) ($parametros['id'] ?? 0)),
            'salvarEmpresa' => $this->salvarEmpresaConfirmado(),
            'excluirEmpresa' => $this->excluirEmpresaConfirmado(),
            'salvarComercial' => $this->salvarComercialConfirmado(),
            default => null,
        };
    }

    private function somenteNumeros($valor): string
    {
        return preg_replace('/[^0-9]/', '', (string) $valor);
    }

    private function textoOuNulo($valor): ?string
    {
        $texto = trim((string) $valor);

        return $texto === '' ? null : $texto;
    }

    private function textoMaiusculoOuNulo($valor): ?string
    {
        $texto = $this->textoOuNulo($valor);

        return $texto ? mb_strtoupper($texto, 'UTF-8') : null;
    }

    private function formatarCnpj($valor): string
    {
        $cnpj = substr($this->somenteNumeros($valor), 0, 14);
        $tamanho = strlen($cnpj);

        if ($tamanho <= 2) {
            return $cnpj;
        }

        if ($tamanho <= 5) {
            return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2);
        }

        if ($tamanho <= 8) {
            return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5);
        }

        if ($tamanho <= 12) {
            return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8);
        }

        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12);
    }

    private function cnpjValido($valor): bool
    {
        $cnpj = $this->somenteNumeros($valor);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $multiplicadoresPrimeiro = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $multiplicadoresSegundo = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $soma = 0;
        for ($i = 0; $i < 12; $i++) {
            $soma += (int) $cnpj[$i] * $multiplicadoresPrimeiro[$i];
        }

        $resto = $soma % 11;
        $primeiroDigito = $resto < 2 ? 0 : 11 - $resto;

        if ((int) $cnpj[12] !== $primeiroDigito) {
            return false;
        }

        $soma = 0;
        for ($i = 0; $i < 13; $i++) {
            $soma += (int) $cnpj[$i] * $multiplicadoresSegundo[$i];
        }

        $resto = $soma % 11;
        $segundoDigito = $resto < 2 ? 0 : 11 - $resto;

        return (int) $cnpj[13] === $segundoDigito;
    }

    public function buscarDadosCnpj(bool $mostrarMensagem = true)
    {
        $cnpj = $this->somenteNumeros($this->cnpj);
        $this->cnpj = $this->formatarCnpj($cnpj);

        if (strlen($cnpj) !== 14) {
            if ($mostrarMensagem) {
                $this->addError('cnpj', 'Informe os 14 digitos do CNPJ para buscar.');
            }
            return;
        }

        if (!$this->cnpjValido($cnpj)) {
            if ($mostrarMensagem) {
                $this->addError('cnpj', 'Informe um CNPJ valido para buscar.');
            }
            return;
        }

        $this->ultimo_cnpj_consultado = $cnpj;

        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

            if (!$response->successful()) {
                if ($mostrarMensagem) {
                    session()->flash('info', 'Nao encontramos dados publicos para este CNPJ.');
                }
                return;
            }

            $dados = $response->json();
            $razaoSocial = $dados['razao_social'] ?? null;
            $nomeFantasia = $dados['nome_fantasia'] ?? null;

            $this->razao_social = $this->textoMaiusculoOuNulo($razaoSocial) ?: $this->razao_social;
            $this->nome_fantasia = $this->textoMaiusculoOuNulo($nomeFantasia) ?: $this->nome_fantasia ?: $this->razao_social;
            $this->telefone = $this->somenteNumeros($dados['ddd_telefone_1'] ?? $dados['telefone'] ?? $this->telefone);
            $this->email = $this->textoOuNulo($dados['email'] ?? $this->email);
            $this->endereco = $this->montarEnderecoCnpj($dados) ?: $this->endereco;

            foreach (['cnpj', 'nome_fantasia', 'razao_social', 'telefone', 'email', 'endereco'] as $campo) {
                $this->resetErrorBag($campo);
            }

            if ($mostrarMensagem) {
                session()->flash('success', 'Dados da empresa preenchidos pelo CNPJ.');
            }
        } catch (\Throwable $e) {
            if ($mostrarMensagem) {
                session()->flash('info', 'Nao foi possivel consultar o CNPJ agora. Voce ainda pode preencher manualmente.');
            }
        }
    }

    private function montarEnderecoCnpj(array $dados): ?string
    {
        $partes = array_filter([
            $dados['logradouro'] ?? null,
            $dados['numero'] ?? null,
            $dados['complemento'] ?? null,
            $dados['bairro'] ?? null,
            $dados['municipio'] ?? null,
            $dados['uf'] ?? null,
            isset($dados['cep']) ? 'CEP ' . $this->somenteNumeros($dados['cep']) : null,
        ]);

        return $partes ? $this->textoMaiusculoOuNulo(implode(', ', $partes)) : null;
    }

    public function salvarFuncionario()
    {
        $this->abrirConfirmacaoAdmin(
            'salvarFuncionario',
            $this->funcionario_id ? 'Salvar funcionario' : 'Cadastrar funcionario',
            'Digite a senha de administrador para gravar este funcionario.'
        );
    }

    private function salvarFuncionarioConfirmado()
    {
        $this->nome_funcionario = mb_strtoupper(trim((string) $this->nome_funcionario), 'UTF-8');

        $this->validate([
            'nome_funcionario' => 'required|min:3',
            'cargo_funcionario' => 'required|in:ATENDENTE,MECANICO,GERENTE',
        ], [
            'nome_funcionario.required' => 'Informe o nome do funcionario.',
            'nome_funcionario.min' => 'O nome precisa ter pelo menos 3 caracteres.',
            'cargo_funcionario.required' => 'Informe o cargo.',
        ]);

        $funcionario = Funcionario::updateOrCreate(
            ['id' => $this->funcionario_id],
            [
                'nome' => $this->nome_funcionario,
                'cargo' => $this->cargo_funcionario,
                'ativo' => (bool) $this->ativo_funcionario,
            ]
        );

        AuditLog::registrar(
            'configuracoes',
            $this->funcionario_id ? 'funcionario_atualizado' : 'funcionario_criado',
            'Funcionario salvo nas configuracoes.',
            $funcionario,
            ['nome' => $funcionario->nome, 'cargo' => $funcionario->cargo, 'ativo' => $funcionario->ativo]
        );

        $this->limparFuncionario();
        session()->flash('success', 'Funcionario salvo com sucesso.');
    }

    public function editarFuncionario($id)
    {
        $funcionario = Funcionario::findOrFail($id);

        $this->funcionario_id = $funcionario->id;
        $this->nome_funcionario = $funcionario->nome;
        $this->cargo_funcionario = $funcionario->cargo;
        $this->ativo_funcionario = (bool) $funcionario->ativo;
        $this->abaAtiva = 'funcionarios';
    }

    public function limparFuncionario()
    {
        $this->funcionario_id = null;
        $this->nome_funcionario = '';
        $this->cargo_funcionario = 'ATENDENTE';
        $this->ativo_funcionario = true;
        $this->resetErrorBag();
    }

    public function alternarFuncionario($id)
    {
        $this->abrirConfirmacaoAdmin(
            'alternarFuncionario',
            'Alterar status do funcionario',
            'Digite a senha de administrador para ativar ou desativar este funcionario.',
            ['id' => (int) $id]
        );
    }

    private function alternarFuncionarioConfirmado(int $id)
    {
        $funcionario = Funcionario::findOrFail($id);
        $funcionario->ativo = !$funcionario->ativo;
        $funcionario->save();

        AuditLog::registrar('configuracoes', 'funcionario_status', 'Status do funcionario alterado.', $funcionario, [
            'ativo' => $funcionario->ativo,
        ]);
    }

    public function excluirFuncionario($id)
    {
        $this->abrirConfirmacaoAdmin(
            'excluirFuncionario',
            'Excluir funcionario',
            'Digite a senha de administrador para excluir ou desativar este funcionario.',
            ['id' => (int) $id]
        );
    }

    private function excluirFuncionarioConfirmado(int $id)
    {
        $funcionario = Funcionario::findOrFail($id);

        if ($funcionario->ordensAtendidas()->exists() || $funcionario->ordensMecanico()->exists()) {
            $funcionario->ativo = false;
            $funcionario->save();
            AuditLog::registrar('configuracoes', 'funcionario_desativado', 'Funcionario com historico foi desativado.', $funcionario);
            session()->flash('info', 'Funcionario ja possui historico e foi apenas desativado.');
            return;
        }

        AuditLog::registrar('configuracoes', 'funcionario_excluido', 'Funcionario excluido das configuracoes.', $funcionario, [
            'nome' => $funcionario->nome,
        ]);
        $funcionario->delete();
        session()->flash('success', 'Funcionario excluido com sucesso.');
    }

    public function salvarUsuario()
    {
        $this->abrirConfirmacaoAdmin(
            'salvarUsuario',
            $this->usuario_id ? 'Salvar usuario' : 'Cadastrar usuario',
            'Digite a senha de administrador para gravar este usuario.'
        );
    }

    private function salvarUsuarioConfirmado()
    {
        $this->nome_usuario = mb_strtoupper(trim((string) $this->nome_usuario), 'UTF-8');
        $this->email_usuario = mb_strtolower(trim((string) $this->email_usuario), 'UTF-8');

        $regrasSenha = $this->usuario_id
            ? ['nullable', 'string', 'min:8']
            : ['required', 'string', 'min:8'];

        $this->validate([
            'nome_usuario' => ['required', 'string', 'min:3', 'max:120'],
            'email_usuario' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($this->usuario_id)],
            'perfil_usuario' => ['required', Rule::in(array_keys(User::PERFIS))],
            'senha_usuario' => $regrasSenha,
        ], [
            'nome_usuario.required' => 'Informe o nome do usuario.',
            'nome_usuario.min' => 'O nome precisa ter pelo menos 3 caracteres.',
            'email_usuario.required' => 'Informe o e-mail do usuario.',
            'email_usuario.email' => 'Informe um e-mail valido.',
            'email_usuario.unique' => 'Este e-mail ja esta cadastrado.',
            'perfil_usuario.required' => 'Informe o perfil do usuario.',
            'senha_usuario.required' => 'Informe a senha do usuario.',
            'senha_usuario.min' => 'A senha precisa ter pelo menos 8 caracteres.',
        ]);

        $dados = [
            'name' => $this->nome_usuario,
            'email' => $this->email_usuario,
            'perfil' => $this->perfil_usuario,
            'ativo' => (bool) $this->ativo_usuario,
        ];

        if ($this->senha_usuario !== '') {
            $dados['password'] = $this->senha_usuario;
        }

        if ($this->usuario_id && auth()->id() === (int) $this->usuario_id) {
            $dados['ativo'] = true;
        }

        $usuario = User::updateOrCreate(['id' => $this->usuario_id], $dados);

        AuditLog::registrar(
            'usuarios',
            $this->usuario_id ? 'usuario_atualizado' : 'usuario_criado',
            'Usuario salvo nas configuracoes.',
            $usuario,
            ['email' => $usuario->email, 'perfil' => $usuario->perfil, 'ativo' => $usuario->ativo]
        );

        $this->limparUsuario();
        session()->flash('success', 'Usuario salvo com sucesso.');
    }

    public function editarUsuario($id)
    {
        $usuario = User::findOrFail($id);

        $this->usuario_id = $usuario->id;
        $this->nome_usuario = $usuario->name;
        $this->email_usuario = $usuario->email;
        $this->perfil_usuario = $usuario->perfil;
        $this->ativo_usuario = (bool) $usuario->ativo;
        $this->senha_usuario = '';
        $this->abaAtiva = 'usuarios';
    }

    public function limparUsuario()
    {
        $this->usuario_id = null;
        $this->nome_usuario = '';
        $this->email_usuario = '';
        $this->perfil_usuario = 'ATENDENTE';
        $this->senha_usuario = '';
        $this->ativo_usuario = true;
        $this->resetErrorBag();
    }

    public function alternarUsuario($id)
    {
        $this->abrirConfirmacaoAdmin(
            'alternarUsuario',
            'Alterar status do usuario',
            'Digite a senha de administrador para ativar ou desativar este usuario.',
            ['id' => (int) $id]
        );
    }

    private function alternarUsuarioConfirmado(int $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            session()->flash('info', 'Voce nao pode desativar o proprio usuario logado.');
            return;
        }

        if ($usuario->perfil === 'ADMIN' && $usuario->ativo && User::where('perfil', 'ADMIN')->where('ativo', true)->count() <= 1) {
            session()->flash('info', 'O sistema precisa manter pelo menos um administrador ativo.');
            return;
        }

        $usuario->ativo = !$usuario->ativo;
        $usuario->save();

        AuditLog::registrar('usuarios', 'usuario_status', 'Status do usuario alterado.', $usuario, [
            'ativo' => $usuario->ativo,
        ]);
    }

    public function excluirUsuario($id)
    {
        $this->abrirConfirmacaoAdmin(
            'excluirUsuario',
            'Excluir usuario',
            'Digite a senha de administrador para excluir ou desativar este usuario.',
            ['id' => (int) $id]
        );
    }

    private function excluirUsuarioConfirmado(int $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === auth()->id()) {
            session()->flash('info', 'Voce nao pode excluir o proprio usuario logado.');
            return;
        }

        if ($usuario->perfil === 'ADMIN' && User::where('perfil', 'ADMIN')->where('ativo', true)->count() <= 1) {
            session()->flash('info', 'O sistema precisa manter pelo menos um administrador ativo.');
            return;
        }

        AuditLog::registrar('usuarios', 'usuario_excluido', 'Usuario excluido das configuracoes.', $usuario, [
            'email' => $usuario->email,
            'perfil' => $usuario->perfil,
        ]);

        $usuario->delete();
        session()->flash('success', 'Usuario excluido com sucesso.');
    }

    public function salvarEmpresa()
    {
        $this->abrirConfirmacaoAdmin(
            'salvarEmpresa',
            'Salvar empresa',
            'Digite a senha de administrador para salvar os dados da empresa.'
        );
    }

    private function salvarEmpresaConfirmado()
    {
        $this->nome_fantasia = $this->textoMaiusculoOuNulo($this->nome_fantasia);
        $this->razao_social = $this->textoMaiusculoOuNulo($this->razao_social);
        $this->endereco = $this->textoMaiusculoOuNulo($this->endereco);
        $this->email = $this->textoOuNulo($this->email);

        $cnpjNumeros = $this->somenteNumeros($this->cnpj);
        $telefoneNumeros = $this->somenteNumeros($this->telefone);
        $this->cnpj = $this->formatarCnpj($cnpjNumeros);
        $this->telefone = $telefoneNumeros;

        Validator::make([
            'nome_fantasia' => $this->nome_fantasia,
            'razao_social' => $this->razao_social,
            'cnpj' => $cnpjNumeros,
            'telefone' => $telefoneNumeros,
            'email' => $this->email,
            'endereco' => $this->endereco,
        ], [
            'nome_fantasia' => ['required', 'string', 'min:3', 'max:120'],
            'razao_social' => ['required', 'string', 'min:3', 'max:180'],
            'cnpj' => [
                'required',
                'digits:14',
                Rule::unique('empresas', 'cnpj')->ignore($this->empresa_id),
                function ($attribute, $value, $fail) {
                    if (!$this->cnpjValido($value)) {
                        $fail('Informe um CNPJ valido.');
                    }
                },
            ],
            'telefone' => ['required', 'digits_between:10,11'],
            'email' => ['nullable', 'email', 'max:120'],
            'endereco' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'nome_fantasia.required' => 'Informe o nome fantasia.',
            'nome_fantasia.min' => 'O nome fantasia precisa ter pelo menos 3 caracteres.',
            'razao_social.required' => 'Informe a razao social.',
            'razao_social.min' => 'A razao social precisa ter pelo menos 3 caracteres.',
            'cnpj.required' => 'Informe o CNPJ.',
            'cnpj.digits' => 'O CNPJ deve ter 14 digitos.',
            'cnpj.unique' => 'Este CNPJ ja esta cadastrado.',
            'telefone.required' => 'Informe o telefone da empresa.',
            'telefone.digits_between' => 'O telefone deve ter 10 ou 11 digitos.',
            'email.email' => 'Informe um e-mail valido.',
            'endereco.required' => 'Informe o endereco da empresa.',
            'endereco.min' => 'O endereco precisa ter pelo menos 5 caracteres.',
        ])->validate();

        $empresa = Empresa::updateOrCreate(
            ['id' => $this->empresa_id],
            [
                'nome_fantasia' => $this->nome_fantasia,
                'razao_social' => $this->razao_social,
                'cnpj' => $cnpjNumeros,
                'telefone' => $telefoneNumeros,
                'email' => $this->email ?: null,
                'endereco' => $this->endereco,
            ]
        );

        $this->empresa_id = $empresa->id;
        $this->cnpj = $this->formatarCnpj($empresa->cnpj);
        $this->telefone = $empresa->telefone;
        AuditLog::registrar('configuracoes', 'empresa_salva', 'Dados da empresa foram salvos.', $empresa, [
            'razao_social' => $empresa->razao_social,
            'cnpj' => $empresa->cnpj,
        ]);
        session()->flash('success', 'Dados da empresa salvos com sucesso.');
    }

    public function excluirEmpresa()
    {
        $this->abrirConfirmacaoAdmin(
            'excluirEmpresa',
            'Excluir empresa',
            'Digite a senha de administrador para excluir a empresa cadastrada.'
        );
    }

    private function excluirEmpresaConfirmado()
    {
        if (!$this->empresa_id) {
            session()->flash('info', 'Nenhuma empresa cadastrada para excluir.');
            return;
        }

        $empresa = Empresa::findOrFail($this->empresa_id);
        AuditLog::registrar('configuracoes', 'empresa_excluida', 'Empresa cadastrada foi excluida.', $empresa, [
            'razao_social' => $empresa->razao_social,
            'cnpj' => $empresa->cnpj,
        ]);

        $empresa->delete();

        $this->empresa_id = null;
        $this->nome_fantasia = '';
        $this->razao_social = '';
        $this->cnpj = '';
        $this->telefone = '';
        $this->email = '';
        $this->endereco = '';
        $this->ultimo_cnpj_consultado = null;

        session()->flash('success', 'Empresa excluida com sucesso.');
    }

    public function salvarComercial()
    {
        $this->abrirConfirmacaoAdmin(
            'salvarComercial',
            'Salvar regras comerciais',
            'Digite a senha de administrador para alterar as regras comerciais.'
        );
    }

    private function salvarComercialConfirmado()
    {
        $this->desconto_vista_padrao = str_replace(',', '.', (string) $this->desconto_vista_padrao);

        $this->validate([
            'desconto_vista_padrao' => 'required|numeric|min:0|max:100',
        ], [
            'desconto_vista_padrao.required' => 'Informe o desconto padrao a vista.',
            'desconto_vista_padrao.numeric' => 'Informe um numero valido.',
            'desconto_vista_padrao.max' => 'O desconto nao pode passar de 100%.',
        ]);

        $empresa = Empresa::first();

        if (!$empresa) {
            $empresa = Empresa::create([
                'nome_fantasia' => 'NOTVIS',
                'razao_social' => 'EMPRESA NAO CONFIGURADA',
                'cnpj' => '11222333000181',
            ]);
        }

        $empresa->desconto_vista_padrao = (float) $this->desconto_vista_padrao;
        $empresa->save();

        $this->empresa_id = $empresa->id;
        AuditLog::registrar('configuracoes', 'regras_comerciais', 'Regras comerciais foram alteradas.', $empresa, [
            'desconto_vista_padrao' => $empresa->desconto_vista_padrao,
        ]);
        session()->flash('success', 'Configuracoes comerciais salvas com sucesso.');
    }

    public function limparFiltrosAuditoria()
    {
        $this->auditoria_busca = '';
        $this->auditoria_modulo = '';
        $this->auditoria_acao = '';
        $this->auditoria_usuario_id = '';
        $this->auditoria_data_inicio = '';
        $this->auditoria_data_fim = '';
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $auditoriaQuery = AuditLog::query()
            ->with('usuario')
            ->when($this->auditoria_busca, function ($query) {
                $termo = '%' . $this->auditoria_busca . '%';
                $query->where(function ($subQuery) use ($termo) {
                    $subQuery->where('descricao', 'like', $termo)
                        ->orWhere('modulo', 'like', $termo)
                        ->orWhere('acao', 'like', $termo);
                });
            })
            ->when($this->auditoria_modulo, fn ($query) => $query->where('modulo', $this->auditoria_modulo))
            ->when($this->auditoria_acao, fn ($query) => $query->where('acao', $this->auditoria_acao))
            ->when($this->auditoria_usuario_id, fn ($query) => $query->where('user_id', $this->auditoria_usuario_id))
            ->when($this->auditoria_data_inicio, fn ($query) => $query->whereDate('created_at', '>=', $this->auditoria_data_inicio))
            ->when($this->auditoria_data_fim, fn ($query) => $query->whereDate('created_at', '<=', $this->auditoria_data_fim));

        return view('livewire.configuracoes', [
            'funcionarios' => Funcionario::orderBy('ativo', 'desc')->orderBy('nome')->get(),
            'usuarios' => User::orderBy('ativo', 'desc')->orderBy('name')->get(),
            'perfisUsuario' => User::PERFIS,
            'logsAuditoria' => (clone $auditoriaQuery)->latest()->limit(80)->get(),
            'modulosAuditoria' => AuditLog::query()->select('modulo')->distinct()->orderBy('modulo')->pluck('modulo'),
            'acoesAuditoria' => AuditLog::query()->select('acao')->distinct()->orderBy('acao')->pluck('acao'),
        ]);
    }
}
