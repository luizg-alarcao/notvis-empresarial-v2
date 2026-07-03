@php
    $cliente = $os->cliente;
    $telefone = preg_replace('/[^0-9]/', '', (string) ($cliente->whatsapp ?? ''));
    $telefoneWhatsapp = strlen($telefone) >= 10 ? (str_starts_with($telefone, '55') ? $telefone : '55' . ltrim($telefone, '0')) : null;
    $dataEmissao = $os->finalizado_em ? \Carbon\Carbon::parse($os->finalizado_em) : now();
    $dataVencimento = $os->data_vencimento ? \Carbon\Carbon::parse($os->data_vencimento) : null;
    $ehOrcamento = ($tipoDocumento ?? 'comprovante') === 'orcamento';
    $documentoTitulo = $ehOrcamento ? 'Orcamento' : 'Comprovante';
    $mensagem = rawurlencode('Ola, segue o ' . mb_strtolower($documentoTitulo, 'UTF-8') . ' da OS #' . $os->id . ' no valor de R$ ' . number_format($os->valor_total_liquido ?? 0, 2, ',', '.') . '.');
    $formatarCpfCnpj = function ($valor) {
        $numero = preg_replace('/[^0-9]/', '', (string) $valor);

        if (strlen($numero) === 11) {
            return substr($numero, 0, 3) . '.' . substr($numero, 3, 3) . '.' . substr($numero, 6, 3) . '-' . substr($numero, 9, 2);
        }

        if (strlen($numero) === 14) {
            return substr($numero, 0, 2) . '.' . substr($numero, 2, 3) . '.' . substr($numero, 5, 3) . '/' . substr($numero, 8, 4) . '-' . substr($numero, 12, 2);
        }

        return $valor ?: '-';
    };
    $documentoCliente = $formatarCpfCnpj($cliente->cpf_cnpj ?? '');
    $documentoEmpresa = $formatarCpfCnpj($empresa?->cnpj ?? '');
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentoTitulo }} OS #{{ $os->id }} - NOTVIS</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #e5e7eb;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            background: #0f172a;
        }
        .toolbar a,
        .toolbar button {
            border: 0;
            border-radius: 5px;
            padding: 9px 14px;
            background: #2563eb;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
            cursor: pointer;
        }
        .toolbar .secondary { background: #334155; }
        .sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 13mm;
            background: #fff;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .18);
            position: relative;
        }
        .header {
            display: grid;
            grid-template-columns: 1fr 38mm;
            gap: 16px;
            align-items: start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
        }
        .system-mark {
            margin-bottom: 6px;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }
        .company-name {
            font-size: 18px;
            font-weight: 900;
            line-height: 1.15;
            text-transform: uppercase;
        }
        .muted {
            color: #475569;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.45;
            text-transform: uppercase;
        }
        .document-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            text-align: right;
        }
        .document-title {
            color: #334155;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .document-number {
            margin-top: 4px;
            font-size: 24px;
            font-weight: 900;
        }
        .document-date {
            margin-top: 2px;
            color: #475569;
            font-size: 11px;
            font-weight: 800;
        }
        .document-meta {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            color: #334155;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.45;
            text-transform: uppercase;
        }
        .info-stack {
            display: grid;
            gap: 10px;
            margin-top: 14px;
        }
        .info-block {
            border: 1px solid #dbe3ef;
            border-radius: 6px;
            padding: 10px 12px 12px;
        }
        .info-block h2 {
            margin: 0 0 9px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .info-grid {
            display: grid;
            gap: 9px 14px;
        }
        .client-grid { grid-template-columns: 1.35fr .85fr .75fr; }
        .payment-grid { grid-template-columns: repeat(3, 1fr); }
        .field {
            min-width: 0;
            padding-left: 8px;
            border-left: 2px solid #e2e8f0;
        }
        .field span {
            display: block;
            margin-bottom: 2px;
            color: #64748b;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .field strong {
            display: block;
            overflow-wrap: anywhere;
            font-size: 11px;
            font-weight: 900;
            line-height: 1.25;
        }
        .field-wide { grid-column: span 2; }
        .field-full { grid-column: 1 / -1; }
        .line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 3px 0;
        }
        .line span { color: #334155; }
        .line strong {
            text-align: right;
            font-weight: 800;
        }
        table {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
        }
        th {
            background: #eef2f7;
            border: 1px solid #cbd5e1;
            padding: 7px;
            color: #334155;
            font-size: 10px;
            text-align: left;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #e2e8f0;
            padding: 7px;
            vertical-align: top;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .summary-panel {
            display: grid;
            grid-template-columns: 1fr 74mm;
            margin-top: 14px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }
        .summary-payment {
            padding: 10px 12px;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
        }
        .summary-payment h2 {
            margin: 0 0 8px;
            color: #334155;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .summary-totals {
            padding: 10px;
            background: #fff;
        }
        .total-final {
            margin-top: 7px;
            padding-top: 8px;
            border-top: 2px solid #0f172a;
            font-size: 18px;
            font-weight: 900;
        }
        .note {
            margin-top: 14px;
            border: 1px solid #dbe3ef;
            border-radius: 6px;
            padding: 10px;
            color: #334155;
            line-height: 1.45;
        }
        .signature-area {
            margin-top: 20mm;
            display: flex;
            justify-content: center;
        }
        .signature {
            width: 92mm;
            border-top: 1px solid #475569;
            padding-top: 7px;
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .footer-mark {
            position: absolute;
            right: 13mm;
            bottom: 8mm;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .footer-mark {
                right: 0;
                bottom: -4mm;
            }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Imprimir / Salvar PDF</button>
        <a class="secondary" href="{{ $urlVoltarOs ?? route('os.nova') }}">Voltar para OS</a>
        @if($telefoneWhatsapp)
            <a href="https://wa.me/{{ $telefoneWhatsapp }}?text={{ $mensagem }}" target="_blank">WhatsApp</a>
        @endif
        @if($cliente?->email)
            <a href="mailto:{{ $cliente->email }}?subject={{ rawurlencode($documentoTitulo . ' OS #' . $os->id) }}&body={{ $mensagem }}">E-mail</a>
        @endif
    </div>

    <main class="sheet">
        <section class="header">
            <div>
                <div class="system-mark">Sistema Notvis</div>
                <div class="company-name">{{ $empresa?->razao_social ?: ($empresa?->nome_fantasia ?? 'Empresa nao configurada') }}</div>
                @if($empresa?->nome_fantasia && $empresa?->razao_social)
                    <div class="muted">{{ $empresa->nome_fantasia }}</div>
                @endif
                <div class="muted">CNPJ: {{ $documentoEmpresa }}</div>
                <div class="muted">
                    {{ $empresa?->telefone ? 'Tel: ' . $empresa->telefone . ' - ' : '' }}{{ $empresa?->email ?? '' }}
                </div>
                <div class="muted">{{ $empresa?->endereco ?? '' }}</div>
            </div>
            <div class="document-box">
                <div class="document-title">{{ $documentoTitulo }}</div>
                <div class="document-number">#{{ $os->id }}</div>
                <div class="document-date">{{ $dataEmissao->format('d/m/Y H:i') }}</div>
                <div class="document-meta">
                    Status: {{ $os->status }}<br>
                    Atendente: {{ $os->atendente->nome ?? '-' }}
                </div>
            </div>
        </section>

        <section class="info-stack">
            <div class="info-block">
                <h2>Cliente</h2>
                <div class="info-grid client-grid">
                    <div class="field field-wide">
                        <span>Nome / Razao social</span>
                        <strong>{{ $cliente->nome ?? 'Consumidor' }}</strong>
                    </div>
                    <div class="field">
                        <span>CPF/CNPJ</span>
                        <strong>{{ $documentoCliente }}</strong>
                    </div>
                    <div class="field">
                        <span>WhatsApp</span>
                        <strong>{{ $cliente->whatsapp ?? '-' }}</strong>
                    </div>
                    <div class="field field-wide">
                        <span>E-mail</span>
                        <strong>{{ $cliente->email ?? '-' }}</strong>
                    </div>
                    <div class="field">
                        <span>Cidade / UF</span>
                        <strong>{{ trim(($cliente->cidade ?? '') . '/' . ($cliente->estado ?? ''), '/') ?: '-' }}</strong>
                    </div>
                    <div class="field field-wide">
                        <span>Veiculo</span>
                        <strong>{{ $os->marca_modelo_veiculo ?? '-' }}</strong>
                    </div>
                    <div class="field">
                        <span>Placa</span>
                        <strong>{{ $os->placa_veiculo ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <table>
            <thead>
                <tr>
                    <th class="center">Cod.</th>
                    <th>Descricao</th>
                    <th class="right">Qtd</th>
                    <th class="right">Val. unit.</th>
                    <th class="right">Desc.</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($os->itens as $item)
                    @php
                        $bruto = (float) $item->quantidade * (float) $item->valor_unitario;
                        $desconto = (float) ($item->desconto_valor ?? 0);
                        if (($item->desconto_tipo ?? 'VALOR') === 'PORCENTAGEM') {
                            $desconto = $bruto * ($desconto / 100);
                        }
                        $subtotal = max(0, $bruto - $desconto);
                    @endphp
                    <tr>
                        <td class="center">{{ $item->produto_id ? '#' . $item->produto_id : '-' }}</td>
                        <td>{{ $item->descricao }}</td>
                        <td class="right">{{ number_format($item->quantidade, 3, ',', '.') }}</td>
                        <td class="right">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td class="right">R$ {{ number_format($desconto, 2, ',', '.') }}</td>
                        <td class="right">R$ {{ number_format($subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <section class="summary-panel">
            <div class="summary-payment">
                <h2>Pagamento</h2>
                <div class="info-grid payment-grid">
                    <div class="field">
                        <span>Forma</span>
                        <strong>{{ $os->forma_pagamento ?? '-' }}</strong>
                    </div>
                    <div class="field">
                        <span>Status</span>
                        <strong>{{ $os->status_pagamento ?? '-' }}</strong>
                    </div>
                    <div class="field">
                        <span>Vencimento</span>
                        <strong>{{ $dataVencimento ? $dataVencimento->format('d/m/Y') : '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="summary-totals">
                <div class="line"><span>Pecas</span><strong>R$ {{ number_format($os->valor_total_pecas ?? 0, 2, ',', '.') }}</strong></div>
                <div class="line"><span>Servicos</span><strong>R$ {{ number_format($os->valor_total_servicos ?? 0, 2, ',', '.') }}</strong></div>
                <div class="total-final line"><span>Total</span><strong>R$ {{ number_format($os->valor_total_liquido ?? 0, 2, ',', '.') }}</strong></div>
            </div>
        </section>

        @if($os->sintoma_reclamacao || $os->observacao_fechamento)
            <section class="note">
                @if($os->sintoma_reclamacao)
                    <strong>Observacoes/defeito:</strong> {{ $os->sintoma_reclamacao }}<br>
                @endif
                @if($os->observacao_fechamento)
                    <strong>Fechamento:</strong> {{ $os->observacao_fechamento }}
                @endif
            </section>
        @endif

        <section class="signature-area">
            <div class="signature">Assinatura do cliente</div>
        </section>

        <div class="footer-mark">Sistema Notvis</div>
    </main>
</body>
</html>
