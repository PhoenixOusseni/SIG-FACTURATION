<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Reçu de paiement {{ $reglement->code }}</title>
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
        .footer { margin-top: auto; padding-top: 16px; border-top: 4px solid #1d3f7f; font-size: 8px; color: #777; text-align: center; line-height: 1.6; }
        .footer .d-flex { display: flex; align-items: center; }
        .footer .justify-content-between { justify-content: space-between; }

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

        /* ============ HEADER ============ */
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
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
        }
        .header-grid { display: table; width: 100%; position: relative; z-index: 1; }
        .header-grid > div { display: table-cell; vertical-align: top; }
        .company-name { font-size: 22px; font-weight: 800; letter-spacing: .5px; }
        .company-tagline { font-size: 11.5px; opacity: .9; margin-top: 4px; }
        .company-meta { font-size: 11px; opacity: .9; margin-top: 8px; line-height: 1.7; }

        .doc-block { text-align: right; }
        .doc-block .doc-label {
            display: inline-block;
            background: rgba(255,255,255,0.18);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .doc-block .doc-number { font-size: 22px; font-weight: 800; }
        .doc-block .doc-meta   { font-size: 11.5px; opacity: .92; margin-top: 6px; }

        /* ============ INFO GRID ============ */
        .info-grid {
            display: table;
            width: 100%;
            border-spacing: 14px 0;
            margin: 0 -14px 22px -14px;
            width: calc(100% + 28px);
        }
        .info-cell {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            padding: 16px 18px;
        }
        .info-cell .heading {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6c7a89;
            margin-bottom: 8px;
        }
        .info-cell .name { font-size: 15px; font-weight: 700; color: #1a2942; margin-bottom: 4px; }
        .info-cell .lines { font-size: 11.5px; color: #4a5568; line-height: 1.7; }
        .info-cell .lines strong { color: #1a2942; }

        /* ============ AMOUNT BOX ============ */
        .amount-box {
            background: linear-gradient(135deg, #315192 0%, #1a2942 100%);
            color: #fff;
            padding: 24px 28px;
            border-radius: 12px;
            margin-bottom: 22px;
            display: table;
            width: 100%;
        }
        .amount-box .lbl {
            display: table-cell;
            vertical-align: middle;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: .9;
        }
        .amount-box .val {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: .5px;
        }
        .amount-letters {
            margin-top: -10px;
            margin-bottom: 22px;
            padding: 14px 18px;
            background: #fff8e7;
            border: 1px solid #f3d88a;
            border-left: 4px solid #f39c12;
            border-radius: 0 8px 8px 0;
            font-size: 12px;
            color: #6b4f0f;
        }
        .amount-letters strong { color: #b07c1e; font-style: italic; }

        /* ============ DETAILS TABLE ============ */
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

        table.details {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(49, 81, 146, 0.08);
            margin-bottom: 22px;
        }
        table.details th {
            background: #315192;
            color: #fff;
            font-weight: 600;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 11px 14px;
            text-align: left;
            width: 35%;
        }
        table.details td {
            padding: 11px 14px;
            border-bottom: 1px solid #e5e9ec;
            font-size: 12px;
            background: #fff;
            vertical-align: top;
        }
        table.details tr:nth-child(even) td { background: #fafbfd; }
        table.details tr:last-child td { border-bottom: none; }
        table.details strong { color: #1a2942; }

        .mode-tag {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11.5px;
        }

        /* ============ FACTURE STATUS ============ */
        .status-grid {
            display: table;
            width: 100%;
            border-spacing: 10px 0;
            margin: 0 -10px 22px -10px;
            width: calc(100% + 20px);
        }
        .status-cell {
            display: table-cell;
            vertical-align: middle;
            width: 25%;
            background: #fff;
            border: 1px solid #d5dbdf;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
        }
        .status-cell .lbl {
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #6c7a89;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .status-cell .val {
            font-size: 14px;
            font-weight: 700;
            color: #1a2942;
        }
        .status-cell.paye .val   { color: #047857; }
        .status-cell.reste .val  { color: #b91c1c; }

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

        /* ============ SIGNATURES ============ */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 28px;
            border-spacing: 28px 0;
            margin-left: -28px;
            margin-right: -28px;
            width: calc(100% + 56px);
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

        /* ============ FOOTER ============ */
        .footer-band {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 2px solid #315192;
            font-size: 10px;
            color: #6c7a89;
        }
        .footer-grid { display: table; width: 100%; }
        .footer-grid > div { display: table-cell; vertical-align: top; }
        .footer-grid .left  { width: 60%; }
        .footer-grid .right {
            text-align: right;
            font-style: italic;
            color: #315192;
            font-weight: 600;
        }
        .footer-grid b { color: #1a2942; }

        /* ============ WATERMARK ============ */
        .watermark {
            position: absolute;
            top: 38%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 110px;
            font-weight: 900;
            color: rgba(49, 81, 146, 0.05);
            letter-spacing: 8px;
            pointer-events: none;
            user-select: none;
            z-index: 0;
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

    <div class="invoice">
        <div class="watermark">REÇU</div>

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
                <div class="doc-title">Reçu de règlement fournisseur</div>
                <div class="doc-meta">
                    N° <b>{{ $reglement->code }}</b> &nbsp;·&nbsp;
                    Émis le {{ \Carbon\Carbon::parse($reglement->date)->format('d/m/Y') }}<br>
                    @if ($facture)
                        Facture : <b>{{ $facture->code }}</b>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ INFOS PARTIES ============ --}}
        <div class="info-grid">
            <div class="info-cell">
                <div class="heading">Payé à</div>
                @if ($facture && $facture->Fournisseur)
                    <div class="name">{{ $facture->Fournisseur->assujeti }}</div>
                    <div class="lines">
                        @if (!empty($facture->Fournisseur->ifu))
                            <strong>IFU :</strong> {{ $facture->Fournisseur->ifu }}<br>
                        @endif
                        @if (!empty($facture->Fournisseur->rccm))
                            <strong>RCCM :</strong> {{ $facture->Fournisseur->rccm }}<br>
                        @endif
                        @if (!empty($facture->Fournisseur->adresse))
                            {{ $facture->Fournisseur->adresse }}<br>
                        @endif
                        @if (!empty($facture->Fournisseur->telephone))
                            Tél : {{ $facture->Fournisseur->telephone }}
                        @endif
                    </div>
                @else
                    <div class="lines"><em>Fournisseur non renseigné</em></div>
                @endif
            </div>
            <div class="info-cell">
                <div class="heading">Référence facture fournisseur</div>
                @if ($facture)
                    <div class="name">{{ $facture->code }}</div>
                    <div class="lines">
                        <strong>Date :</strong> {{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}<br>
                        @if (!empty($facture->reference))
                            <strong>Réf. :</strong> {{ $facture->reference }}<br>
                        @endif
                        @if ($facture->Service)
                            <strong>Service :</strong> {{ $facture->Service->libelle }}
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ MONTANT ============ --}}
        <div class="amount-box">
            <div class="lbl">Montant payé</div>
            <div class="val">{{ number_format($reglement->montant, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="amount-letters">
            <strong>Arrêté à la somme de :</strong> {{ ucfirst($montantLettres) }}.
        </div>

        {{-- ============ DÉTAILS ============ --}}
        <div class="section-heading">Détails du règlement</div>
        <table class="details">
            <tbody>
                <tr>
                    <th>Mode de paiement</th>
                    <td><span class="mode-tag">{{ $reglement->mode }}</span></td>
                </tr>
                <tr>
                    <th>Référence paiement</th>
                    <td>{{ $reglement->reference_paiement ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Date du règlement</th>
                    <td><strong>{{ \Carbon\Carbon::parse($reglement->date)->format('d/m/Y') }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- ============ ÉTAT DE LA FACTURE ============ --}}
        @if ($facture)
            @php
                $total = $facture->montant_total;
                $paye  = $facture->montant_paye;
                $reste = $facture->reste_a_payer;
                $statutP = $facture->statut_paiement;
            @endphp
            <div class="section-heading">Situation de la facture après règlement</div>
            <div class="status-grid">
                <div class="status-cell">
                    <div class="lbl">Total facture</div>
                    <div class="val">{{ number_format($total, 0, ',', ' ') }}</div>
                </div>
                <div class="status-cell paye">
                    <div class="lbl">Total réglé</div>
                    <div class="val">{{ number_format($paye, 0, ',', ' ') }}</div>
                </div>
                <div class="status-cell reste">
                    <div class="lbl">Reste à payer</div>
                    <div class="val">{{ number_format($reste, 0, ',', ' ') }}</div>
                </div>
                <div class="status-cell">
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
