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
        }

        .invoice {
            max-width: 100%;
            position: relative;
        }

        /* ============ HEADER BAND ============ */
        .header-band {
            background: linear-gradient(135deg, #c2410c 0%, #7c2d12 100%);
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
            background: #c2410c;
            margin-right: 6px;
            vertical-align: middle;
        }

        .meta-card .value {
            font-size: 14px;
            font-weight: 700;
            color: #7c2d12;
        }

        .meta-card .sub {
            font-size: 10px;
            color: #6c7a89;
            margin-top: 2px;
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
            border-left: 4px solid #c2410c;
            width: 50%;
        }

        .client-block.right {
            background: #fdf6e3;
            border-left-color: #f39c12;
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
            color: #7c2d12;
            margin-bottom: 4px;
        }

        .client-block .lines {
            font-size: 11px;
            line-height: 1.7;
            color: #4a5568;
        }

        .client-block .lines strong {
            color: #7c2d12;
        }

        /* ============ SECTION HEADING ============ */
        .section-heading {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #c2410c;
            margin-bottom: 10px;
        }

        .section-heading::before {
            content: '';
            display: inline-block;
            width: 24px;
            height: 3px;
            background: #c2410c;
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
            box-shadow: 0 2px 8px rgba(194, 65, 12, 0.10);
        }

        table.items thead th {
            background: #c2410c;
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
            color: #c2410c;
            font-size: 10.5px;
        }

        table.items tbody .qty-cell {
            font-weight: 700;
            color: #7c2d12;
            font-size: 12.5px;
        }

        .check-cell {
            text-align: center;
            width: 30px;
            height: 22px;
            border: 1.5px solid #b9c2c9;
            border-radius: 4px;
            display: inline-block;
        }

        /* ============ RECAP ============ */
        .recap-row {
            display: table;
            border-spacing: 14px 0;
            margin: 22px -14px 0 -14px;
            width: calc(100% + 28px);
        }

        .recap-card {
            display: table-cell;
            vertical-align: top;
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            padding: 14px 18px;
            width: 50%;
        }

        .recap-card .card-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #7c2d12;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e9ec;
        }

        .recap-row-line {
            display: table;
            width: 100%;
            padding: 6px 0;
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
            color: #7c2d12;
        }

        .totals-total {
            margin-top: 8px;
            padding: 10px 14px;
            background: linear-gradient(135deg, #c2410c 0%, #7c2d12 100%);
            color: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: table;
            width: 100%;
        }

        .totals-total .lbl-cell {
            display: table-cell;
        }

        .totals-total .val-cell {
            display: table-cell;
            text-align: right;
            font-size: 16px;
        }

        /* ============ SIGNATURES ============ */
        .signatures {
            display: table;
            width: 100%;
            border-spacing: 18px 0;
            margin: 26px -18px 0 -18px;
            width: calc(100% + 36px);
        }

        .sign-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 1px dashed #b9c2c9;
            border-radius: 10px;
            padding: 14px 18px;
            height: 120px;
        }

        .sign-box .lbl {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #c2410c;
            margin-bottom: 6px;
        }

        .sign-box .hint {
            font-size: 10px;
            color: #93a1ab;
            font-style: italic;
        }

        /* ============ CONDITIONS + FOOTER ============ */
        .conditions {
            margin-top: 22px;
            background: #fff5eb;
            border: 1px solid #f3c898;
            border-left: 4px solid #c2410c;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 11px;
        }

        .conditions .title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #c2410c;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .footer-band {
            margin-top: 22px;
            padding-top: 12px;
            border-top: 2px solid #c2410c;
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
            color: #c2410c;
            font-weight: 600;
        }

        .footer-grid b {
            color: #7c2d12;
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
            background: #c2410c;
            color: #fff;
        }

        .btn-back {
            background: #fff;
            color: #7c2d12;
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
        <a class="btn btn-back" href="{{ url('module_ordre_facture/' . $facture->id) }}">← Retour</a>
        <button class="btn btn-print" onclick="window.print()">🖨 Imprimer</button>
    </div>

    <div class="invoice">

        {{-- ============ HEADER ============ --}}
        <div class="header-band">
            <div class="header-grid">
                <div class="col-left">
                    <div class="company-name">{{ $entete->denomination ?? config('app.name') }}</div>
                    @if (!empty($entete->activite))
                        <div class="company-tagline">{{ $entete->activite }}</div>
                    @endif
                    <div class="company-meta">
                        @if (!empty($entete->postale))
                            {{ $entete->postale }}<br>
                        @endif
                        @if (!empty($entete->telephone))
                            Tél : {{ $entete->telephone }}
                        @endif
                    </div>
                </div>
                <div class="col-right">
                    <div class="invoice-subtitle">Bon de</div>
                    <div class="invoice-title">Livraison</div>
                    <div class="invoice-num">N° BL-{{ $facture->code }}</div>
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
                    <th class="center" style="width:6%;">N°</th>
                    <th style="width:14%;">Réf.</th>
                    <th style="width:54%;">Désignation</th>
                    <th class="right" style="width:14%;">Quantité livrée</th>
                    <th class="center" style="width:12%;">Reçu</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($elements as $index => $element)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="ref-cell">{{ optional($element->Produit)->code }}</td>
                        <td>{{ $element->designation ?? optional($element->Produit)->designation }}</td>
                        <td class="right qty-cell">{{ number_format((float) $element->quantite, 2, ',', ' ') }}</td>
                        <td class="center"><span class="check-cell"></span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ============ RECAP ============ --}}
        <div class="recap-row">
            <div class="recap-card">
                <div class="card-title">Récapitulatif de la livraison</div>
                <div class="recap-row-line">
                    <div class="lbl-cell">Nombre de lignes</div>
                    <div class="val-cell">{{ $elements->count() }}</div>
                </div>
                <div class="recap-row-line">
                    <div class="lbl-cell">Quantité totale</div>
                    <div class="val-cell">
                        {{ number_format($elements->sum(fn($e) => (float) $e->quantite), 2, ',', ' ') }}</div>
                </div>
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

        {{-- ============ FOOTER ============ --}}
        <div class="footer-band">
            <div class="footer-grid">
                <div class="left">
                    @if (!empty($entete->telephone))
                        <b>Tél :</b> {{ $entete->telephone }} &nbsp;·&nbsp;
                    @endif
                    @if (!empty($entete->fax))
                        <b>Fax :</b> {{ $entete->fax }} &nbsp;·&nbsp;
                    @endif
                    @if (!empty($entete->email))
                        <b>Email :</b> {{ $entete->email }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>
