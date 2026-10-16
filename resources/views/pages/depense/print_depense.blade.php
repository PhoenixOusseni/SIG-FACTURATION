<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>État de dépense — {{ $depense->motif }}</title>
    <style>
        @page { size: A4; margin: 14mm 14mm 14mm 14mm; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #2c3e50;
            background: #fff;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .invoice { max-width: 100%; position: relative; }

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
        .btn-print { background: #315192; color: #fff; }
        .btn-back  { background: #fff; color: #1a2942; border: 1px solid #d5dbdf; }

        /* ============ DEPENSE INFO CARD ============ */
        .depense-card {
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 22px;
        }
        .depense-card .heading {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6c7a89;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        .depense-meta-grid { display: table; width: 100%; }
        .depense-meta-cell {
            display: table-cell;
            vertical-align: top;
            padding-right: 20px;
        }
        .depense-meta-cell:last-child { padding-right: 0; }
        .meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6c7a89; margin-bottom: 4px; }
        .meta-value { font-size: 13px; color: #1a2942; font-weight: 600; }

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

        /* ============ SUMMARY BOXES ============ */
        .summary-grid {
            display: table;
            width: calc(100% + 16px);
            border-spacing: 8px 0;
            margin: 0 -8px 22px -8px;
        }
        .summary-cell {
            display: table-cell;
            vertical-align: middle;
            width: 25%;
            border-radius: 10px;
            padding: 16px 14px;
            text-align: center;
        }
        .summary-cell.total   { background: #f1f5f9; border: 1px solid #d5dbdf; }
        .summary-cell.paye    { background: #d1fae5; border: 1px solid #a7f3d0; }
        .summary-cell.reste   { background: #fee2e2; border: 1px solid #fca5a5; }
        .summary-cell.statut  { background: #fff; border: 1px solid #d5dbdf; }
        .summary-cell .lbl {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6c7a89;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .summary-cell .val { font-size: 15px; font-weight: 800; color: #1a2942; }
        .summary-cell.paye  .val { color: #047857; }
        .summary-cell.reste .val { color: #b91c1c; }

        .badge-statut {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .badge-soldée    { background:#d1fae5; color:#065f46; }
        .badge-partielle { background:#fef3c7; color:#92400e; }
        .badge-impayée   { background:#fee2e2; color:#991b1b; }

        /* ============ REGLEMENTS TABLE ============ */
        table.reglements {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(49,81,146,0.08);
            margin-bottom: 22px;
        }
        table.reglements thead th {
            background: #315192;
            color: #fff;
            font-weight: 600;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 11px 13px;
        }
        table.reglements tbody td {
            padding: 10px 13px;
            border-bottom: 1px solid #e5e9ec;
            font-size: 11.5px;
            background: #fff;
            vertical-align: middle;
        }
        table.reglements tbody tr:nth-child(even) td { background: #fafbfd; }
        table.reglements tbody tr:last-child td { border-bottom: none; }

        table.reglements tfoot td {
            padding: 10px 13px;
            font-size: 12px;
            font-weight: 700;
            background: #f1f5f9;
            border-top: 2px solid #315192;
        }

        .mode-tag {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 11px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #047857; }
        .text-danger  { color: #b91c1c; }

        /* ============ SIGNATURES ============ */
        .signatures {
            display: table;
            width: calc(100% + 56px);
            margin: 28px -28px 0 -28px;
            border-spacing: 28px 0;
        }
        .signatures > div {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            text-align: center;
        }
        .sig-label {
            font-size: 11px;
            font-weight: 700;
            color: #315192;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 50px;
        }
        .sig-line {
            border-top: 1.5px solid #1a2942;
            padding-top: 6px;
            font-size: 10.5px;
            color: #6c7a89;
            font-style: italic;
        }

        /* ============ FOOTER (standard) ============ */
        .footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 4px solid #1d3f7f;
            font-size: 8px;
            color: #777;
            text-align: center;
            line-height: 1.6;
        }
        .footer .d-flex { display: flex; align-items: center; }
        .footer .justify-content-between { justify-content: space-between; }

        /* ============ WATERMARK ============ */
        .watermark {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 90px;
            font-weight: 900;
            color: rgba(49,81,146,0.04);
            letter-spacing: 6px;
            pointer-events: none;
            user-select: none;
            z-index: 0;
        }

        .no-reglements {
            text-align: center;
            padding: 30px;
            color: #6c7a89;
            font-style: italic;
            border: 1px dashed #d5dbdf;
            border-radius: 10px;
            margin-bottom: 22px;
        }

        @media print {
            .toolbar { display: none !important; }
            body { font-size: 11.5px; }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <a href="javascript:history.back()" class="btn btn-back">← Retour</a>
        <button onclick="window.print()" class="btn btn-print">Imprimer</button>
    </div>

    @php
        $montantPaye = $depense->montant_paye;
        $resteAPayer = $depense->reste_a_payer;
        $statutP     = $depense->statut_paiement;
        $reglements  = $depense->reglementDepenses->sortByDesc('date');
    @endphp

    <div class="invoice">
        <div class="watermark">DÉPENSE</div>

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
                <div class="doc-title">Bon de dépense</div>
                <div class="doc-meta">
                    {{ $depense->motif }}<br>
                    Du {{ $depense->date ? $depense->date->format('d/m/Y') : '—' }} &nbsp;·&nbsp;
                    Imprimé le {{ now()->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>

        {{-- ============ INFORMATIONS DE LA DÉPENSE ============ --}}
        <div class="depense-card">
            <div class="heading">Informations de la dépense</div>
            <div class="depense-meta-grid">
                <div class="depense-meta-cell" style="width:40%">
                    <div class="meta-label">Motif</div>
                    <div class="meta-value">{{ $depense->motif }}</div>
                </div>
                <div class="depense-meta-cell" style="width:20%">
                    <div class="meta-label">Date</div>
                    <div class="meta-value">
                        {{ $depense->date ? $depense->date->format('d/m/Y') : '—' }}
                    </div>
                </div>
                <div class="depense-meta-cell" style="width:20%">
                    <div class="meta-label">Montant total</div>
                    <div class="meta-value">{{ number_format($depense->montant, 0, ',', ' ') }} FCFA</div>
                </div>
                <div class="depense-meta-cell" style="width:20%">
                    <div class="meta-label">Fournisseur</div>
                    <div class="meta-value">{{ optional($depense->fournisseur)->assujeti ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- ============ RÉSUMÉ DES PAIEMENTS ============ --}}
        <div class="section-heading">Situation des paiements</div>
        <div class="summary-grid">
            <div class="summary-cell total">
                <div class="lbl">Montant total</div>
                <div class="val">{{ number_format($depense->montant, 0, ',', ' ') }}</div>
            </div>
            <div class="summary-cell paye">
                <div class="lbl">Total réglé</div>
                <div class="val">{{ number_format($montantPaye, 0, ',', ' ') }}</div>
            </div>
            <div class="summary-cell reste">
                <div class="lbl">Reste à payer</div>
                <div class="val">{{ number_format($resteAPayer, 0, ',', ' ') }}</div>
            </div>
            <div class="summary-cell statut">
                <div class="lbl">Statut</div>
                <div class="val">
                    @if ($statutP === 'soldée')
                        <span class="badge-statut badge-soldée">Soldée</span>
                    @elseif ($statutP === 'partielle')
                        <span class="badge-statut badge-partielle">Partielle</span>
                    @else
                        <span class="badge-statut badge-impayée">Impayée</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ HISTORIQUE DES RÈGLEMENTS ============ --}}
        <div class="section-heading">Historique des règlements ({{ $reglements->count() }})</div>

        @if ($reglements->count() > 0)
            <table class="reglements">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th class="text-right">Montant (FCFA)</th>
                        <th>Référence</th>
                        <th>Enregistré par</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reglements as $r)
                        <tr>
                            <td><strong>{{ $r->code }}</strong></td>
                            <td>{{ $r->date ? $r->date->format('d/m/Y') : '—' }}</td>
                            <td><span class="mode-tag">{{ $r->mode }}</span></td>
                            <td class="text-right">
                                <strong>{{ number_format($r->montant, 0, ',', ' ') }}</strong>
                            </td>
                            <td>{{ $r->reference_paiement ?? '—' }}</td>
                            <td>{{ optional($r->user)->login ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-right">Total réglé :</td>
                        <td class="text-right text-success">
                            {{ number_format($montantPaye, 0, ',', ' ') }} FCFA
                        </td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right">Reste à payer :</td>
                        <td class="text-right text-danger">
                            {{ number_format($resteAPayer, 0, ',', ' ') }} FCFA
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        @else
            <div class="no-reglements">Aucun règlement enregistré pour cette dépense.</div>
        @endif

        {{-- ============ SIGNATURES ============ --}}
        <div class="signatures">
            <div>
                <div class="sig-label">Le fournisseur</div>
                <div class="sig-line">Nom & signature</div>
            </div>
            <div>
                <div class="sig-label">Pour {{ $entete->denomination ?? config('app.name') }}</div>
                <div class="sig-line">Cachet & signature</div>
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
