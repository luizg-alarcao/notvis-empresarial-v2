<?php

namespace App\Livewire\Produtos;

use Livewire\Component;
use App\Models\AuditLog;
use App\Models\Empresa;
use App\Models\Produto;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

class CriarProduto extends Component
{
    public $nome, $descricao_detalhada, $tipo = 'P', $marca, $categoria = 'ELÉTRICA';
    public $codigo_interno, $codigo_barras, $unidade = 'UN';
    public $preco_custo = '', $margem_lucro = 0, $preco_venda_vista = '';
    public $preco_venda_prazo = '', $preco_minimo = 0;
    public $estoque_atual = 0, $estoque_minimo = 0, $estoque_maximo = 0;
    public $localizacao, $lote, $ncm, $cfop = '5102', $cst_csosn = '102', $origem = 0, $aliquota_icms = 0;
    public $ativo = true;
    public $abaAtiva = 'geral';
    public bool $tentouSalvar = false;

    public function setAba($aba) { $this->abaAtiva = $aba; }

    public function updatedPrecoCusto($value)
    {
        $this->resetErrorBag('preco_custo');
        $this->recalcularPrecos();
    }

    public function updatedMargemLucro($value)
    {
        $this->resetErrorBag('margem_lucro');
        $this->recalcularPrecos();
    }

    public function updatedPrecoVendaPrazo($value)
    {
        $this->resetErrorBag('preco_venda_prazo');
        $this->resetErrorBag('preco_venda_vista');

        $custo = (float) $this->preco_custo;
        $vendaPrazo = (float) $value;

        if ($custo > 0 && $vendaPrazo > 0) {
            $this->margem_lucro = round((($vendaPrazo - $custo) / $custo) * 100, 2);
            $this->preco_venda_vista = $this->calcularPrecoVista($vendaPrazo);
        }
    }

    public function updatedCodigoBarras($value)
    {
        $this->resetErrorBag('codigo_barras');

        // Limpa o código para deixar apenas números
        $ean = $this->somenteNumeros($value);

        // EANs geralmente têm 8, 12, 13 ou 14 dígitos
        if (in_array(strlen($ean), [8, 12, 13, 14])) {
            $this->consultarCosmos($ean);
        }
    }

    private function consultarCosmos($ean)
    {
        $token = config('notvis.cosmos_token');

        if (!$token) {
            return;
        }

        try {
            $response = Http::timeout(5)->withHeaders([
                'User-Agent' => 'Cosmos-API-Request',
                'X-Cosmos-Token' => $token,
                'Content-Type' => 'application/json',
            ])->get("https://api.cosmos.bluesoft.com.br/gtins/{$ean}.json");

            if ($response->successful()) {
                $dados = $response->json();

                // Preenche os campos automaticamente
                // Usamos mb_strtoupper para manter o seu padrão de letras maiúsculas
                $this->nome = mb_strtoupper($dados['description'] ?? $this->nome, 'UTF-8');

                // A marca na Cosmos vem dentro de um array ['brand']['name']
                if (isset($dados['brand']['name'])) {
                    $this->marca = mb_strtoupper($dados['brand']['name'], 'UTF-8');
                }

                // Dica extra: Se você quiser capturar o peso para o gás de ar condicionado
                // if (isset($dados['net_weight'])) { $this->estoque_atual = $dados['net_weight'] / 1000; }

                session()->flash('info', 'PRODUTO LOCALIZADO COM SUCESSO!');
            } elseif ($response->status() == 404) {
                session()->flash('error', 'PRODUTO NÃO ENCONTRADO NA BASE COSMOS.');
            } elseif ($response->status() == 429) {
                session()->flash('error', 'LIMITE DE CONSULTAS DA API EXCEDIDO.');
            }
        } catch (\Exception) {
            session()->flash('info', 'Nao foi possivel consultar o codigo de barras agora. Preencha o produto manualmente.');
            // Log de erro silencioso para não travar a tela do usuário
        }
    }

    private function recalcularPrecos()
    {
        $custo = (float) $this->preco_custo;
        $margem = (float) $this->margem_lucro;

        if ($custo > 0) {
            $this->preco_venda_prazo = round($custo + ($custo * ($margem / 100)), 2);
            $this->preco_venda_vista = $this->calcularPrecoVista($this->preco_venda_prazo);
        }
    }

