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
        $typeDoc = strtoupper(optional($facture->Typefacture)->code ?? 'FV');
        $isAvoir = in_array($typeDoc, ['FA', 'EA'], true);
        $titreDoc = match ($typeDoc) {
            'EA' => "FACTURE D'AVOIR À L'EXPORTATION",
            'FA' => "FACTURE D'AVOIR",
            'ET' => "FACTURE D'ACOMPTE À L'EXPORTATION",
            'EV' => "FACTURE DE VENTE À L'EXPORTATION",
            'FT' => "FACTURE D'ACOMPTE OU D'AVANCE",
            default => 'FACTURE DE VENTE',
        };
        $currentModePrix = $mode_prix ?? 'HT';
        if (!empty($facture->notes) && str_starts_with(trim($facture->notes), '{')) {
            $notesArr = json_decode($facture->notes, true);
            if (!empty($notesArr['mode_prix'])) {
                $currentModePrix = strtoupper($notesArr['mode_prix']);
            }
        }
        $isHtMode = ($currentModePrix === 'HT');
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
            @else
                {{ $titreDoc }}
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
        <p><strong>Mode(s) convenu(s) :</strong>
            @php
                $modesTicket = [];
                $rawNotesTicket = (!empty($facture->notes) && str_starts_with(trim($facture->notes), '{')) ? (json_decode($facture->notes, true) ?: []) : [];
                if (!empty($rawNotesTicket['modes_paiement_convenus']) && is_array($rawNotesTicket['modes_paiement_convenus'])) {
                    foreach ($rawNotesTicket['modes_paiement_convenus'] as $mc) {
                        $modesTicket[] = ($mc['libelle'] ?? $mc['mode']) . ' (' . number_format($mc['montant'], 0, ',', ' ') . ' F)';
                    }
                } elseif ($facture->Reglements && $facture->Reglements->isNotEmpty()) {
                    foreach ($facture->Reglements as $r) {
                        $modesTicket[] = ($r->mode ?: 'Paiement') . ' (' . number_format($r->montant, 0, ',', ' ') . ' F)';
                    }
                    if ($facture->reste_a_payer > 0.009) {
                        $modesTicket[] = 'Crédit (' . number_format($facture->reste_a_payer, 0, ',', ' ') . ' F)';
                    }
                } elseif ($facture->reste_a_payer > 0.009) {
                    $modesTicket[] = (optional($facture->ModePaie)->libelle ?: 'Espèces') . ' à terme (' . number_format($facture->reste_a_payer, 0, ',', ' ') . ' F)';
                } else {
                    $modesTicket[] = optional($facture->ModePaie)->libelle ?: 'Espèces';
                }
            @endphp
            {{ implode(', ', $modesTicket) }}
        </p>
        @if($isAvoir && !empty($facture->reference))
            <p style="color: #c00;"><strong>Réf. Initiale :</strong> {{ $facture->reference }}</p>
        @endif
    </div>

    <div class="dashed-line"></div>

    @php
        $formatMonnaie = function($val) {
            $val = (float) $val;
            $hasDecimals = abs($val - round($val)) > 0.001;
            return number_format($val, $hasDecimals ? 2 : 0, ',', ' ');
        };
        $formatQte = function($qte) {
            $qte = (float) $qte;
            if ($qte == (int)$qte) {
                return number_format($qte, 0, ',', ' ');
            }
            return rtrim(rtrim(number_format($qte, 3, ',', ' '), '0'), ',');
        };
    @endphp

    <!-- Articles -->
    <table>
        <thead>
            <tr>
                <th class="text-start" style="width: 42%;">Désignation</th>
                <th class="text-center" style="width: 12%;">Qté</th>
                <th class="text-end" style="width: 23%;">P.U. {{ $isHtMode ? 'HT' : 'TTC' }}</th>
                <th class="text-end" style="width: 23%;">Total {{ $isHtMode ? 'HT' : 'TTC' }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($elements as $el)
                @php
                    $codeProd = strtoupper((string) optional($el->Produit)->code);
                    if ($codeProd === 'TRANS') continue;
                    $qte = (float) $el->quantite;
                    $pu  = (float) $el->prix_unitaire;
                    $ligneBrut = $qte * $pu;
                    $remiseLigne = (float) ($el->calculated_remise ?? 0);
                    $ligneNet = max(0, $ligneBrut - $remiseLigne);
                    $tauxTva = (float) optional(optional($el->Produit)->tva)->taux ?? 0;
                    $grpTva = $tauxTva > 0 ? 'B' : 'A';
                    $tsUnit = 0.0;
                    if ($el->Produit) {
                        if ($el->Produit->type_ts === 'POURCENTAGE' && (float) ($el->Produit->pourcentage_ts ?? 0) > 0) {
                            $tsUnit = round($pu * ((float) $el->Produit->pourcentage_ts / 100.0), 2);
                        } else {
                            $tsUnit = (float) ($el->Produit->montant_ts ?? 0);
                        }
                    }
                    $tsLigne = round($tsUnit * $qte, 2);
                    $totalLigneHt = round($ligneNet + $tsLigne, 2);

                    if ($isHtMode) {
                        $puDisplay = $pu;
                        $remiseDisplay = $remiseLigne;
                        $ligneTotalDisplay = $totalLigneHt;
                    } else {
                        $puDisplay = round(($pu + $tsUnit) * (1 + $tauxTva / 100.0), 2);
                        $remiseDisplay = round($remiseLigne * (1 + $tauxTva / 100.0), 2);
                        $ligneTotalDisplay = round($totalLigneHt * (1 + $tauxTva / 100.0), 2);
                    }
                @endphp
                <tr>
                    <td class="text-start">
                        <div class="fw-bold">{{ $el->designation ?? optional($el->Produit)->designation }}</div>
                        <div style="font-size: 8.5px; color: #444;">
                            [{{ $grpTva }} - {{ $tauxTva > 0 ? $tauxTva.'%' : 'EX' }}]
                            @if($tsUnit > 0) +TS {{ $formatMonnaie($tsLigne) }}@endif
                            @if($remiseLigne > 0)
                                <span style="color: #dc2626;">(Rem: -{{ $formatMonnaie($remiseDisplay) }}@if($el->remise_type === 'POURCENTAGE' && (float)$el->remise_valeur > 0) [{{ rtrim(rtrim(number_format((float)$el->remise_valeur, 2, ',', ' '), '0'), ',') }}%]@endif)</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">{{ $formatQte($qte) }}</td>
                    <td class="text-end">{{ $formatMonnaie($puDisplay) }}</td>
                    <td class="text-end fw-bold">{{ $formatMonnaie($ligneTotalDisplay) }}</td>
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
                    <td class="text-end">{{ $formatMonnaie($info['base']) }}</td>
                </tr>
            @endforeach
            @if($remise_totale > 0)
                <tr>
                    <td class="text-start">Remises commerciales :</td>
                    <td class="text-end">-{{ $formatMonnaie($remise_totale) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-start">Total Brut HT :</td>
                <td class="text-end">{{ $formatMonnaie($total_ht) }}</td>
            </tr>
            @if($total_ts > 0)
                <tr>
                    <td class="text-start">Total Taxe Spécifique :</td>
                    <td class="text-end">{{ $formatMonnaie($total_ts) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-start">Total Base Imposable HT :</td>
                <td class="text-end fw-bold">{{ $formatMonnaie($total_base_imposable_ht ?? ($net_commercial + $total_ts)) }}</td>
            </tr>
            <tr>
                <td class="text-start">Total TVA :</td>
                <td class="text-end fw-bold">{{ $formatMonnaie($total_taxe) }}</td>
            </tr>
            @if($total_bic > 0)
                <tr>
                    <td class="text-start">Total PSVB :</td>
                    <td class="text-end">{{ $formatMonnaie($total_bic) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-start" style="font-size: 8px;">Montant timbre quittance en cas de paiement en espèces :</td>
                <td class="text-end font-mono" style="font-size: 8px;">{{ $formatMonnaie($total_timbre ?? 0) }} FCFA</td>
            </tr>
            @if($montant_transport > 0)
                <tr>
                    <td class="text-start">{{ $transport_label }} :</td>
                    <td class="text-end">{{ $formatMonnaie($montant_transport) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="double-line"></div>

    <!-- Total à Payer -->
    <div style="display: flex; justify-content: space-between; margin: 4px 0;" class="large-total">
        <span>{{ $isAvoir ? 'NET A DÉDUIRE :' : 'TOTAL A PAYER :' }}</span>
        <span>{{ $formatMonnaie($net_a_payer) }} F</span>
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
                @if(!is_array($v) && !in_array($k, ['mode_prix', 'modes_paiement_convenus', 'sfe_error', 'action_type', 'cert_status', 'rrr_designation', 'rrr_sous_type', 'factures_concernees']) && !empty(trim((string)$v)))
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
            <p style="font-size: 8.5px; font-weight: bold; margin: 2px 0; font-family: monospace;">{{ implode('-', str_split(str_replace('-', '', $sigDgi), 4)) }}</p>
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
