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
                                Détails de la facture {{ $facture->code }}
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
                            <a href="{{ route('module_ordre_facture.index') }}" class="btn btn-outline-secondary btn-sm fw-bold">
                                <i class="fas fa-arrow-left me-1"></i> Fermer / Retour au Hub
                            </a>
                            @if(!$facture->is_fiscal_locked)<a href="{{ route('module_ordre_facture.edit', $facture->id) }}" class="btn btn-warning btn-sm">
                                <i data-feather="edit"></i>
                            </a>@endif
                            <a href="{{ url('print_ordre_facture/' . $facture->id) }}" class="btn btn-success btn-sm"
                                target="_blank" title="Imprimer Format A4 Officiel DGI">
                                <i data-feather="printer"></i> A4 Officiel
                            </a>
                            <a href="{{ route('print_ticket_facture', $facture->id) }}" class="btn btn-primary btn-sm"
                                target="_blank" title="Imprimer Ticket Thermique 80mm">
                                <i data-feather="file-text"></i> Ticket 80mm
                            </a>
                            <a href="{{ url('print_bon_livraison/' . $facture->id) }}" class="btn btn-info btn-sm"
                                target="_blank" title="Imprimer Bon de livraison">
                                <i data-feather="truck"></i> Bon de livraison
                            </a>
                            {{-- Traçabilité fiscale : les factures doivent être conservées et recertifiées --}}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certification électronique MCF (SECeF) -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i data-feather="shield" style="width:16px;height:16px;"></i> Fiscalisation par boîtier physique</span>
                    <a href="{{ route('mcf.index') }}" class="badge bg-primary">Console SFE</a>
                </div>
                <div class="card-body">
                    @php 
                        $certif = $facture->certification_active;
                        $hasValidation = $certif || $facture->dgi_signature;
                    @endphp
                    @if ($hasValidation)
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-label">Code SECeF/DGI</div>
                                <div class="info-value"><code style="font-size:.85rem;">{{ $certif->sig ?? $facture->dgi_signature }}</code></div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">NIM Machine</div>
                                <div class="info-value">{{ $certif->nim ?? $facture->dgi_nim_machine }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Compteurs</div>
                                <div class="info-value">{{ $certif ? ($certif->compteur_facture . ' / ' . $certif->compteur_total) : $facture->dgi_compteur }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Certifiée le</div>
                                <div class="info-value">{{ $certif?->date_certification->format('d/m/Y H:i:s') ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="badge bg-success badge-status">
                                <i class="fas fa-check-circle me-1"></i> Facture certifiée
                            </span>
                            <a href="{{ route('module_facture_avoir.index') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-invoice me-1"></i> Créer un avoir correctif</a>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                Cette facture est un brouillon. La fiscalisation la verrouillera définitivement.
                            </span>
                            <form action="{{ route('mcf.certifier', $facture->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-1">
                                    <i class="fas fa-stamp me-1"></i> Fiscaliser avec le boîtier
                                </button>
                            </form>
                        </div>
                    @endif
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
                            <div class="info-label">Numéro de facture</div>
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
                            <div class="info-label">Type de Facture</div>
                            <div class="info-value">
                                @if ($facture->Typefacture)
                                    <span class="badge bg-primary">{{ $facture->Typefacture->code }}</span>
                                    <strong>{{ $facture->Typefacture->libelle ?? 'N/A' }}</strong>
                                @else
                                    <span class="text-muted">Non défini</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="info-label">Mode de Paiement</div>
                            <div class="info-value">
                                @if ($facture->ModePaie)
                                    <span class="badge bg-info">{{ $facture->ModePaie->code }}</span>
                                    <strong>{{ $facture->ModePaie->libelle ?? 'N/A' }}</strong>
                                @else
                                    <span class="text-muted">Non défini</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">PSVB</div>
                            <div class="info-value">
                                @if ($facture->Bic)
                                    <span class="badge bg-warning">{{ $facture->Bic->code }}</span>
                                    <strong>{{ $facture->Bic->intitule ?? 'N/A' }}</strong>
                                @else
                                    <span class="text-muted">Non défini</span>
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
                                    @foreach ($elements as $index => $element)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
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
                                        <td colspan="3" class="text-end"><strong>TOTAL BRUT :</strong></td>
                                        <td colspan="4">
                                            <div class="total-amount">
                                                {{ number_format($elements->filter(fn($e) => optional($e->Produit)->code !== 'TRANS')->sum(fn($e) => (float) $e->quantite * (float) $e->prix_unitaire), 0, ',', ' ') }}
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

            {{-- ==================== RÈGLEMENTS ==================== --}}
            @php
                $reglements = $facture->Reglements()->orderByDesc('date')->get();
                $montantPaye = (float) ($avance ?? $facture->Reglements()->sum('montant'));
                $resteAPayer = max(0, (float) ($net_a_payer ?? ($total_ttc - $montantPaye)));
                if ($montantPaye <= 0) {
                    $statutPaiement = 'impayée';
                } elseif ($resteAPayer <= 0.009) {
                    $statutPaiement = 'soldée';
                } else {
                    $statutPaiement = 'partielle';
                }
            @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="section-title mb-0">Règlements ({{ $reglements->count() }})</h5>
                    @if ($resteAPayer > 0.009)
                        <div>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addReglementModal">
                                <i class="fa fa-plus"></i>&nbsp; Ajouter un règlement
                            </button>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#solderFactureModal">
                                <i class="fa fa-check-double"></i>&nbsp; Solder la facture
                            </button>
                        </div>
                    @else
                        <span class="badge bg-success">Facture soldée</span>
                    @endif
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
                        @if($total_ts > 0)
                        <div class="col">
                            <div class="info-label">Taxe Spécifique (TS)</div>
                            <div class="info-value text-info"><strong>{{ number_format($total_ts, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        @endif
                        <div class="col">
                            <div class="info-label">Total TTC Brut</div>
                            <div class="info-value"><strong>{{ number_format($total_ttc_brut ?? ($net_commercial + $total_ts + $total_taxe), 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        @if($total_bic > 0)
                        <div class="col">
                            <div class="info-label">PSVB</div>
                            <div class="info-value text-warning"><strong>{{ number_format($total_bic, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        @endif
                        @if($total_timbre > 0)
                        <div class="col">
                            <div class="info-label">Timbre (espèces)</div>
                            <div class="info-value text-secondary"><strong>{{ number_format($total_timbre, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        @endif
                        @if($montant_transport > 0)
                        <div class="col">
                            <div class="info-label">{{ $transport_label ?? 'Transport' }}</div>
                            <div class="info-value"><strong>+ {{ number_format($montant_transport, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        @endif
                    </div>

                    {{-- Détail des taux TVA / PSVBB --}}
                    @if (!empty($tva_groups) || !empty($bic_groups))
                        <div class="row mb-3">
                            @if (!empty($tva_groups))
                                <div class="col-md-6">
                                    <div class="info-label mb-1">Récapitulatif Groupes de Taxation DGI</div>
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Groupe</th>
                                                <th class="text-end">Base HT</th>
                                                <th class="text-end">Taux</th>
                                                <th class="text-end">Montant TVA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tva_groups as $g)
                                                <tr>
                                                    <td><span class="badge bg-primary">Groupe {{ $g['groupe'] ?? 'B' }}</span></td>
                                                    <td class="text-end font-monospace">{{ number_format($g['base'], 0, ',', ' ') }}</td>
                                                    <td class="text-end">{{ number_format($g['taux'], 2, ',', ' ') }} %</td>
                                                    <td class="text-end font-monospace"><strong>{{ number_format($g['taxe'], 0, ',', ' ') }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                            @if (!empty($bic_groups))
                                <div class="col-md-6">
                                    <div class="info-label mb-1">Récapitulatif PSVB</div>
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
                        <div class="alert alert-light border mb-3">
                            <strong class="d-block mb-1 text-primary"><i class="fas fa-comment-alt me-1"></i> Informations complémentaires FEC DGI (Commande 36h) :</strong>
                            <div class="row">
                                @foreach($commentaires as $k => $v)
                                    @if(!empty(trim((string)$v)))
                                        <div class="col-md-6 small">
                                            <span class="text-muted fw-bold">Ligne {{ $k }} :</span> {{ $v }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Ligne 2 : paiements --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-label">{{ $isAvoir ? 'Net à déduire' : 'Total Net à Payer' }}</div>
                            <div class="info-value text-primary fs-5"><strong>{{ number_format($net_a_payer, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Avance (déjà payé)</div>
                            <div class="info-value text-success"><strong>{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Reste à payer</div>
                            <div class="info-value text-danger"><strong>{{ number_format($resteAPayer, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Statut paiement</div>
                            <div class="info-value">
                                @if ($statutPaiement === 'soldée')
                                    <span class="badge bg-success">Soldée</span>
                                @elseif ($statutPaiement === 'partielle')
                                    <span class="badge bg-warning text-dark">Partielle</span>
                                @else
                                    <span class="badge bg-danger">Impayée</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($reglements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Référence</th>
                                        <th>Observation</th>
                                        <th style="width:140px; text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reglements as $r)
                                        <tr>
                                            <td><strong>{{ $r->code }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                                            <td><span class="badge bg-info text-dark">{{ $r->mode }}</span></td>
                                            <td class="text-end"><strong>{{ number_format($r->montant, 0, ',', ' ') }} FCFA</strong></td>
                                            <td>{{ $r->reference_paiement ?? '—' }}</td>
                                            <td>{{ $r->observation ?? '—' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('module_reglement.show', $r->id) }}"
                                                    class="btn btn-sm btn-info" title="Détails"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('print_reglement', $r->id) }}" target="_blank"
                                                    class="btn btn-sm btn-primary" title="Imprimer le reçu"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a href="{{ route('supp_reglement', $r->id) }}"
                                                    class="btn btn-sm btn-danger" title="Supprimer"
                                                    onclick="return confirm('Supprimer ce règlement ?')"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3 mb-0">Aucun règlement enregistré</p>
                    @endif
                </div>
            </div>

            {{-- Modal ajout règlement --}}
            <div class="modal fade" id="addReglementModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('module_reglement.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="facture_id" value="{{ $facture->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Nouveau règlement — Facture {{ $facture->code }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    Reste à payer : <strong>{{ number_format($resteAPayer, 2, ',', ' ') }} FCFA</strong>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="small">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Mode <span class="text-danger">*</span></label>
                                        <select name="mode" class="form-select" required>
                                            @foreach (['Espèces','Chèque','Virement','Mobile Money','Carte bancaire'] as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Montant (FCFA) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0.01" max="{{ $resteAPayer }}"
                                            name="montant" class="form-control" value="{{ $resteAPayer }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Référence paiement</label>
                                        <input type="text" name="reference_paiement" class="form-control" placeholder="N° chèque / transaction...">
                                    </div>
                                    <div class="col-12">
                                        <label class="small">Observation</label>
                                        <textarea name="observation" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="m-3">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal SOLDER la facture --}}
            <div class="modal fade" id="solderFactureModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('solder_facture', $facture->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Solder la facture {{ $facture->code }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-success">
                                    <i class="fa fa-info-circle"></i>
                                    Reste à payer :
                                    <strong>{{ number_format($resteAPayer, 2, ',', ' ') }} FCFA</strong>.
                                    Saisissez le montant à régler (≤ reste).
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small">Date du solde <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Mode de paiement <span class="text-danger">*</span></label>
                                        <select name="mode" class="form-select" required>
                                            @foreach (['Espèces','Chèque','Virement','Mobile Money','Carte bancaire'] as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Montant à régler (FCFA) <span class="text-danger">*</span></label>
                                        <input type="number" name="montant" class="form-control" step="0.01" min="0.01" max="{{ $resteAPayer }}" value="{{ $resteAPayer }}" required>
                                        <small class="text-muted">Max : {{ number_format($resteAPayer, 2, ',', ' ') }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Référence paiement</label>
                                        <input type="text" name="reference_paiement" class="form-control" placeholder="N° chèque / transaction...">
                                    </div>
                                    <div class="col-12">
                                        <label class="small">Observation</label>
                                        <textarea name="observation" class="form-control" rows="2" placeholder="Solde de la facture">Solde de la facture {{ $facture->code }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check-double"></i>&nbsp; Solder
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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

                    checkboxes.forEach(function(checkbox) {
                        checkbox.addEventListener('change', updateMailLinks);
                    });

                    var mailModal = document.getElementById('mailChoiceModal');
                    if (mailModal) {
                        mailModal.addEventListener('hidden.bs.modal', function() {
                            checkboxes.forEach(function(checkbox) {
                                checkbox.checked = false;
                            });
                            updateMailLinks();
                        });
                    }

                    var avoirLink = Array.from(document.querySelectorAll('a')).find(function(link) {
                        return link.textContent.includes('Créer un avoir correctif');
                    });
                    if (avoirLink) {
                        avoirLink.href = '{{ route('module_facture_avoir.index', ['facture_id' => $facture->id]) }}';
                    }

                    updateMailLinks();
                });
    </script>
@endsection