    private function calcularPrecoVista(float $valorPrazo): float
    {
        $desconto = (float) (Empresa::query()->value('desconto_vista_padrao') ?? 5);

        return round($valorPrazo * (1 - ($desconto / 100)), 2);
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

    private function prepararDadosParaValidacao(): void
    {
        $this->nome = $this->textoOuNulo($this->nome);
        $this->marca = $this->textoOuNulo($this->marca);
        $this->categoria = $this->textoOuNulo($this->categoria);
        $this->tipo = substr((string) $this->tipo, 0, 1);
        $this->unidade = $this->textoOuNulo($this->unidade);
        $this->codigo_interno = $this->textoOuNulo($this->codigo_interno);
        $this->codigo_barras = $this->somenteNumeros($this->codigo_barras) ?: null;
        $this->ncm = $this->somenteNumeros($this->ncm) ?: null;
        $this->cfop = $this->somenteNumeros($this->cfop) ?: null;
        $this->cst_csosn = $this->somenteNumeros($this->cst_csosn) ?: null;
    }

    private function regrasValidacao(): array
    {
        return [
            'nome' => ['required', 'string', 'min:3', 'max:255'],
            'marca' => ['nullable', 'string', 'max:120'],
            'categoria' => ['required', 'string', 'max:80'],
            'tipo' => ['required', 'in:P,S'],
            'unidade' => ['required', 'string', 'max:10'],
            'codigo_interno' => ['nullable', 'string', 'max:80', 'unique:produtos,codigo_interno'],
            'codigo_barras' => ['nullable', 'digits_between:8,14', 'unique:produtos,codigo_barras'],
            'descricao_detalhada' => ['nullable', 'string', 'max:1000'],
            'preco_custo' => ['required', 'numeric', 'min:0.01'],
            'margem_lucro' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'preco_venda_vista' => ['required', 'numeric', 'min:0.01'],
            'preco_venda_prazo' => ['required', 'numeric', 'min:0.01'],
            'estoque_atual' => ['required', 'numeric', 'min:0'],
            'estoque_minimo' => ['nullable', 'numeric', 'min:0'],
            'estoque_maximo' => ['nullable', 'numeric', 'min:0'],
            'localizacao' => ['nullable', 'string', 'max:120'],
            'lote' => ['nullable', 'string', 'max:120'],
            'ncm' => ['nullable', 'digits:8'],
            'cfop' => ['required', 'digits:4'],
            'cst_csosn' => ['required', 'digits_between:2,3'],
            'origem' => ['required', 'in:0,1,2'],
            'aliquota_icms' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ativo' => ['boolean'],
        ];
    }

    private function mensagensValidacao(): array
    {
        return [
            'nome.required' => 'Informe o nome do produto.',
            'nome.min' => 'O nome precisa ter pelo menos :min caracteres.',
            'categoria.required' => 'Informe a categoria do produto.',
            'tipo.in' => 'Selecione se o cadastro e produto ou servico.',
            'unidade.required' => 'Informe a unidade de venda.',
            'codigo_interno.unique' => 'Este codigo interno ja esta cadastrado.',
            'codigo_barras.digits_between' => 'O codigo de barras deve ter entre 8 e 14 numeros.',
            'codigo_barras.unique' => 'Este codigo de barras ja esta cadastrado.',
            'preco_custo.required' => 'Informe o custo unitario para cadastrar.',
            'preco_custo.min' => 'O custo unitario precisa ser maior que zero.',
            'preco_venda_vista.required' => 'Informe o valor de venda a vista.',
            'preco_venda_vista.min' => 'O valor a vista precisa ser maior que zero.',
            'preco_venda_prazo.required' => 'Informe o valor de venda a prazo.',
            'preco_venda_prazo.min' => 'O valor a prazo precisa ser maior que zero.',
            'estoque_atual.required' => 'Informe a quantidade em estoque. Use zero quando nao houver estoque.',
            'estoque_atual.min' => 'A quantidade em estoque nao pode ser negativa.',
            'estoque_minimo.min' => 'O estoque minimo nao pode ser negativo.',
            'estoque_maximo.min' => 'O estoque maximo nao pode ser negativo.',
            'ncm.digits' => 'O NCM deve ter exatamente 8 numeros.',
            'cfop.required' => 'Informe o CFOP.',
            'cfop.digits' => 'O CFOP deve ter exatamente 4 numeros.',
            'cst_csosn.required' => 'Informe o CST/CSOSN.',
            'cst_csosn.digits_between' => 'O CST/CSOSN deve ter 2 ou 3 numeros.',
            'aliquota_icms.max' => 'A aliquota de ICMS nao pode passar de 100%.',
            'numeric' => 'Informe um numero valido em :attribute.',
            'min' => 'O campo :attribute nao pode ser menor que :min.',
            'max' => 'O campo :attribute ultrapassou o limite permitido.',
            'required' => 'Preencha o campo :attribute.',
        ];
    }

    private function nomesCamposValidacao(): array
    {
        return [
            'preco_custo' => 'custo unitario',
            'preco_venda_vista' => 'venda a vista',
            'preco_venda_prazo' => 'venda a prazo',
            'estoque_atual' => 'quantidade em estoque',
            'estoque_minimo' => 'estoque minimo',
            'estoque_maximo' => 'estoque maximo',
            'codigo_barras' => 'codigo de barras',
            'codigo_interno' => 'codigo interno',
            'cst_csosn' => 'CST/CSOSN',
            'aliquota_icms' => 'aliquota de ICMS',
        ];
    }

    public function salvar()
    {
        $this->tentouSalvar = true;
        $this->prepararDadosParaValidacao();

        $this->validate(
            $this->regrasValidacao(),
            $this->mensagensValidacao(),
            $this->nomesCamposValidacao()
        );

        try {
            $produto = Produto::create([
                'nome' => $this->textoMaiusculoOuNulo($this->nome),
                'descricao_detalhada' => $this->textoMaiusculoOuNulo($this->descricao_detalhada),
                'tipo' => substr($this->tipo, 0, 1),
                'marca' => $this->textoMaiusculoOuNulo($this->marca),
                'categoria' => $this->textoMaiusculoOuNulo($this->categoria),
                'codigo_interno' => $this->codigo_interno,
                'codigo_barras' => $this->codigo_barras,
                'unidade' => $this->textoMaiusculoOuNulo($this->unidade),
                'preco_custo' => (float)$this->preco_custo,
                'margem_lucro' => (float)$this->margem_lucro,
                'preco_venda_vista' => (float)$this->preco_venda_vista,
                'preco_venda_prazo' => (float)$this->preco_venda_prazo,
                'preco_venda_vista_desconto' => (float)$this->preco_minimo,
                'permite_desconto' => 1,
                'estoque_atual' => (float)$this->estoque_atual,
                'estoque_minimo' => (float)$this->estoque_minimo,
                'estoque_maximo' => (float)$this->estoque_maximo,
                'controla_estoque' => 1,
                'localizacao' => $this->textoMaiusculoOuNulo($this->localizacao),
                'lote' => $this->textoMaiusculoOuNulo($this->lote),
                'ncm' => $this->ncm,
                'cfop' => $this->cfop,
                'cst_csosn' => $this->cst_csosn, // Agora com no máximo 3 caracteres
                'origem' => (int)$this->origem,
                'aliquota_icms' => (float)$this->aliquota_icms,
                'ativo' => $this->ativo ? 1 : 0
            ]);

            AuditLog::registrar('produtos', 'produto_criado', 'Produto ou servico cadastrado.', $produto, [
                'nome' => $produto->nome,
                'tipo' => $produto->tipo,
                'codigo_barras' => $produto->codigo_barras,
            ]);

            session()->flash('message', 'PRODUTO CADASTRADO COM SUCESSO!');
            return redirect()->route('produtos.index');

        } catch (\Exception $e) {
            session()->flash('error', 'ERRO AO SALVAR: ' . $e->getMessage());
        }
    }

    #[Layout('layouts.app')]
    public function render() { return view('livewire.produtos.criar-produto'); }
}
