<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->code }}{{ !empty($isDuplicata) ? ' (DUPLICATA)' : '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 0; /* Élimine l'URL du navigateur, la date et le titre lors de l'impression */
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
            padding: 8mm 10mm 18mm 10mm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Filigrane DUPLICATA légal DGI */
        .watermark-duplicata {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-32deg);
            font-size: 76pt;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.18);
            letter-spacing: 12px;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 9999;
            border: 6px dashed rgba(220, 38, 38, 0.28);
            padding: 14px 50px;
            border-radius: 14px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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
        .btn-primary { background: #1e3a8a; color: #fff; border: 1px solid #1e3a8a; }
        .btn-ticket { background: #0284c7; color: #fff; border: 1px solid #0284c7; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }

        /* Conteneur principal */
        .invoice-wrapper {
            width: 100%;
        }

        /* 1. Header Corporate Exécutif à 3 Colonnes Équilibrées */
        .executive-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 12px;
            gap: 12px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Colonne 1 : Entreprise & Logo (28%) */
        .header-col-company {
            flex: 0 0 28%;
            max-width: 28%;
            min-width: 0;
        }
        .company-logo {
            max-height: 44px;
            max-width: 140px;
            object-fit: contain;
            margin-bottom: 3px;
            display: block;
        }
        .company-name {
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: -0.3px;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .company-meta {
            font-size: 8px;
            color: #475569;
            line-height: 1.4;
        }
        .company-meta strong {
            color: #0f172a;
        }

        /* Colonne 2 : Cartouche Officiel SECeF DGI Compact (40%) */
        .header-col-secef {
            flex: 0 0 40%;
            max-width: 40%;
            min-width: 0;
        }
        .header-secef-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 8px;
            border: 1.5px solid #1e3a8a;
            border-radius: 6px;
            background: #f8fafc;
            width: 100%;
            box-sizing: border-box;
        }
        .header-secef-badge .seal-qr {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px;
        }
        .header-secef-info {
            font-size: 7.8px;
            line-height: 1.35;
            color: #1e293b;
            flex: 1;
            min-width: 0;
        }
        .header-secef-info .badge-ok {
            display: inline-block;
            background: #1e3a8a;
            color: #fff;
            font-size: 7.5px;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 3px;
            margin-bottom: 2px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .secef-code-row {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 1.5px;
            white-space: nowrap;
        }
        .header-secef-info .sig-full {
            font-family: 'JetBrains Mono', monospace;
            font-size: 7px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.2px;
            background: #eef2ff;
            padding: 1px 4px;
            border-radius: 3px;
            line-height: 1.2;
        }

        /* Colonne 3 : Identification Document (30%) */
        .header-col-doc {
            flex: 0 0 30%;
            max-width: 30%;
            min-width: 0;
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
        }
        .doc-title {
            font-size: 14.5px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
            line-height: 1.2;
            white-space: nowrap;
        }
        .doc-number-badge {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: #1e3a8a;
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            padding: 2.5px 8px;
            border-radius: 5px;
            margin-bottom: 3px;
            white-space: nowrap;
        }
        .doc-meta-date {
            font-size: 8.5px;
            color: #64748b;
            white-space: nowrap;
        }
        .doc-meta-date strong {
            color: #0f172a;
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

        /* 2. Cartes d'informations (Émetteur/Règlement & Client) */
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
            background: #1e3a8a;
            color: #fff;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 8px;
            border-right: 1px solid #2563eb;
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
        .fiscal-badge {
            display: inline-block;
            font-size: 7.5px;
            font-weight: 700;
            background: #e2e8f0;
            color: #1e293b;
            padding: 1px 4px;
            border-radius: 3px;
            margin-left: 4px;
            vertical-align: middle;
        }

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
            background: #1e3a8a;
            color: #fff;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 800;
            border-bottom: none;
        }
        .finance-table tr.grand-total-row .font-mono {
            color: #38bdf8;
        }

        /* 5. Mini-tableau structuré : Mentions SECeF ST 1.0 */
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

        /* 7. Attestation de Sécurité Fiscale SECeF & Signature */
        .bottom-blocks {
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            gap: 15px;
            margin-bottom: 12px;
        }
        .secef-seal-box {
            flex: 1.3;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .secef-seal-box.draft {
            background: #fef2f2;
            border: 1px dashed #f87171;
            color: #991b1b;
            text-align: center;
            justify-content: center;
            flex-direction: column;
            padding: 10px;
        }
        .seal-qr {
            width: 64px;
            height: 64px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .seal-meta {
            font-size: 8.5px;
            line-height: 1.4;
            color: #1e293b;
        }
        .seal-title {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0284c7;
            margin-bottom: 2px;
        }
        .seal-signature-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 8px;
            font-weight: 700;
            word-break: break-all;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .signature-box {
            flex: 1;
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

        /* 8. Pied de page légal et bancaire FIXE */
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
            body { padding: 8mm 10mm 18mm 10mm; }
            .watermark-duplicata {
                display: block !important;
                visibility: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-footer-fixed {
                position: fixed;
                bottom: 6mm;
                left: 10mm;
                right: 10mm;
                width: auto;
            }
        }
    </style>
</head>
<body>

    @if(!empty($isDuplicata))
        <div class="watermark-duplicata">DUPLICATA</div>
    @endif

    @if(!request()->has('iframe'))
    <!-- Barre d'outils écran -->
    <div class="toolbar">
        <div>
            <a href="{{ url('module_ordre_facture/' . $facture->id) }}" class="btn btn-back">← Retour Facture</a>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('print_ticket_facture', $facture->id) }}" class="btn btn-ticket" target="_blank">📄 Format Ticket 80mm</a>
            <button onclick="imprimerEtMarquer()" class="btn btn-primary">🖨 Imprimer Format A4</button>
        </div>
    </div>
    @endif

    @php
        $certif = $facture->certification_active;
        $sigDgi = $certif->sig ?? $facture->dgi_signature;
        $nimDgi = $certif->nim ?? $facture->dgi_nim_machine;
        $cptDgi = $certif ? ($certif->compteur_facture . '/' . $certif->compteur_total . ' ' . $certif->type_facture) : $facture->dgi_compteur;
        $heureDgi = $certif?->date_certification?->format('d/m/Y H:i:s') ?? ($facture->dgi_signature ? \Carbon\Carbon::parse($facture->date)->format('d/m/Y H:i:s') : null);
        $qrData = $certif->qr_content ?? $facture->dgi_qrcode;
        $typeDoc = strtoupper(optional($facture->Typefacture)->code ?? 'FV');
        $isAvoir = in_array($typeDoc, ['FA', 'EA'], true);
        $isVenteExoneree = ($total_taxe == 0);
        $isHtMode = true;
        if (!empty($facture->notes) && str_starts_with(trim($facture->notes), '{')) {
            $notesArr = json_decode($facture->notes, true);
            if (isset($notesArr['mode_prix'])) {
                $isHtMode = ($notesArr['mode_prix'] === 'HT');
            }
        }
    @endphp

    <div class="invoice-wrapper">
        <!-- 1. En-tête Corporate Exécutif à 3 Colonnes Équilibrées -->
        <header class="executive-header">
            <!-- Colonne 1 : Entreprise & Logo (28%) -->
            <div class="header-col-company">
                @if(!empty($entete->logo))
                    <img src="{{ asset('storage/' . $entete->logo) }}" alt="{{ $entete->denomination ?? 'Logo' }}" class="company-logo">
                @endif
                <div class="company-name">{{ $entete->denomination ?? 'ENTREPRISE' }}</div>
                <div class="company-meta">
                    <strong>IFU :</strong> {{ $entete->ifu ?? '—' }}<br>
                    @if(!empty($entete->telephone))<strong>Tél :</strong> {{ $entete->telephone }}@endif
                    @if(!empty($entete->email)) · {{ $entete->email }}@endif
                </div>
            </div>

            <!-- Colonne 2 : Cartouche Officiel SECeF DGI Compact (40%) -->
            <div class="header-col-secef">
                @if($sigDgi)
                <div class="header-secef-badge">
                    <div id="header-dgi-qr" class="seal-qr"></div>
                    <div class="header-secef-info">
                        <span class="badge-ok">Certification</span>
                        <div><strong>Machine :</strong> <span class="font-mono">{{ $nimDgi }}</span></div>
                        <div><strong>Compteurs :</strong> <span class="font-mono">{{ $cptDgi }}</span></div>
                        @if($heureDgi)<div><strong>Date :</strong> {{ $heureDgi }}</div>@endif
                        <div class="secef-code-row"><strong>CODE SECeF DGI :</strong> <span class="sig-full">{{ $sigDgi }}</span></div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Colonne 3 : Identification Document (30%) -->
            <div class="header-col-doc">
                <div class="doc-title">
                    @if($facture->statut === 'BROUILLON')
                        PROJET DE FACTURE
                    @elseif($isAvoir)
                        FACTURE D'AVOIR
                    @elseif(!empty($isExport))
                        FACTURE EXPORT
                    @elseif(!empty($isAcompte))
                        FACTURE D'ACOMPTE
                    @else
                        FACTURE DE VENTE
                    @endif
                    @if(!empty($isDuplicata))
                        <span style="display: block; font-size: 10px; color: #dc2626; font-weight: 800; letter-spacing: 1px; margin-top: 2px;">(DUPLICATA)</span>
                    @endif
                </div>
                <div class="doc-number-badge">N° {{ $facture->code }}</div>
                <div class="doc-meta-date">Date : <strong>{{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}</strong></div>
            </div>
        </header>

        <!-- 2. Section Métadonnées & Client -->
        <div class="info-cards-row">
            <div class="info-card">
                <div class="info-card-header">
                    <span>Modalités de règlement</span>
                    <span class="font-mono text-muted">RÉF : {{ $facture->reference ?? $facture->code }}</span>
                </div>
                <div class="card-line"><strong>Mode de règlement :</strong> {{ optional($facture->ModePaie)->libelle ?? 'Espèces' }}</div>
                <div class="card-line"><strong>Opérateur :</strong> {{ optional($facture->User)->prenom }} {{ optional($facture->User)->nom ?? 'Système' }}</div>
                @if(!empty($facture->objet))
                    <div class="card-line"><strong>Objet :</strong> {{ $facture->objet }}</div>
                @endif
                @if($isAvoir)
                    <div class="card-line" style="color: #dc2626;"><strong>Facture d'origine :</strong> {{ $facture->reference ?? '—' }}</div>
                @endif
            </div>

            <div class="info-card">
                <div class="info-card-header">
                    <span>Facturé à / Destinataire</span>
                    @if(optional($facture->Contribuable)->ifu)<span class="font-mono">IFU : {{ $facture->Contribuable->ifu }}</span>@endif
                </div>
                <div class="client-name-bold">{{ optional($facture->Contribuable)->assujeti ?? 'Client Comptoir' }}</div>
                <div class="card-line">
                    @if(optional($facture->Contribuable)->rccm)<strong>RCCM :</strong> {{ $facture->Contribuable->rccm }} · @endif
                    @if(optional($facture->Contribuable)->telephone)<strong>Tél :</strong> {{ $facture->Contribuable->telephone }}@endif
                </div>
                <div class="card-line">
                    @if(optional($facture->Contribuable)->regime_fiscal)<strong>Régime :</strong> {{ $facture->Contribuable->regime_fiscal }} · @endif
                    @if(optional($facture->Contribuable)->Categorie)<strong>Type :</strong> {{ $facture->Contribuable->Categorie->libelle }}@endif
                </div>
            </div>
        </div>

        <!-- 3. Tableau des Articles -->
        <table class="invoice-items">
            <thead>
                <tr>
                    <th style="width: 4%;" class="text-center">#</th>
                    <th style="width: 50%;">Article / Prestation &bull; Classification Fiscale</th>
                    <th class="text-center" style="width: 8%;">Qté</th>
                    <th class="text-end" style="width: 13%;">P.U. {{ $isHtMode ? 'HT' : 'TTC' }}</th>
                    <th class="text-end" style="width: 8%;">T.S.</th>
                    <th class="text-end" style="width: 7%;">Remise</th>
                    <th class="text-end" style="width: 10%;">Total {{ $isHtMode ? 'HT' : 'TTC' }}</th>
                </tr>
            </thead>
            <tbody>
                @php $lineIdx = 1; @endphp
                @foreach($elements as $el)
                    @php
                        $codeProd = strtoupper((string) optional($el->Produit)->code);
                        if ($codeProd === 'TRANS') continue;
                        $qte = (float) $el->quantite;
                        $pu  = (float) $el->prix_unitaire;
                        $ligneBrut = $qte * $pu;
                        $tauxTva = (float) optional(optional($el->Produit)->tva)->taux ?? 0;
                        $grpTva = strtoupper(optional(optional($el->Produit)->tva)->code ?: ($tauxTva > 0 ? 'B' : 'A'));
                        $prod = $el->Produit;
                        $tsUnit = 0.0;
                        if ($prod) {
                            if ($prod->type_ts === 'POURCENTAGE' && (float) ($prod->pourcentage_ts ?? 0) > 0) {
                                $tsUnit = round($pu * ((float) $prod->pourcentage_ts / 100.0), 2);
                            } else {
                                $tsUnit = (float) ($prod->montant_ts ?? 0);
                            }
                        }
                        $labelFiscal = $prod ? $prod->label_fiscal : ($el->designation ?? optional($el->Produit)->designation ?? 'Article');
                    @endphp
                    <tr>
                        <td class="text-center font-mono text-muted">{{ $lineIdx++ }}</td>
                        <td>
                            <div class="fw-bold">{{ $labelFiscal }}</div>
                        </td>
                        <td class="text-center fw-bold">{{ (float)$qte == (int)$qte ? number_format($qte, 0, ',', ' ') : rtrim(rtrim(number_format($qte, 3, ',', ' '), '0'), ',') }}</td>
                        <td class="text-end font-mono">{{ number_format($pu, 0, ',', ' ') }}</td>
                        <td class="text-end font-mono text-muted">{{ $tsUnit > 0 ? number_format($tsUnit * $qte, 0, ',', ' ') : '-' }}</td>
                        <td class="text-end font-mono text-danger">-</td>
                        <td class="text-end font-mono fw-bold">{{ number_format($ligneBrut, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- 4. Bloc Dual des Totaux -->
        <div class="totals-section">
            <!-- Gauche: Tableau Récapitulatif Fiscal DGI -->
            <div class="taxes-block">
                <table class="taxes-table">
                    <thead>
                        <tr>
                            <th class="text-start">Groupe TVA DGI</th>
                            <th class="text-end">Base HT</th>
                            <th class="text-center">Taux</th>
                            <th class="text-end">Montant TVA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tva_groups as $grp => $info)
                        <tr>
                            <td class="fw-bold">Groupe [{{ $grp }}]</td>
                            <td class="text-end font-mono">{{ number_format($info['base'], 0, ',', ' ') }}</td>
                            <td class="text-center">{{ $info['taux'] > 0 ? $info['taux'].'%' : '0%' }}</td>
                            <td class="text-end font-mono fw-bold">{{ number_format($info['taxe'], 0, ',', ' ') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end">TOTAL TVA :</td>
                            <td class="text-end font-mono fw-bold">{{ number_format($total_taxe, 0, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                </table>
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
                        <td>Total TVA</td>
                        <td class="text-end font-mono">{{ number_format($total_taxe, 0, ',', ' ') }}</td>
                    </tr>
                    @if($total_ts > 0)
                    <tr>
                        <td>Taxes spécifiques</td>
                        <td class="text-end font-mono">{{ number_format($total_ts, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                    @if($total_bic > 0)
                    <tr>
                        <td>Acompte BIC / PSVB</td>
                        <td class="text-end font-mono text-warning">{{ number_format($total_bic, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                    @if($montant_transport > 0)
                    <tr>
                        <td>{{ $transport_label }}</td>
                        <td class="text-end font-mono">{{ number_format($montant_transport, 0, ',', ' ') }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td>{{ $isAvoir ? 'NET À DÉDUIRE' : 'NET À PAYER' }}</td>
                        <td class="text-end font-mono">{{ number_format($net_a_payer, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if($total_timbre > 0)
                    <tr>
                        <td colspan="2" class="text-muted" style="font-size: 7.5px; border-top: 1px dashed #cbd5e1; padding-top: 3px; font-style: italic;">
                            Timbre quittance (paiement espèces) : {{ number_format($total_timbre, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @endif
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
                    $commentaires['Observations'] = trim($facture->notes);
                }
            }
        @endphp

        <!-- 5. Mentions & Observations Particulières -->
        @if(!empty($commentaires))
        <div class="secef-mentions-card">
            <div class="secef-mentions-header">
                <span class="secef-dot"></span>
                <span>Observations & Mentions Particulières</span>
            </div>
            <div style="padding: 6px 12px; font-size: 8.5px; line-height: 1.5; color: #1e293b;">
                @foreach($commentaires as $label => $valeur)
                    <div>
                        @if($label === 'mention')
                            <strong>Mention légale :</strong> {{ $valeur }}
                        @elseif($label === 'notes_libres')
                            <strong>Note :</strong> {{ $valeur }}
                        @else
                            <span style="color: #64748b; margin-right: 4px;">•</span> {{ $valeur }}
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 6. Montant en toutes lettres -->
        <div class="amount-in-words">
            Arrêtée la présente facture à la somme de :
            <span>{{ ucfirst(conversion((int) round($net_a_payer))) }} FRANCS CFA {{ $isVenteExoneree ? '(HORS TAXES / EXONÉRÉ DE TAXES)' : '(TOUTES TAXES COMPRISES)' }}</span>
        </div>

        <!-- 7. Attestation & Signature autorisée -->
        <div class="bottom-blocks" style="justify-content: flex-end;">
            <div class="signature-box" style="width: 280px; min-height: 85px;">
                <div class="signature-title">Pour l'Entreprise (Direction / Comptabilité)</div>
                <div style="height: 48px;"></div>
                <div class="signature-hint">Signature autorisée & Cachet officiel</div>
            </div>
        </div>

        @if($sigDgi)
        <script src="{{ asset('js/qrcode.min.js') }}"></script>
        <script>
            const qrText = @json($qrData ?: "BFSECEF01;{$nimDgi};{$sigDgi};{$entete->ifu};");
            if (document.getElementById('header-dgi-qr')) {
                new QRCode(document.getElementById('header-dgi-qr'), {
                    text: qrText,
                    width: 50,
                    height: 50,
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        </script>
        @endif

        <!-- 8. Pied de page bancaire & fiscal FIXE -->
        <footer class="invoice-footer-fixed">
            <div class="footer-row">
                @if(!empty($entete->postale))<span>{{ $entete->postale }}</span> · @endif
                @if(!empty($entete->rccm))<strong>RCCM :</strong> {{ $entete->rccm }} · @endif
                <strong>Régime fiscal :</strong> {{ $entete->regime_fiscal ?? '—' }}
                @if(!empty($entete->division_fiscal)) · <strong>CDI :</strong> {{ $entete->division_fiscal }} @endif
                @if(!empty($entete->infos_cadastrales)) · <strong>Cadastre :</strong> {{ $entete->infos_cadastrales }} @endif
                @if(!empty($entete->comptes_bancaires)) · <strong>Banque :</strong> {{ $entete->comptes_bancaires }} @endif
            </div>
            <div class="footer-row" style="font-style: italic;">
                Facture établie conformément à la réglementation fiscale en vigueur.
            </div>
        </footer>
    </div>

    <script>
        function imprimerEtMarquer() {
            window.print();
            fetch('{{ route("module_ordre_facture.marquer_imprimee", $facture->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        }
        window.onafterprint = function() {
            fetch('{{ route("module_ordre_facture.marquer_imprimee", $facture->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        };
    </script>
</body>
</html>
