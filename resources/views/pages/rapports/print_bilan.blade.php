<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bilan Périodique Fiscal DGI — {{ $dateDebut }} au {{ $dateFin }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #222; background: #fff; padding: 10px; }
        .toolbar { background: #f1f5f9; padding: 8px 14px; border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 5px 12px; font-size: 11px; font-weight: 600; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .btn-print { background: #1e3a8a; color: #fff; border: 1px solid #1e3a8a; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 12px; }
        .comp-title { font-size: 14px; font-weight: bold; color: #1e3a8a; }
        .doc-title { font-size: 14px; font-weight: bold; text-align: right; color: #1e3a8a; }
        .kpi-row { display: flex; gap: 8px; margin-bottom: 12px; }
        .kpi-card { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; background: #f8fafc; }
        .kpi-label { font-size: 8px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 3px; }
        .kpi-val { font-size: 13px; font-weight: bold; font-family: 'Consolas', monospace; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 4.5px 6px; }
        th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 8px; color: #334155; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .section-title { font-size: 10.5px; font-weight: bold; color: #1e3a8a; margin-bottom: 6px; text-transform: uppercase; }
        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; }
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
    <div class="header">
        <div>
            <div class="comp-title">{{ $entete->denomination ?? 'ENTREPRISE' }}</div>
            <div style="font-size: 9px; color: #475569; margin-top: 3px; line-height: 1.4;">
                IFU : <strong>{{ $entete->ifu ?? '—' }}</strong> · RCCM : {{ $entete->rccm ?? '—' }} · ISF : {{ $entete->identifiant_sfe ?? '—' }}<br>
                Régime fiscal : {{ $entete->regime_fiscal ?? '—' }} · CDI : {{ $entete->division_fiscal ?? '—' }}<br>
                Cadastre : {{ $entete->infos_cadastrales ?? '—' }} · Tél : {{ $entete->telephone ?? '—' }}
            </div>
        </div>
        <div>
            <div class="doc-title">BILAN FISCAL PÉRIODIQUE</div>
            <div style="font-size: 9.5px; color: #475569; text-align: right; margin-top: 3px;">
                Période du <strong>{{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}</strong> au <strong>{{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</strong><br>
                Édité le {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    @php $stats = $donnees['statistiques']; @endphp
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total C.A. HT</div>
            <div class="kpi-val">{{ number_format($stats['chiffre_affaires_ht'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">TVA Collectée</div>
            <div class="kpi-val" style="color: #2563eb;">{{ number_format($stats['tva_collectee'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Taxe Spécifique</div>
            <div class="kpi-val">{{ number_format($stats['taxes_specifiques'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">PSVB Collecté</div>
            <div class="kpi-val" style="color: #d97706;">{{ number_format($stats['psvb_collecte'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Timbre Quittance</div>
            <div class="kpi-val">{{ number_format($stats['timbre_quittance'], 0, ',', ' ') }} F</div>
        </div>
        <div class="kpi-card" style="background: #eff6ff; border-color: #bfdbfe;">
            <div class="kpi-label" style="color: #1e3a8a;">TOTAL TTC NET</div>
            <div class="kpi-val" style="color: #1e3a8a;">{{ number_format($stats['total_ttc'], 0, ',', ' ') }} F</div>
        </div>
    </div>

    <!-- 1. Récapitulatif par Type de Facture -->
    @php
        $parType = $stats['par_type_facture'] ?? [];
        $totalNb = 0; $totalHt = 0; $totalTva = 0; $totalTtc = 0;
    @endphp
    <div class="section-title">1. Récapitulatif par Type de Facture (Norme DGI SECeF)</div>
    <table>
        <thead>
            <tr>
                <th>Type de Facture</th>
                <th class="text-center">Nombre</th>
                <th class="text-end">Total HT</th>
                <th class="text-end">Total TVA</th>
                <th class="text-end">Total TTC</th>
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
                    <td><strong>{{ $tCode }}</strong></td>
                    <td class="text-center">{{ $tRow['count'] ?? 0 }}</td>
                    <td class="text-end font-monospace">{{ number_format($tRow['total_ht'] ?? 0, 0, ',', ' ') }} F</td>
                    <td class="text-end font-monospace">{{ number_format($tRow['total_tva'] ?? 0, 0, ',', ' ') }} F</td>
                    <td class="text-end font-monospace fw-bold">{{ number_format($tRow['total_ttc'] ?? 0, 0, ',', ' ') }} F</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot style="background: #f8fafc; font-weight: bold;">
            <tr>
                <td>TOTAL</td>
                <td class="text-center">{{ $totalNb }}</td>
                <td class="text-end font-monospace">{{ number_format($totalHt, 0, ',', ' ') }} F</td>
                <td class="text-end font-monospace">{{ number_format($totalTva, 0, ',', ' ') }} F</td>
                <td class="text-end font-monospace">{{ number_format($totalTtc, 0, ',', ' ') }} F</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. Montants par groupe de taxation et type de facture -->
    @php
        $parTypeGroupe = $stats['par_type_et_groupe_taxe'] ?? [];
    @endphp
    <div class="section-title">2. Montants par Groupe de Taxation et Type de Facture (Norme DGI SECeF)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Type</th>
                <th style="width: 8%;" class="text-center">Grp</th>
                <th>Description</th>
                <th style="width: 10%;" class="text-center">Taux</th>
                <th style="width: 15%;" class="text-end">HT (FCFA)</th>
                <th style="width: 15%;" class="text-end">TVA (FCFA)</th>
                <th style="width: 16%;" class="text-end">TTC (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parTypeGroupe as $row)
            <tr>
                <td><strong>{{ $row['type'] }}</strong></td>
                <td class="text-center fw-bold">{{ $row['groupe'] }}</td>
                <td>{{ $row['description'] }}</td>
                <td class="text-center">{{ isset($row['taux']) && $row['taux'] > 0 ? $row['taux'].'%' : '0%' }}</td>
                <td class="text-end font-monospace">{{ number_format($row['ht'] ?? 0, 0, ',', ' ') }} F</td>
                <td class="text-end font-monospace">{{ number_format($row['tva'] ?? 0, 0, ',', ' ') }} F</td>
                <td class="text-end font-monospace fw-bold">{{ number_format($row['ttc'] ?? 0, 0, ',', ' ') }} F</td>
            </tr>
            @empty
                @foreach($stats['groupes_taxes'] as $codeG => $g)
                <tr>
                    <td><strong>FV</strong></td>
                    <td class="text-center fw-bold">{{ $codeG }}</td>
                    <td>{{ $g['libelle'] }}</td>
                    <td class="text-center">{{ $g['taux'] > 0 ? $g['taux'].'%' : '0%' }}</td>
                    <td class="text-end font-monospace">{{ number_format($g['base_ht'], 0, ',', ' ') }} F</td>
                    <td class="text-end font-monospace">{{ number_format($g['tva'], 0, ',', ' ') }} F</td>
                    <td class="text-end font-monospace fw-bold">{{ number_format($g['base_ht'] + $g['tva'] + $g['ts'], 0, ',', ' ') }} F</td>
                </tr>
                @endforeach
            @endforelse
        </tbody>
    </table>

    <!-- Modes de règlement & Statistiques -->
    <div style="display: flex; gap: 12px; margin-bottom: 12px;">
        <div style="flex: 1;">
            <div class="section-title">3. Modes de Règlement</div>
            <table>
                <thead>
                    <tr><th>Mode</th><th class="text-end">Montant Encaissé</th></tr>
                </thead>
                <tbody>
                    @foreach($stats['modes_paiement'] as $m => $totalM)
                    <tr><td>{{ $m }}</td><td class="text-end font-monospace fw-bold">{{ number_format($totalM, 0, ',', ' ') }} F</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="flex: 1;">
            <div class="section-title">4. Activité Opérationnelle</div>
            <table>
                <tbody>
                    <tr><td>Factures de vente émises (FV) :</td><td class="text-end fw-bold">{{ $stats['nb_ventes'] }}</td></tr>
                    <tr><td>Factures d'avoir émises (FA) :</td><td class="text-end fw-bold text-danger">{{ $stats['nb_avoirs'] }} (-{{ number_format($stats['total_avoirs'], 0, ',', ' ') }} F)</td></tr>
                    <tr><td>Réductions commerciales totales :</td><td class="text-end fw-bold text-danger">-{{ number_format($stats['reductions_commerciales'], 0, ',', ' ') }} F</td></tr>
                    <tr><td>Ventes incomplètes / non certifiées :</td><td class="text-end fw-bold">{{ $stats['ventes_incompletes'] }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Liste des factures de la période -->
    <div class="section-title">5. Registre des Factures & Avoirs Émis sur la Période (Extrait)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Numéro</th>
                <th>Type</th>
                <th>Client</th>
                <th>IFU Client</th>
                <th class="text-end">Net HT</th>
                <th class="text-end">TVA</th>
                <th class="text-end">Net à Payer</th>
                <th class="text-center">Statut DGI</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donnees['factures']->take(35) as $f)
            <tr>
                <td>{{ \Carbon\Carbon::parse($f->date)->format('d/m/Y') }}</td>
                <td class="fw-bold">{{ $f->code }}</td>
                <td>{{ optional($f->Typefacture)->code ?? 'FV' }}</td>
                <td>{{ optional($f->Contribuable)->assujeti ?? 'Client Comptoir' }}</td>
                <td class="font-monospace">{{ optional($f->Contribuable)->ifu ?? '—' }}</td>
                <td class="text-end font-monospace">{{ number_format($f->montant_ht ?? 0, 0, ',', ' ') }}</td>
                <td class="text-end font-monospace">{{ number_format($f->montant_tva ?? 0, 0, ',', ' ') }}</td>
                <td class="text-end font-monospace fw-bold">{{ number_format($f->net_a_payer ?? $f->montant_total, 0, ',', ' ') }}</td>
                <td class="text-center">
                    @if($f->dgi_signature)
                        <span style="color: #16a34a; font-weight: bold;">Certifiée</span>
                    @else
                        <span style="color: #dc2626;">Brouillon</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-2">Aucune facture sur la période sélectionnée.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 25px; display: flex; justify-content: space-between; font-size: 9px;">
        <div style="width: 40%; text-align: center; border-top: 1px solid #94a3b8; padding-top: 4px;">
            Le Responsable Financier / Comptable
        </div>
        <div style="width: 40%; text-align: center; border-top: 1px solid #94a3b8; padding-top: 4px;">
            La Direction Générale
        </div>
    </div>

</body>
</html>
