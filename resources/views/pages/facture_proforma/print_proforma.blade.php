<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis Proforma {{ $facture->code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 24mm 10mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 9.5px;
            color: #0f172a;
            background: #fff;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Toolbar écran */
        .toolbar {
            background: #f8fafc;
            padding: 10px 16px;
            margin: -8mm -10mm 15px -10mm;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary { background: #0f172a; color: #fff; border: 1px solid #0f172a; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }

        .invoice-wrapper {
            width: 100%;
        }

        /* 1. Header Corporate Exécutif */
        .executive-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 12px;
            gap: 20px;
        }
        .company-brand {
            flex: 1;
        }
        .company-name {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .company-meta {
            font-size: 9px;
            color: #475569;
            line-height: 1.5;
        }
        .company-meta strong {
            color: #0f172a;
        }

        .document-panel {
            text-align: right;
            min-width: 260px;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .doc-number-badge {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            background: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            padding: 3px 10px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .doc-meta-date {
            font-size: 9.5px;
            color: #475569;
            margin-bottom: 5px;
        }
        .tax-mode-badge {
            display: inline-block;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 2.5px 8px;
            border-radius: 4px;
        }
        .badge-ttc {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .badge-ht {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        /* 2. Cartes d'informations */
        .info-cards-row {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }
        .info-card {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            background: #fafbfd;
        }
        .info-card-header {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .client-name-bold {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .card-line {
            font-size: 9px;
            color: #334155;
            line-height: 1.45;
        }
        .card-line strong {
            color: #0f172a;
        }

        /* 3. Tableau des Articles */
        table.invoice-items {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #cbd5e1;
            margin-bottom: 12px;
            font-size: 9px;
        }
        table.invoice-items thead th {
            background: #0f172a;
            color: #fff;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            border-right: 1px solid #334155;
        }
        table.invoice-items thead th:last-child {
            border-right: none;
        }
        table.invoice-items tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        table.invoice-items tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        table.invoice-items tbody tr:last-child td {
            border-bottom: none;
        }
        table.invoice-items tbody td:last-child {
            border-right: none;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .fw-bold { font-weight: 700; }

        /* 4. Bloc Dual des Totaux */
        .totals-section {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }
        .taxes-block {
            width: 50%;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .taxes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.8px;
        }
        .taxes-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 5px 8px;
            border-bottom: 1px solid #cbd5e1;
        }
        .taxes-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .taxes-table tfoot td {
            background: #f8fafc;
            font-weight: 700;
            border-top: 1px solid #cbd5e1;
        }

        .finance-block {
            width: 50%;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }
        .finance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .finance-table td {
            padding: 4.5px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .finance-table tr.highlight-row td {
            background: #f8fafc;
            font-weight: 600;
        }
        .finance-table tr.grand-total-row td {
            background: #0f172a;
            color: #fff;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 800;
            border-bottom: none;
        }
        .finance-table tr.grand-total-row .font-mono {
            color: #38bdf8;
        }

        /* 5. Mini-tableau structuré : Mentions et conditions */
        .secef-mentions-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 10px;
            overflow: hidden;
            background: #fafbfd;
        }
        .secef-mentions-header {
            background: #f1f5f9;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            padding: 4px 10px;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .secef-dot {
            width: 6px;
            height: 6px;
            background: #0284c7;
            border-radius: 50%;
        }
        .secef-mentions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        .secef-mentions-table td {
            padding: 3.5px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .secef-mentions-table tr:last-child td {
            border-bottom: none;
        }
        .secef-lbl {
            width: 35%;
            font-weight: 700;
            color: #475569;
        }
        .secef-val {
            width: 65%;
            color: #0f172a;
        }

        /* 6. Montant en toutes lettres */
        .amount-in-words {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 6px 12px;
            font-size: 9.5px;
            margin-bottom: 12px;
        }
        .amount-in-words span {
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }

        /* 7. Signatures */
        .bottom-blocks {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }
        .signature-box {
            width: 280px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 85px;
            text-align: center;
        }
        .signature-title {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
        }
        .signature-hint {
            font-size: 7.5px;
            color: #94a3b8;
            font-style: italic;
        }

        /* 8. Pied de page bancaire & légal FIXE */
        .invoice-footer-fixed {
            border-top: 1.5px solid #0f172a;
            padding-top: 6px;
            font-size: 7.8px;
            color: #475569;
            text-align: center;
            line-height: 1.45;
            background: #fff;
        }
        .footer-row {
            margin-bottom: 2px;
        }
        .footer-row strong {
            color: #0f172a;
        }

        @media print {
            .toolbar { display: none !important; }
            body { padding: 0; }
            .invoice-footer-fixed {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    @if(!request()->has('iframe'))
    <!-- Barre d'outils écran -->
    <div class="toolbar">
        <div>
            <a href="{{ route('module_facture_proforma.show', $facture->id) }}" class="btn btn-back">← Retour Proforma</a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">🖨 Imprimer Devis Proforma</button>
        </div>
    </div>
    @endif

    @php
        $remise_totale = (float)($remise_1 ?? 0) + (float)($remise_2 ?? 0);
        $isHtMode = ($total_taxe == 0);
    @endphp

    <div class="invoice-wrapper">
        <!-- 1. En-tête Corporate Exécutif -->
        <header class="executive-header">
            <div class="company-brand">
                <div class="company-name">{{ $entete->denomination ?? 'ENTREPRISE' }}</div>
                <div class="company-meta">
                    @if(!empty($entete->postale)){{ $entete->postale }}<br>@endif
                    @if(!empty($entete->telephone))<strong>Tél :</strong> {{ $entete->telephone }} · @endif
                    @if(!empty($entete->email))<strong>Email :</strong> {{ $entete->email }}<br>@endif
                    <strong>IFU :</strong> {{ $entete->ifu ?? '—' }} · <strong>RCCM :</strong> {{ $entete->rccm ?? '—' }}
                </div>
            </div>

            <div class="document-panel">
                <div class="doc-title">DEVIS / FACTURE PROFORMA</div>
                <div class="doc-number-badge">N° {{ $facture->code }}</div>
                <div class="doc-meta-date">Date : <strong>{{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}</strong></div>
                <div>
                    <span class="tax-mode-badge {{ $isHtMode ? 'badge-ht' : 'badge-ttc' }}">
                        Proposition établie en : <strong>{{ $isHtMode ? 'Hors Taxe (HT)' : 'TTC' }}</strong>
                    </span>
                </div>
            </div>
        </header>

        <!-- 2. Section Métadonnées & Client -->
        <div class="info-cards-row">
            <div class="info-card">
                <div class="info-card-header">
                    <span>Conditions du Devis</span>
                    <span class="font-mono text-muted">RÉF : {{ $facture->reference ?? $facture->code }}</span>
                </div>
                <div class="card-line"><strong>Établi par :</strong> {{ optional($facture->User)->prenom }} {{ optional($facture->User)->nom ?? 'Commercial' }}</div>
                <div class="card-line"><strong>Validité de l'offre :</strong> 30 jours à compter de la date d'émission</div>
                @if(!empty($facture->objet))
                    <div class="card-line"><strong>Objet :</strong> {{ $facture->objet }}</div>
                @endif
                @if($facture->estConvertie())
                    <div class="card-line" style="color: #15803d;">
                        <strong>Statut :</strong> Convertie en facture définitive
                    </div>
                @endif
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <span>Destinataire / Client</span>
                    @if(optional($facture->Contribuable)->ifu)<span class="font-mono">IFU : {{ $facture->Contribuable->ifu }}</span>@endif
                </div>
                <div class="client-name-bold">{{ optional($facture->Contribuable)->assujeti ?? 'Client' }}</div>
                <div class="card-line">
                    @if(optional($facture->Contribuable)->rccm)<strong>RCCM :</strong> {{ $facture->Contribuable->rccm }} · @endif
                    @if(optional($facture->Contribuable)->telephone)<strong>Tél :</strong> {{ $facture->Contribuable->telephone }}@endif
                </div>
                <div class="card-line">
                    @if(optional($facture->Contribuable)->regime_fiscal)<strong>Régime :</strong> {{ $facture->Contribuable->regime_fiscal }}@endif
                </div>
            </div>
        </div>

        <!-- 3. Tableau des Articles -->
        <table class="invoice-items">
            <thead>
                <tr>
                    <th style="width: 4%;" class="text-center">#</th>
                    <th style="width: 50%;">Article / Prestation proposée</th>
                    <th class="text-center" style="width: 8%;">Qté</th>
                    <th class="text-end" style="width: 14%;">P.U. {{ $isHtMode ? 'HT' : 'TTC' }}</th>
                    <th class="text-center" style="width: 10%;">TVA</th>
                    <th class="text-end" style="width: 14%;">Total {{ $isHtMode ? 'HT' : 'TTC' }}</th>
                </tr>
            </thead>
            <tbody>
                @php $lineIdx = 1; @endphp
                @foreach($elements as $el)
                    @php
                        $qte = (float) $el->quantite;
                        $pu  = (float) $el->prix_unitaire;
                        $ligneBrut = $qte * $pu;
                        $tauxTva = (float) optional(optional($el->Produit)->tva)->taux ?? 0;
                        $desig = $el->designation ?? optional($el->Produit)->designation ?? 'Article';
                        $codeProd = optional($el->Produit)->code;
                    @endphp
                    <tr>
                        <td class="text-center font-mono text-muted">{{ $lineIdx++ }}</td>
                        <td>
                            <div class="fw-bold">{{ $desig }}</div>
                            @if($codeProd)
                                <small class="text-muted font-mono" style="font-size: 8px;">Code : {{ $codeProd }}</small>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ (float)$qte == (int)$qte ? number_format($qte, 0, ',', ' ') : rtrim(rtrim(number_format($qte, 3, ',', ' '), '0'), ',') }}</td>
                        <td class="text-end font-mono">{{ number_format($pu, 0, ',', ' ') }}</td>
                        <td class="text-center font-mono">{{ $tauxTva > 0 ? $tauxTva.'%' : '0% (Exo)' }}</td>
                        <td class="text-end font-mono fw-bold">{{ number_format($ligneBrut, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 4. Bloc Dual des Totaux -->
        <div class="totals-section">
            <!-- Gauche: Récapitulatif TVA prévisionnelle -->
            <div class="taxes-block">
                <table class="taxes-table">
                    <thead>
                        <tr>
                            <th class="text-start">Taxes Applicables</th>
                            <th class="text-end">Base HT</th>
                            <th class="text-center">Taux</th>
                            <th class="text-end">Montant TVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tva_groups as $grp => $info)
                        <tr>
                            <td class="fw-bold">TVA ({{ $info['taux'] }}%)</td>
                            <td class="text-end font-mono">{{ number_format($info['base'], 0, ',', ' ') }}</td>
                            <td class="text-center">{{ $info['taux'] > 0 ? $info['taux'].'%' : '0%' }}</td>
                            <td class="text-end font-mono fw-bold">{{ number_format($info['taxe'], 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end">TOTAL TAXES :</td>
                            <td class="text-end font-mono fw-bold">{{ number_format($total_taxe, 0, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div style="padding: 6px 10px; background: #fafbfd; border-top: 1px solid #cbd5e1; font-size: 8.5px;">
                    <span class="text-muted">Clause de validité :</span> Les prix mentionnés sont fermes sous réserve de disponibilité des stocks.
                </div>
            </div>

            <!-- Droite: Synthèse Financière -->
            <div class="finance-block">
                <table class="finance-table">
                    <tr>
                        <td>Total Brut HT</td>
                        <td class="text-end font-mono">{{ number_format($total_ht, 0, ',', ' ') }}</td>
                    </tr>
                    @if($remise_totale > 0)
                    <tr>
                        <td class="text-danger">Remise commerciale</td>
                        <td class="text-end font-mono text-danger">- {{ number_format($remise_totale, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                    <tr class="highlight-row">
                        <td>Total Net HT</td>
                        <td class="text-end font-mono fw-bold">{{ number_format($net_commercial, 0, ',', ' ') }}</td>
                    </tr>
                    <tr>
                        <td>Total TVA (estimée)</td>
                        <td class="text-end font-mono">{{ number_format($total_taxe, 0, ',', ' ') }}</td>
                    </tr>
                    @if(!empty($total_bic) && $total_bic > 0)
                    <tr>
                        <td>Acompte BIC / PSVB</td>
                        <td class="text-end font-mono text-warning">{{ number_format($total_bic, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                    @if(!empty($montant_transport) && $montant_transport > 0)
                    <tr>
                        <td>{{ $transport_label ?? 'Transport' }}</td>
                        <td class="text-end font-mono">{{ number_format($montant_transport, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td>{{ $isHtMode ? 'TOTAL ESTIMÉ (HT)' : 'TOTAL ESTIMÉ (TTC)' }}</td>
                        <td class="text-end font-mono">{{ number_format($net_a_payer, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>
        </div>

        @php
            $commentaires = [];
            if (!empty($facture->notes)) {
                if (str_starts_with(trim($facture->notes), '{')) {
                    $rawNotes = json_decode($facture->notes, true) ?: [];
                    foreach ($rawNotes as $k => $v) {
                        if (in_array($k, ['mode_prix', 'sfe_error', 'action_type', 'cert_status'])) continue;
                        if (!empty(trim((string)$v))) {
                            $commentaires[$k] = trim((string)$v);
                        }
                    }
                } else {
                    $commentaires['OBS'] = trim($facture->notes);
                }
            }
            $nomsLignes = [
                'A' => 'Ligne A (Chauffeur / Commande)',
                'B' => 'Ligne B (Immatriculation / Destination)',
                'C' => 'Ligne C (Observations)',
                'D' => 'Ligne D (Conditions de livraison)',
                'E' => 'Ligne E (Mode de règlement prévisionnel)',
                'F' => 'Ligne F (Mention commerciale)',
                'G' => 'Ligne G (Réf. Dossier / Projet)',
                'H' => 'Ligne H (Référence Contrat)',
                'OBS' => 'Mentions particulières'
            ];
            $hasComments = !empty($commentaires);
        @endphp

        <!-- 5. Mini-tableau structuré : Mentions et conditions -->
        @if($hasComments)
        <div class="secef-mentions-card">
            <div class="secef-mentions-header">
                <span class="secef-dot"></span>
                <span>Conditions spécifiques & Mentions du Devis</span>
            </div>
            <table class="secef-mentions-table">
                <tbody>
                    @foreach($commentaires as $label => $valeur)
                        @if($label !== 'mode_prix' && !empty(trim((string)$valeur)))
                            <tr>
                                <td class="secef-lbl">{{ is_numeric($label) ? "Ligne $label" : ($nomsLignes[$label] ?? $label) }}</td>
                                <td class="secef-val">{{ $valeur }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- 6. Montant en toutes lettres -->
        <div class="amount-in-words">
            Arrêté le présent devis proforma à la somme de :
            <span>{{ ucfirst(conversion((int) round($net_a_payer))) }} FRANCS CFA {{ $isHtMode ? '(HORS TAXES)' : '(TOUTES TAXES COMPRISES)' }}</span>
        </div>

        <!-- 7. Signatures -->
        <div class="bottom-blocks">
            <div class="signature-box">
                <div class="signature-title">Pour l'Entreprise (Service Commercial)</div>
                <div style="flex-grow: 1;"></div>
                <div class="signature-hint">Signature & Cachet commercial</div>
            </div>
        </div>

        <!-- 8. Pied de page bancaire & légal FIXE (sans répétition d'adresse/tél) -->
        <footer class="invoice-footer-fixed">
            <div class="footer-row">
                @if(!empty($entete->comptes_bancaires))
                    <strong>Coordonnées Bancaires :</strong> {{ $entete->comptes_bancaires }} ·
                @endif
                <strong>Régime fiscal :</strong> {{ $entete->regime_fiscal ?? '—' }} · <strong>Centre des Impôts (CDI) :</strong> {{ $entete->division_fiscal ?? '—' }}
                @if(!empty($entete->infos_cadastrales)) · <strong>Cadastre :</strong> {{ $entete->infos_cadastrales }} @endif
            </div>
            <div class="footer-row" style="font-style: italic;">
                Document proforma émis à titre d'estimation commerciale sans valeur fiscale d'encaissement.
            </div>
        </footer>
    </div>

</body>
</html>
