<?php

namespace App\Livewire\Produtos;

use Livewire\Component;
use App\Models\Produto;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

class CriarProduto extends Component
{
    public $nome, $descricao_detalhada, $tipo = 'P', $marca, $categoria = 'ELÉTRICA';
    public $codigo_interno, $codigo_barras, $unidade = 'UN';
    public $preco_custo = 0, $margem_lucro = 0, $preco_venda_vista = 0;
    public $preco_venda_prazo = 0, $preco_minimo = 0;
    public $estoque_atual = 0, $estoque_minimo = 0, $estoque_maximo = 0;
    public $localizacao, $lote, $ncm, $cfop = '5102', $cst_csosn = '102', $origem = 0, $aliquota_icms = 0;
    public $ativo = true;
    public $abaAtiva = 'geral';

    public function setAba($aba) { $this->abaAtiva = $aba; }

    public function updatedPrecoCusto($value)
    {
        $this->recalcularPrecos();
    }

    public function updatedMargemLucro($value)
    {
        $this->recalcularPrecos();
    }

    public function updatedPrecoVendaPrazo($value)
    {
        $custo = (float) $this->preco_custo;
        $vendaPrazo = (float) $value;

        if ($custo > 0 && $vendaPrazo > 0) {
            $this->margem_lucro = round((($vendaPrazo - $custo) / $custo) * 100, 2);
            $this->preco_venda_vista = round($vendaPrazo * 0.95, 2);
        }
    }

    public function updatedCodigoBarras($value)
    {
        // Limpa o código para deixar apenas números
        $ean = preg_replace('/[^0-9]/', '', $value);

        // EANs geralmente têm 8, 12 ou 13 dígitos
        if (in_array(strlen($ean), [8, 12, 13, 14])) {
            $this->consultarCosmos($ean);
        }
    }

    private function consultarCosmos($ean)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Cosmos-API-Request',
                'X-Cosmos-Token' => '68COfwKMVASH-d4-2XVUqg', // Seu token ativo
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
        } catch (\Exception $e) {
            // Log de erro silencioso para não travar a tela do usuário
            //Log::error("Erro API Cosmos: " . $e->getMessage());
        }
    }

    private function recalcularPrecos()
    {
        $custo = (float) $this->preco_custo;
        $margem = (float) $this->margem_lucro;

        if ($custo > 0) {
            $this->preco_venda_prazo = round($custo + ($custo * ($margem / 100)), 2);
            $this->preco_venda_vista = round($this->preco_venda_prazo * 0.95, 2);
        }
    }

    public function salvar()
    {
        $this->validate([
            'nome' => 'required',
            'categoria' => 'required',
            'cfop' => 'required|max:4',
            'cst_csosn' => 'required|max:3',
            'preco_custo' => 'required|numeric|min:0.01',
            'marca' => 'nullable',
        ]);

        // Remove qualquer letra que possa ter vindo no código de barras
        $codigoLimpo = preg_replace('/[^0-9]/', '', $this->codigo_barras);
        $codigoFinal = empty($codigoLimpo) ? null : $codigoLimpo;

        try {
            Produto::create([
                'nome' => mb_strtoupper($this->nome, 'UTF-8'),
                'descricao_detalhada' => mb_strtoupper($this->descricao_detalhada, 'UTF-8'),
                'tipo' => substr($this->tipo, 0, 1),
                'marca' => $this->marca ? mb_strtoupper($this->marca, 'UTF-8') : null,
                'categoria' => mb_strtoupper($this->categoria, 'UTF-8'),
                'codigo_interno' => $this->codigo_interno ?: null,
                'codigo_barras' => $codigoFinal,
                'unidade' => mb_strtoupper($this->unidade, 'UTF-8'),
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
                'localizacao' => mb_strtoupper($this->localizacao, 'UTF-8'),
                'lote' => mb_strtoupper($this->lote, 'UTF-8'),
                'ncm' => $this->ncm,
                'cfop' => $this->cfop,
                'cst_csosn' => $this->cst_csosn, // Agora com no máximo 3 caracteres
                'origem' => (int)$this->origem,
                'aliquota_icms' => (float)$this->aliquota_icms,
                'ativo' => $this->ativo ? 1 : 0
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
