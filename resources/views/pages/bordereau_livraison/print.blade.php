<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Bon de livraison {{ $facture->code }}</title>
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
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ============ HEADER (standard) ============ */
        .header {
            display: table;
            width: 100%;
            border-bottom: 6px solid #1d3f7f;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .header .left,
        .header .right {
            display: table-cell;
            vertical-align: top;
        }

        .header .left {
            width: 58%;
        }

        .header .right {
            width: 42%;
            text-align: right;
        }

        .std-company-name {
            font-size: 15px;
            font-weight: 700;
            color: #1d3f7f;
        }

        .std-company-meta {
            font-size: 9px;
            color: #444;
            margin-top: 3px;
            line-height: 1.65;
        }

        .std-company-meta b {
            color: #1a2942;
        }

        .std-company-meta .sep {
            color: #c3c9d1;
        }

        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #1d3f7f;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .std-doc-meta {
            font-size: 9.5px;
            margin-top: 3px;
            color: #444;
        }

        .footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 4px solid #1d3f7f;
            font-size: 8px;
            color: #777;
            text-align: center;
            line-height: 1.6;
        }

        .footer .d-flex {
            display: flex;
            align-items: center;
        }

        .footer .justify-content-between {
            justify-content: space-between;
        }

        /* ============ HEADER BAND ============ */
        .header-band {
            background: linear-gradient(135deg, #1d3f7f 0%, #1a2942 100%);
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
            background: rgba(255, 255, 255, 0.10);
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
            font-size: 30px;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            line-height: 1;
            margin-bottom: 6px;
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
            background: #1d3f7f;
            margin-right: 6px;
            vertical-align: middle;
        }

        .meta-card .value {
            font-size: 14px;
            font-weight: 700;
            color: #1a2942;
        }

        /* ============ CLIENT BLOCKS ============ */
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
            border-left: 4px solid #1d3f7f;
            width: 50%;
        }

        .client-block.right {
            background: #eef2f8;
            border-left-color: #4a6fa5;
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
            color: #1d3f7f;
            margin-bottom: 10px;
        }

        .section-heading::before {
            content: '';
            display: inline-block;
            width: 24px;
            height: 3px;
            background: #1d3f7f;
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
            box-shadow: 0 2px 8px rgba(29, 63, 127, 0.10);
        }

        table.items thead th {
            background: #1d3f7f;
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
            color: #1d3f7f;
            font-size: 10.5px;
        }

        table.items tbody .qty-cell {
            font-weight: 700;
            color: #1a2942;
            font-size: 12.5px;
        }

        .check-box {
            display: inline-block;
            width: 26px;
            height: 22px;
            border: 1.5px solid #b9c2c9;
            border-radius: 4px;
            background: #fff;
        }

        /* ============ RECAP ============ */
        .recap-card {
            margin-top: 18px;
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            overflow: hidden;
        }

        .recap-card .card-title {
            padding: 9px 16px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6c7a89;
            border-bottom: 1px solid #e5e9ec;
            background: #fafbfd;
        }

        .recap-row-line {
            display: table;
            width: 100%;
            padding: 8px 16px;
            border-bottom: 1px solid #f0f2f4;
        }

        .recap-row-line:last-child {
            border-bottom: none;
        }

        .recap-row-line .lbl-cell {
            display: table-cell;
            font-size: 11px;
            color: #6c7a89;
            font-weight: 500;
        }

        .recap-row-line .val-cell {
            display: table-cell;
            text-align: right;
            font-size: 12px;
            font-weight: 700;
            color: #1a2942;
        }

        /* ============ CONDITIONS ============ */
        .conditions {
            margin-top: 18px;
            background: #eef2f8;
            border: 1px solid #c9d6ea;
            border-left: 4px solid #1d3f7f;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 11px;
        }

        .conditions .title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #1d3f7f;
            font-weight: 700;
            margin-bottom: 4px;
        }

        /* ============ SIGNATURES ============ */
        .signatures {
            display: table;
            width: 100%;
            border-spacing: 18px 0;
            margin: 22px -18px 0 -18px;
            width: calc(100% + 36px);
        }

        .sign-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 1px dashed #b9c2c9;
            border-radius: 10px;
            padding: 14px 18px;
            height: 110px;
        }

        .sign-box .lbl {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #1d3f7f;
            margin-bottom: 6px;
        }

        .sign-box .hint {
            font-size: 10px;
            color: #93a1ab;
            font-style: italic;
        }

        /* ============ FOOTER ============ */
        .footer-band {
            margin-top: 22px;
            padding-top: 12px;
            border-top: 2px solid #1d3f7f;
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
            background: #1d3f7f;
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
        <a class="btn btn-back" href="{{ url('module_bordereau_livraison/' . $facture->id) }}">← Retour</a>
        <button class="btn btn-print" onclick="window.print()">🖨 Imprimer</button>
    </div>

    <div class="invoice">

        {{-- ============ EN-TÊTE ============ --}}
        <div class="header">
            <div class="left">
                <div class="std-company-name">{{ $entete->denomination ?? config('app.name') }}</div>
                <div class="std-company-meta">
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
                <div class="doc-title">Bordereau de livraison</div>
                <div class="std-doc-meta">
                    N° <b>BL-{{ $facture->code }}</b> &nbsp;·&nbsp;
                    {{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}
                </div>
            </div>
        </div>

        {{-- ============ META CARDS ============ --}}
        <div class="meta-row">
            <div class="meta-card">
                <div class="label">Date de livraison</div>
                <div class="value">{{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}</div>
            </div>
            <div class="meta-card">
                <div class="label">Référence</div>
                <div class="value">{{ $facture->reference ?: '—' }}</div>
            </div>
            <div class="meta-card">
                <div class="label">Facture liée</div>
                <div class="value">{{ $facture->code }}</div>
            </div>
        </div>

        {{-- ============ EXPEDITEUR / DESTINATAIRE ============ --}}
        <div class="client-section">
            <div class="client-block">
                <div class="heading">Expéditeur</div>
                <div class="name">{{ $entete->denomination ?? config('app.name') }}</div>
                <div class="lines">
                    @if (!empty($entete->postale))
                        {{ $entete->postale }}<br>
                    @endif
                </div>
            </div>
            <div class="client-block right">
                <div class="heading">Livré à</div>
                <div class="name">{{ optional($facture->Contribuable)->assujeti }}</div>
                <div class="lines">
                    @if (optional($facture->Contribuable)->ifu)
                        <strong>IFU :</strong> {{ $facture->Contribuable->ifu }} &nbsp;
                    @endif
                    @if (optional($facture->Contribuable)->rccm)
                        <strong>RCCM :</strong> {{ $facture->Contribuable->rccm }}
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ ITEMS ============ --}}
        <div class="section-heading">Détail des articles livrés</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width:6%;">N°</th>
                    <th style="width:14%;">Réf.</th>
                    <th>Désignation</th>
                    <th class="right" style="width:18%;">Quantité livrée</th>
                    <th class="center" style="width:10%;">Reçu</th>
                </tr>
            </thead>
            <tbody>
                @php $totalQte = 0; @endphp
                @foreach ($elements as $i => $element)
                    @php
                        $quantite = (float) $element->quantite;
                        $totalQte += $quantite;
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="ref-cell">{{ optional($element->Produit)->code }}</td>
                        <td>{{ $element->designation ?? optional($element->Produit)->designation }}</td>
                        <td class="right qty-cell">{{ (float)$quantite == (int)$quantite ? number_format($quantite, 0, ',', ' ') : rtrim(rtrim(number_format($quantite, 3, ',', ' '), '0'), ',') }}</td>
                        <td class="center"><span class="check-box"></span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ============ RECAP ============ --}}
        <div class="recap-card">
            <div class="card-title">Récapitulatif de la livraison</div>
            <div class="recap-row-line">
                <div class="lbl-cell">Nombre de lignes</div>
                <div class="val-cell">{{ $elements->count() }}</div>
            </div>
            <div class="recap-row-line">
                <div class="lbl-cell">Quantité totale</div>
                <div class="val-cell">{{ number_format($totalQte, 2, ',', ' ') }}</div>
            </div>
        </div>

        {{-- ============ CONDITIONS ============ --}}
        <div class="conditions">
            <div class="title">Conditions de réception</div>
            Toute réserve sur la marchandise livrée doit être formulée par écrit au moment de la réception ou
            dans un délai de <strong>48 heures</strong>. La signature du présent bon vaut acceptation des
            articles livrés conformes en quantité et qualité. <em>Ce document ne tient pas lieu de facture.</em>
        </div>

        {{-- ============ SIGNATURES ============ --}}
        <div class="signatures">
            <div class="sign-box">
                <div class="lbl">Signature de l'expéditeur</div>
                <div class="hint">Nom, qualité, date et cachet</div>
            </div>
            <div class="sign-box">
                <div class="lbl">Signature du destinataire</div>
                <div class="hint">Bon pour réception — date, cachet et mention « Reçu en bon état »</div>
            </div>
        </div>

        {{-- ============ PIED DE PAGE ============ --}}
        <div class="footer">
            <div style="text-align: center; font-style: italic; color: #666; margin-bottom: 8px;">
                Merci de votre confiance.
            </div>
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
