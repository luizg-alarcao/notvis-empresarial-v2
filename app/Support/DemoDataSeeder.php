<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\EstoqueMovimentacao;
use App\Models\Funcionario;
use App\Models\OrdemServico;
use App\Models\OsItem;
use App\Models\Produto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder
{
    public function run(?User $usuario = null): array
    {
        return DB::transaction(function () use ($usuario) {
            $empresa = $this->empresa();
            $usuarios = $this->usuarios();
            $funcionarios = $this->funcionarios();
            $clientes = $this->clientes();
            $produtos = $this->produtos($usuario);
            $ordens = $this->ordensServico($clientes, $funcionarios, $produtos, $usuario);

            AuditLog::registrar(
                'demonstracao',
                'dados_demo',
                'Dados de demonstracao preparados para apresentacao.',
                $empresa,
                [
                    'clientes' => count($clientes),
                    'produtos' => count($produtos),
                    'ordens' => count($ordens),
                ]
            );

            return [
                'empresa' => $empresa->nome_fantasia,
                'usuarios' => count($usuarios),
                'funcionarios' => count($funcionarios),
                'clientes' => count($clientes),
                'produtos' => count($produtos),
                'ordens' => count($ordens),
                'movimentacoes' => EstoqueMovimentacao::count(),
            ];
        });
    }

    private function empresa(): Empresa
    {
        return Empresa::updateOrCreate(
            ['cnpj' => '11222333000181'],
            [
                'nome_fantasia' => 'AUTO ELETRICA ROSEIRA',
                'razao_social' => 'AUTO ELETRICA E ACESSORIOS ROSEIRA LTDA',
                'telefone' => '44998101318',
                'email' => 'contato@notvisdemo.com.br',
                'endereco' => 'PR 323, SN, KM 254,5, ZONA RURAL, TAPEJARA, PR',
                'desconto_vista_padrao' => 5,
            ]
        );
    }

    private function usuarios(): array
    {
        $dados = [
            ['name' => 'ADMIN DEMO', 'email' => 'admin@notvis.com', 'perfil' => 'ADMIN'],
            ['name' => 'GERENTE DEMO', 'email' => 'gerente@notvis.com', 'perfil' => 'GERENTE'],
            ['name' => 'ATENDENTE DEMO', 'email' => 'atendente@notvis.com', 'perfil' => 'ATENDENTE'],
            ['name' => 'ESTOQUE DEMO', 'email' => 'estoque@notvis.com', 'perfil' => 'ESTOQUE'],
        ];

        $usuarios = [];
        foreach ($dados as $usuario) {
            $usuarios[$usuario['perfil']] = User::updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'perfil' => $usuario['perfil'],
                    'ativo' => true,
                    'password' => Hash::make('Notvis@2026'),
                ]
            );
        }

        return $usuarios;
    }

    private function funcionarios(): array
    {
        $dados = [
            ['nome' => 'GUSTAVO', 'cargo' => 'ATENDENTE'],
            ['nome' => 'MARCOS', 'cargo' => 'MECANICO'],
            ['nome' => 'ANDERSON', 'cargo' => 'MECANICO'],
            ['nome' => 'LUIZ', 'cargo' => 'GERENTE'],
        ];

        $funcionarios = [];
        foreach ($dados as $funcionario) {
            $funcionarios[$funcionario['nome']] = Funcionario::updateOrCreate(
                ['nome' => $funcionario['nome'], 'cargo' => $funcionario['cargo']],
                ['ativo' => true]
            );
        }

        return $funcionarios;
    }

    private function clientes(): array
    {
        $dados = [
            [
                'nome' => 'MARINS SILVA POSTO EPP',
                'cpf_cnpj' => '11222333000181',
                'whatsapp' => '92979216430',
                'email' => 'posto.marins@geradornv.com.br',
                'cidade' => 'Manaus',
                'estado' => 'AM',
                'limite_credito' => 15000,
            ],
            [
                'nome' => 'JR SILVA TRANSPORTES',
                'cpf_cnpj' => '04252011000110',
                'whatsapp' => '44999655421',
                'email' => 'jrsilva.transportes@email.com',
                'cidade' => 'Tapejara',
                'estado' => 'PR',
                'limite_credito' => 8000,
            ],
            [
                'nome' => 'ALICE DAIANA DA SILVA',
                'cpf_cnpj' => '52998224725',
                'whatsapp' => '44998110318',
                'email' => 'alice.silva@email.com',
                'cidade' => 'Cianorte',
                'estado' => 'PR',
                'limite_credito' => 3000,
            ],
            [
                'nome' => 'ANTONIO CARLOS DO NASCIMENTO',
                'cpf_cnpj' => '11144477735',
                'whatsapp' => '44998110315',
                'email' => 'antonio@email.com',
                'cidade' => 'Umuarama',
                'estado' => 'PR',
                'limite_credito' => 4500,
            ],
            [
                'nome' => 'ANDREA E AYLA ALIMENTOS LTDA',
                'cpf_cnpj' => '27865757000102',
                'whatsapp' => '43994089890',
                'email' => 'financeiro@andreaylaalimentos.com.br',
                'cidade' => 'Porto Alegre',
                'estado' => 'RS',
                'limite_credito' => 12000,
            ],
            [
                'nome' => 'CONSUMIDOR FINAL',
                'cpf_cnpj' => '12345678909',
                'whatsapp' => '00000000000',
                'email' => 'consumidor@email.com',
                'cidade' => 'Tapejara',
                'estado' => 'PR',
                'limite_credito' => 0,
            ],
        ];

        $clientes = [];
        foreach ($dados as $cliente) {
            $clientes[$cliente['nome']] = Cliente::updateOrCreate(
                ['cpf_cnpj' => $cliente['cpf_cnpj']],
                $cliente + [
                    'cep' => '87430000',
                    'rua' => 'AVENIDA PRINCIPAL',
                    'numero' => '100',
                    'bairro' => 'CENTRO',
                ]
            );
        }

        return $clientes;
    }

    private function produtos(?User $usuario): array
    {
        $dados = [
            ['codigo_interno' => 'P001', 'codigo_barras' => '7890000000011', 'nome' => 'LAMPADA H4 24V PHILIPS', 'marca' => 'PHILIPS', 'categoria' => 'ELETRICA', 'preco_custo' => 35, 'preco_venda_vista' => 57, 'preco_venda_prazo' => 60, 'estoque_atual' => 24, 'estoque_minimo' => 8, 'estoque_maximo' => 80, 'localizacao' => 'A1'],
            ['codigo_interno' => 'P002', 'codigo_barras' => '7890000000028', 'nome' => 'LAMPADA 67 24V', 'marca' => 'HELLA', 'categoria' => 'ELETRICA', 'preco_custo' => 2.8, 'preco_venda_vista' => 4.75, 'preco_venda_prazo' => 5, 'estoque_atual' => 38, 'estoque_minimo' => 15, 'estoque_maximo' => 150, 'localizacao' => 'A2'],
            ['codigo_interno' => 'P003', 'codigo_barras' => '7890000000035', 'nome' => 'CAPA DE PORCA BOLIVIANA 32/33', 'marca' => 'IMPORTADA', 'categoria' => 'ACESSORIOS', 'preco_custo' => 3.2, 'preco_venda_vista' => 5.7, 'preco_venda_prazo' => 6, 'estoque_atual' => 120, 'estoque_minimo' => 40, 'estoque_maximo' => 400, 'localizacao' => 'B1'],
            ['codigo_interno' => 'P004', 'codigo_barras' => '7890000000042', 'nome' => 'CAPA DE PORCA SEXTAVADA 32/33', 'marca' => 'IMPORTADA', 'categoria' => 'ACESSORIOS', 'preco_custo' => 2.1, 'preco_venda_vista' => 3.33, 'preco_venda_prazo' => 3.5, 'estoque_atual' => 85, 'estoque_minimo' => 35, 'estoque_maximo' => 300, 'localizacao' => 'B2'],
            ['codigo_interno' => 'P005', 'codigo_barras' => '7890000000059', 'nome' => '(DNI-0212) RELE AUX 40A 4T 24V', 'marca' => 'DNI', 'categoria' => 'ELETRICA', 'preco_custo' => 28, 'preco_venda_vista' => 42.75, 'preco_venda_prazo' => 45, 'estoque_atual' => 18, 'estoque_minimo' => 10, 'estoque_maximo' => 70, 'localizacao' => 'C1'],
            ['codigo_interno' => 'P006', 'codigo_barras' => '7890000000066', 'nome' => 'TERMINAL DE ENCAIXE', 'marca' => 'RAINHA DAS SETE', 'categoria' => 'ELETRICA', 'preco_custo' => 0.9, 'preco_venda_vista' => 1.82, 'preco_venda_prazo' => 1.92, 'estoque_atual' => 560, 'estoque_minimo' => 100, 'estoque_maximo' => 1000, 'localizacao' => 'C2'],
            ['codigo_interno' => 'P007', 'codigo_barras' => '7890000000073', 'nome' => 'FUSIVEL LAMINA 20A', 'marca' => 'DNI', 'categoria' => 'ELETRICA', 'preco_custo' => 0.7, 'preco_venda_vista' => 1.5, 'preco_venda_prazo' => 1.6, 'estoque_atual' => 12, 'estoque_minimo' => 20, 'estoque_maximo' => 200, 'localizacao' => 'D1'],
            ['codigo_interno' => 'P008', 'codigo_barras' => '7890000000080', 'nome' => 'BATERIA 150AH', 'marca' => 'MOURA', 'categoria' => 'BATERIAS', 'preco_custo' => 780, 'preco_venda_vista' => 950, 'preco_venda_prazo' => 1000, 'estoque_atual' => 4, 'estoque_minimo' => 2, 'estoque_maximo' => 12, 'localizacao' => 'E1'],
            ['codigo_interno' => 'P009', 'codigo_barras' => '7890000000097', 'nome' => 'LAMPADA 1141 24V', 'marca' => 'TESLLA', 'categoria' => 'ELETRICA', 'preco_custo' => 2.7, 'preco_venda_vista' => 4.75, 'preco_venda_prazo' => 5, 'estoque_atual' => 0, 'estoque_minimo' => 10, 'estoque_maximo' => 120, 'localizacao' => 'A3'],
            ['codigo_interno' => 'P010', 'codigo_barras' => '7890000000103', 'nome' => 'CHAVE DE SETA VOLVO FH', 'marca' => 'IMPORTADA', 'categoria' => 'ELETRICA', 'preco_custo' => 245, 'preco_venda_vista' => 360, 'preco_venda_prazo' => 380, 'estoque_atual' => 3, 'estoque_minimo' => 2, 'estoque_maximo' => 10, 'localizacao' => 'F1'],
        ];

        $produtos = [];
        foreach ($dados as $produto) {
            $produtos[$produto['codigo_interno']] = Produto::updateOrCreate(
                ['codigo_interno' => $produto['codigo_interno']],
                $produto + [
                    'tipo' => 'P',
                    'unidade' => 'UN',
                    'descricao_detalhada' => 'Produto cadastrado para demonstracao do NOTVIS.',
                    'margem_lucro' => 35,
                    'preco_venda_vista_desconto' => $produto['preco_venda_vista'],
                    'permite_desconto' => true,
                    'controla_estoque' => true,
                    'ncm' => '85122022',
                    'cfop' => '5102',
                    'cst_csosn' => '102',
                    'origem' => 0,
                    'aliquota_icms' => 0,
                    'ativo' => true,
                ]
            );

            $this->movimentoEstoque($produtos[$produto['codigo_interno']], 'ENTRADA', (float) $produto['estoque_atual'], 0, (float) $produto['estoque_atual'], 'CARGA INICIAL DEMONSTRACAO', $usuario, 'DEMO', $produtos[$produto['codigo_interno']]->id);
        }

        return $produtos;
    }

    private function ordensServico(array $clientes, array $funcionarios, array $produtos, ?User $usuario): array
    {
        $ordens = [];
        $ordens[] = $this->ordem(
            'DEMO MARINS',
            $clientes['MARINS SILVA POSTO EPP'],
            $funcionarios['GUSTAVO'],
            [$funcionarios['MARCOS'], $funcionarios['ANDERSON']],
            [
                'status' => 'FINALIZADO',
                'placa_veiculo' => 'TST8455',
                'marca_modelo_veiculo' => 'VOLVO FH',
                'km_veiculo' => '482000',
                'forma_pagamento' => 'PRAZO',
                'status_pagamento' => 'PENDENTE',
                'data_vencimento' => now()->addDays(30)->toDateString(),
                'finalizado_em' => now()->subDays(2),
                'comprovante_emitido_em' => now()->subDays(2),
                'sintoma_reclamacao' => 'Falha em farol auxiliar e revisao eletrica.',
            ],
            [
                ['tipo' => 'PECA', 'produto' => $produtos['P001'], 'descricao' => 'LAMPADA H4 24V PHILIPS', 'quantidade' => 1, 'valor_unitario' => 60],
                ['tipo' => 'PECA', 'produto' => $produtos['P005'], 'descricao' => '(DNI-0212) RELE AUX 40A 4T 24V', 'quantidade' => 5, 'valor_unitario' => 45],
                ['tipo' => 'SERVICO', 'descricao' => 'TROCA DA LAMPADA DE FAROL', 'quantidade' => 1, 'valor_unitario' => 40],
                ['tipo' => 'SERVICO', 'descricao' => 'SERVICO REPARO CURTO NO FAROL', 'quantidade' => 1, 'valor_unitario' => 60],
            ],
            $usuario
        );

        $ordens[] = $this->ordem(
            'DEMO ALICE',
            $clientes['ALICE DAIANA DA SILVA'],
            $funcionarios['GUSTAVO'],
            [$funcionarios['MARCOS']],
            [
                'status' => 'FINALIZADO',
                'placa_veiculo' => 'ABC1234',
                'marca_modelo_veiculo' => 'CHEVROLET S10 PRETA',
                'km_veiculo' => '98500',
                'forma_pagamento' => 'PIX',
                'status_pagamento' => 'PAGO',
                'data_vencimento' => now()->toDateString(),
                'finalizado_em' => now()->subDay(),
                'comprovante_emitido_em' => now()->subDay(),
                'sintoma_reclamacao' => 'Instalacao de acessorios eletricos.',
            ],
            [
                ['tipo' => 'PECA', 'produto' => $produtos['P003'], 'descricao' => 'CAPA DE PORCA BOLIVIANA 32/33', 'quantidade' => 8, 'valor_unitario' => 6],
                ['tipo' => 'PECA', 'produto' => $produtos['P006'], 'descricao' => 'TERMINAL DE ENCAIXE', 'quantidade' => 20, 'valor_unitario' => 1.92],
                ['tipo' => 'SERVICO', 'descricao' => 'INSTALACAO ELETRICA AUXILIAR', 'quantidade' => 1, 'valor_unitario' => 120],
            ],
            $usuario
        );

        $ordens[] = $this->ordem(
            'DEMO JR SILVA',
            $clientes['JR SILVA TRANSPORTES'],
            $funcionarios['GUSTAVO'],
            [$funcionarios['ANDERSON']],
            [
                'status' => 'RASCUNHO',
                'placa_veiculo' => 'BDL2190',
                'marca_modelo_veiculo' => 'CONSTELLATION 19.360',
                'km_veiculo' => '350200',
                'forma_pagamento' => 'DINHEIRO',
                'status_pagamento' => 'PENDENTE',
                'sintoma_reclamacao' => 'Cliente aguardando orcamento de lampadas e fusivel.',
            ],
            [
                ['tipo' => 'PECA', 'produto' => $produtos['P002'], 'descricao' => 'LAMPADA 67 24V', 'quantidade' => 6, 'valor_unitario' => 5],
                ['tipo' => 'PECA', 'produto' => $produtos['P007'], 'descricao' => 'FUSIVEL LAMINA 20A', 'quantidade' => 10, 'valor_unitario' => 1.6],
            ],
            $usuario
        );

        $ordens[] = $this->ordem(
            'DEMO ANTONIO',
            $clientes['ANTONIO CARLOS DO NASCIMENTO'],
            $funcionarios['GUSTAVO'],
            [$funcionarios['MARCOS']],
            [
                'status' => 'FINALIZADO',
                'placa_veiculo' => 'HTR9021',
                'marca_modelo_veiculo' => 'FORD CARGO 2428',
                'km_veiculo' => '620000',
                'forma_pagamento' => 'CARTAO',
                'status_pagamento' => 'PAGO',
                'data_vencimento' => now()->toDateString(),
                'finalizado_em' => now(),
                'comprovante_emitido_em' => now(),
                'desconto_geral_tipo' => 'VALOR',
                'desconto_geral_valor' => 25,
                'sintoma_reclamacao' => 'Substituicao de chave de seta.',
            ],
            [
                ['tipo' => 'PECA', 'produto' => $produtos['P010'], 'descricao' => 'CHAVE DE SETA VOLVO FH', 'quantidade' => 1, 'valor_unitario' => 380],
                ['tipo' => 'SERVICO', 'descricao' => 'DIAGNOSTICO ELETRICO COMPLETO', 'quantidade' => 1, 'valor_unitario' => 90],
            ],
            $usuario
        );

        $ordens[] = $this->ordem(
            'DEMO CANCELADA',
            $clientes['ANDREA E AYLA ALIMENTOS LTDA'],
            $funcionarios['GUSTAVO'],
            [$funcionarios['ANDERSON']],
            [
                'status' => 'CANCELADO',
                'placa_veiculo' => 'KLM7788',
                'marca_modelo_veiculo' => 'MERCEDES ATEGO',
                'km_veiculo' => '210500',
                'forma_pagamento' => 'PRAZO',
                'status_pagamento' => 'PENDENTE',
                'cancelado_em' => now()->subHours(3),
                'motivo_cancelamento' => 'Cliente desistiu antes da retirada do veiculo.',
            ],
            [
                ['tipo' => 'PECA', 'produto' => $produtos['P008'], 'descricao' => 'BATERIA 150AH', 'quantidade' => 1, 'valor_unitario' => 1000],
            ],
            $usuario
        );

        return $ordens;
    }

    private function ordem(string $nomeCartao, Cliente $cliente, Funcionario $atendente, array $mecanicos, array $dados, array $itens, ?User $usuario): OrdemServico
    {
        $dataReferencia = $dados['finalizado_em'] ?? $dados['cancelado_em'] ?? now();

        $os = OrdemServico::updateOrCreate(
            ['nome_cartao' => $nomeCartao],
            [
                'cliente_id' => $cliente->id,
                'atendente_id' => $atendente->id,
                'placa_veiculo' => $dados['placa_veiculo'] ?? null,
                'marca_modelo_veiculo' => $dados['marca_modelo_veiculo'] ?? null,
                'km_veiculo' => $dados['km_veiculo'] ?? null,
                'sintoma_reclamacao' => $dados['sintoma_reclamacao'] ?? null,
                'observacoes_internas' => 'Dados preparados para demonstracao do TCC.',
                'status' => $dados['status'],
                'forma_pagamento' => $dados['forma_pagamento'] ?? null,
                'status_pagamento' => $dados['status_pagamento'] ?? 'PENDENTE',
                'data_vencimento' => $dados['data_vencimento'] ?? null,
                'finalizado_em' => $dados['finalizado_em'] ?? null,
                'cupom_fiscal_emitido' => false,
                'comprovante_emitido_em' => $dados['comprovante_emitido_em'] ?? null,
                'observacao_fechamento' => $dados['observacao_fechamento'] ?? null,
                'cancelado_em' => $dados['cancelado_em'] ?? null,
                'motivo_cancelamento' => $dados['motivo_cancelamento'] ?? null,
                'desconto_geral_tipo' => $dados['desconto_geral_tipo'] ?? null,
                'desconto_geral_valor' => $dados['desconto_geral_valor'] ?? 0,
                'created_at' => $dataReferencia instanceof Carbon ? $dataReferencia->copy()->subHours(2) : now()->subDays(3),
                'updated_at' => $dataReferencia,
            ]
        );

        $os->itens()->delete();
        $totais = ['pecas' => 0.0, 'servicos' => 0.0];

        foreach ($itens as $item) {
            $base = (float) $item['quantidade'] * (float) $item['valor_unitario'];
            $desconto = $this->descontoItem($base, $item);
            $total = max(0, $base - $desconto);
            $tipo = $item['tipo'];

            OsItem::create([
                'ordem_servico_id' => $os->id,
                'tipo' => $tipo,
                'produto_id' => $item['produto']->id ?? null,
                'descricao' => $item['descricao'],
                'quantidade' => $item['quantidade'],
                'quantidade_devolvida' => $item['quantidade_devolvida'] ?? 0,
                'valor_unitario' => $item['valor_unitario'],
                'desconto_tipo' => $item['desconto_tipo'] ?? null,
                'desconto_valor' => $item['desconto_valor'] ?? 0,
                'valor_total' => $total,
            ]);

            if ($tipo === 'PECA') {
                $totais['pecas'] += $total;
                if (($dados['status'] ?? '') === 'FINALIZADO' && isset($item['produto'])) {
                    $this->movimentoEstoque($item['produto'], 'SAIDA', (float) $item['quantidade'], (float) $item['produto']->estoque_atual + (float) $item['quantidade'], (float) $item['produto']->estoque_atual, 'SAIDA POR OS FINALIZADA #' . $os->id, $usuario, 'OS', $os->id);
                }
            } else {
                $totais['servicos'] += $total;
            }
        }

        $descontoGeral = 0.0;
        $subtotal = $totais['pecas'] + $totais['servicos'];
        if (($dados['desconto_geral_tipo'] ?? null) === 'PORCENTAGEM') {
            $descontoGeral = $subtotal * ((float) $dados['desconto_geral_valor'] / 100);
        } elseif (($dados['desconto_geral_tipo'] ?? null) === 'VALOR') {
            $descontoGeral = (float) $dados['desconto_geral_valor'];
        }

        $os->forceFill([
            'valor_total_pecas' => $totais['pecas'],
            'valor_total_servicos' => $totais['servicos'],
            'valor_total_liquido' => max(0, $subtotal - $descontoGeral),
        ])->save();

        $os->mecanicos()->sync(collect($mecanicos)->pluck('id')->all());

        return $os;
    }

    private function descontoItem(float $base, array $item): float
    {
        if (($item['desconto_tipo'] ?? null) === 'PORCENTAGEM') {
            return $base * ((float) ($item['desconto_valor'] ?? 0) / 100);
        }

        if (($item['desconto_tipo'] ?? null) === 'VALOR') {
            return (float) ($item['desconto_valor'] ?? 0);
        }

        return 0;
    }

    private function movimentoEstoque(Produto $produto, string $tipo, float $quantidade, float $anterior, float $posterior, string $motivo, ?User $usuario, string $origem, int $origemId): void
    {
        EstoqueMovimentacao::updateOrCreate(
            [
                'produto_id' => $produto->id,
                'origem' => $origem,
                'origem_id' => $origemId,
                'motivo' => $motivo,
            ],
            [
                'user_id' => $usuario?->id,
                'tipo' => $tipo,
                'quantidade' => $quantidade,
                'estoque_anterior' => $anterior,
                'estoque_posterior' => $posterior,
                'observacao' => 'Movimento criado para demonstracao.',
            ]
        );
    }
}
