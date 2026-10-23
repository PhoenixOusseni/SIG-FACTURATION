<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Reçu de Règlement {{ $reglement->code }}</title>
    <!-- Google Fonts: Inter pour un rendu typographique ultra-net -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        /* Définition Page & Marges : suppression des en-têtes et pieds de page par défaut du navigateur */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 9.5pt;
            color: #0f172a;
            background: #ffffff;
            line-height: 1.45;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding: 12mm 15mm;
            min-height: 297mm;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* ── Toolbar pour écran uniquement ── */
        .toolbar {
            background: #f8fafc;
            padding: 10px 18px;
            margin: -12mm -15mm 16px -15mm;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print { background: #1d3f7f; color: #fff; box-shadow: 0 2px 6px rgba(29,63,127,0.3); }
        .btn-back  { background: #fff; color: #334155; border: 1px solid #cbd5e1; }

        @media print {
            .toolbar { display: none !important; }
            body { padding: 10mm 14mm !important; }
        }

        /* ── Filigrane Sécurisé ── */
        .watermark-bg {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 100pt;
            font-weight: 900;
            color: rgba(16, 185, 129, 0.04);
            letter-spacing: 12px;
            pointer-events: none;
            user-select: none;
            z-index: 0;
            text-transform: uppercase;
        }

        /* ── Header 3 Colonnes ── */
        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .header-col-company {
            flex: 0 0 42%;
            max-width: 42%;
        }

        .header-col-logo {
            flex: 0 0 24%;
            max-width: 24%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 8px;
        }

        .header-col-doc {
            flex: 0 0 34%;
            max-width: 34%;
            text-align: right;
        }

        .company-name {
            font-size: 15pt;
            font-weight: 900;
            color: #1d3f7f;
            line-height: 1.15;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .company-info {
            font-size: 8pt;
            color: #334155;
            line-height: 1.45;
        }

        .company-logo {
            max-height: 60px;
            max-width: 130px;
            object-fit: contain;
        }

        .doc-badge-title {
            font-size: 13pt;
            font-weight: 900;
            color: #047857;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .doc-number-pill {
            display: inline-block;
            background: #f1f5f9;
            color: #0f172a;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10pt;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            margin-bottom: 4px;
        }

        .doc-meta-date {
            font-size: 8.5pt;
            color: #475569;
        }

        /* ── Cartes d'Information ── */
        .info-cards-row {
            display: flex;
            gap: 14px;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .info-card {
            flex: 1;
            background: #fafbfd;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .info-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 6px;
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
        }

        .party-name {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 3px;
        }

        .party-details {
            font-size: 8.2pt;
            color: #475569;
            line-height: 1.5;
        }

        /* ── Grand Cadre Montant Reçu ── */
        .amount-banner {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            color: #ffffff;
            border-radius: 8px;
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(4, 120, 87, 0.15);
            position: relative;
            z-index: 1;
        }

        .amount-banner-lbl {
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.92;
        }

        .amount-banner-val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 20pt;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .amount-banner-words {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #047857;
            padding: 8px 14px;
            border-radius: 0 6px 6px 0;
            font-size: 8.8pt;
            color: #1e293b;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }

        .amount-banner-words strong {
            font-weight: 700;
            color: #047857;
        }

        /* ── Tableaux Récapitulatifs ── */
        .section-title {
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1d3f7f;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 18px;
            background: #ffffff;
        }

        .data-table th, .data-table td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
        }

        .data-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-mode {
            background: #e0f2fe;
            color: #0369a1;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            font-size: 8pt;
        }

        /* ── Situation Facture (4 Cartes KPI) ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 22px;
            position: relative;
            z-index: 1;
        }

        .kpi-cell {
            background: #fafbfd;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }

        .kpi-cell .lbl {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 3px;
        }

        .kpi-cell .val {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
        }

        .kpi-cell.success .val { color: #047857; }
        .kpi-cell.danger .val { color: #dc2626; }

        /* ── Signatures ── */
        .signatures-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .sig-box {
            width: 44%;
            text-align: center;
        }

        .sig-box-title {
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1d3f7f;
            margin-bottom: 45px;
        }

        .sig-box-line {
            border-top: 1.5px dashed #94a3b8;
            padding-top: 4px;
            font-size: 7.5pt;
            color: #64748b;
            font-style: italic;
        }

        /* ── Pied de page ── */
        .receipt-footer {
            margin-top: auto;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            font-size: 7.5pt;
            color: #64748b;
            text-align: center;
            line-height: 1.4;
        }
    </style>
</head>

@php
    $isRemboursement = ((float) $reglement->montant < 0);
    $montantAbsolu = abs((float) $reglement->montant);
@endphp
<body>
    <!-- Boutons d'action pour écran -->
    <div class="toolbar">
        <a href="javascript:history.back()" class="btn btn-back">← Retour</a>
        <button onclick="window.print()" class="btn btn-print">Imprimer ce Reçu</button>
    </div>

    <div class="watermark-bg" style="{{ $isRemboursement ? 'color: rgba(220, 38, 38, 0.05);' : '' }}">{{ $isRemboursement ? 'DÉCAISSEMENT' : 'REÇU' }}</div>

    <!-- 1. En-tête officiel 3 Colonnes -->
    <header class="receipt-header">
        <div class="header-col-company">
            <h1 class="company-name">{{ $entete->denomination ?? config('app.name') }}</h1>
            <div class="company-info">
                @if(!empty($entete->ifu))<strong>IFU :</strong> <span class="font-mono">{{ $entete->ifu }}</span> · @endif
                @if(!empty($entete->rccm))<strong>RCCM :</strong> {{ $entete->rccm }}<br>@endif
                @if(!empty($entete->telephone))<strong>Tél :</strong> {{ $entete->telephone }} · @endif
                @if(!empty($entete->email))<strong>Email :</strong> {{ $entete->email }}<br>@endif
                @if(!empty($entete->regime_fiscal))<strong>Régime :</strong> {{ $entete->regime_fiscal }}@endif
                @if(!empty($entete->division_fiscal)) · <strong>CDI :</strong> {{ $entete->division_fiscal }}@endif
            </div>
        </div>

        <div class="header-col-logo">
            @if(!empty($entete->logo) && file_exists(public_path('photos/' . $entete->logo)))
                <img src="{{ asset('photos/' . $entete->logo) }}" class="company-logo" alt="Logo">
            @endif
        </div>

        <div class="header-col-doc">
            <div class="doc-badge-title" style="{{ $isRemboursement ? 'background: #991b1b; color: #fff;' : '' }}">
                {{ $isRemboursement ? 'Reçu de Remboursement' : 'Reçu de Règlement' }}
            </div>
            <div class="doc-number-pill">N° {{ $reglement->code }}</div>
            <div class="doc-meta-date">Date : <strong>{{ \Carbon\Carbon::parse($reglement->date)->format('d/m/Y') }}</strong></div>
            @if($facture)
                <div class="doc-meta-date mt-1">Facture liée : <strong class="font-mono text-primary">{{ $facture->code }}</strong></div>
            @endif
        </div>
    </header>

    <!-- 2. Cartes d'Information (Client & Facture) -->
    <div class="info-cards-row">
        <div class="info-card">
            <div class="info-card-header">
                <span>{{ $isRemboursement ? 'Bénéficiaire du Remboursement' : 'Reçu de / Client Payeur' }}</span>
                @if($facture && optional($facture->Contribuable)->ifu)
                    <span class="font-mono">IFU : {{ $facture->Contribuable->ifu }}</span>
                @endif
            </div>
            <div class="party-name">
                {{ $facture && optional($facture->Contribuable)->assujeti ? $facture->Contribuable->assujeti : 'Client Comptoir' }}
            </div>
            <div class="party-details">
                @if($facture && optional($facture->Contribuable)->telephone)
                    <strong>Tél :</strong> {{ $facture->Contribuable->telephone }} · 
                @endif
                @if($facture && optional($facture->Contribuable)->rccm)
                    <strong>RCCM :</strong> {{ $facture->Contribuable->rccm }}
                @endif
                @if($facture && optional($facture->Contribuable)->adresse)
                    <br>{{ $facture->Contribuable->adresse }}
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-header">
                <span>Détails Facture & Références</span>
                <span class="font-mono">{{ $facture ? \Carbon\Carbon::parse($facture->date)->format('d/m/Y') : '' }}</span>
            </div>
            <div class="party-name font-mono">
                {{ $facture ? $facture->code : 'Sans facture' }}
            </div>
            <div class="party-details">
                @if($facture && !empty($facture->reference))
                    <strong>Réf. Commande / BC :</strong> {{ $facture->reference }}<br>
                @endif
                @if($facture && !empty($facture->objet))
                    <strong>Objet :</strong> {{ $facture->objet }}<br>
                @endif
                <strong>Caissier / Opérateur :</strong> {{ optional($reglement->User)->prenom }} {{ optional($reglement->User)->nom ?? 'Système' }}
            </div>
        </div>
    </div>

    <!-- 3. Bannière Montant Reçu / Remboursé -->
    <div class="amount-banner" style="{{ $isRemboursement ? 'background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%); box-shadow: 0 4px 12px rgba(185, 28, 28, 0.2);' : '' }}">
        <div class="amount-banner-lbl">{{ $isRemboursement ? 'Montant Décaissement / Remboursé' : 'Montant Reçu & Encaissé' }}</div>
        <div class="amount-banner-val">{{ number_format($montantAbsolu, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="amount-banner-words" style="{{ $isRemboursement ? 'border-left-color: #b91c1c;' : '' }}">
        {{ $isRemboursement ? 'Arrêté le présent décaissement à la somme de :' : 'Arrêtée la présente quittance de paiement à la somme de :' }} <strong>{{ ucfirst($montantLettres) }}</strong>.
    </div>

    <!-- 4. Tableau des Modalités du Règlement / Décaissement -->
    <div class="section-title">{{ $isRemboursement ? 'Modalités du Décaissement / Remboursement' : 'Modalités de l\'Encaissement' }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Mode de {{ $isRemboursement ? 'Décaissement' : 'Paiement' }}</th>
                <th style="width: 25%;">Date {{ $isRemboursement ? 'Décaissement' : 'Encaissement' }}</th>
                <th style="width: 30%;">Référence Transaction / Pièce</th>
                <th style="width: 20%;" class="text-end">Montant {{ $isRemboursement ? 'Décaissé' : 'Versé' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge-mode">{{ $reglement->mode }}</span></td>
                <td><strong>{{ \Carbon\Carbon::parse($reglement->date)->format('d/m/Y') }}</strong></td>
                <td class="font-mono text-muted">{{ $reglement->reference_paiement ?: ($isRemboursement ? 'Décaissement caisse' : 'Paiement direct') }}</td>
                <td class="text-end font-mono fw-bold" style="color: {{ $isRemboursement ? '#b91c1c' : '#047857' }}; font-size: 10pt;">
                    {{ number_format($montantAbsolu, 0, ',', ' ') }} FCFA
                </td>
            </tr>
            @if(!empty($reglement->observation))
            <tr>
                <td colspan="4" class="text-muted" style="font-size: 8pt; font-style: italic;">
                    <strong>Observation :</strong> {{ $reglement->observation }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- 5. Situation Comptable de la Facture après ce Règlement -->
    @if($facture)
        @php
            $totFacture = (float) $facture->montant_total;
            $totPaye    = (float) $facture->montant_paye;
            $resteDu    = (float) $facture->reste_a_payer;
            $isSoldee   = $resteDu <= 0.009;
        @endphp
        <div class="section-title">Situation de la Facture après {{ $isRemboursement ? 'Remboursement' : 'Règlement' }}</div>
        <div class="kpi-grid">
            <div class="kpi-cell">
                <div class="lbl">Total Facturé TTC</div>
                <div class="val">{{ number_format($totFacture, 0, ',', ' ') }} F</div>
            </div>
            <div class="kpi-cell success">
                <div class="lbl">Cumul Net Encaissé</div>
                <div class="val">{{ number_format($totPaye, 0, ',', ' ') }} F</div>
            </div>
            <div class="kpi-cell {{ $isSoldee ? 'success' : 'danger' }}">
                <div class="lbl">Reste Dû</div>
                <div class="val">{{ number_format($resteDu, 0, ',', ' ') }} F</div>
            </div>
            <div class="kpi-cell">
                <div class="lbl">Statut Facture</div>
                <div class="val" style="font-size: 9pt; color: {{ $isSoldee ? '#047857' : '#d97706' }};">
                    {{ $isSoldee ? '● SOLDÉE' : '● PARTIELLEMENT PAYÉE' }}
                </div>
            </div>
        </div>
    @endif

    <!-- 6. Blocs de Signature -->
    <div class="signatures-row">
        <div class="sig-box">
            <div class="sig-box-title">{{ $isRemboursement ? 'Le Bénéficiaire (Client)' : 'Le Client / Déposant' }}</div>
            <div class="sig-box-line">Nom, Date & Signature</div>
        </div>
        <div class="sig-box">
            <div class="sig-box-title">{{ $isRemboursement ? 'Le Caissier / Payeur' : 'Pour ' . ($entete->denomination ?? 'La Direction') }}</div>
            <div class="sig-box-line">Cachet & Signature du Caissier</div>
        </div>
    </div>

    <!-- 7. Pied de page légal -->
    <footer class="receipt-footer">
        <div>{{ $entete->denomination ?? config('app.name') }} · Document de quittance certifié conforme pour justification de trésorerie</div>
        @if(!empty($entete->comptes_bancaires))
            <div>Comptes bancaires : {{ $entete->comptes_bancaires }}</div>
        @endif
    </footer>
</body>

</html>
