<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Articles (A) - {{ $rapport ? $rapport->numero_rapport : 'Bilan Périodique' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 10.5px;
            color: #0f172a;
            background: #fff;
            padding: 20px;
            max-width: 850px;
            margin: 0 auto;
            line-height: 1.4;
        }
        .toolbar {
            background: #f8fafc;
            padding: 10px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #002b49; color: #fff; border: 1px solid #002b49; }
        .btn-print:hover { background: #001f35; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .btn-back:hover { background: #f1f5f9; }

        /* En-tête */
        .report-header {
            border-bottom: 2px solid #002b49;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #002b49;
            letter-spacing: -0.3px;
            margin-bottom: 3px;
        }
        .company-meta {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.45;
        }
        .company-meta strong {
            color: #0f172a;
        }
        .report-badge-box {
            text-align: right;
        }
        .report-type-badge {
            display: inline-block;
            background: #002b49;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 4px;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .report-meta {
            font-size: 9.5px;
            color: #475569;
            line-height: 1.4;
        }
        .report-meta strong {
            color: #0f172a;
        }

        /* KPI Cards */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .kpi-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            background: #f8fafc;
        }
        .kpi-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .kpi-val {
            font-size: 13px;
            font-weight: 800;
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            color: #0f172a;
        }
        .kpi-card.kpi-net {
            background: #e8f0fe;
            border-color: #bfdbfe;
        }
        .kpi-card.kpi-net .kpi-label,
        .kpi-card.kpi-net .kpi-val {
            color: #002b49;
        }

        /* Titre de section avec le trait vertical cyan/bleu */
        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #002b49;
        }
        .section-bar {
            width: 4px;
            height: 16px;
            background: #0056b3;
            border-radius: 2px;
            display: inline-block;
        }

        /* Tables */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 10px;
        }
        table.report-table th {
            background: #002b49;
            color: #ffffff;
            padding: 6px 10px;
            font-weight: 700;
            font-size: 10px;
            border: none;
            letter-spacing: 0.2px;
        }
        table.report-table td {
            padding: 5.5px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        table.report-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        table.report-table tr.total-row td {
            background: #e8f0fe !important;
            font-weight: 700;
            color: #002b49;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        .font-mono {
            font-family: 'JetBrains Mono', 'Consolas', monospace;
        }
        .text-start { text-align: left; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .text-danger { color: #dc2626; }
        .text-muted { color: #64748b; }

        /* Signatures */
        .signatures-row {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 10px;
        }
        .signature-box {
            width: 42%;
            text-align: center;
            border-top: 1px solid #94a3b8;
            padding-top: 5px;
            font-size: 10px;
            font-weight: 700;
            color: #1e293b;
        }
        .footer-note {
            text-align: center;
            font-size: 8.5px;
            color: #64748b;
            margin-top: 20px;
        }

        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; max-width: 100%; font-size: 9.5px; }
            table.report-table th { background: #002b49 !important; color: #fff !important; }
            table.report-table tr.total-row td { background: #e8f0fe !important; }
            .section-bar { background: #0056b3 !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <div>
            <a href="{{ route('rapports.index') }}" class="btn btn-back">← Retour aux rapports</a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-print">🖨 Imprimer le Rapport A</button>
        </div>
    </div>

    <!-- En-tête -->
    <div class="report-header">
        <div>
            <div class="company-name">{{ $entete->denomination ?? 'ENTREPRISE' }}</div>
            <div class="company-meta">
                IFU : <strong>{{ $entete->ifu ?? '—' }}</strong> · RCCM : {{ $entete->rccm ?? '—' }} · ISF : <strong>{{ $entete->identifiant_sfe ?? '—' }}</strong><br>
                Régime fiscal : {{ $entete->regime_fiscal ?? '—' }} · Division : {{ $entete->division_fiscal ?? '—' }}<br>
                Cadastre : {{ $entete->infos_cadastrales ?? '—' }} · Tél : {{ $entete->telephone ?? '—' }}
            </div>
        </div>
        <div class="report-badge-box">
            <div class="report-type-badge">RAPPORT ARTICLES & STOCKS (RAPPORT A)</div>
            <div class="report-meta">
                @if($rapport)
                    N° : <strong>{{ $rapport->numero_rapport }}</strong> · 
                @endif
                NIM Machine : <strong>{{ $entete->nim_mcf ?? 'ELTRADE-CC300' }}</strong><br>
                Période du <strong>{{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}</strong> au <strong>{{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</strong><br>
                Édité le {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    @php
        $totQteVendue = 0;
        $totMntVendu = 0;
        $totQteRetour = 0;
        $totMntRetour = 0;
        foreach($articlesData as $art) {
            $totQteVendue += (float)($art['quantite_vendue'] ?? 0);
            $totMntVendu += (float)($art['montant_vendu'] ?? 0);
            $totQteRetour += (float)($art['quantite_retournee'] ?? 0);
            $totMntRetour += (float)($art['montant_retourne'] ?? 0);
        }
        $netVentes = $totMntVendu - $totMntRetour;
    @endphp

    <!-- KPIs Articles -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Articles Réf.</div>
            <div class="kpi-val">{{ count($articlesData) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Ventes Brutes</div>
            <div class="kpi-val">{{ number_format($totMntVendu, 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Retours / Avoirs</div>
            <div class="kpi-val" style="color: #dc2626;">- {{ number_format($totMntRetour, 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card kpi-net">
            <div class="kpi-label">VENTES NETTES</div>
            <div class="kpi-val">{{ number_format($netVentes, 0, ',', ' ') }} F</div>
        </div>
    </div>

    <!-- 1. Mouvements et ventes par article -->
    <div class="section-header">
        <span class="section-bar"></span>
        <span>1. Mouvements et ventes par article</span>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-start" style="width: 12%;">Code</th>
                <th class="text-start" style="width: 28%;">Désignation</th>
                <th class="text-end" style="width: 10%;">PU (FCFA)</th>
                <th class="text-center" style="width: 8%;">TVA</th>
                <th class="text-center" style="width: 8%;">Qté V.</th>
                <th class="text-end" style="width: 12%;">Total Ventes</th>
                <th class="text-center" style="width: 8%;">Qté Ret.</th>
                <th class="text-end" style="width: 12%;">Total Retours</th>
                <th class="text-center" style="width: 8%;">Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse($articlesData as $art)
            <tr>
                <td class="text-start font-mono fw-bold">{{ $art['code'] ?? '—' }}</td>
                <td class="text-start fw-semibold">{{ $art['nom'] }}</td>
                <td class="text-end font-mono">{{ number_format($art['prix_unitaire'], 0, ',', ' ') }}</td>
                <td class="text-center font-mono">{{ isset($art['taux_impot']) && $art['taux_impot'] > 0 ? $art['taux_impot'].'%' : '0%' }}</td>
                <td class="text-center font-mono">{{ (float)$art['quantite_vendue'] == (int)$art['quantite_vendue'] ? (int)$art['quantite_vendue'] : number_format($art['quantite_vendue'], 2, ',', ' ') }}</td>
                <td class="text-end font-mono fw-bold">{{ number_format($art['montant_vendu'], 0, ',', ' ') }}</td>
                <td class="text-center font-mono text-danger">{{ (float)$art['quantite_retournee'] == (int)$art['quantite_retournee'] ? (int)$art['quantite_retournee'] : number_format($art['quantite_retournee'], 2, ',', ' ') }}</td>
                <td class="text-end font-mono text-danger">{{ number_format($art['montant_retourne'], 0, ',', ' ') }}</td>
                <td class="text-center font-mono fw-bold" style="color: {{ ($art['quantite_stock'] ?? 0) > 0 ? '#16a34a' : '#dc2626' }};">
                    {{ (float)$art['quantite_stock'] == (int)$art['quantite_stock'] ? (int)$art['quantite_stock'] : number_format($art['quantite_stock'], 2, ',', ' ') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-2 text-muted fst-italic">Aucun article enregistré sur cette période.</td>
            </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="text-start">TOTAL GÉNÉRAL</td>
                <td class="text-center font-mono">{{ (float)$totQteVendue == (int)$totQteVendue ? (int)$totQteVendue : number_format($totQteVendue, 2, ',', ' ') }}</td>
                <td class="text-end font-mono">{{ number_format($totMntVendu, 0, ',', ' ') }}</td>
                <td class="text-center font-mono">{{ (float)$totQteRetour == (int)$totQteRetour ? (int)$totQteRetour : number_format($totQteRetour, 2, ',', ' ') }}</td>
                <td class="text-end font-mono">{{ number_format($totMntRetour, 0, ',', ' ') }}</td>
                <td class="text-center font-mono">—</td>
            </tr>
        </tbody>
    </table>

    <div class="signatures-row">
        <div class="signature-box">
            Le Gestionnaire de Stock / Magasinier
        </div>
        <div class="signature-box">
            La Direction Générale
        </div>
    </div>

    <div class="footer-note">
        <p>Document officiel conforme au décret de facturation électronique de la DGI · Protocole SECeF ST 1.0</p>
        <p>À conserver pendant 10 ans dans le registre légal des ventes</p>
    </div>

</body>
</html>
