@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Facturation</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1.1rem;
            color: #212529;
            margin-bottom: 15px;
        }

        .section-title {
            border-bottom: 2px solid #315192fa;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .badge-status {
            font-size: 0.9rem;
            padding: 8px 15px;
        }

        .total-amount {
            background: #315192fa;
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-size: 1.3rem;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <main>
        <header class="page-header page-header-dark header-gradient pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="file-text"></i></div>
                                Détails de la facture proforma {{ $facture->code }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Main page content-->

        <div class="container-xl px-4 mt-n10" style="margin-bottom: 8rem;">
            <!-- Boutons d'action -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div></div>
                        <div>
                            <a href="{{ route('module_facture_proforma.index') }}" class="btn btn-light btn-sm">
                                <i data-feather="arrow-left"></i>
                            </a>
                            @if ($facture->statut !== 'transformée' && !$facture->bordereau_livraison_id)
                                <a href="{{ route('bordereau_from_proforma', $facture->id) }}" class="btn btn-info btn-sm"
                                    onclick="return confirm('Transformer cette proforma en bordereau de livraison ?')">
                                    <i data-feather="repeat"></i>&nbsp;Transformer en BL
                                </a>
                            @else
                                @if ($facture->bordereau_livraison_id)
                                    <a href="{{ route('module_bordereau_livraison.show', $facture->bordereau_livraison_id) }}"
                                        class="btn btn-success btn-sm">
                                        <i data-feather="truck"></i> Voir le BL lié
                                    </a>
                                @endif
                            @endif
                            <a href="{{ route('module_facture_proforma.edit', $facture->id) }}" class="btn btn-warning btn-sm">
                                <i data-feather="edit"></i>
                            </a>
                            <a href="{{ url('print_facture_proforma/' . $facture->id) }}" class="btn btn-success btn-sm"
                                target="_blank">
                                <i data-feather="printer"></i>
                            </a>
                            <a href="{{ url('supp_facture_proforma/' . $facture->id) }}" class="btn btn-danger btn-sm"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')">
                                <i data-feather="trash-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations générales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">
                        Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-label">Numéro de facture proforma</div>
                            <div class="info-value">
                                <strong>{{ $facture->code }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Date de la facture</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Créé par</div>
                            <div class="info-value">
                                {{ $facture->User->login ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="info-label">Référence</div>
                            <div class="info-value">{{ $facture->reference }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Dépôt</div>
                            <div class="info-value">
                                @if ($facture->Service)
                                    <strong>{{ $facture->Service->libelle ?? 'N/A' }}</strong>
                                @else
                                    <span class="text-muted">Non défini</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Objet</div>
                            <div class="info-value">
                                @if ($facture->objet)
                                    <strong>{{ $facture->objet }}</strong>
                                @else
                                    <span class="text-muted">Non définie</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Éléments de la facture -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">
                        Éléments de la facture ({{ $elements->count() }} ligne(s))
                    </h5>
                </div>
                <div class="card-body">
                    @if ($elements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>N°</th>
                                        <th>Code</th>
                                        <th>Désignation</th>
                                        <th>Quantité</th>
                                        <th>TVA</th>
                                        <th>Prix unitaire</th>
                                        <th>Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNum = 0; @endphp
                                    @foreach ($elements as $element)
                                        @php
                                            $code = strtoupper((string) optional($element->Produit)->code);
                                        @endphp
                                        @if ($code === 'TRANS')
                                            @continue
                                        @endif
                                        @php $rowNum++; @endphp
                                        <tr>
                                            <td>{{ $rowNum }}</td>
                                            <td>
                                                <strong>{{ $element->Produit->code ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $element->Produit->designation ?? 'N/A' }}</td>
                                            <td>{{ number_format($element->quantite, 0, ',', ' ') }}</td>
                                            <td>{{ number_format($element->Produit->tva->taux ?? 0, 0, ',', ' ') }}%</td>
                                            <td>{{ number_format($element->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                            <td>
                                                <strong>{{ number_format($element->montant ?? $element->quantite * $element->prix_unitaire, 0, ',', ' ') }}
                                                    FCFA</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>MONTANT BRUT :</strong></td>
                                        <td colspan="4">
                                            <div class="total-amount">
                                                {{ number_format($elements->reject(fn($e) => strtoupper((string) optional($e->Produit)->code) === 'TRANS')->sum(fn($e) => (float) $e->quantite * (float) $e->prix_unitaire), 0, ',', ' ') }}
                                                FCFA
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">Aucun élément dans cette facture</p>
                    @endif
                </div>
            </div>

            {{-- Récapitulatif financier avec remise --}}
            @php
                // ----- Calculs alignés sur la page d'impression (print_proforma) -----
                // 1) Total brut HT + recensement des taux uniques TVA / PSVBB
                //    Le produit avec le code "TRANS" est exclu et traité à part.
                $total_ht          = 0.0;
                $montant_transport = 0.0;
                $transport_qte     = 0.0;
                $transport_prix    = 0.0;
                $transport_label   = 'Frais de Transport';
                $tva_groups = []; // [taux => ['taux','base','taxe']]
                $bic_groups = []; // [taux => ['taux','base_ttc','montant']]

                foreach ($elements as $el) {
                    $code = strtoupper((string) optional($el->Produit)->code);
                    if ($code === 'TRANS') {
                        $qte = (float) $el->quantite;
                        $pu  = (float) $el->prix_unitaire;
                        $montant_transport += $qte * $pu;
                        $transport_qte  += $qte;
                        $transport_prix  = $pu;
                        if ($desig = $el->designation ?? optional($el->Produit)->designation) {
                            $transport_label = $desig;
                        }
                        continue;
                    }

                    $montantHt = (float) $el->quantite * (float) $el->prix_unitaire;
                    $total_ht += $montantHt;

                    $tauxTva = (float) optional(optional($el->Produit)->tva)->taux;
                    $keyTva  = (string) $tauxTva;
                    if (!isset($tva_groups[$keyTva])) {
                        $tva_groups[$keyTva] = ['taux' => $tauxTva, 'base' => 0.0, 'taxe' => 0.0];
                    }

                    // Note: BIC not applicable to proforma
                }

                // 2) Net commercial = Total brut HT − Remise 1 − Remise 2
                $remise_1       = (float) ($facture->remise_1 ?? 0);
                $remise_2       = (float) ($facture->remise_2 ?? 0);
                $net_commercial = max(0, $total_ht - $remise_1 - $remise_2);

                // 3) TVA = Net commercial × taux
                foreach ($tva_groups as $k => $g) {
                    $tva_groups[$k]['base'] = $net_commercial;
                    $tva_groups[$k]['taxe'] = $net_commercial * $g['taux'] / 100;
                }
                $total_taxe = array_sum(array_column($tva_groups, 'taxe'));

                // 4) Total TTC
                $total_ttc = $net_commercial + $total_taxe;

                // 5) PSVBB = Total TTC × taux
                foreach ($bic_groups as $k => $g) {
                    $bic_groups[$k]['base_ttc'] = $total_ttc;
                    $bic_groups[$k]['montant']  = $total_ttc * $g['taux'] / 100;
                }
                $total_bic = array_sum(array_column($bic_groups, 'montant'));

                // 6) Net à payer = TTC + PSVBB − Transport (proforma : pas d'avance)
                $netAPayer = max(0, round($total_ttc + $total_bic - $montant_transport, 2));
            @endphp

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">Récapitulatif financier</h5>
                </div>
                <div class="card-body">
                    {{-- Ligne 1 : décomposition HT → Net commercial → TVA → TTC → PSVBB --}}
                    <div class="row mb-3">
                        <div class="col">
                            <div class="info-label">Total brut HT</div>
                            <div class="info-value"><strong>{{ number_format($total_ht, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col">
                            <div class="info-label">Remise 1</div>
                            <div class="info-value text-warning"><strong>− {{ number_format($remise_1, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col">
                            <div class="info-label">Remise 2</div>
                            <div class="info-value text-warning"><strong>− {{ number_format($remise_2, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col">
                            <div class="info-label">Net commercial</div>
                            <div class="info-value"><strong>{{ number_format($net_commercial, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col">
                            <div class="info-label">TVA</div>
                            <div class="info-value"><strong>{{ number_format($total_taxe, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col">
                            <div class="info-label">Total TTC</div>
                            <div class="info-value"><strong>{{ number_format($total_ttc, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col">
                            <div class="info-label">PSVBB</div>
                            <div class="info-value"><strong>{{ number_format($total_bic, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                    </div>

                    {{-- Détail des taux TVA / PSVBB --}}
                    @if (!empty($tva_groups) || !empty($bic_groups))
                        <div class="row mb-3">
                            @if (!empty($tva_groups))
                                <div class="col-md-6">
                                    <div class="info-label mb-1">Récapitulatif TVA</div>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th class="text-end">Base (Net comm.)</th>
                                                <th class="text-end">Taux</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tva_groups as $g)
                                                <tr>
                                                    <td>C{{ rtrim(rtrim(number_format($g['taux'], 2, '', ''), '0'), '.') }}</td>
                                                    <td class="text-end">{{ number_format($g['base'], 0, ',', ' ') }}</td>
                                                    <td class="text-end">{{ number_format($g['taux'], 2, ',', ' ') }} %</td>
                                                    <td class="text-end"><strong>{{ number_format($g['taxe'], 0, ',', ' ') }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            @if (!empty($bic_groups))
                                <div class="col-md-6">
                                    <div class="info-label mb-1">Récapitulatif PSVBB</div>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th class="text-end">Base (Total TTC)</th>
                                                <th class="text-end">Taux</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($bic_groups as $b)
                                                <tr>
                                                    <td>B{{ rtrim(rtrim(number_format($b['taux'], 2, '', ''), '0'), '.') }}</td>
                                                    <td class="text-end">{{ number_format($b['base_ttc'], 0, ',', ' ') }}</td>
                                                    <td class="text-end">{{ number_format($b['taux'], 2, ',', ' ') }} %</td>
                                                    <td class="text-end"><strong>{{ number_format($b['montant'], 0, ',', ' ') }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Montant estimé net --}}
                    <div class="row">
                        @if ($montant_transport > 0)
                            <div class="col-md-4">
                                <div class="info-label">
                                    Frais de transport (TRANS)
                                    <span class="text-muted" style="font-weight:400;font-size:0.8rem;">
                                        ({{ rtrim(rtrim(number_format($transport_qte, 2, ',', ' '), '0'), ',') }}
                                        × {{ number_format($transport_prix, 0, ',', ' ') }})
                                    </span>
                                </div>
                                <div class="info-value text-warning"><strong>− {{ number_format($montant_transport, 0, ',', ' ') }} FCFA</strong></div>
                            </div>
                            <div class="col-md-4 offset-md-4">
                                <div class="info-label">Net à payer (TTC + PSVBB − Transport)</div>
                                <div class="info-value"><strong class="text-primary" style="font-size:1.2rem;">{{ number_format($netAPayer, 0, ',', ' ') }} FCFA</strong></div>
                            </div>
                        @else
                            <div class="col-md-4 offset-md-8">
                                <div class="info-label">Net à payer (TTC + PSVBB)</div>
                                <div class="info-value"><strong class="text-primary" style="font-size:1.2rem;">{{ number_format($netAPayer, 0, ',', ' ') }} FCFA</strong></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations système -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">
                        <i data-feather="clock"></i> Informations système
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Date de création</div>
                            <div class="info-value">
                                <i data-feather="calendar"></i> {{ $facture->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Dernière modification</div>
                            <div class="info-value">
                                <i data-feather="calendar"></i> {{ $facture->updated_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="mailChoiceModal" tabindex="-1" aria-labelledby="mailChoiceModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mailChoiceModalLabel">Envoyer par messagerie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Sélectionnez un ou plusieurs destinataires :</p>
                        <div id="mailRecipientList">
                            <div class="form-check mb-2">
                                <input class="form-check-input mail-recipient" type="checkbox"
                                    value="sylvain.zoungrana@forvismazars.com" id="rec1">
                                <label class="small" for="rec1">sylvain.zoungrana@forvismazars.com</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input mail-recipient" type="checkbox"
                                    value="hamade.ouedraogo@forvismazars.com" id="rec2">
                                <label class="small" for="rec2">hamade.ouedraogo@forvismazars.com</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input mail-recipient" type="checkbox"
                                    value="amidou.ouedraogo@forvismazars.com" id="rec3">
                                <label class="small" for="rec3">amidou.ouedraogo@forvismazars.com</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input mail-recipient" type="checkbox"
                                    value="eric.kinda@forvismazars.com" id="rec4">
                                <label class="small" for="rec4">eric.kinda@forvismazars.com</label>
                            </div>
                        </div>
                        <div id="mailNoSelection" class="text-danger small mt-2" style="display:none;">
                            Veuillez sélectionner au moins un destinataire.
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-start gap-2">
                        <a href="#" id="sendViaGmail" class="btn btn-danger btn-sm" target="_blank"
                            rel="noopener noreferrer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="currentColor" class="me-1">
                                <path
                                    d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.907 1.528-1.148C21.69 2.28 24 3.434 24 5.457z" />
                            </svg> Gmail
                        </a>
                        <a href="#" id="sendViaOutlook" class="btn btn-primary btn-sm" target="_blank"
                            rel="noopener noreferrer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="currentColor" class="me-1">
                                <path
                                    d="M7.88 12.04q0 .45-.11.87-.1.41-.33.74-.22.33-.58.52-.37.2-.87.2t-.85-.2q-.35-.21-.57-.55-.22-.33-.33-.75-.10-.42-.10-.86t.10-.87q.10-.43.34-.76.22-.34.59-.54.36-.2.87-.2t.86.2q.35.21.57.55.22.34.31.77.10.43.10.88zM24 12v9.38q0 .46-.33.8-.33.32-.8.32H7.13q-.46 0-.8-.33-.32-.33-.32-.8V18H1q-.41 0-.7-.3-.3-.29-.3-.7V7q0-.41.3-.7Q.58 6 1 6h6.5V2.55q0-.44.3-.75.3-.3.75-.3h12.9q.44 0 .75.3.3.3.3.75V10.85l1.24.72q.01 0 .01.01.04.02.07.06l-.01-.01q.29.26.29.63zm-22.99-.08 8.42 4.9 3.84-2.24V9.83H1.01v2.09zM8.17 18h5.17l-5.17-3.01V18zm8.22-3.05-5.99 3.49 5.99 3.49V14.95zm5.92-1.97-5.92 3.46v6.98l5.92-3.46V12.98zm-11.84-9-5.84 3.42 5.84 3.42V3.98zM18.1 7.4 12.18 3.98v6.82l5.92-3.4z" />
                            </svg> Outlook
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
                    var checkboxes = document.querySelectorAll('.mail-recipient');
                    var gmailLink = document.getElementById('sendViaGmail');
                    var outlookLink = document.getElementById('sendViaOutlook');
                    var noSelectionMsg = document.getElementById('mailNoSelection');

                    var subject = 'Facture {{ $facture->code }}';
                    var body =
                        'Bonjour,\n\nVeuillez trouver les informations de la facture {{ $facture->code }}.\n\nCordialement.';

                    var encodedSubject = encodeURIComponent(subject);
                    var encodedBody = encodeURIComponent(body);

                    function getSelectedRecipients() {
                        return Array.from(checkboxes)
                            .filter(function(cb) {
                                return cb.checked;
                            })
                            .map(function(cb) {
                                return cb.value;
                            });
                    }

                    function updateMailLinks() {
                        var recipients = getSelectedRecipients();

                        if (recipients.length === 0) {
                            noSelectionMsg.style.display = 'block';
                            gmailLink.href = '#';
                            outlookLink.href = '#';
                            return;
                        }

                        noSelectionMsg.style.display = 'none';

                        // Gmail / Outlook accept comma-separated
                        var encodedRecipients = encodeURIComponent(recipients.join(','));

                        gmailLink.href = 'https://mail.google.com/mail/?view=cm&fs=1&to=' +
                            encodedRecipients + '&su=' + encodedSubject + '&body=' + encodedBody;

                        outlookLink.href = 'https://outlook.office.com/mail/deeplink/compose?to=' +
                            encodedRecipients + '&subject=' + encodedSubject + '&body=' + encodedBody;
                    }

                    // Intercept clicks to block navigation when no recipient is selected
                    [gmailLink, outlookLink].forEach(function(btn) {
                        btn.addEventListener('click', function(e) {
                            if (getSelectedRecipients().length === 0) {
                                e.preventDefault();
                                noSelectionMsg.style.display = 'block';
                            }
                        });
                    });

                    // Reset checkboxes when the modal is closed
                    **
                    *
                    End Patch
