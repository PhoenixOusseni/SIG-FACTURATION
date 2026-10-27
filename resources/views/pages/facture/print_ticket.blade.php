<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket Facture {{ $facture->code }}{{ !empty($isDuplicata) ? ' (DUPLICATA)' : '' }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            width: 78mm;
            margin: 0 auto;
            padding: 4mm 2mm;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .dashed-line { border-top: 1px dashed #000; margin: 6px 0; }
        .double-line { border-top: 2px solid #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { padding: 2px 1px; font-size: 10.5px; }
        th { border-bottom: 1px dashed #000; text-transform: uppercase; }
        .title { font-size: 13px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 11px; font-weight: bold; }
        .large-total { font-size: 14px; font-weight: bold; }
        .dgi-box { border: 1.5px solid #000; padding: 4px; margin: 6px 0; font-size: 9.5px; word-break: break-all; }
        .dgi-box-draft { border: 1.5px dashed #666; padding: 6px; margin: 6px 0; font-size: 10px; color: #555; }
        .toolbar {
            background: #f1f5f9;
            padding: 6px 10px;
            margin-bottom: 8px;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        .btn {
            padding: 4px 10px;
            font-size: 11px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print { background: #0f172a; color: #fff; border: 1px solid #0f172a; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        @media print {
            .toolbar { display: none !important; }
            body { width: 78mm; padding: 2mm; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    @if(!request()->has('iframe'))
    <div class="toolbar">
        <a href="{{ url('module_ordre_facture/' . $facture->id) }}" class="btn btn-back">← Retour</a>
        <button onclick="imprimerEtMarquer()" class="btn btn-print">🖨 Imprimer</button>
    </div>
    @endif

    @php
        $certif = $facture->certification_active;
        $sigDgi = $certif->sig ?? $facture->dgi_signature;
        $nimDgi = $certif->nim ?? $facture->dgi_nim_machine;
        $cptDgi = $certif ? ($certif->compteur_facture . '/' . $certif->compteur_total . ' ' . $certif->type_facture) : $facture->dgi_compteur;
        $heureDgi = $certif?->date_certification?->format('d/m/Y H:i:s') ?? ($facture->dgi_signature ? \Carbon\Carbon::parse($facture->date)->format('d/m/Y H:i:s') : null);
        $qrData = $certif->qr_content ?? $facture->dgi_qrcode;
        $isAvoir = in_array(strtoupper(optional($facture->Typefacture)->code ?? 'FV'), ['FA', 'EA'], true);
    @endphp

    <!-- En-tête Entreprise -->
    <div class="text-center">
        <p class="title">{{ $entete->denomination ?? 'ENTREPRISE' }}</p>
        <p style="font-size: 9.5px;">
            IFU : {{ $entete->ifu ?? '—' }} | RCCM : {{ $entete->rccm ?? '—' }}<br>
            Régime : {{ $entete->regime_fiscal ?? '—' }} | CDI : {{ $entete->division_fiscal ?? '—' }}<br>
            @if(!empty($entete->telephone))Tél : {{ $entete->telephone }}<br>@endif
            @if(!empty($entete->infos_cadastrales))Cadastre : {{ $entete->infos_cadastrales }}<br>@endif
            {{ $entete->postale ?? 'Burkina Faso' }}
        </p>
        <div class="dashed-line"></div>
        <p class="subtitle">
            @if($facture->statut === 'BROUILLON')
                PROJET DE FACTURE (BROUILLON)
            @elseif($isAvoir)
                FACTURE D'AVOIR
            @else
                FACTURE DE VENTE
            @endif
        </p>
        @if(!empty($isDuplicata))
            <p style="font-weight: 900; font-size: 13px; color: #dc2626; letter-spacing: 2px; margin: 4px 0; border: 1.5px dashed #dc2626; padding: 2px 4px; display: inline-block;">*** DUPLICATA ***</p>
        @endif
        <div class="dashed-line"></div>
    </div>

    <!-- Client & Métadonnées -->
    <div>
        <p><strong>Client :</strong> {{ optional($facture->Contribuable)->assujeti ?? 'Client Comptoir' }}</p>
        <p><strong>IFU :</strong> {{ optional($facture->Contribuable)->ifu ?? 'Néant' }}</p>
        <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}</p>
        <p><strong>Facture N° :</strong> {{ $facture->code }}</p>
        <p><strong>Opérateur :</strong> {{ optional($facture->User)->prenom }} {{ optional($facture->User)->nom }}</p>
        <p><strong>Mode règlement :</strong> {{ optional($facture->ModePaie)->libelle ?? 'Espèces' }}</p>
        @if($isAvoir && !empty($facture->reference))
            <p style="color: #c00;"><strong>Réf. Initiale :</strong> {{ $facture->reference }}</p>
        @endif
    </div>

    <div class="dashed-line"></div>

    <!-- Articles -->
    <table>
        <thead>
            <tr>
                <th class="text-start" style="width: 42%;">Désignation</th>
                <th class="text-center" style="width: 12%;">Qté</th>
                <th class="text-end" style="width: 23%;">P.U.</th>
                <th class="text-end" style="width: 23%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($elements as $el)
                @php
                    $codeProd = strtoupper((string) optional($el->Produit)->code);
                    if ($codeProd === 'TRANS') continue;
                    $qte = (float) $el->quantite;
                    $pu  = (float) $el->prix_unitaire;
                    $ligneTotal = $qte * $pu;
                    $tauxTva = (float) optional(optional($el->Produit)->tva)->taux ?? 0;
                    $grpTva = $tauxTva > 0 ? 'B' : 'A';
                    $tsUnit = (float) optional($el->Produit)->montant_ts ?? 0;
                @endphp
                <tr>
                    <td class="text-start">
                        <div class="fw-bold">{{ $el->designation ?? optional($el->Produit)->designation }}</div>
                        <div style="font-size: 8.5px; color: #444;">
                            [{{ $grpTva }} - {{ $tauxTva > 0 ? $tauxTva.'%' : 'EX' }}]
                            @if($tsUnit > 0) +TS {{ number_format($tsUnit * $qte, 0, ',', ' ') }}@endif
                        </div>
                    </td>
                    <td class="text-center">{{ (float)$qte == (int)$qte ? number_format($qte, 0, ',', ' ') : rtrim(rtrim(number_format($qte, 3, ',', ' '), '0'), ',') }}</td>
                    <td class="text-end">{{ number_format($pu, 0, ',', ' ') }}</td>
                    <td class="text-end fw-bold">{{ number_format($ligneTotal, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <!-- Totaux Détaillés -->
    <table>
        <tbody>
            @foreach($tva_groups as $grp => $info)
                <tr>
                    <td class="text-start">Base HT [{{ $grp }}] ({{ $info['taux'] }}%) :</td>
                    <td class="text-end">{{ number_format($info['base'], 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            @if($remise_totale > 0)
                <tr>
                    <td class="text-start">Remise commerciale :</td>
                    <td class="text-end">-{{ number_format($remise_totale, 0, ',', ' ') }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-start">Total Brut HT :</td>
                <td class="text-end">{{ number_format($total_ht, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td class="text-start">Total TVA :</td>
                <td class="text-end fw-bold">{{ number_format($total_taxe, 0, ',', ' ') }}</td>
            </tr>
            @if($total_ts > 0)
                <tr>
                    <td class="text-start">Total Taxe Spécifique :</td>
                    <td class="text-end">{{ number_format($total_ts, 0, ',', ' ') }}</td>
                </tr>
            @endif
            @if($total_bic > 0)
                <tr>
                    <td class="text-start">Total PSVB :</td>
                    <td class="text-end">{{ number_format($total_bic, 0, ',', ' ') }}</td>
                </tr>
            @endif
            @if($total_timbre > 0)
                <tr>
                    <td class="text-start">Timbre Quittance (espèces) :</td>
                    <td class="text-end">{{ number_format($total_timbre, 0, ',', ' ') }}</td>
                </tr>
            @endif
            @if($montant_transport > 0)
                <tr>
                    <td class="text-start">{{ $transport_label }} :</td>
                    <td class="text-end">{{ number_format($montant_transport, 0, ',', ' ') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="double-line"></div>

    <!-- Total à Payer -->
    <div style="display: flex; justify-content: space-between; margin: 4px 0;" class="large-total">
        <span>{{ $isAvoir ? 'NET A DÉDUIRE :' : 'TOTAL A PAYER :' }}</span>
        <span>{{ number_format($net_a_payer, 0, ',', ' ') }} F</span>
    </div>

    @php
        $commentaires = [];
        if (!empty($facture->notes)) {
            if (str_starts_with(trim($facture->notes), '{')) {
                $commentaires = json_decode($facture->notes, true) ?: [];
            } else {
                $commentaires['OBS'] = $facture->notes;
            }
        }
    @endphp
    @if(!empty($commentaires))
        <div style="font-size: 8.5px; border-top: 1px dashed #64748b; padding-top: 3px; margin-top: 3px;">
            @foreach($commentaires as $k => $v)
                @if(!empty(trim((string)$v)))
                    <div><strong>{{ $k }} :</strong> {{ $v }}</div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="dashed-line"></div>

    <!-- Encadré DGI SECeF Strict -->
    @if($sigDgi)
        <div class="dgi-box text-center">
            <p class="fw-bold" style="font-size: 10px;">CODE SECeF / DGI</p>
            <p style="font-size: 8.5px; font-weight: bold; margin: 2px 0;">{{ $sigDgi }}</p>
            <p>SECeF NIM : <strong>{{ $nimDgi }}</strong></p>
            <p>Compteurs : <strong>{{ $cptDgi }}</strong></p>
            <p>Date/Heure : <strong>{{ $heureDgi }}</strong></p>
        </div>

        <!-- QR Code -->
        <div class="text-center" style="margin: 6px 0;">
            <div id="ticket-qr" style="display: inline-block;"></div>
        </div>
        <script src="{{ asset('js/qrcode.min.js') }}"></script>
        <script>
            new QRCode(document.getElementById('ticket-qr'), {
                text: @json($qrData ?: "BFSECEF01;{$nimDgi};{$sigDgi};{$entete->ifu};"),
                width: 85,
                height: 85,
                correctLevel: QRCode.CorrectLevel.M
            });
        </script>
    @else
        <div class="dgi-box-draft text-center">
            *** DOCUMENT NON CERTIFIÉ ***<br>
            (Facture provisoire / Non valable pour déduction)
        </div>
    @endif

    <p class="text-center" style="font-size: 9.5px; margin-top: 6px; font-style: italic;">
        Merci de votre confiance et bonne journée !
    </p>

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
