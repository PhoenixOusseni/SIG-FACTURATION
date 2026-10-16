<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Fiche fournisseur — {{ $fournisseur->assujeti }}</title>
    <style>
        @page { size: A4; margin: 14mm 14mm 14mm 14mm; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

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
        .btn-print { background: #315192; color: #fff; }
        .btn-back  { background: #fff; color: #1a2942; border: 1px solid #d5dbdf; }

        /* ============ HEADER ============ */
        .header-band {
            background: linear-gradient(135deg, #315192 0%, #1a2942 100%);
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
            right: -50px; top: -50px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .header-grid { display: table; width: 100%; position: relative; z-index: 1; }
        .header-grid > div { display: table-cell; vertical-align: top; }

        .company-name    { font-size: 20px; font-weight: 800; }
        .company-tagline { font-size: 11px; opacity: .9; margin-top: 3px; }
        .company-meta    { font-size: 10.5px; opacity: .85; margin-top: 6px; line-height: 1.7; }

        .doc-block { text-align: right; }
        .doc-label {
            display: inline-block;
            background: rgba(255,255,255,0.18);
            padding: 4px 11px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .doc-number { font-size: 18px; font-weight: 800; }
        .doc-meta   { font-size: 11px; opacity: .9; margin-top: 5px; }

        /* ============ IDENTITY BLOCK ============ */
        .identity-block {
            display: table;
            width: 100%;
            border-spacing: 12px 0;
            margin: 0 -12px 18px;
            width: calc(100% + 24px);
        }
        .identity-cell {
            display: table-cell;
            vertical-align: top;
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .identity-cell.wide { width: 60%; }
        .identity-cell.narrow { width: 40%; }

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
        .field-row { display: table; width: 100%; margin-bottom: 4px; }
        .field-lbl { display: table-cell; width: 42%; font-size: 10px; color: #6c7a89; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
        .field-val { display: table-cell; font-size: 11px; color: #1a2942; font-weight: 500; }

        /* ============ STATS BAND ============ */
        .stats-band {
            display: table;
            width: 100%;
            border-spacing: 8px 0;
            margin: 0 -8px 18px;
            width: calc(100% + 16px);
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
        .stat-cell .s-val { font-size: 15px; font-weight: 800; }
        .stat-cell .s-sub { font-size: 9px; opacity: .85; margin-top: 2px; }

        .bg-slate  { background: linear-gradient(135deg, #475569, #1e293b); }
        .bg-blue   { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .bg-amber  { background: linear-gradient(135deg, #f59e0b, #b45309); }
        .bg-green  { background: linear-gradient(135deg, #10b981, #047857); }
        .bg-teal   { background: linear-gradient(135deg, #14b8a6, #0f766e); }
        .bg-red    { background: linear-gradient(135deg, #ef4444, #b91c1c); }

        /* ============ SECTION HEADING ============ */
        .section-heading {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #315192;
            margin: 18px 0 8px;
        }
        .section-heading::before {
            content: '';
            display: inline-block;
            width: 20px; height: 3px;
            background: #315192;
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
            box-shadow: 0 1px 6px rgba(49,81,146,.08);
            margin-bottom: 14px;
            font-size: 10.5px;
        }
        table.doc-table thead th {
            background: #315192;
            color: #fff;
            font-weight: 600;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 8px 10px;
            text-align: left;
        }
        table.doc-table thead th.text-end { text-align: right; }
        table.doc-table thead th.text-center { text-align: center; }

        table.doc-table tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e9ec;
            vertical-align: middle;
            background: #fff;
        }
        table.doc-table tbody tr:nth-child(even) td { background: #fafbfd; }
        table.doc-table tbody tr:last-child td { border-bottom: none; }

        table.doc-table tfoot th {
            padding: 8px 10px;
            background: #f1f5f9;
            font-weight: 700;
            font-size: 10px;
        }

        .text-end    { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold     { font-weight: 700; }
        .text-success { color: #047857; }
        .text-danger  { color: #b91c1c; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-soldee    { background: #d1fae5; color: #065f46; }
        .badge-partielle { background: #fef3c7; color: #92400e; }
        .badge-impayee   { background: #fee2e2; color: #991b1b; }

        /* ============ FOOTER ============ */
        .footer-band {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #315192;
            font-size: 9.5px;
            color: #6c7a89;
        }
        .footer-grid { display: table; width: 100%; }
        .footer-grid > div { display: table-cell; vertical-align: top; }
        .footer-right { text-align: right; font-style: italic; color: #315192; font-weight: 600; }
        .footer-grid b { color: #1a2942; }

        /* ============ WATERMARK ============ */
        .watermark {
            position: fixed;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-28deg);
            font-size: 100px;
            font-weight: 900;
            color: rgba(49,81,146,0.04);
            letter-spacing: 8px;
            pointer-events: none;
            z-index: 0;
        }

        @media print {
            .toolbar { display: none !important; }
            body { font-size: 11px; }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a href="javascript:history.back()" class="btn btn-back">← Retour</a>
        <button onclick="window.print()" class="btn btn-print">&#128438; Imprimer</button>
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
            <div class="doc-title">Fiche fournisseur</div>
            <div class="doc-meta">
                {{ $fournisseur->assujeti }}<br>
                Catégorie : {{ optional($fournisseur->Categorie)->libelle ?? '—' }}<br>
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
                <span class="field-val"><strong>{{ $fournisseur->assujeti ?? '—' }}</strong></span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Catégorie</span>
                <span class="field-val">{{ optional($fournisseur->Categorie)->libelle ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Téléphone</span>
                <span class="field-val">{{ $fournisseur->telephone ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Adresse postale</span>
                <span class="field-val">{{ $fournisseur->adresse ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Localisation</span>
                <span class="field-val">{{ $fournisseur->adresse_localisation ?? '—' }}</span>
            </div>
        </div>
        <div class="identity-cell narrow">
            <div class="cell-heading">Informations fiscales</div>
            <div class="field-row">
                <span class="field-lbl">IFU</span>
                <span class="field-val">{{ $fournisseur->ifu ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">RCCM</span>
                <span class="field-val">{{ $fournisseur->rccm ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Division fiscale</span>
                <span class="field-val">{{ $fournisseur->division_fiscal ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Régime fiscal</span>
                <span class="field-val">{{ $fournisseur->regime_fiscal ?? '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-lbl">Adresse cadastrale</span>
                <span class="field-val">{{ $fournisseur->adresse_cadas ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- ============ STATISTIQUES ============ --}}
    <div class="section-heading">Synthèse financière</div>
    <div class="stats-band">
        <div class="stat-cell bg-slate">
            <div class="s-lbl">Factures reçues</div>
            <div class="s-val">{{ $stats['nb_factures'] }}</div>
            <div class="s-sub">facture(s)</div>
        </div>
        <div class="stat-cell bg-blue">
            <div class="s-lbl">Total facturé</div>
            <div class="s-val">{{ number_format($stats['montant_total'], 0, ',', ' ') }}</div>
            <div class="s-sub">FCFA TTC</div>
        </div>
        <div class="stat-cell bg-amber">
            <div class="s-lbl">Total dépenses</div>
            <div class="s-val">{{ number_format($stats['total_depenses'], 0, ',', ' ') }}</div>
            <div class="s-sub">{{ $stats['nb_depenses'] }} opération(s)</div>
        </div>
        <div class="stat-cell bg-green">
            <div class="s-lbl">Engagement total</div>
            <div class="s-val">{{ number_format($stats['montant_total'] + $stats['total_depenses'], 0, ',', ' ') }}</div>
            <div class="s-sub">Factures + Dépenses</div>
        </div>
        <div class="stat-cell bg-teal">
            <div class="s-lbl">Total soldé</div>
            <div class="s-val">{{ number_format($stats['total_solde'], 0, ',', ' ') }}</div>
            <div class="s-sub">FCFA réglés</div>
        </div>
        <div class="stat-cell bg-red">
            <div class="s-lbl">Reste à solder</div>
            <div class="s-val">{{ number_format(max(0, $stats['reste_a_solder']), 0, ',', ' ') }}</div>
            <div class="s-sub">FCFA restants</div>
        </div>
    </div>

    {{-- ============ FACTURES FOURNISSEURS ============ --}}
    <div class="section-heading">Factures fournisseurs ({{ $stats['nb_factures'] }})</div>
    @if ($factures->isEmpty())
        <p style="color:#6c7a89; font-style:italic; margin-bottom:12px;">Aucune facture enregistrée.</p>
    @else
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Dépôt / Service</th>
                    <th class="text-end">Total TTC</th>
                    <th class="text-end">Réglé</th>
                    <th class="text-end">Reste</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($factures as $f)
                    @php
                        $sp = $f->statut_paiement;
                        $bc = match($sp) { 'soldée' => 'badge-soldee', 'partielle' => 'badge-partielle', default => 'badge-impayee' };
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ $f->code }}</td>
                        <td>{{ $f->date ? \Carbon\Carbon::parse($f->date)->format('d/m/Y') : '—' }}</td>
                        <td>{{ optional($f->Service)->libelle ?? '—' }}</td>
                        <td class="text-end">{{ number_format($f->montant_total, 0, ',', ' ') }}</td>
                        <td class="text-end text-success fw-bold">{{ number_format($f->montant_paye, 0, ',', ' ') }}</td>
                        <td class="text-end text-danger fw-bold">{{ number_format($f->reste_a_payer, 0, ',', ' ') }}</td>
                        <td><span class="badge {{ $bc }}">{{ ucfirst($sp) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th class="text-end">{{ number_format($stats['montant_total'], 0, ',', ' ') }}</th>
                    <th class="text-end text-success">{{ number_format($stats['total_regle_factures'], 0, ',', ' ') }}</th>
                    <th class="text-end text-danger">{{ number_format(max(0, $stats['montant_total'] - $stats['total_regle_factures']), 0, ',', ' ') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ============ RÈGLEMENTS FACTURES ============ --}}
    <div class="section-heading">Règlements de factures fournisseurs ({{ $reglementsFournisseur->count() }})</div>
    @if ($reglementsFournisseur->isEmpty())
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
                    <th class="text-end">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reglementsFournisseur as $r)
                    <tr>
                        <td class="fw-bold">{{ $r->code }}</td>
                        <td>{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('d/m/Y') : '—' }}</td>
                        <td>{{ optional($r->FactureFournisseur)->code ?? '—' }}</td>
                        <td>{{ $r->mode }}</td>
                        <td>{{ $r->reference_paiement ?? '—' }}</td>
                        <td class="text-end text-success fw-bold">{{ number_format($r->montant, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Total réglé (factures)</th>
                    <th class="text-end text-success">{{ number_format($stats['total_regle_factures'], 0, ',', ' ') }}</th>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ============ DÉPENSES ============ --}}
    <div class="section-heading">Dépenses ({{ $stats['nb_depenses'] }})</div>
    @if ($depenses->isEmpty())
        <p style="color:#6c7a89; font-style:italic; margin-bottom:12px;">Aucune dépense enregistrée.</p>
    @else
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Motif</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Réglé</th>
                    <th class="text-end">Reste</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($depenses as $d)
                    @php
                        $sp = $d->statut_paiement;
                        $bc = match($sp) { 'soldée' => 'badge-soldee', 'partielle' => 'badge-partielle', default => 'badge-impayee' };
                    @endphp
                    <tr>
                        <td>{{ $d->date ? $d->date->format('d/m/Y') : '—' }}</td>
                        <td>{{ $d->motif }}</td>
                        <td class="text-end">{{ number_format($d->montant, 0, ',', ' ') }}</td>
                        <td class="text-end text-success fw-bold">{{ number_format($d->montant_paye, 0, ',', ' ') }}</td>
                        <td class="text-end text-danger fw-bold">{{ number_format($d->reste_a_payer, 0, ',', ' ') }}</td>
                        <td><span class="badge {{ $bc }}">{{ ucfirst($sp) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-end">Total</th>
                    <th class="text-end">{{ number_format($stats['total_depenses'], 0, ',', ' ') }}</th>
                    <th class="text-end text-success">{{ number_format($stats['total_regle_depenses'], 0, ',', ' ') }}</th>
                    <th class="text-end text-danger">{{ number_format(max(0, $stats['total_depenses'] - $stats['total_regle_depenses']), 0, ',', ' ') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ============ RÈGLEMENTS DÉPENSES ============ --}}
    <div class="section-heading">Règlements de dépenses ({{ $reglementsDepenses->count() }})</div>
    @if ($reglementsDepenses->isEmpty())
        <p style="color:#6c7a89; font-style:italic; margin-bottom:12px;">Aucun règlement de dépense enregistré.</p>
    @else
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Dépense (motif)</th>
                    <th>Mode</th>
                    <th>Référence</th>
                    <th class="text-end">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reglementsDepenses as $rd)
                    <tr>
                        <td>{{ $rd->date ? $rd->date->format('d/m/Y') : '—' }}</td>
                        <td>{{ optional($rd->depense)->motif ?? '—' }}</td>
                        <td>{{ $rd->mode ?? '—' }}</td>
                        <td>{{ $rd->reference_paiement ?? '—' }}</td>
                        <td class="text-end text-success fw-bold">{{ number_format($rd->montant, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Total réglé (dépenses)</th>
                    <th class="text-end text-success">{{ number_format($stats['total_regle_depenses'], 0, ',', ' ') }}</th>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- ============ RÉCAPITULATIF FINAL ============ --}}
    <div class="section-heading">Récapitulatif global</div>
    <table class="doc-table" style="margin-bottom:20px;">
        <tbody>
            <tr>
                <td style="width:55%;">Total factures fournisseurs (TTC)</td>
                <td class="text-end fw-bold">{{ number_format($stats['montant_total'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td>Total dépenses</td>
                <td class="text-end fw-bold">{{ number_format($stats['total_depenses'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td style="background:#f1f5f9;"><strong>Engagement total (factures + dépenses)</strong></td>
                <td class="text-end fw-bold" style="background:#f1f5f9;">{{ number_format($stats['montant_total'] + $stats['total_depenses'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td>Règlements factures</td>
                <td class="text-end text-success fw-bold">{{ number_format($stats['total_regle_factures'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td>Règlements dépenses</td>
                <td class="text-end text-success fw-bold">{{ number_format($stats['total_regle_depenses'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td style="background:#ecfdf5;"><strong>Total soldé</strong></td>
                <td class="text-end text-success fw-bold" style="background:#ecfdf5; font-size:13px;">{{ number_format($stats['total_solde'], 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td style="background:#fef2f2;"><strong>Reste à solder</strong></td>
                <td class="text-end text-danger fw-bold" style="background:#fef2f2; font-size:13px;">{{ number_format(max(0, $stats['reste_a_solder']), 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    {{-- ============ SIGNATURES ============ --}}
    <div style="display:table; width:100%; margin-top:24px;">
        <div style="display:table-cell; width:50%; text-align:center; padding: 0 20px;">
            <div style="font-size:10px; font-weight:700; color:#315192; text-transform:uppercase; letter-spacing:1px; margin-bottom:40px;">Le fournisseur</div>
            <div style="border-top:1.5px solid #1a2942; padding-top:5px; font-size:9.5px; color:#6c7a89; font-style:italic;">Nom &amp; signature</div>
        </div>
        <div style="display:table-cell; width:50%; text-align:center; padding: 0 20px;">
            <div style="font-size:10px; font-weight:700; color:#315192; text-transform:uppercase; letter-spacing:1px; margin-bottom:40px;">Pour {{ $entete->denomination ?? config('app.name') }}</div>
            <div style="border-top:1.5px solid #1a2942; padding-top:5px; font-size:9.5px; color:#6c7a89; font-style:italic;">Cachet &amp; signature</div>
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
        window.addEventListener('DOMContentLoaded', function() {
            window.print();
        });
    </script>

</body>
</html>
