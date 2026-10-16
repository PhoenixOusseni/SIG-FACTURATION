<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Fiche client — {{ $contribuable->assujeti }}</title>
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

        .company-name {
            font-size: 15px;
            font-weight: 700;
            color: #1d3f7f;
        }

        .company-meta {
            font-size: 9px;
            color: #444;
            margin-top: 3px;
            line-height: 1.65;
        }

        .company-meta b {
            color: #1a2942;
        }

        .company-meta .sep {
            color: #c3c9d1;
        }

        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #1d3f7f;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .doc-meta {
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

        /* ============ TOOLBAR ============ */
        .toolbar {
            background: #f8f9fa;
            padding: 10px 20px;
            margin: -14mm -14mm 18px -14mm;
            border-bottom: 1px solid #dee2e6;
            text-align: right;
        }

        .btn {
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 8px;
        }

        .btn-print {
            background: #1e40af;
            color: #fff;
        }

        .btn-back {
            background: #fff;
            color: #1a2942;
            border: 1px solid #d5dbdf;
        }

        /* ============ HEADER ============ */
        .header-band {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: #fff;
            padding: 24px 28px;
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .header-band::after {
            content: '';
            position: absolute;
            right: -50px;
            top: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        .header-grid {
            display: table;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .header-grid>div {
            display: table-cell;
            vertical-align: top;
        }

        .company-name {
            font-size: 20px;
            font-weight: 800;
        }

        .company-tagline {
            font-size: 11px;
            opacity: .9;
            margin-top: 3px;
        }

        .company-meta {
            font-size: 10.5px;
            opacity: .85;
            margin-top: 6px;
            line-height: 1.7;
        }

        .doc-block {
            text-align: right;
        }

        .doc-label {
            display: inline-block;
            background: rgba(255, 255, 255, 0.18);
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .doc-number {
            font-size: 18px;
            font-weight: 800;
        }

        .doc-meta {
            font-size: 11px;
            opacity: .9;
            margin-top: 5px;
        }

        /* ============ IDENTITY BLOCK ============ */
        .identity-block {
            display: table;
            width: calc(100% + 24px);
            border-spacing: 12px 0;
            margin: 0 -12px 18px;
        }

        .identity-cell {
            display: table-cell;
            vertical-align: top;
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            padding: 14px 16px;
        }

        .identity-cell.wide {
            width: 60%;
        }

        .identity-cell.narrow {
            width: 40%;
        }

        .cell-heading {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6c7a89;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e9ec;
        }

        .field-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .field-lbl {
            display: table-cell;
            width: 42%;
            font-size: 10px;
            color: #6c7a89;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .field-val {
            display: table-cell;
            font-size: 11px;
            color: #1a2942;
            font-weight: 500;
        }

        /* ============ STATS BAND ============ */
        .stats-band {
            display: table;
            width: calc(100% + 16px);
            border-spacing: 8px 0;
            margin: 0 -8px 18px;
        }

        .stat-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            border-radius: 10px;
            padding: 12px 8px;
            color: #fff;
        }

        .stat-cell .s-lbl {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            opacity: .9;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-cell .s-val {
            font-size: 15px;
            font-weight: 800;
        }

        .stat-cell .s-sub {
            font-size: 9px;
            opacity: .85;
            margin-top: 2px;
        }

        .bg-slate {
            background: linear-gradient(135deg, #475569, #1e293b);
        }

        .bg-blue {
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        .bg-green {
            background: linear-gradient(135deg, #10b981, #047857);
        }

        .bg-red {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
        }

        /* ============ PROGRESS BAR ============ */
        .progress-wrap {
            margin-bottom: 18px;
        }

        .progress-label {
            display: table;
            width: 100%;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .progress-label>span:last-child {
            float: right;
            color: #6c7a89;
        }

        .progress-track {
            height: 7px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 7px;
            background: linear-gradient(90deg, #10b981, #047857);
            border-radius: 999px;
        }

        /* ============ SECTION HEADING ============ */
        .section-heading {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #1e40af;
            margin: 18px 0 8px;
        }

        .section-heading::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 3px;
            background: #1e40af;
            margin-right: 7px;
            vertical-align: middle;
            border-radius: 2px;
        }

        /* ============ TABLES ============ */
        table.doc-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(30, 64, 175, .08);
            margin-bottom: 14px;
            font-size: 10.5px;
        }

        table.doc-table thead th {
            background: #1e40af;
            color: #fff;
            font-weight: 600;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 8px 10px;
            text-align: left;
        }

        table.doc-table thead th.text-end {
            text-align: right;
        }

        table.doc-table thead th.text-center {
            text-align: center;
        }

        table.doc-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e9ec;
            vertical-align: middle;
            background: #fff;
        }

        table.doc-table tbody tr:nth-child(even) td {
            background: #fafbfd;
        }

        table.doc-table tbody tr:last-child td {
            border-bottom: none;
        }

        table.doc-table tfoot th {
            padding: 8px 10px;
            background: #f1f5f9;
            font-weight: 700;
            font-size: 10px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: 700;
        }

        .text-success {
            color: #047857;
        }

        .text-danger {
            color: #b91c1c;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-soldee {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-partielle {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-impayee {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ============ RECAP TABLE ============ */
        table.recap-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        table.recap-table td {
            padding: 7px 12px;
            border-bottom: 1px solid #e5e9ec;
        }

        table.recap-table .total-row td {
            background: #f1f5f9;
            font-weight: 700;
        }

        table.recap-table .success-row td {
            background: #ecfdf5;
            font-weight: 700;
        }

        table.recap-table .danger-row td {
            background: #fef2f2;
            font-weight: 700;
        }

        /* ============ SIGNATURES ============ */
        .sig-block {
            display: table;
            width: 100%;
            margin-top: 24px;
        }

        .sig-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }

        .sig-title {
            font-size: 10px;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 40px;
        }

        .sig-line {
            border-top: 1.5px solid #1a2942;
            padding-top: 5px;
            font-size: 9.5px;
            color: #6c7a89;
            font-style: italic;
        }

        /* ============ FOOTER ============ */
        .footer-band {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #1e40af;
            font-size: 9.5px;
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

        .footer-right {
            text-align: right;
            font-style: italic;
            color: #1e40af;
            font-weight: 600;
        }

        .footer-grid b {
            color: #1a2942;
        }

        /* ============ WATERMARK ============ */
        .watermark {
            position: fixed;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 100px;
            font-weight: 900;
            color: rgba(30, 64, 175, 0.04);
            letter-spacing: 8px;
            pointer-events: none;
            z-index: 0;
        }

        @media print {
            .toolbar {
                display: none !important;
            }

            body {
                font-size: 11px;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <a href="javascript:history.back()" class="btn btn-back">&#8592; Retour</a>
        <button onclick="window.print()" class="btn btn-print">&#128438; Imprimer / PDF</button>
    </div>

    <div class="watermark">FICHE</div>

    {{-- ============ EN-TÊTE ============ --}}
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
            <div class="doc-title">Fiche contribuable</div>
            <div class="doc-meta">
                {{ $contribuable->assujeti }}<br>
                Type client : {{ optional($contribuable->Categorie)->libelle ?? '—' }}<br>
                Éditée le {{ now()->format('d/m/Y à H:i') }}
            </div>
        </div>
    </div>

    {{-- ============ IDENTITÉ ============ --}}
    <div class="identity-block">
        <div class="identity-cell wide">
            <div class="cell-heading">Coordonnées</div>
            <div class="field-row">
                <span class="field-lbl">Désignation</span>
                <span class="field-val"><strong>{{ $contribuable->assujeti ?? '—' }}</strong></span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Catégorie</span>
                <span class="field-val">{{ optional($contribuable->Categorie)->libelle ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Téléphone</span>
                <span class="field-val">{{ $contribuable->telephone ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Adresse postale</span>
                <span class="field-val">{{ $contribuable->adresse ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Localisation</span>
                <span class="field-val">{{ $contribuable->adresse_localisation ?? '—' }}</span>
            </div>
        </div>
        <div class="identity-cell narrow">
            <div class="cell-heading">Informations fiscales</div>
            <div class="field-row">
                <span class="field-lbl">IFU</span>
                <span class="field-val">{{ $contribuable->ifu ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">RCCM</span>
                <span class="field-val">{{ $contribuable->rccm ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Division fiscale</span>
                <span class="field-val">{{ $contribuable->division_fiscal ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Régime fiscal</span>
                <span class="field-val">{{ $contribuable->regime_fiscal ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Adresse cadastrale</span>
                <span class="field-val">{{ $contribuable->adresse_cadas ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- ============ STATISTIQUES ============ --}}
    <div class="section-heading">Synthèse financière</div>
    <div class="stats-band">
        <div class="stat-cell bg-slate">
            <div class="s-lbl">Factures</div>
            <div class="s-val">{{ $stats['nb_factures'] }}</div>
            <div class="s-sub">{{ $stats['nb_soldees'] }} soldée(s)</div>
        </div>
        <div class="stat-cell bg-blue">
            <div class="s-lbl">Total facturé</div>
            <div class="s-val">{{ number_format($stats['montant_total'], 0, ',', ' ') }}</div>
            <div class="s-sub">FCFA (TTC + PSVBB)</div>
        </div>
        <div class="stat-cell bg-green">
            <div class="s-lbl">Total encaissé</div>
            <div class="s-val">{{ number_format($stats['montant_paye'], 0, ',', ' ') }}</div>
            <div class="s-sub">{{ $reglements->count() }} règlement(s)</div>
        </div>
        <div class="stat-cell {{ $stats['reste_a_payer'] > 0.009 ? 'bg-red' : 'bg-green' }}">
            <div class="s-lbl">Reste à solder</div>
            <div class="s-val">{{ number_format($stats['reste_a_payer'], 0, ',', ' ') }}</div>
            <div class="s-sub">FCFA</div>
        </div>
    </div>

    {{-- Taux de recouvrement --}}
    @if ($stats['montant_total'] > 0)
        @php $pct = min(100, round($stats['montant_paye'] * 100 / $stats['montant_total'], 1)); @endphp
        <div class="progress-wrap">
            <div class="progress-label">
                <span><strong>Taux de recouvrement</strong></span>
                <span>{{ $pct }} %</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width:{{ $pct }}%"></div>
            </div>
        </div>
    @endif

    {{-- ============ FACTURES ============ --}}
    <div class="section-heading">Historique des factures ({{ $stats['nb_factures'] }})</div>
    @if ($factures->isEmpty())
        <p style="color:#6c7a89; font-style:italic; margin-bottom:12px;">Aucune facture enregistrée.</p>
    @else
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Service</th>
                    <th class="text-end">Total TTC+PSVBB</th>
                    <th class="text-end">Encaissé</th>
                    <th class="text-end">Reste</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factures as $f)
                    @php
                        $sp = $f->statut_paiement;
                        $bc = match ($sp) {
                            'soldée' => 'badge-soldee',
                            'partielle' => 'badge-partielle',
                            default => 'badge-impayee',
                        };
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $f->code }}</td>
                        <td>{{ $f->date ? \Carbon\Carbon::parse($f->date)->format('d/m/Y') : '—' }}</td>
                        <td>{{ optional($f->Service)->intitule ?? '—' }}</td>
                        <td class="text-end">{{ number_format($f->montant_total, 0, ',', ' ') }}</td>
                        <td class="text-end text-success fw-bold">{{ number_format($f->montant_paye, 0, ',', ' ') }}
                        </td>
                        <td class="text-end text-danger fw-bold">{{ number_format($f->reste_a_payer, 0, ',', ' ') }}
                        </td>
                        <td><span class="badge {{ $bc }}">{{ ucfirst($sp) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th class="text-end">{{ number_format($stats['montant_total'], 0, ',', ' ') }}</th>
                    <th class="text-end text-success">{{ number_format($stats['montant_paye'], 0, ',', ' ') }}</th>
                    <th class="text-end text-danger">{{ number_format($stats['reste_a_payer'], 0, ',', ' ') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ============ RÈGLEMENTS ============ --}}
    <div class="section-heading">Historique des règlements ({{ $reglements->count() }})</div>
    @if ($reglements->isEmpty())
        <p style="color:#6c7a89; font-style:italic; margin-bottom:12px;">Aucun règlement enregistré.</p>
    @else
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Facture</th>
                    <th>Mode</th>
                    <th>Référence</th>
                    <th class="text-end">Montant (FCFA)</th>
                    <th>Enregistré par</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reglements as $r)
                    <tr>
                        <td class="fw-bold">{{ $r->code ?? '—' }}</td>
                        <td>{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('d/m/Y') : '—' }}</td>
                        <td>{{ optional($r->Facture)->code ?? '—' }}</td>
                        <td>{{ $r->mode }}</td>
                        <td>{{ $r->reference_paiement ?? '—' }}</td>
                        <td class="text-end text-success fw-bold">{{ number_format($r->montant, 0, ',', ' ') }}</td>
                        <td>{{ optional($r->User)->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Total encaissé</th>
                    <th class="text-end text-success">{{ number_format($reglements->sum('montant'), 0, ',', ' ') }}
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ============ RÉCAPITULATIF FINAL ============ --}}
    <div class="section-heading">Récapitulatif global</div>
    <table class="recap-table">
        <tbody>
            <tr>
                <td style="width:55%;">Total facturé (TTC + PSVBB)</td>
                <td class="text-end fw-bold">{{ number_format($stats['montant_total'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr class="total-row">
                <td><strong>Nombre de factures</strong></td>
                <td class="text-end fw-bold">{{ $stats['nb_factures'] }} dont {{ $stats['nb_soldees'] }} soldée(s) ·
                    {{ $stats['nb_partielles'] }} partielle(s) · {{ $stats['nb_impayees'] }} impayée(s)</td>
            </tr>
            <tr>
                <td>Total règlements encaissés</td>
                <td class="text-end text-success fw-bold">{{ number_format($stats['montant_paye'], 0, ',', ' ') }}
                    FCFA</td>
            </tr>
            <tr class="success-row">
                <td><strong>Taux de recouvrement</strong></td>
                <td class="text-end text-success fw-bold">
                    @if ($stats['montant_total'] > 0)
                        {{ number_format(($stats['montant_paye'] * 100) / $stats['montant_total'], 1, ',', ' ') }} %
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr class="danger-row">
                <td><strong>Reste à solder</strong></td>
                <td class="text-end text-danger fw-bold" style="font-size:13px;">
                    {{ number_format($stats['reste_a_payer'], 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    {{-- ============ SIGNATURES ============ --}}
    <div class="sig-block">
        <div class="sig-cell">
            <div class="sig-title">Le client</div>
            <div class="sig-line">Nom &amp; signature</div>
        </div>
        <div class="sig-cell">
            <div class="sig-title">Pour {{ $entete->denomination ?? config('app.name') }}</div>
            <div class="sig-line">Cachet &amp; signature</div>
        </div>
    </div>

    {{-- ============ PIED DE PAGE ============ --}}
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

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
