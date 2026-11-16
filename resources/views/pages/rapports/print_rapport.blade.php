<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Fiscal {{ $rapport->type_rapport }} - {{ $rapport->numero_rapport }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .toolbar {
            background: #f8f9fa;
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .btn {
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print { background: #0f172a; color: #fff; border: 1px solid #0f172a; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .dashed-line { border-bottom: 1px dashed #000; margin: 10px 0; }
        .double-line { border-bottom: 2px solid #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { padding: 4px 6px; }
        th { border-bottom: 1px solid #000; border-top: 1px solid #000; text-transform: uppercase; }
        .fs-title { font-size: 15px; font-weight: bold; }
        .fs-sub { font-size: 12px; }
        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; max-width: 100%; }
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
            <button onclick="window.print()" class="btn btn-print">🖨 Imprimer le Rapport</button>
        </div>
    </div>

    <!-- En-tête officiel Entreprise -->
    <div class="text-center">
        <p class="fs-title">{{ $entete->denomination ?? 'ENTREPRISE' }}</p>
        <p>IFU : {{ $entete->ifu ?? '—' }} | RCCM : {{ $entete->rccm ?? '—' }}</p>
        <p>ISF : {{ $entete->identifiant_sfe ?? $entete->isf ?? '—' }} | CDI : {{ $entete->division_fiscal ?? '—' }}</p>
        <p>Régime : {{ $entete->regime_fiscal ?? '—' }}</p>
        @if(!empty($entete->telephone))<p>Tél : {{ $entete->telephone }}</p>@endif
        @if(!empty($entete->infos_cadastrales))<p>Cadastre : {{ $entete->infos_cadastrales }}</p>@endif
        <div class="double-line"></div>
    </div>

    <!-- Titre du Rapport -->
    <div class="text-center">
        <p class="fs-title">
            @if($rapport->type_rapport === 'Z')
                *** CLÔTURE FISCALE (RAPPORT Z) ***
            @else
                *** LECTURE FISCALE (RAPPORT X) ***
            @endif
        </p>
        <p class="fs-sub fw-bold">N° : {{ $rapport->numero_rapport }}</p>
    </div>
    <div class="dashed-line"></div>

    <!-- Informations machine & Période -->
    <div>
        <p><strong>NIM MCF :</strong> {{ $entete->nim_mcf ?? 'ELTRADE-CC300' }}</p>
        <p><strong>Date d'émission :</strong> {{ $rapport->date_rapport ? $rapport->date_rapport->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</p>
        <p><strong>Période du :</strong> {{ $rapport->date_debut ? $rapport->date_debut->format('d/m/Y H:i') : 'Début' }}</p>
        <p><strong>Période au :</strong> {{ $rapport->date_fin ? $rapport->date_fin->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</p>
        <p><strong>Opérateur :</strong> {{ optional($rapport->user)->name ?? 'Administrateur' }}</p>
        <p><strong>Statut :</strong> {{ $rapport->statut }}</p>
    </div>
    <div class="dashed-line"></div>

    @php
        $statData = $stats ?? ($rapport->details_bruts ?? []);
        $parType = $statData['par_type_facture'] ?? [];
        $totalNb = 0; $totalHt = 0; $totalTva = 0; $totalTtc = 0;
    @endphp

    <!-- 1. Récapitulatif par type de facture (Norme DGI SECeF) -->
    <p class="fw-bold fs-sub text-center">1. RÉCAPITULATIF PAR TYPE DE FACTURE</p>
    <table>
        <thead>
            <tr>
                <th class="text-start">Type</th>
                <th class="text-center">Nb</th>
                <th class="text-end">HT (FCFA)</th>
                <th class="text-end">TVA (FCFA)</th>
                <th class="text-end">TTC (FCFA)</th>
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
                    <td class="text-center">{{ $tRow['count'] ?? 0 }}</td>
                    <td class="text-end">{{ number_format($tRow['total_ht'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($tRow['total_tva'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold">{{ number_format($tRow['total_ttc'] ?? 0, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            <tr style="border-top: 1px solid #000; border-bottom: 2px solid #000; font-weight: bold;">
                <td class="text-start">TOTAL</td>
                <td class="text-center">{{ $totalNb }}</td>
                <td class="text-end">{{ number_format($totalHt, 0, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($totalTva, 0, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($totalTtc, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>
    <div class="dashed-line"></div>

    @php
        $parTypeGroupe = $statData['par_type_et_groupe_taxe'] ?? [];
    @endphp

    <!-- 2. Montants par groupe de taxation et type de facture (Norme DGI SECeF) -->
    <p class="fw-bold fs-sub text-center">2. MONTANTS PAR GROUPE DE TAXATION ET TYPE DE FACTURE</p>
    <table>
        <thead>
            <tr>
                <th class="text-start" style="width: 10%;">Type</th>
                <th class="text-center" style="width: 8%;">Grp</th>
                <th class="text-start" style="width: 34%;">Description</th>
                <th class="text-center" style="width: 10%;">Taux</th>
                <th class="text-end" style="width: 12%;">HT (FCFA)</th>
                <th class="text-end" style="width: 12%;">TVA (FCFA)</th>
                <th class="text-end" style="width: 14%;">TTC (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parTypeGroupe as $row)
            <tr>
                <td class="text-start fw-bold">{{ $row['type'] }}</td>
                <td class="text-center fw-bold">{{ $row['groupe'] }}</td>
                <td class="text-start">{{ $row['description'] }}</td>
                <td class="text-center">{{ isset($row['taux']) && $row['taux'] > 0 ? $row['taux'].'%' : '0%' }}</td>
                <td class="text-end">{{ number_format($row['ht'] ?? 0, 0, ',', ' ') }}</td>
                <td class="text-end">{{ number_format($row['tva'] ?? 0, 0, ',', ' ') }}</td>
                <td class="text-end fw-bold">{{ number_format($row['ttc'] ?? 0, 0, ',', ' ') }}</td>
            </tr>
            @empty
                @php $detailsTaxes = $rapport->details_taxes ?? ($statData['groupes_taxes'] ?? []); @endphp
                @foreach($detailsTaxes as $codeG => $g)
                <tr>
                    <td class="text-start fw-bold">FV</td>
                    <td class="text-center fw-bold">{{ $codeG }}</td>
                    <td class="text-start">{{ $g['libelle'] ?? '' }}</td>
                    <td class="text-center">{{ isset($g['taux']) && $g['taux'] > 0 ? $g['taux'].'%' : '0%' }}</td>
                    <td class="text-end">{{ number_format($g['base_ht'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($g['tva'] ?? 0, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold">{{ number_format(($g['base_ht'] ?? 0) + ($g['tva'] ?? 0) + ($g['ts'] ?? 0), 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            @endforelse
        </tbody>
    </table>
    <div class="dashed-line"></div>

    @php
        $detailsReg = $statData['modes_paiement'] ?? ($rapport->details_reglements ?? []);
        $totalReglements = 0;
    @endphp

    <!-- 3. Montants par mode de paiement (Norme DGI SECeF) -->
    <p class="fw-bold fs-sub text-center">3. MONTANTS PAR MODE DE PAIEMENT</p>
    <table>
        <thead>
            <tr>
                <th class="text-start">Mode de paiement</th>
                <th class="text-end">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detailsReg as $mode => $mt)
                @if($mt != 0)
                @php $totalReglements += (float)$mt; @endphp
                <tr>
                    <td class="text-start">{{ $mode }}</td>
                    <td class="text-end fw-bold">{{ number_format($mt, 0, ',', ' ') }}</td>
                </tr>
                @endif
            @endforeach
            <tr style="border-top: 1px solid #000; font-weight: bold;">
                <td class="text-start">TOTAL RÈGLEMENTS :</td>
                <td class="text-end">{{ number_format($totalReglements, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>
    <div class="dashed-line"></div>

    <!-- Synthèse Financière & Contrôles DGI -->
    <p class="fw-bold fs-sub text-center">SYNTHÈSE FINANCIÈRE & CONTRÔLES DGI</p>
    <table>
        <tbody>
            <tr>
                <td class="text-start">Chiffre d'Affaires Brut HT :</td>
                <td class="text-end fw-bold">{{ number_format($rapport->chiffre_affaires_ht, 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td class="text-start">Total TVA Collectée :</td>
                <td class="text-end fw-bold">{{ number_format($rapport->total_tva, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($rapport->total_ts > 0)
            <tr>
                <td class="text-start">Total Taxes Spécifiques (TS) :</td>
                <td class="text-end">{{ number_format($rapport->total_ts, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if($rapport->total_psvb > 0)
            <tr>
                <td class="text-start">Total PSVB / BIC Collecté :</td>
                <td class="text-end">{{ number_format($rapport->total_psvb, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if($rapport->total_timbre > 0)
            <tr>
                <td class="text-start">Total Timbre Quittance :</td>
                <td class="text-end">{{ number_format($rapport->total_timbre, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if($rapport->reductions_commerciales > 0)
            <tr>
                <td class="text-start text-danger">Toutes les réductions commerciales accordées :</td>
                <td class="text-end text-danger fw-bold">- {{ number_format($rapport->reductions_commerciales, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if($rapport->nb_factures_avoir > 0)
            <tr>
                <td class="text-start text-danger">Total des Avoirs émis ({{ $rapport->nb_factures_avoir }}) :</td>
                <td class="text-end text-danger fw-bold">- {{ number_format($statData['total_avoirs'] ?? 0, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr>
                <td class="text-start">Nombre de ventes incomplètes (brouillons/non signées) :</td>
                <td class="text-end fw-bold">{{ $rapport->ventes_incompletes }}</td>
            </tr>
            <tr style="border-top: 1px solid #000; border-bottom: 2px solid #000;">
                <td class="text-start fs-title fw-bold">TOTAL TTC NET PÉRIODE :</td>
                <td class="text-end fs-title fw-bold">{{ number_format($rapport->total_ttc, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>
    <div class="double-line"></div>

    <!-- Signatures et Mentions de contrôle -->
    <div style="margin-top: 30px; display: flex; justify-content: space-between;">
        <div style="width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
            <p class="fw-bold">Visa Caissier / Opérateur</p>
        </div>
        <div style="width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
            <p class="fw-bold">Visa Superviseur / Direction</p>
        </div>
    </div>

    <div class="text-center" style="margin-top: 40px; font-size: 9px;">
        <p>Document officiel conforme au décret de facturation électronique de la DGI Burkina Faso</p>
        <p>A conserver pendant 10 ans dans le registre légal des ventes</p>
    </div>

</body>
</html>
