<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>État des taxes</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm 12mm 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            line-height: 1.4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sheet {
            max-width: 100%;
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
        .header .left, .header .right { display: table-cell; vertical-align: top; }
        .header .left { width: 58%; }
        .header .right { width: 42%; text-align: right; }
        .company-name { font-size: 15px; font-weight: 700; color: #1d3f7f; }
        .company-meta { font-size: 9px; color: #444; margin-top: 3px; line-height: 1.65; }
        .company-meta b { color: #1a2942; }
        .company-meta .sep { color: #c3c9d1; }
        .doc-title { font-size: 18px; font-weight: 800; color: #1d3f7f; letter-spacing: 1px; text-transform: uppercase; }
        .doc-meta { font-size: 9.5px; margin-top: 3px; color: #444; }

        /* ====== Filtres ====== */
        .filters {
            border: 1px solid #cfd8dc;
            background: #f5f7fa;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 10.5px;
            margin-bottom: 10px;
        }
        .filters strong { color: #1a2942; }

        /* ====== Titre ====== */
        .title-wrap {
            display: flex;
            justify-content: center;
            margin: 12px 0 14px;
        }
        .title {
            border: 2px solid #000;
            padding: 8px 60px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: center;
        }

        /* ====== Tableau ====== */
        table.etat {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table.etat thead th {
            background: #d8e6f5;
            color: #1a2942;
            text-align: left;
            font-weight: 700;
            font-size: 11px;
            padding: 8px 6px;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }
        table.etat thead th.right { text-align: right; }
        table.etat thead th.center { text-align: center; }

        table.etat tbody td {
            padding: 6px;
            font-size: 10.5px;
            border-bottom: 1px dashed #888;
            vertical-align: top;
            word-wrap: break-word;
        }
        table.etat tbody td.right { text-align: right; }
        table.etat tbody td.center { text-align: center; }

        table.etat tbody tr:last-child td {
            border-bottom: 1.5px solid #000;
        }

        table.etat tfoot td {
            padding: 8px 6px;
            font-weight: 700;
            font-size: 11px;
            background: #f0f4fa;
            border-bottom: 1.5px solid #000;
        }
        table.etat tfoot td.right { text-align: right; }

        .col-tva { color: #1565c0; font-weight: 600; }
        .col-bic { color: #c2410c; font-weight: 600; }

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

        /* ====== Bouton impression ====== */
        .print-bar {
            text-align: right;
            margin-bottom: 10px;
        }
        .print-bar button {
            background: #315192;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        @media print {
            .print-bar { display: none; }
        }
    </style>
</head>

<body>
    <div class="sheet">

        <div class="print-bar">
            <button onclick="window.print()">Imprimer</button>
        </div>

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
                <div class="doc-title">État des taxes</div>
                <div class="doc-meta">
                    Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>

        {{-- ====== Filtres ====== --}}
        <div class="filters">
            <strong>Critères :</strong>
            TVA : {{ $tvaLibelle ?? 'Toutes' }}
            &nbsp;|&nbsp; PSVBB : {{ $bicLibelle ?? 'Tous' }}
            &nbsp;|&nbsp; Période :
            {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : '—' }}
            au
            {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : '—' }}
            &nbsp;|&nbsp; Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
        </div>

        {{-- ====== Titre ====== --}}
        <div class="title-wrap">
            <div class="title">ÉTAT DES TAXES</div>
        </div>

        {{-- ====== Tableau ====== --}}
        <table class="etat">
            <colgroup>
                <col style="width:9%;">
                <col style="width:13%;">
                <col style="width:11%;">
                <col style="width:25%;">
                <col style="width:14%;">
                <col style="width:14%;">
                <col style="width:14%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Numéro facture</th>
                    <th>N° IFU</th>
                    <th>Nom client</th>
                    <th class="right">Montant TTC</th>
                    <th class="right">Montant TVA</th>
                    <th class="right">Montant PSVBB</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($factures as $facture)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}</td>
                        <td>{{ $facture->code }}</td>
                        <td>{{ optional($facture->Contribuable)->ifu ?? '—' }}</td>
                        <td>{{ optional($facture->Contribuable)->assujeti ?? '—' }}</td>
                        <td class="right">{{ number_format($facture->total_ttc, 0, ',', ' ') }}</td>
                        <td class="right col-tva">{{ number_format($facture->total_tva, 0, ',', ' ') }}</td>
                        <td class="right col-bic">{{ number_format($facture->total_bic, 0, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center; padding:20px; color:#666;">
                            Aucune taxe trouvée pour les critères sélectionnés.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if ($factures->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="4" class="right">TOTAL GÉNÉRAL</td>
                        <td class="right">{{ number_format($totalTtc, 0, ',', ' ') }}</td>
                        <td class="right col-tva">{{ number_format($totalTva, 0, ',', ' ') }}</td>
                        <td class="right col-bic">{{ number_format($totalBic, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

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
