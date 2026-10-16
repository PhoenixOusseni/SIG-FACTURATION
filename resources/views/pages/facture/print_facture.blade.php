<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Facture {{ $facture->code }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #222;
            line-height: 1.35;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ============ HEADER ============ */
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

        .badge {
            display: inline-block;
            font-size: 8px;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 10px;
            margin-top: 4px;
            letter-spacing: .3px;
        }

        .badge-ok {
            background: #e7f7ee;
            color: #1d7a44;
            border: 1px solid #1d7a44;
        }

        .badge-ko {
            background: #fdecea;
            color: #c0392b;
            border: 1px solid #c0392b;
        }

        /* ============ BANDE SECeF (en haut) ============ */
        .secef-strip {
            display: table;
            width: 100%;
            background: #f7f9fc;
            border: 1px solid #dde2eb;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 9px;
        }

        .secef-strip .col {
            display: table-cell;
            vertical-align: middle;
            font-size: 8.5px;
            color: #333;
            line-height: 1.5;
        }

        .secef-strip .col b {
            color: #1a2942;
        }

        .secef-strip .qr-col {
            display: table-cell;
            width: 64px;
            text-align: center;
            vertical-align: middle;
        }

        .secef-strip .qr-col .qr-caption {
            font-size: 6.5px;
            color: #6c7a89;
            text-transform: uppercase;
            letter-spacing: .3px;
            margin-top: 2px;
        }

        .secef-strip.ko {
            background: #fdecea;
            border-color: #f1b3ba;
            text-align: center;
            color: #c0392b;
            font-weight: 700;
            font-size: 9.5px;
            letter-spacing: .5px;
            text-transform: uppercase;
            padding: 7px 10px;
        }

        /* ============ INFOS CLIENT ============ */
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 9px;
            font-size: 9.5px;
            border-top: 1px solid #e3e7ee;
            border-bottom: 1px solid #e3e7ee;
            padding: 7px 0;
        }

        .info-row .col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }

        .info-row .col+.col {
            padding-right: 0;
            padding-left: 12px;
            border-left: 1px solid #e3e7ee;
        }

        .info-row .lbl {
            text-transform: uppercase;
            font-size: 8px;
            color: #888;
            font-weight: 700;
            letter-spacing: .5px;
            margin-bottom: 3px;
        }

        .info-row .name {
            font-weight: 700;
            font-size: 11.5px;
            color: #1d3f7f;
            margin-bottom: 2px;
        }

        .info-row .lines {
            color: #4a5568;
            line-height: 1.6;
        }

        .info-row .lines b {
            color: #1a2942;
        }

        /* ============ TABLEAU ARTICLES ============ */
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.items thead th {
            background: #1d3f7f;
            color: #fff;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: .3px;
            padding: 6px 6px;
            text-align: left;
        }

        table.items thead th.r {
            text-align: right;
        }

        table.items thead th.c {
            text-align: center;
        }

        table.items tbody td {
            padding: 4.5px 6px;
            font-size: 9.5px;
            border-bottom: 1px solid #e7eaee;
            vertical-align: middle;
        }

        table.items tbody td.r {
            text-align: right;
        }

        table.items tbody td.c {
            text-align: center;
        }

        table.items tbody tr:nth-child(even) td {
            background: #fafbfd;
        }

        table.items tbody .ref-cell {
            font-weight: 600;
            color: #1d3f7f;
            font-size: 9px;
        }

        table.items tbody .amount-cell {
            font-weight: 700;
            color: #1a2942;
            white-space: nowrap;
        }

        .tag {
            display: inline-block;
            font-size: 8px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 8px;
            background: #eef2f8;
            color: #1d3f7f;
        }

        .tag-muted {
            background: #f4f4f4;
            color: #9aa3ab;
        }

        /* ============ TOTAUX ============ */
        .totals-wrap {
            display: table;
            width: 100%;
            margin-top: 4px;
        }

        .totals-wrap .left,
        .totals-wrap .right {
            display: table-cell;
            vertical-align: top;
        }

        .totals-wrap .left {
            width: 52%;
            padding-right: 14px;
            font-size: 8.8px;
            color: #444;
        }

        .totals-wrap .right {
            width: 48%;
        }

        .tax-group-title {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .8px;
            font-weight: 700;
            color: #6c7a89;
            margin: 6px 0 3px;
        }

        .tax-group-title:first-child {
            margin-top: 0;
        }

        .tax-line {
            display: table;
            width: 100%;
            padding: 1.5px 0;
        }

        .tax-line .l,
        .tax-line .v {
            display: table-cell;
        }

        .tax-line .v {
            text-align: right;
            font-weight: 600;
            color: #1a2942;
        }

        table.summary {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }

        table.summary td {
            padding: 3.5px 8px;
            border-bottom: 1px solid #eee;
        }

        table.summary td.r {
            text-align: right;
            font-weight: 600;
            color: #1a2942;
        }

        table.summary tr.subtotal td {
            background: #f7f9fc;
            font-weight: 700;
        }

        table.summary tr.total td {
            font-weight: 800;
            font-size: 12px;
            background: #1d3f7f;
            color: #fff;
            border: none;
            padding: 6px 8px;
        }

        /* ============ DIVERS ============ */
        .amount-words {
            margin-top: 10px;
            font-size: 9.5px;
            font-style: italic;
            color: #1a2942;
            border-top: 1px dashed #ccc;
            padding-top: 7px;
        }

        .sign {
            margin-top: 20px;
            display: table;
            width: 100%;
        }

        .sign .blank {
            display: table-cell;
            width: 58%;
        }

        .sign .box {
            display: table-cell;
            width: 42%;
            text-align: center;
            font-size: 9px;
        }

        .sign .box .role {
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 700;
            color: #1d3f7f;
        }

        .sign .box .line {
            border-top: 1px solid #333;
            margin-top: 38px;
            padding-top: 4px;
            font-weight: 700;
            color: #1a2942;
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

        /* ============ TOOLBAR (écran uniquement) ============ */
        .toolbar {
            background: #f8f9fa;
            padding: 10px 16px;
            margin: -12mm -12mm 14px -12mm;
            border-bottom: 1px solid #dee2e6;
            text-align: right;
        }

        .btn {
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 11px;
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

            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body onload="window.print();">

    <div class="toolbar">
        <a class="btn btn-back" href="{{ url('module_ordre_facture/' . $facture->id) }}">← Retour</a>
        <button class="btn btn-print" onclick="window.print()">🖨 Imprimer</button>
    </div>

    @php
        // Certification MCF active (s'il y en a une) + table de correspondance
// taux de TVA → lettre de groupe SECeF (A, B, C...), telle qu'elle a
        // été figée au moment de la certification.
        $certif = $facture->certification_active;
        $groupeParTaux = collect($certif->detail_taxation ?? [])->keyBy(
            fn($g) => number_format((float) $g['taux'], 2, '.', ''),
        );
    @endphp

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
            <div class="doc-title">
                Facture
                @if (!empty($isAcompte)) d'acompte @endif
            </div>
            <div class="doc-meta">
                N° <b>{{ $facture->code }}</b> &nbsp;·&nbsp;
                {{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}
            </div>
            @if (!empty($isExport))
                <div class="badge badge-ok" style="background:#0d6efd;margin-bottom:4px;">EXPORTATION</div>
            @endif
            @if (!empty($isAcompte))
                <div class="badge badge-ok" style="background:#6f42c1;margin-bottom:4px;">D'ACOMPTE</div>
            @endif
            @if ($certif || $facture->dgi_signature)
                <div class="badge badge-ok">✓ Certifiée — {{ $certif->type_facture ?? optional($facture->Typefacture)->code ?? 'FV' }}</div>
            @else
                <div class="badge badge-ko">⚠ Non certifiée</div>
            @endif
        </div>
    </div>

    @if (!empty($requireCertForPrint) && empty($isCertified))
        <div class="secef-strip ko" style="margin-bottom:12px;">
            ⚠ Document non fiscalisé — impression finale interdite (signature automatique active).
            Fiscalisez la facture avant émission.
        </div>
    @endif

    {{-- ============ BANDE SECeF (placée en haut) ============ --}}
    @if ($certif || $facture->dgi_signature)
        <div class="secef-strip">
            <div class="col" style="width:78%;">
                <b>Code SECeF/DGI :</b> {{ $certif->sig ?? $facture->dgi_signature }}<br>
                NIM : <b>{{ $certif->nim ?? $facture->dgi_nim_machine }}</b> &nbsp;·&nbsp;
                Compteurs : <b>{{ $certif ? ($certif->compteur_facture . '/' . $certif->compteur_total . ' ' . $certif->type_facture) : $facture->dgi_compteur }}</b>
                &nbsp;·&nbsp;
                Heure : <b>{{ $certif?->date_certification->format('d/m/Y H:i:s') ?? 'N/A' }}</b> <br>
                <b>ISF :</b> {{ $entete->identifiant_sfe ?? '—' }}<br>
            </div>
            <div class="qr-col">
                <div id="secef-qr"></div>
                {{-- <div class="qr-caption">Vérifier</div> --}}
            </div>
        </div>
        <script src="{{ asset('js/qrcode.min.js') }}"></script>
        <script>
            new QRCode(document.getElementById('secef-qr'), {
                text: @json($certif->qr_content ?? $facture->dgi_qrcode),
                width: 60,
                height: 60,
                correctLevel: QRCode.CorrectLevel.M
            });
        </script>
    @else
        <div class="secef-strip ko">⚠ Facture non certifiée par le MCF</div>
    @endif

    {{-- ============ CLIENT + INFOS FISCALES ============ --}}
    <div class="info-row">
        <div class="col">
            <div class="lbl">Adressée à</div>
            <div class="name">{{ optional($facture->Contribuable)->assujeti }}</div>
            <div class="lines">
                @if (optional($facture->Contribuable)->telephone)
                    <b>Tél :</b> {{ $facture->Contribuable->telephone }}<br>
                @endif
                @if (optional($facture->Contribuable)->ifu)
                    <b>IFU :</b> {{ $facture->Contribuable->ifu }}<br>
                @endif
                @if (optional($facture->Contribuable)->rccm)
                    <b>RCCM :</b> {{ $facture->Contribuable->rccm }}
                @endif
            </div>
        </div>
        <div class="col">
            <div class="lbl">Référence</div>
            <div class="lines">
                <b>Type client :</b> {{ $facture->Contribuable->Categorie->code ?? '—' }} -
                {{ $facture->Contribuable->Categorie->libelle ?? '—' }}<br>
                <b>Référence :</b> {{ $facture->reference ?: '—' }}<br>
                @if (optional($facture->Contribuable)->regime_fiscal)
                    <b>Régime fiscal :</b> {{ $facture->Contribuable->regime_fiscal }}
                @endif
            </div>
        </div>
    </div>

    <div class="info-row">
        <b>Objet : </b> <span>{{ $facture->objet ?? 'N/A' }}</span><br>
    </div>

    {{-- ============ ARTICLES ============ --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:7%;">Réf.</th>
                <th style="width:24%;">Désignation</th>
                <th class="c" style="width:10%;">Type</th>
                <th class="r" style="width:6%;">Qté</th>
                <th class="r" style="width:12%;">P.U.</th>
                <th class="r" style="width:16%;">Montant HT</th>
                <th class="c" style="width:13%;">TVA</th>
                <th class="c" style="width:12%;">PSVBB</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($elements as $element)
                @php $code = strtoupper((string) optional($element->Produit)->code); @endphp
                @if ($code === 'TRANS')
                    @continue
                @endif
                @php
                    $quantite = (float) $element->quantite;
                    $prix = (float) $element->prix_unitaire;
                    $montantHt = $quantite * $prix;
                    $taux = (float) optional(optional($element->Produit)->tva)->taux;
                    $tauxBic = (float) optional($facture->Bic)->taux;
                    $groupeSecef = $groupeParTaux[number_format($taux, 2, '.', '')]['groupe'] ?? null;
                    $typeProduit = optional($element->Produit)->famille_produit ?? '—';
                @endphp
                <tr>
                    <td class="ref-cell">{{ optional($element->Produit)->code }}</td>
                    <td>{{ $element->designation ?? optional($element->Produit)->designation }}</td>
                    <td class="c">{{ $typeProduit }}</td>
                    <td class="r">{{ number_format($quantite, 0, ',', ' ') }}</td>
                    <td class="r">{{ number_format($prix, 0, ',', ' ') }}</td>
                    <td class="r amount-cell">{{ number_format($montantHt, 0, ',', ' ') }}</td>
                    <td class="c">
                        @if ($taux)
                            <span
                                class="tag">{{ $groupeSecef ? $groupeSecef . ' · ' : '' }}{{ rtrim(rtrim(number_format($taux, 2, '.', ''), '0'), '.') }}%</span>
                        @else
                            <span class="tag tag-muted">{{ $groupeSecef ? $groupeSecef . '-EX' : '—' }}</span>
                        @endif
                    </td>
                    <td class="c">
                        @if ($tauxBic)
                            <span
                                class="tag">{{ rtrim(rtrim(number_format($tauxBic, 2, '.', ''), '0'), '.') }}%</span>
                        @else
                            <span class="tag tag-muted">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ============ TOTAUX ============ --}}
    <div class="totals-wrap">
        <div class="left">
            <div class="tax-group-title">Récapitulatif TVA</div>
            @foreach ($tva_groups as $g)
                @php
                    $lettre =
                        $groupeParTaux[number_format($g['taux'], 2, '.', '')]['groupe'] ??
                        rtrim(rtrim(number_format($g['taux'], 2, '', ''), '0'), '.');
                @endphp
                <div class="tax-line">
                    <div class="l">{{ $lettre }} ({{ number_format($g['taux'], 2, ',', ' ') }}%) — base
                        {{ number_format($g['base'], 0, ',', ' ') }}</div>
                    <div class="v">{{ number_format($g['taxe'], 0, ',', ' ') }}</div>
                </div>
            @endforeach

            @if (!empty($bic_groups))
                <div class="tax-group-title">Récapitulatif PSVBB</div>
                @foreach ($bic_groups as $b)
                    <div class="tax-line">
                        <div class="l">{{ number_format($b['taux'], 2, ',', ' ') }}% — base
                            {{ number_format($b['base_ttc'], 0, ',', ' ') }}</div>
                        <div class="v">{{ number_format($b['montant'], 0, ',', ' ') }}</div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="right">
            @php
                $avance = (float) $facture->Reglements()->sum('montant');
                $net_a_payer = max(0, $total_ttc + $total_bic + $montant_transport - $avance);
            @endphp
            <table class="summary">
                <tr>
                    <td>Total brut HT</td>
                    <td class="r">{{ number_format($total_ht, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>Remises</td>
                    <td class="r">− {{ number_format($remise_1 + $remise_2, 0, ',', ' ') }}</td>
                </tr>
                <tr class="subtotal">
                    <td>Net commercial</td>
                    <td class="r">{{ number_format($net_commercial, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td>TVA</td>
                    <td class="r">{{ number_format($total_taxe, 0, ',', ' ') }}</td>
                </tr>
                <tr class="subtotal">
                    <td>Total TTC</td>
                    <td class="r">{{ number_format($total_ttc, 0, ',', ' ') }}</td>
                </tr>
                @if ($total_bic > 0)
                    <tr>
                        <td>PSVBB</td>
                        <td class="r">{{ number_format($total_bic, 0, ',', ' ') }}</td>
                    </tr>
                @endif
                @if ($montant_transport > 0)
                    <tr>
                        <td>{{ $transport_label }}</td>
                        <td class="r">{{ number_format($montant_transport, 0, ',', ' ') }}</td>
                    </tr>
                @endif
                @if ($avance > 0)
                    <tr>
                        <td>Avance perçue</td>
                        <td class="r">− {{ number_format($avance, 0, ',', ' ') }}</td>
                    </tr>
                @endif
                <tr class="total">
                    <td>Net à payer</td>
                    <td class="r" style="color:#fff;">{{ number_format($net_a_payer, 0, ',', ' ') }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- ============ MONTANT EN LETTRES ============ --}}
    <div class="amount-words">
        Arrêtée la présente facture à la somme de :
        <b>{{ ucfirst(conversion((int) round($net_a_payer))) }} francs CFA</b>
    </div>

    {{-- ============ SIGNATURE ============ --}}
    @php
        $signataire = $facture->User;
        $role = $signataire->role ?? '';
        $prenom = $signataire->prenom ?? '';
        $nom = $signataire->nom ?? '';
    @endphp
    <div class="sign" style="margin-top: 28px;">
        <div class="blank"></div>
        <div class="box">
            <div class="role">Operateur</div>
            <br><br><br><br><br><br><br>
            <div class="">{{ trim($prenom . ' ' . $nom) ?: '—' }}</div>
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

</body>

</html>
