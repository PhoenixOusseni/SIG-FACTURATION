@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Détails Dépense</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .badge-soldée     { background:#d1fae5; color:#065f46; }
        .badge-partielle  { background:#fef3c7; color:#92400e; }
        .badge-impayée    { background:#fee2e2; color:#991b1b; }
        .statut-badge     { font-size:.85rem; padding:5px 12px; border-radius:6px; font-weight:500; }
        .badge-mode       { font-size:.85rem; padding:5px 10px; border-radius:6px; background:#e0e7ff; color:#3730a3; }
    </style>
@endsection

@section('content')
    <main>
        <x-page-header icon="credit-card" title="Détails de la dépense"></x-page-header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom:6rem;">

            <!-- Informations Dépense -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Informations générales</span>
                    <div style="display:flex;gap:.5rem;">
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                            data-bs-target="#editDepenseModal{{ $item->id }}"
                            style="background:var(--warning-color);border:none;color:white;">
                            <i class="fa fa-edit"></i>&nbsp; Modifier
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteDepenseModal{{ $item->id }}">
                            <i class="fa fa-trash"></i>&nbsp; Supprimer
                        </button>
                        <a href="{{ route('print_depense', $item->id) }}" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="fa fa-print"></i>&nbsp; Imprimer l'état
                        </a>
                        <a href="{{ route('module_depense.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fa fa-arrow-left"></i>&nbsp; Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label text-muted small">Date</label>
                            <div style="font-weight:600;color:var(--primary-color);font-size:1.1rem;">
                                {{ $item->date ? $item->date->format('d/m/Y') : '—' }}
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small">Motif</label>
                            <div style="font-size:1.1rem;">{{ $item->motif }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Montant</label>
                            <div>
                                <span class="badge bg-info" style="font-size:1rem;">
                                    {{ number_format($item->montant, 2, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Fournisseur</label>
                            <div style="font-size:1.05rem;">
                                {{ $item->fournisseur->assujeti ?? '— Aucun fournisseur associé —' }}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <small class="text-muted">Créé le :</small>
                            <span>{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Dernière modification :</small>
                            <span>{{ $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suivi du paiement -->
            @php
                $montantPaye  = $item->montant_paye;
                $resteAPayer  = $item->reste_a_payer;
                $statutP      = $item->statut_paiement;
                $reglements   = $item->reglementDepenses->sortByDesc('date');
            @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i data-feather="dollar-sign" style="width:16px;height:16px;"></i>&nbsp; Suivi du paiement</span>
                    <div style="display:flex;gap:.5rem;align-items:center;">
                        @if ($statutP === 'soldée')
                            <span class="statut-badge badge-soldée">Soldée</span>
                        @elseif ($statutP === 'partielle')
                            <span class="statut-badge badge-partielle">Partiellement payée</span>
                        @else
                            <span class="statut-badge badge-impayée">Impayée</span>
                        @endif
                        @if ($resteAPayer > 0.009)
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#newReglementDepenseModal">
                                <i class="fa fa-plus"></i>&nbsp; Nouveau règlement
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">

                    <!-- Résumé montants -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card text-center border-0" style="background:#f1f5f9;">
                                <div class="card-body py-3">
                                    <div class="text-muted small mb-1">Montant total</div>
                                    <div class="fw-bold" style="font-size:1.15rem;">
                                        {{ number_format($item->montant, 2, ',', ' ') }} FCFA
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0" style="background:#d1fae5;">
                                <div class="card-body py-3">
                                    <div class="text-muted small mb-1">Déjà réglé</div>
                                    <div class="fw-bold text-success" style="font-size:1.15rem;">
                                        {{ number_format($montantPaye, 2, ',', ' ') }} FCFA
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center border-0" style="background:#fee2e2;">
                                <div class="card-body py-3">
                                    <div class="text-muted small mb-1">Reste à payer</div>
                                    <div class="fw-bold text-danger" style="font-size:1.15rem;">
                                        {{ number_format($resteAPayer, 2, ',', ' ') }} FCFA
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historique des règlements -->
                    @if ($reglements->count() > 0)
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
                                        <th style="width:80px;text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reglements as $r)
                                        <tr>
                                            <td><strong>{{ $r->code }}</strong></td>
                                            <td>{{ $r->date ? $r->date->format('d/m/Y') : '—' }}</td>
                                            <td><span class="badge-mode">{{ $r->mode }}</span></td>
                                            <td class="text-end">
                                                <strong>{{ number_format($r->montant, 2, ',', ' ') }} FCFA</strong>
                                            </td>
                                            <td>{{ $r->reference_paiement ?? '—' }}</td>
                                            <td>{{ optional($r->user)->login ?? '—' }}</td>
                                            <td class="d-flex justify-content-center align-items-center" style="gap:4px;">
                                                <a href="{{ route('print_reglement_depense', $r->id) }}"
                                                    target="_blank"
                                                    class="btn btn-sm btn-success"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                                    title="Imprimer le reçu">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a href="{{ route('supp_reglement_depense', $r->id) }}"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Supprimer ce règlement ?')"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;"
                                                    title="Supprimer">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total réglé :</strong></td>
                                        <td class="text-end">
                                            <strong class="text-success">{{ number_format($montantPaye, 2, ',', ' ') }} FCFA</strong>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Reste à payer :</strong></td>
                                        <td class="text-end">
                                            <strong class="text-danger">{{ number_format($resteAPayer, 2, ',', ' ') }} FCFA</strong>
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if ($resteAPayer > 0.009)
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal"
                                    data-bs-target="#solderDepenseModal">
                                    <i class="fa fa-check-double"></i>&nbsp; Solder la dépense
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-muted">
                            <i data-feather="inbox" style="width:40px;height:40px;opacity:.4;"></i>
                            <p class="mt-2">Aucun règlement enregistré pour cette dépense.</p>
                            @if ($resteAPayer > 0.009)
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#newReglementDepenseModal">
                                    <i class="fa fa-plus"></i>&nbsp; Enregistrer un premier règlement
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- ===================== MODAL : MODIFIER ===================== -->
        <div class="modal fade" id="editDepenseModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Modifier la dépense</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('module_depense.update', [$item->id]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="motif{{ $item->id }}" class="form-label">Motif</label>
                                <input type="text" class="form-control" id="motif{{ $item->id }}" name="motif"
                                    value="{{ $item->motif }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date{{ $item->id }}" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date{{ $item->id }}" name="date"
                                        value="{{ $item->date ? $item->date->format('Y-m-d') : '' }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="montant{{ $item->id }}" class="form-label">Montant (FCFA)</label>
                                    <input type="number" step="0.01" min="0" class="form-control"
                                        id="montant{{ $item->id }}" name="montant" value="{{ $item->montant }}"
                                        required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="fournisseur{{ $item->id }}" class="form-label">Fournisseur</label>
                                <select class="form-control" id="fournisseur{{ $item->id }}" name="fournisseurs_id">
                                    <option value="">-- Aucun --</option>
                                    @foreach ($fournisseurs as $fournisseur)
                                        <option value="{{ $fournisseur->id }}"
                                            {{ $item->fournisseurs_id == $fournisseur->id ? 'selected' : '' }}>
                                            {{ $fournisseur->assujeti }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex justify-content-start">
                                <button type="submit" class="btn btn-1">
                                    <i class="fas fa-save"></i>&nbsp; Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== MODAL : SUPPRIMER ===================== -->
        <div class="modal fade" id="deleteDepenseModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="deleteModalLabel">Confirmation de suppression</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                            aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fa fa-exclamation-triangle text-danger" style="font-size:3rem;"></i>
                        </div>
                        <p class="text-center">
                            Êtes-vous sûr de vouloir supprimer la dépense
                            <strong>{{ $item->motif }}</strong> ?
                        </p>
                        <p class="text-center text-muted">Cette action est irréversible.</p>
                    </div>
                    <div class="m-3">
                        <form action="{{ route('module_depense.destroy', [$item->id]) }}" method="POST"
                            style="display:inline;">
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

        <!-- ===================== MODAL : NOUVEAU RÈGLEMENT ===================== -->
        <div class="modal fade" id="newReglementDepenseModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('reglement_depense.store', $item->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Enregistrer un règlement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                Reste à payer :
                                <strong>{{ number_format($resteAPayer, 2, ',', ' ') }} FCFA</strong>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="small">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="small">Mode <span class="text-danger">*</span></label>
                                    <select name="mode" class="form-select" required>
                                        @foreach (['Espèces', 'Chèque', 'Virement', 'Mobile Money', 'Carte bancaire'] as $m)
                                            <option value="{{ $m }}">{{ $m }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="small">Montant (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" name="montant" class="form-control"
                                        step="0.01" min="0.01" max="{{ $resteAPayer }}"
                                        value="{{ $resteAPayer }}" required>
                                    <small class="text-muted">Max : {{ number_format($resteAPayer, 2, ',', ' ') }}</small>
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
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i>&nbsp; Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ===================== MODAL : SOLDER ===================== -->
        @if ($resteAPayer > 0.009)
            <div class="modal fade" id="solderDepenseModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('solder_depense', $item->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Solder la dépense</h5>
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
                                        <label class="small">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Mode <span class="text-danger">*</span></label>
                                        <select name="mode" class="form-select" required>
                                            @foreach (['Espèces', 'Chèque', 'Virement', 'Mobile Money', 'Carte bancaire'] as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Montant à régler (FCFA) <span class="text-danger">*</span></label>
                                        <input type="number" name="montant" class="form-control"
                                            step="0.01" min="0.01" max="{{ $resteAPayer }}"
                                            value="{{ $resteAPayer }}" required>
                                        <small class="text-muted">Max : {{ number_format($resteAPayer, 2, ',', ' ') }}</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Référence paiement</label>
                                        <input type="text" name="reference_paiement" class="form-control"
                                            placeholder="N° chèque / transaction...">
                                    </div>
                                    <div class="col-12">
                                        <label class="small">Observation</label>
                                        <textarea name="observation" class="form-control" rows="2">Solde de la dépense : {{ $item->motif }}</textarea>
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
        @endif

    </main>
@endsection
