<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Facture Fournisseur {{ $facture->code }}</title>
    <style>
        @page {
            size: A4;
            margin: 14mm 14mm 14mm 14mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11.5px;
            color: #2c3e50;
            background: #fff;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .invoice {
            max-width: 100%;
            position: relative;
        }

        /* ============ HEADER (standard) ============ */
        .header {
            display: table;
            width: 100%;
            border-bottom: 6px solid #1d3f7f;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .header .left, .header .right { display: table-cell; vertical-align: top; }
        .header .left { width: 58%; }
        .header .right { width: 42%; text-align: right; }
        .company-name { font-size: 15px; font-weight: 700; color: #1d3f7f; }
        .company-meta { font-size: 9px; color: #444; margin-top: 3px; line-height: 1.65; }
        .company-meta b { color: #1a2942; }
        .company-meta .sep { color: #c3c9d1; }
        .doc-title { font-size: 18px; font-weight: 800; color: #1d3f7f; letter-spacing: 1px; text-transform: uppercase; }
        .doc-meta { font-size: 9.5px; margin-top: 3px; color: #444; }
        .footer { margin-top: auto; padding-top: 16px; border-top: 4px solid #1d3f7f; font-size: 8px; color: #777; text-align: center; line-height: 1.6; }
        .footer .d-flex { display: flex; align-items: center; }
        .footer .justify-content-between { justify-content: space-between; }

        /* ============ HEADER BAND ============ */
        .header-band {
            background: linear-gradient(135deg, #315192 0%, #1a2942 100%);
            color: #fff;
            padding: 28px 30px;
            border-radius: 12px;
            margin-bottom: 26px;
            position: relative;
            overflow: hidden;
        }

        .header-band::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
        }

        .header-band::before {
            content: '';
            position: absolute;
            right: 40px;
            bottom: -90px;
            width: 170px;
            height: 170px;
            background: rgba(46, 204, 113, 0.14);
            border-radius: 50%;
        }

        .header-grid {
            display: table;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .header-grid .col-left {
            display: table-cell;
            vertical-align: top;
            width: 60%;
        }

        .header-grid .col-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .company-tagline {
            font-size: 11px;
            opacity: 0.85;
            font-weight: 300;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-meta {
            font-size: 10.5px;
            opacity: 0.92;
            line-height: 1.7;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            line-height: 1;
            margin-bottom: 8px;
        }

        .invoice-subtitle {
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .invoice-num {
            display: inline-block;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.28);
            padding: 6px 16px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        /* ============ STATUS WATERMARK ============ */
        .watermark {
            position: fixed;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 110px;
            font-weight: 800;
            color: rgba(46, 204, 113, 0.10);
            letter-spacing: 14px;
            z-index: 0;
            pointer-events: none;
            text-transform: uppercase;
        }

        .watermark.attente {
            color: rgba(243, 156, 18, 0.10);
        }

        .watermark.annulee {
            color: rgba(231, 76, 60, 0.10);
        }

        /* ============ META CARDS ============ */
        .meta-row {
            display: table;
            border-spacing: 14px 0;
            margin: 0 -14px 24px -14px;
            width: calc(100% + 28px);
        }

        .meta-card {
            display: table-cell;
            vertical-align: top;
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            padding: 14px 16px;
            width: 33.33%;
        }

        .meta-card .label {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6c7a89;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .meta-card .label::before {
            content: '';
            display: inline-block;
            width: 12px;
            height: 2px;
            background: #315192;
            margin-right: 6px;
            vertical-align: middle;
        }

        .meta-card .value {
            font-size: 14px;
            font-weight: 700;
            color: #1a2942;
        }

        .meta-card .sub {
            font-size: 10px;
            color: #6c7a89;
            margin-top: 2px;
        }

        /* ============ FOURNISSEUR BLOCKS ============ */
        .client-section {
            display: table;
            border-spacing: 14px 0;
            margin: 0 -14px 20px -14px;
            width: calc(100% + 28px);
        }

        .client-block {
            display: table-cell;
            vertical-align: top;
            padding: 16px 20px;
            border-radius: 10px;
            background: #ecf0f1;
            border-left: 4px solid #315192;
            width: 50%;
        }

        .client-block.right {
            background: #eafaf1;
            border-left-color: #2ecc71;
        }

        .client-block .heading {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6c7a89;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .client-block .name {
            font-size: 15px;
            font-weight: 700;
            color: #1a2942;
            margin-bottom: 4px;
        }

        .client-block .lines {
            font-size: 11px;
            line-height: 1.7;
            color: #4a5568;
        }

        .client-block .lines strong {
            color: #1a2942;
        }

        /* ============ SECTION HEADING ============ */
        .section-heading {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #315192;
            margin-bottom: 10px;
        }

        .section-heading::before {
            content: '';
            display: inline-block;
            width: 24px;
            height: 3px;
            background: #315192;
            margin-right: 8px;
            vertical-align: middle;
            border-radius: 2px;
        }

        /* ============ ITEMS TABLE ============ */
        table.items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(49, 81, 146, 0.08);
        }

        table.items thead th {
            background: #315192;
            color: #fff;
            font-weight: 600;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 11px 10px;
            text-align: left;
        }

        table.items thead th.right {
            text-align: right;
        }

        table.items thead th.center {
            text-align: center;
        }

        table.items tbody td {
            padding: 11px 10px;
            border-bottom: 1px solid #e5e9ec;
            font-size: 11.5px;
            vertical-align: middle;
        }

        table.items tbody tr:nth-child(even) td {
            background: #fafbfd;
        }

        table.items tbody tr:last-child td {
            border-bottom: none;
        }

        table.items tbody td.right {
            text-align: right;
        }

        table.items tbody td.center {
            text-align: center;
        }

        table.items tbody .ref-cell {
            font-weight: 600;
            color: #315192;
            font-size: 10.5px;
        }

        table.items tbody .amount-cell {
            font-weight: 700;
            color: #1a2942;
            white-space: nowrap;
        }

        .tva-tag {
            display: inline-block;
            background: #ecf0f1;
            color: #315192;
            font-size: 9.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
        }

        /* ============ TOTALS ============ */
        .totals-row {
            display: table;
            border-spacing: 10px 0;
            margin: 14px -10px 0 -10px;
            width: calc(100% + 20px);
        }

        .totals-left,
        .totals-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .tva-card {
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 8px;
            overflow: hidden;
        }

        .tva-card .card-title {
            background: #ecf0f1;
            padding: 5px 10px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1a2942;
        }

        table.tva-grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.tva-grid th {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #6c7a89;
            padding: 4px 8px;
            text-align: left;
            border-bottom: 1px solid #e5e9ec;
            font-weight: 600;
        }

        table.tva-grid td {
            padding: 4px 8px;
            font-size: 10px;
            border-bottom: 1px solid #e5e9ec;
            line-height: 1.3;
        }

        table.tva-grid tfoot td {
            font-weight: 700;
            background: #fafbfd;
            color: #1a2942;
        }

        table.tva-grid .right {
            text-align: right;
        }

        .summary {
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 8px;
            overflow: hidden;
        }

        .summary-row {
            display: table;
            width: 100%;
            padding: 4px 12px;
            border-bottom: 1px solid #e5e9ec;
            line-height: 1.3;
        }

        .summary-row .lbl-cell {
            display: table-cell;
            font-size: 10px;
            color: #6c7a89;
            font-weight: 500;
        }

        .summary-row .val-cell {
            display: table-cell;
            text-align: right;
            font-size: 10.5px;
            font-weight: 600;
            color: #1a2942;
        }

        .summary-row.subtotal-ttc {
            background: #fafbfd;
        }

        .summary-row.subtotal-ttc .val-cell {
            color: #315192;
            font-size: 12px;
            font-weight: 800;
        }

        .summary-row.total {
            background: linear-gradient(135deg, #315192 0%, #1a2942 100%);
            border-bottom: none;
            padding: 7px 12px;
        }

        .summary-row.total .lbl-cell {
            color: rgba(255, 255, 255, 0.9);
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .summary-row.total .val-cell {
            color: #fff;
            font-size: 14px;
            font-weight: 800;
        }

        /* ============ AMOUNT IN LETTERS ============ */
        .amount-letters {
            margin-top: 18px;
            background: #ecf0f1;
            border-left: 4px solid #315192;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 11px;
        }

        .amount-letters .title {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6c7a89;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .amount-letters .value {
            font-size: 12px;
            font-weight: 700;
            color: #1a2942;
            font-style: italic;
        }

        /* ============ FOOTER ============ */
        .footer-band {
            margin-top: 22px;
            padding-top: 12px;
            border-top: 2px solid #315192;
            font-size: 10px;
            color: #6c7a89;
        }

        .footer-grid {
            display: table;
            width: 100%;
        }

        .footer-grid>div {
            display: table-cell;
            vertical-align: top;
        }

        .footer-grid .left {
            width: 60%;
        }

        .footer-grid .right {
            text-align: right;
            font-style: italic;
            color: #315192;
            font-weight: 600;
        }

        .footer-grid b {
            color: #1a2942;
        }

        /* ============ TOOLBAR ============ */
        .toolbar {
            background: #f8f9fa;
            padding: 12px 20px;
            margin: -14mm -14mm 20px -14mm;
            border-bottom: 1px solid #dee2e6;
            text-align: right;
        }

        .btn {
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 8px;
        }

        .btn-print {
            background: #315192;
            color: #fff;
        }

        .btn-back {
            background: #fff;
            color: #1a2942;
            border: 1px solid #d5dbdf;
        }

        @media print {
            .toolbar {
                display: none !important;
            }

            body {
                font-size: 10.5px;
            }

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <a class="btn btn-back" href="{{ url('module_facture_fournisseur/' . $facture->id) }}">← Retour</a>
        <button class="btn btn-print" onclick="window.print()">🖨 Imprimer</button>
    </div>

    @php
        $statut = strtolower($facture->statut ?? '');
        $watermarkClass = 'attente';
        $watermarkText = 'En attente';
        if (str_contains($statut, 'payé') || str_contains($statut, 'paye')) {
            $watermarkClass = 'payee';
            $watermarkText = 'Payée';
        } elseif (str_contains($statut, 'annul')) {
            $watermarkClass = 'annulee';
            $watermarkText = 'Annulée';
        }
    @endphp

    <div class="watermark {{ $watermarkClass }}">{{ $watermarkText }}</div>

    <div class="invoice">

        {{-- ============ HEADER ============ --}}
        <div class="header">
            <div class="left">
                <div class="company-name">{{ $entete->denomination ?? config('app.name') }}</div>
                <div class="company-meta">
                    @if (!empty($entete->activite))
                        {{ $entete->activite }}<br>
                    @endif

                    @if (!empty($entete->postale))
                        <b>Adresse :</b> {{ $entete->postale }}
                    @endif
                    @if (!empty($entete->telephone))
                        <span class="sep">·</span> <b>Tél :</b> {{ $entete->telephone }}
                    @endif
                    @if (!empty($entete->email))
                        <span class="sep">·</span> <b>Email :</b> {{ $entete->email }}
                    @endif
                    <br>

                    @if (!empty($entete->ifu))
                        <b>IFU :</b> {{ $entete->ifu }}
                    @endif
                    @if (!empty($entete->rccm))
                        <span class="sep">·</span> <b>RCCM :</b> {{ $entete->rccm }}
                    @endif
                    @if (!empty($entete->identifiant_sfe))
                        <span class="sep">·</span> <b>ISF :</b> {{ $entete->identifiant_sfe }}
                    @endif
                    @if (!empty($entete->ifu) || !empty($entete->rccm) || !empty($entete->identifiant_sfe))
                        <br>
                    @endif

                    @if (!empty($entete->regime_fiscal))
                        <b>Régime fiscal :</b> {{ $entete->regime_fiscal }}
                    @endif
                    @if (!empty($entete->division_fiscal))
                        <span class="sep">·</span> <b>Division fiscale :</b> {{ $entete->division_fiscal }}
                    @endif
                    @if (!empty($entete->regime_fiscal) || !empty($entete->division_fiscal))
                        <br>
                    @endif

                    @if (!empty($entete->comptes_bancaires))
                        <b>Compte bancaire :</b> {{ $entete->comptes_bancaires }}
                    @endif
                </div>
            </div>
            <div class="right">
                <div class="doc-title">Facture fournisseur</div>
                <div class="doc-meta">
                    N° <b>{{ $facture->code }}</b>
                </div>
            </div>
        </div>

        {{-- ============ META CARDS ============ --}}
        <div class="meta-row">
            <div class="meta-card">
                <div class="label">Date d'émission</div>
                <div class="value">
                    {{ $facture->date ? \Carbon\Carbon::parse($facture->date)->format('d/m/Y') : '—' }}
                </div>
            </div>
            <div class="meta-card">
                <div class="label">Référence</div>
                <div class="value">{{ $facture->reference ?: '—' }}</div>
            </div>
            <div class="meta-card">
                <div class="label">Statut</div>
                <div class="value">{{ ucfirst($facture->statut ?? '—') }}</div>
            </div>
        </div>

        {{-- ============ FOURNISSEUR ============ --}}
        <div class="client-section">
            <div class="client-block left">
                <div class="heading">Reçue de</div>
                <div class="name">{{ optional($facture->Fournisseur)->assujeti ?? '—' }}</div>
                <div class="lines">
                    @if (optional($facture->Fournisseur)->adresse)
                        <strong>Adresse :</strong> {{ $facture->Fournisseur->adresse }}<br>
                    @endif
                    @if (optional($facture->Fournisseur)->telephone)
                        <strong>Tél :</strong> {{ $facture->Fournisseur->telephone }}<br>
                    @endif
                    @if (optional($facture->Fournisseur)->ifu)
                        <strong>IFU :</strong> {{ $facture->Fournisseur->ifu }}<br>
                    @endif
                    @if (optional($facture->Fournisseur)->rccm)
                        <strong>RCCM :</strong> {{ $facture->Fournisseur->rccm }}
                    @endif
                </div>
            </div>
            <div class="client-block right">
                <div class="heading">Informations fiscales</div>
                <div class="lines">
                    @if (optional($facture->Fournisseur)->division_fiscal)
                        <strong>Division fiscale :</strong> {{ $facture->Fournisseur->division_fiscal }}<br>
                    @endif
                    @if (optional($facture->Fournisseur)->regime_fiscal)
                        <strong>Régime fiscal :</strong> {{ $facture->Fournisseur->regime_fiscal }}<br>
                    @endif
                    @if (optional($facture->Fournisseur)->adresse_cadas)
                        <strong>Adresse cadastrale :</strong> {{ $facture->Fournisseur->adresse_cadas }}<br>
                    @endif
                    @if (optional($facture->Service)->libelle)
                        <strong>Dépôt :</strong> {{ $facture->Service->libelle }}
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ ITEMS ============ --}}
        <div class="section-heading">Objet : <span>{{ $facture->objet ?? 'N/A' }}</span></div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width:10%;">Réf.</th>
                    <th style="width:38%;">Désignation</th>
                    <th class="right" style="width:10%;">Qté</th>
                    <th class="right" style="width:14%;">Prix unitaire</th>
                    <th class="right" style="width:18%; white-space:nowrap;">Montant HT</th>
                    <th class="center" style="width:10%;">TVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($elements as $element)
                    @php
                        $quantite  = (float) $element->quantite;
                        $prix      = (float) $element->prix_unitaire;
                        $montantHt = $quantite * $prix;
                        $taux      = (float) optional(optional($element->Produit)->tva)->taux;
                    @endphp
                    <tr>
                        <td class="ref-cell">{{ optional($element->Produit)->code ?? '—' }}</td>
                        <td>{{ $element->designation ?? optional($element->Produit)->designation ?? '—' }}</td>
                        <td class="right">{{ number_format($quantite, 0, ',', ' ') }}</td>
                        <td class="right">{{ number_format($prix, 0, ',', ' ') }}</td>
                        <td class="right amount-cell">{{ number_format($montantHt, 0, ',', ' ') }}</td>
                        <td class="center">
                            @if ($taux)
                                <span class="tva-tag">
                                    {{ rtrim(rtrim(number_format($taux, 2, '.', ''), '0'), '.') }}%
                                </span>
                            @else
                                <span class="tva-tag" style="background:#fff5f5;color:#c53030;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ============ TOTALS ============ --}}
        <div class="totals-row">
            <div class="totals-left">
                <div class="tva-card">
                    <div class="card-title">Récapitulatif TVA</div>
                    <table class="tva-grid">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th class="right">Base HT</th>
                                <th class="right">Taux</th>
                                <th class="right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tva_groups as $g)
                                <tr>
                                    <td>C{{ rtrim(rtrim(number_format($g['taux'], 2, '', ''), '0'), '.') }}</td>
                                    <td class="right">{{ number_format($g['base'], 0, ',', ' ') }}</td>
                                    <td class="right">{{ number_format($g['taux'], 2, ',', ' ') }} %</td>
                                    <td class="right">{{ number_format($g['taxe'], 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center;color:#6c7a89;font-style:italic;">
                                        Aucune TVA applicable
                                    </td>
                                </tr>
                            @endforelse
                            @if (!empty($tva_groups))
                                <tr>
                                    <td style="font-weight:700;">Total TVA</td>
                                    <td class="right" style="font-weight:600;">
                                        {{ number_format(array_sum(array_column($tva_groups, 'base')), 0, ',', ' ') }}
                                    </td>
                                    <td></td>
                                    <td class="right" style="font-weight:700;">
                                        {{ number_format($total_taxe, 0, ',', ' ') }}
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="totals-right">
                <div class="summary">
                    <div class="summary-row">
                        <div class="lbl-cell">Total brut (HT)</div>
                        <div class="val-cell">{{ number_format($total_ht, 0, ',', ' ') }}</div>
                    </div>
                    <div class="summary-row">
                        <div class="lbl-cell">TVA</div>
                        <div class="val-cell">{{ number_format($total_taxe, 0, ',', ' ') }}</div>
                    </div>
                    <div class="summary-row subtotal-ttc">
                        <div class="lbl-cell">Total TTC</div>
                        <div class="val-cell">{{ number_format($total_ttc, 0, ',', ' ') }}</div>
                    </div>
                    <div class="summary-row total">
                        <div class="lbl-cell">Net à payer</div>
                        <div class="val-cell">{{ number_format($total_ttc, 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ AMOUNT IN LETTERS ============ --}}
        <div class="amount-letters">
            <div class="title">Arrêtée la présente facture à la somme de</div>
            <div class="value">
                {{ ucfirst(function_exists('conversion') ? conversion((int) round($total_ttc)) : number_format($total_ttc, 0, ',', ' ')) }}
                franc(s) CFA
            </div>
        </div>

        {{-- ============ FOOTER ============ --}}
        <div class="footer">
            <div class="d-flex justify-content-between">
                <div>
                    @if (!empty($entete->comptes_bancaires))
                        <p>Compte bancaire : <strong>{{ $entete->comptes_bancaires }}</strong></p>
                    @endif
                </div>
                <div>
                    <p><strong>{{ $entete->denomination ?? '' }}</strong></p>
                </div>
            </div>
            @if (!empty($entete->pied_page))
                <p style="margin-top:4px;">{{ $entete->pied_page }}</p>
            @endif
        </div>
    </div>
</body>

</html>
