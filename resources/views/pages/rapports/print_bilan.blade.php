<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bilan Périodique Fiscal DGI — {{ $dateDebut }} au {{ $dateFin }}</title>
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
            grid-template-columns: repeat(6, 1fr);
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
            font-size: 12px;
            font-weight: 800;
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            color: #0f172a;
        }
        .kpi-card.kpi-ttc {
            background: #e8f0fe;
            border-color: #bfdbfe;
        }
        .kpi-card.kpi-ttc .kpi-label,
        .kpi-card.kpi-ttc .kpi-val {
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

        /* Boîte ventes incomplètes */
        .incomplete-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

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
            <button onclick="window.print()" class="btn btn-print">🖨 Imprimer le Bilan Périodique</button>
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
            <div class="report-type-badge">BILAN FISCAL PÉRIODIQUE (RAPPORT Z)</div>
            <div class="report-meta">
                Période du <strong>{{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}</strong> au <strong>{{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</strong><br>
                NIM Machine : <strong>{{ $entete->nim_mcf ?? 'ELTRADE-CC300' }}</strong><br>
                Édité le {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    @php
        $stats = $donnees['statistiques'];
        $parType = $stats['par_type_facture'] ?? [];
        $totalNb = 0; $totalHt = 0; $totalTva = 0; $totalTtc = 0;
    @endphp
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total C.A. HT</div>
            <div class="kpi-val">{{ number_format($stats['chiffre_affaires_ht'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">TVA Collectée</div>
            <div class="kpi-val" style="color: #0056b3;">{{ number_format($stats['tva_collectee'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Taxe Spécifique</div>
            <div class="kpi-val">{{ number_format($stats['taxes_specifiques'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">PSVB / BIC</div>
            <div class="kpi-val" style="color: #b45309;">{{ number_format($stats['psvb_collecte'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Timbre Quittance</div>
            <div class="kpi-val">{{ number_format($stats['timbre_quittance'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card kpi-ttc">
            <div class="kpi-label">TOTAL TTC NET</div>
            <div class="kpi-val">{{ number_format($stats['total_ttc'], 0, ',', ' ') }} F</div>
        </div>
    </div>

    <!-- 1. Récapitulatif par Type de Facture -->
    <div class="section-header">
        <span class="section-bar"></span>
        <span>1. Récapitulatif par type de facture</span>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-start" style="width: 15%;">Type de Facture</th>
                <th class="text-center" style="width: 15%;">Nombre</th>
                <th class="text-end" style="width: 23%;">Total HT</th>
                <th class="text-end" style="width: 23%;">Total TVA</th>
                <th class="text-end" style="width: 24%;">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['EA', 'EV', 'FA', 'FT', 'FV'] as $tCode)
                @php
                    $tRow = $parType[$tCode] ?? ['count' => 0, 'total_ht' => 0, 'total_tva' => 0, 'total_ttc' => 0];
                    $totalNb += (int)($tRow['count'] ?? 0);
                    $totalHt += (float)($tRow['total_ht'] ?? 0);
                    $totalTva += (float)($tRow['total_tva'] ?? 0);
                    $totalTtc += (float)($tRow['total_ttc'] ?? 0);
                @endphp
                <tr>
                    <td class="text-start fw-bold">{{ $tCode }}</td>
                    <td class="text-center font-mono">{{ $tRow['count'] ?? 0 }}</td>
                    <td class="text-end font-mono">{{ number_format($tRow['total_ht'] ?? 0, 0, ',', ' ') }} F</td>
                    <td class="text-end font-mono">{{ number_format($tRow['total_tva'] ?? 0, 0, ',', ' ') }} F</td>
                    <td class="text-end font-mono fw-bold">{{ number_format($tRow['total_ttc'] ?? 0, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td class="text-start">TOTAL</td>
                <td class="text-center font-mono">{{ $totalNb }}</td>
                <td class="text-end font-mono">{{ number_format($totalHt, 0, ',', ' ') }} F</td>
                <td class="text-end font-mono">{{ number_format($totalTva, 0, ',', ' ') }} F</td>
                <td class="text-end font-mono">{{ number_format($totalTtc, 0, ',', ' ') }} F</td>
            </tr>
        </tbody>
    </table>

    <!-- 2. Montants par groupe de taxation et type de facture -->
    @php
        $parTypeGroupe = $stats['par_type_et_groupe_taxe'] ?? [];
    @endphp
    <div class="section-header">
        <span class="section-bar"></span>
        <span>2. Montants par groupe de taxation et type de facture</span>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-start" style="width: 8%;">Type</th>
                <th class="text-center" style="width: 8%;">Grp</th>
                <th class="text-start" style="width: 32%;">Description</th>
                <th class="text-center" style="width: 10%;">Taux</th>
                <th class="text-end" style="width: 14%;">HT (FCFA)</th>
                <th class="text-end" style="width: 14%;">TVA (FCFA)</th>
                <th class="text-end" style="width: 14%;">TTC (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parTypeGroupe as $row)
            <tr>
                <td class="text-start fw-bold">{{ $row['type'] }}</td>
                <td class="text-center fw-bold">{{ $row['groupe'] }}</td>
                <td class="text-start">{{ $row['description'] }}</td>
                <td class="text-center font-mono">{{ isset($row['taux']) && $row['taux'] > 0 ? $row['taux'].'%' : '0%' }}</td>
                <td class="text-end font-mono">{{ number_format($row['ht'] ?? 0, 0, ',', ' ') }} F</td>
                <td class="text-end font-mono">{{ number_format($row['tva'] ?? 0, 0, ',', ' ') }} F</td>
                <td class="text-end font-mono fw-bold">{{ number_format($row['ttc'] ?? 0, 0, ',', ' ') }} F</td>
            </tr>
            @empty
                @foreach($stats['groupes_taxes'] as $codeG => $g)
                <tr>
                    <td class="text-start fw-bold">FV</td>
                    <td class="text-center fw-bold">{{ $codeG }}</td>
                    <td class="text-start">{{ $g['libelle'] }}</td>
                    <td class="text-center font-mono">{{ $g['taux'] > 0 ? $g['taux'].'%' : '0%' }}</td>
                    <td class="text-end font-mono">{{ number_format($g['base_ht'], 0, ',', ' ') }} F</td>
                    <td class="text-end font-mono">{{ number_format($g['tva'], 0, ',', ' ') }} F</td>
                    <td class="text-end font-mono fw-bold">{{ number_format($g['base_ht'] + $g['tva'] + $g['ts'], 0, ',', ' ') }} F</td>
                </tr>
                @endforeach
            @endforelse
        </tbody>
    </table>

    <!-- 3. Montants par mode de paiement -->
    <div class="section-header">
        <span class="section-bar"></span>
        <span>3. Montants par mode de paiement</span>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-start" style="width: 60%;">Mode de paiement</th>
                <th class="text-end" style="width: 40%;">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalReg = 0; @endphp
            @foreach($stats['modes_paiement'] as $m => $totalM)
                @if($totalM != 0)
                @php $totalReg += (float)$totalM; @endphp
                <tr>
                    <td class="text-start fw-semibold">{{ $m }}</td>
                    <td class="text-end font-mono fw-bold">{{ number_format($totalM, 0, ',', ' ') }} F</td>
                </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td class="text-start">TOTAL</td>
                <td class="text-end font-mono">{{ number_format($totalReg, 0, ',', ' ') }} F</td>
            </tr>
        </tbody>
    </table>

    @php
        $listeRrr = $stats['liste_rrr'] ?? [];
        $totalRrr = (float)($stats['total_rrr'] ?? ($stats['reductions_commerciales'] ?? 0));
        $listeAutresAvoirs = $stats['liste_autres_avoirs'] ?? [];
        $totalAutresAvoirs = (float)($stats['total_autres_avoirs'] ?? ($stats['total_avoirs'] ?? 0));
        $listeVentesIncompletes = $stats['liste_ventes_incompletes'] ?? [];
        $nbVentesIncompletes = (int)($stats['ventes_incompletes'] ?? count($listeVentesIncompletes));
    @endphp

    <!-- 4. Remises, ristournes et rabais (réductions commerciales) -->
    <div class="section-header">
        <span class="section-bar"></span>
        <span>4. Remises, ristournes et rabais (réductions commerciales)</span>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-start" style="width: 50%;">N° facture RRR</th>
                <th class="text-center" style="width: 25%;">Date</th>
                <th class="text-end" style="width: 25%;">Montant (XOF)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listeRrr as $itemRrr)
            <tr>
                <td class="text-start font-mono fw-bold">{{ $itemRrr['numero'] }}</td>
                <td class="text-center font-mono">{{ $itemRrr['date'] }}</td>
                <td class="text-end font-mono fw-bold">{{ number_format($itemRrr['montant'], 0, ',', ' ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-start text-muted fst-italic">Aucune réduction commerciale (RRR) sur la période</td>
                <td class="text-end font-mono">0</td>
            </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-start">TOTAL RRR</td>
                <td class="text-end font-mono">{{ number_format($totalRrr, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 5. Autres enregistrements ayant réduit les ventes -->
    <div class="section-header">
        <span class="section-bar"></span>
        <span>5. Autres enregistrements ayant réduit les ventes</span>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-start" style="width: 40%;">N° facture</th>
                <th class="text-center" style="width: 20%;">Nature</th>
                <th class="text-center" style="width: 20%;">Date</th>
                <th class="text-end" style="width: 20%;">Montant (XOF)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($listeAutresAvoirs as $itemAv)
            <tr>
                <td class="text-start font-mono fw-bold">{{ $itemAv['numero'] }}</td>
                <td class="text-center font-mono fw-bold">{{ $itemAv['nature'] }}</td>
                <td class="text-center font-mono">{{ $itemAv['date'] }}</td>
                <td class="text-end font-mono fw-bold">{{ number_format($itemAv['montant'], 0, ',', ' ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-start text-muted fst-italic">Aucun autre enregistrement ayant réduit les ventes</td>
                <td class="text-end font-mono">0</td>
            </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="3" class="text-start">TOTAL</td>
                <td class="text-end font-mono">{{ number_format($totalAutresAvoirs, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- 6. Ventes incomplètes (brouillons) -->
    <div class="section-header">
        <span class="section-bar"></span>
        <span>6. Ventes incomplètes (brouillons)</span>
    </div>
    <div class="incomplete-box">
        Nombre de ventes incomplètes : {{ $nbVentesIncompletes }}
    </div>
    @if(count($listeVentesIncompletes) > 0)
    <table class="report-table" style="margin-top: 4px;">
        <thead>
            <tr>
                <th class="text-start" style="width: 30%;">N° Facture</th>
                <th class="text-center" style="width: 20%;">Date</th>
                <th class="text-start" style="width: 30%;">Client</th>
                <th class="text-end" style="width: 20%;">Montant (XOF)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($listeVentesIncompletes as $inc)
            <tr>
                <td class="text-start font-mono">{{ $inc['numero'] }}</td>
                <td class="text-center font-mono">{{ $inc['date'] }}</td>
                <td class="text-start">{{ $inc['client'] }}</td>
                <td class="text-end font-mono">{{ number_format($inc['montant'], 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- 7. Registre des Factures & Avoirs Émis sur la Période -->
    <div class="section-header">
        <span class="section-bar"></span>
        <span>7. Registre des factures et avoirs de la période (Extrait)</span>
    </div>
    <table class="report-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 10%;">Date</th>
                <th class="text-start" style="width: 14%;">Numéro</th>
                <th class="text-center" style="width: 6%;">Type</th>
                <th class="text-start" style="width: 26%;">Client</th>
                <th class="text-center" style="width: 12%;">IFU Client</th>
                <th class="text-end" style="width: 10%;">Net HT</th>
                <th class="text-end" style="width: 10%;">TVA</th>
                <th class="text-end" style="width: 12%;">Net à Payer</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donnees['factures']->take(35) as $f)
            <tr>
                <td class="text-center font-mono">{{ \Carbon\Carbon::parse($f->date)->format('d/m/Y') }}</td>
                <td class="text-start font-mono fw-bold">{{ $f->code }}</td>
                <td class="text-center fw-bold">{{ optional($f->Typefacture)->code ?? 'FV' }}</td>
                <td class="text-start">{{ optional($f->Contribuable)->assujeti ?? 'Client Comptoir' }}</td>
                <td class="text-center font-mono">{{ optional($f->Contribuable)->ifu ?? '—' }}</td>
                <td class="text-end font-mono">{{ number_format($f->montant_ht ?? 0, 0, ',', ' ') }}</td>
                <td class="text-end font-mono">{{ number_format($f->montant_tva ?? 0, 0, ',', ' ') }}</td>
                <td class="text-end font-mono fw-bold">{{ number_format($f->net_a_payer ?? $f->montant_total, 0, ',', ' ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-2 text-muted fst-italic">Aucune facture sur la période sélectionnée.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures-row">
        <div class="signature-box">
            Le Responsable Financier / Comptable
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
