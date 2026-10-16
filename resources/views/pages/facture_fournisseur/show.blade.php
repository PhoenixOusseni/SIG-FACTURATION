@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Détail facture fournisseur</title>
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

        .total-amount {
            background: #315192fa;
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-size: 1.3rem;
            font-weight: bold;
        }

        .badge-soldée    { background:#d1fae5; color:#065f46; padding:5px 12px; border-radius:6px; font-size:.85rem; }
        .badge-partielle { background:#fef3c7; color:#92400e; padding:5px 12px; border-radius:6px; font-size:.85rem; }
        .badge-impayée   { background:#fee2e2; color:#991b1b; padding:5px 12px; border-radius:6px; font-size:.85rem; }

        .payment-stat { text-align:center; padding:16px; border-radius:10px; border:1px solid #dee2e6; }
        .payment-stat .ps-label { font-size:.75rem; text-transform:uppercase; color:#6c757d; font-weight:600; margin-bottom:6px; }
        .payment-stat .ps-value { font-size:1.2rem; font-weight:700; color:#212529; }
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
                                <div class="page-header-icon"><i data-feather="truck"></i></div>
                                Facture fournisseur {{ $facture->code }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom: 8rem;">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div></div>
                        <div style="display: flex; gap: 0.5rem;">
                            <a href="{{ route('module_facture_fournisseur.index') }}" class="btn btn-light btn-sm">
                                <i data-feather="arrow-left"></i>&thinsp; Retour
                            </a>
                            <a href="{{ route('module_facture_fournisseur.edit', $facture->id) }}"
                                class="btn btn-warning btn-sm">
                                <i data-feather="edit"></i>&thinsp; Modifier
                            </a>
                            <a href="{{ route('print_facture_fournisseur', $facture->id) }}"
                                class="btn btn-success btn-sm" target="_blank">
                                <i data-feather="printer"></i>&thinsp; Imprimer
                            </a>
                            @if ($facture->reste_a_payer > 0.009)
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#newReglementModal">
                                    <i data-feather="dollar-sign"></i>&thinsp; Règlement
                                </button>
                            @endif
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#deleteFactureModal{{ $facture->id }}">
                                <i data-feather="trash-2"></i>&thinsp; Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations générales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-label">Numéro de facture</div>
                            <div class="info-value"><strong>{{ $facture->code }}</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Date</div>
                            <div class="info-value">
                                {{ $facture->date ? \Carbon\Carbon::parse($facture->date)->format('d/m/Y') : 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Statut</div>
                            <div class="info-value">
                                <span class="badge bg-info">{{ $facture->statut }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="info-label">Fournisseur</div>
                            <div class="info-value">
                                <strong>{{ $facture->Fournisseur->assujeti ?? 'N/A' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Référence</div>
                            <div class="info-value">{{ $facture->reference ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Dépôt</div>
                            <div class="info-value">{{ $facture->Service->libelle ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="info-label">Objet</div>
                            <div class="info-value">{{ $facture->objet ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Éléments -->
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
                                        <th class="text-end">Quantité</th>
                                        <th class="text-end">TVA</th>
                                        <th class="text-end">Prix unitaire</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($elements as $index => $element)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $element->Produit->code ?? 'N/A' }}</strong></td>
                                            <td>{{ $element->Produit->designation ?? $element->designation ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                {{ number_format($element->quantite, 2, ',', ' ') }}
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($element->Produit->tva->taux ?? 0, 0, ',', ' ') }}%
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($element->prix_unitaire, 2, ',', ' ') }} FCFA
                                            </td>
                                            <td class="text-end">
                                                <strong>
                                                    {{ number_format($element->montant_total ?? $element->quantite * $element->prix_unitaire, 2, ',', ' ') }}
                                                    FCFA
                                                </strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>MONTANT BRUT (HT) :</strong></td>
                                        <td colspan="3">
                                            <div class="total-amount">
                                                {{ number_format($montant_total, 2, ',', ' ') }} FCFA
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

            <!-- Récapitulatif financier -->
            @php
                $totalTtc = (float) $facture->montant_total;
            @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">Récapitulatif financier</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Montant brut (HT)</div>
                            <div class="info-value">
                                <strong>{{ number_format($montant_total, 2, ',', ' ') }} FCFA</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Total TTC (TVA incluse)</div>
                            <div class="info-value">
                                <strong>{{ number_format($totalTtc, 2, ',', ' ') }} FCFA</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Situation de paiement -->
            @php
                $totalTtcPmt = (float) $facture->montant_total;
                $montantPaye = (float) $facture->montant_paye;
                $resteAPayer = (float) $facture->reste_a_payer;
                $statutPmt   = $facture->statut_paiement;
                $historique  = $facture->ReglementFournisseurs()->with('User')->orderByDesc('date')->orderByDesc('id')->get();
            @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="section-title mb-0">Situation de paiement</h5>
                    @if ($resteAPayer > 0.009)
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newReglementModal">
                            <i class="fa fa-plus"></i>&nbsp; Nouveau règlement
                        </button>
                    @else
                        <span class="badge-soldée">Facture soldée</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="payment-stat">
                                <div class="ps-label">Total TTC</div>
                                <div class="ps-value">{{ number_format($totalTtcPmt, 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-stat">
                                <div class="ps-label">Total payé</div>
                                <div class="ps-value text-success">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-stat">
                                <div class="ps-label">Reste à payer</div>
                                <div class="ps-value text-danger">{{ number_format($resteAPayer, 0, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="payment-stat">
                                <div class="ps-label">Statut</div>
                                <div class="ps-value">
                                    @if ($statutPmt === 'soldée')
                                        <span class="badge-soldée">Soldée</span>
                                    @elseif ($statutPmt === 'partielle')
                                        <span class="badge-partielle">Partielle</span>
                                    @else
                                        <span class="badge-impayée">Impayée</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($historique->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Référence</th>
                                        <th>Enregistré par</th>
                                        <th class="text-center" style="width:110px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historique as $h)
                                        <tr>
                                            <td><strong>{{ $h->code }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($h->date)->format('d/m/Y') }}</td>
                                            <td><span style="background:#e0e7ff;color:#3730a3;padding:4px 10px;border-radius:6px;font-size:.85rem;">{{ $h->mode }}</span></td>
                                            <td class="text-end"><strong>{{ number_format($h->montant, 0, ',', ' ') }} FCFA</strong></td>
                                            <td>{{ $h->reference_paiement ?? '—' }}</td>
                                            <td>{{ optional($h->User)->login ?? '—' }}</td>
                                            <td class="d-flex justify-content-center align-items-center" style="gap:0.25rem;">
                                                <a href="{{ route('module_reglement_fournisseur.show', $h->id) }}"
                                                    class="btn btn-sm btn-info" title="Détails"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('print_reglement_fournisseur', $h->id) }}" target="_blank"
                                                    class="btn btn-sm btn-success" title="Imprimer"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a href="{{ route('supp_reglement_fournisseur', $h->id) }}"
                                                    class="btn btn-sm btn-danger" title="Supprimer"
                                                    onclick="return confirm('Supprimer ce règlement ?')"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total payé :</strong></td>
                                        <td class="text-end"><strong class="text-success">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</strong></td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Aucun règlement enregistré pour cette facture.</p>
                    @endif
                </div>
            </div>

            <!-- Informations système -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">Informations système</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-label">Créé par</div>
                            <div class="info-value">{{ $facture->User->login ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Date de création</div>
                            <div class="info-value">
                                {{ $facture->created_at ? $facture->created_at->format('d/m/Y H:i') : '—' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Dernière modification</div>
                            <div class="info-value">
                                {{ $facture->updated_at ? $facture->updated_at->format('d/m/Y H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal nouveau règlement -->
        @if ($facture->reste_a_payer > 0.009)
            <div class="modal fade" id="newReglementModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('module_reglement_fournisseur.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="facture_fournisseur_id" value="{{ $facture->id }}">
                            <div class="modal-header">
                                <h5 class="modal-title">Nouveau règlement — {{ $facture->code }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="small">Total facture</label>
                                        <input type="text" class="form-control bg-light" readonly
                                            value="{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Déjà payé</label>
                                        <input type="text" class="form-control bg-light" readonly
                                            value="{{ number_format($facture->montant_paye, 0, ',', ' ') }} FCFA">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Reste à payer</label>
                                        <input type="text" class="form-control bg-light" readonly
                                            value="{{ number_format($facture->reste_a_payer, 0, ',', ' ') }} FCFA">
                                    </div>
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
                                        <input type="number" step="0.01" min="0.01" max="{{ $facture->reste_a_payer }}"
                                            name="montant" class="form-control"
                                            value="{{ $facture->reste_a_payer }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Référence paiement</label>
                                        <input type="text" name="reference_paiement" class="form-control"
                                            placeholder="N° chèque / transaction...">
                                    </div>
                                    <div class="col-12">
                                        <label class="small">Observation</label>
                                        <textarea name="observation" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i>&nbsp; Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Modal de suppression -->
        <div class="modal fade" id="deleteFactureModal{{ $facture->id }}" tabindex="-1"
            aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="deleteModalLabel">
                            Confirmation de suppression
                        </h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                            aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fa fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-center">
                            Êtes-vous sûr de vouloir supprimer la facture fournisseur
                            <strong>{{ $facture->code }}</strong> ?
                        </p>
                        <p class="text-center text-muted">
                            Cette action est irréversible et supprimera également tous ses éléments.
                        </p>
                    </div>
                    <div class="m-3">
                        <form action="{{ route('module_facture_fournisseur.destroy', [$facture->id]) }}" method="POST"
                            style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i>&nbsp; Supprimer
                            </button>
                        </form>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>&nbsp; Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
