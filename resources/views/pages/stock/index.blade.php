@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Inventaire Stock</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
<main class="py-4">
    <div class="container-xl px-4" style="margin-bottom: 8rem;">

        {{-- En-tête Exécutif Harmonisé (Style Hub Facturation) --}}
        <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 14px; border: 1px solid #e2e8f0;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h2 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">
                                <i class="fas fa-boxes me-2 text-primary"></i>Inventaire du Stock
                            </h2>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <i class="fas fa-warehouse me-1"></i> Contrôle Multidépôt SFE
                            </span>
                        </div>
                        <p class="text-muted mb-0 small">
                            Suivi des mouvements, entrées, sorties, inventaires groupés et ajustements conformes aux exigences DGI.
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEntree">
                            <i class="fas fa-arrow-down me-1"></i> Entrée en stock
                        </button>
                        <button class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSortie">
                            <i class="fas fa-arrow-up me-1"></i> Sortie de stock
                        </button>
                        <button class="btn btn-outline-secondary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjustementGroupe">
                            <i class="fas fa-layer-group me-1"></i> Inventaire groupé
                        </button>
                        <button class="btn btn-outline-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjustement">
                            <i class="fas fa-sliders-h me-1"></i> Ajustement unitaire
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i data-feather="filter" style="width:16px;height:16px;"></i> Filtrer
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('gestion_stock.index') }}">
                    <div class="row gx-3 align-items-end">
                        <div class="col-md-4">
                            <label class="small mb-1">Dépôt</label>
                            <select name="service_id" class="form-select">
                                <option value="">-- Tous les dépôts --</option>
                                @foreach ($services as $s)
                                    <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small mb-1">Rechercher un produit</label>
                            <input type="text" name="search" class="form-control"
                                value="{{ request('search') }}" placeholder="Code ou désignation...">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-1 w-100">
                                <i class="fas fa-search me-1"></i> Filtrer
                            </button>
                            <a href="{{ route('gestion_stock.index') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-times me-1"></i> Réinitialiser
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tableau des stocks --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">
                        Stock par produit / dépôt
                        <span class="badge bg-secondary ms-2">{{ $stocks->total() }}</span>
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Produit</th>
                                <th>Unité</th>
                                <th>Dépôt</th>
                                <th class="text-end">Quantité en stock</th>
                                <th>Dernière mise à jour</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stocks as $stock)
                                @php
                                    $rowClass = '';
                                    if ($stock->quantite <= 0) $rowClass = 'table-danger';
                                    elseif ($stock->quantite < 5) $rowClass = 'table-warning';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td><span class="fw-bold">{{ optional($stock->produit)->code }}</span></td>
                                    <td>{{ optional($stock->produit)->designation }}</td>
                                    <td>{{ optional($stock->produit)->unite }}</td>
                                    <td>{{ optional($stock->service)->libelle }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($stock->quantite, 2, ',', ' ') }}
                                        @if ($stock->quantite <= 0)
                                            <span class="badge bg-danger ms-1">Rupture</span>
                                        @elseif ($stock->quantite < 5)
                                            <span class="badge bg-warning text-dark ms-1">Faible</span>
                                        @endif
                                    </td>
                                    <td>{{ $stock->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Aucun stock enregistré
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $stocks->links() }}
            </div>
        </div>

        {{-- Derniers mouvements --}}
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <i data-feather="activity" style="width:16px;height:16px;"></i>
                Derniers mouvements de stock
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Produit</th>
                                <th>Dépôt</th>
                                <th class="text-center">Type</th>
                                <th class="text-end">Quantité</th>
                                <th>Référence</th>
                                <th>Opérateur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mouvements as $mv)
                                <tr>
                                    <td>{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ optional($mv->produit)->designation }}</td>
                                    <td>{{ optional($mv->service)->libelle }}</td>
                                    <td class="text-center">
                                        @switch($mv->type)
                                            @case('entree')
                                                <span class="badge bg-success">Entrée</span>
                                                @break
                                            @case('sortie')
                                                <span class="badge bg-danger">Sortie</span>
                                                @break
                                            @case('transfert_entree')
                                                <span class="badge bg-info">Transfert ↓</span>
                                                @break
                                            @case('transfert_sortie')
                                                <span class="badge bg-warning text-dark">Transfert ↑</span>
                                                @break
                                            @case('ajustement')
                                                <span class="badge bg-secondary">Ajustement</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="text-end fw-bold
                                        {{ in_array($mv->type, ['entree','transfert_entree']) ? 'text-success' : 'text-danger' }}">
                                        {{ in_array($mv->type, ['entree','transfert_entree']) ? '+' : '-' }}{{ number_format($mv->quantite, 2, ',', ' ') }}
                                    </td>
                                    <td><small class="text-muted">{{ $mv->note }}</small></td>
                                    <td>{{ optional($mv->user)->nom }} {{ optional($mv->user)->prenom }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">Aucun mouvement</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

{{-- Modal Inventaire groupé --}}
<div class="modal fade" id="modalAjustementGroupe" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('gestion_stock.ajustement_groupe') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-layer-group me-2"></i> Inventaire groupé
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">

                    {{-- En-tête commun --}}
                    <div class="row gx-3 mb-4 p-3 rounded" style="background:#f8f9fb;border:1px solid #e5e9ec;">
                        <div class="col-md-5">
                            <label class="small mb-1 fw-bold">Dépôt concerné <span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" required>
                                <option value="">-- Sélectionner le dépôt --</option>
                                @foreach ($services as $s)
                                    <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="small mb-1 fw-bold">Note / Motif</label>
                            <input type="text" name="note" class="form-control"
                                placeholder="Ex : Inventaire physique juin 2026, correction stock...">
                        </div>
                    </div>

                    {{-- Lignes produits --}}
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="tableGroupe">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45%">Produit</th>
                                    <th style="width:20%" class="text-center">Stock actuel</th>
                                    <th style="width:25%">Nouvelle quantité réelle</th>
                                    <th style="width:10%" class="text-center">
                                        <button type="button" id="addGroupeRow"
                                            class="btn btn-success btn-sm"
                                            style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                            <i data-feather="plus" style="width:14px;height:14px;"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="groupeRows">
                                <tr class="groupe-row">
                                    <td>
                                        <select name="produit_id[]" class="form-select select2-groupe" style="width:100%" required>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach ($produits as $p)
                                                <option value="{{ $p->id }}"
                                                    data-code="{{ $p->code }}"
                                                    data-designation="{{ $p->designation }}">
                                                    {{ $p->code }} — {{ $p->designation }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <span class="stock-actuel text-muted">—</span>
                                    </td>
                                    <td>
                                        <input type="number" name="quantite[]" class="form-control"
                                            min="0" step="0.001" placeholder="0" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm removeGroupeRow"
                                            style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                            <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            La quantité saisie remplace le stock existant (ajustement à la valeur réelle comptée).
                        </small>
                        <span class="badge bg-primary" id="nbLignes">1 ligne</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-1">
                        <i class="fas fa-save me-1"></i> Enregistrer l'inventaire
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Entrée en stock (multi-produits) --}}
<div class="modal fade" id="modalEntree" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('gestion_stock.entree') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-arrow-down me-2 text-success"></i> Entrée en stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">

                    {{-- En-tête commun --}}
                    <div class="row gx-3 mb-4 p-3 rounded" style="background:#f8f9fb;border:1px solid #e5e9ec;">
                        <div class="col-md-5">
                            <label class="small mb-1 fw-bold">Dépôt concerné <span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" required>
                                <option value="">-- Sélectionner le dépôt --</option>
                                @foreach ($services as $s)
                                    <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="small mb-1 fw-bold">Note / Motif</label>
                            <input type="text" name="note" class="form-control"
                                placeholder="Ex : Réception fournisseur, approvisionnement...">
                        </div>
                    </div>

                    {{-- Lignes produits --}}
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45%">Produit</th>
                                    <th style="width:20%" class="text-center">Stock actuel</th>
                                    <th style="width:25%">Quantité à ajouter</th>
                                    <th style="width:10%" class="text-center">
                                        <button type="button" id="addEntreeRow"
                                            class="btn btn-success btn-sm"
                                            style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                            <i data-feather="plus" style="width:14px;height:14px;"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="entreeRows">
                                <tr class="entree-row">
                                    <td>
                                        <select name="produit_id[]" class="form-select select2-entree" style="width:100%" required>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach ($produits as $p)
                                                <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <span class="stock-actuel text-muted">—</span>
                                    </td>
                                    <td>
                                        <input type="number" name="quantite[]" class="form-control"
                                            min="0.001" step="0.001" placeholder="0" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm removeEntreeRow"
                                            style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                            <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-2">
                        <span class="badge bg-success" id="nbLignesEntree">1 ligne</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer l'entrée
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Sortie de stock (multi-produits) --}}
<div class="modal fade" id="modalSortie" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('gestion_stock.sortie') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-arrow-up me-2 text-danger"></i> Sortie de stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">

                    {{-- En-tête commun --}}
                    <div class="row gx-3 mb-4 p-3 rounded" style="background:#f8f9fb;border:1px solid #e5e9ec;">
                        <div class="col-md-5">
                            <label class="small mb-1 fw-bold">Dépôt concerné <span class="text-danger">*</span></label>
                            <select name="service_id" class="form-select" id="sortieServiceId" required>
                                <option value="">-- Sélectionner le dépôt --</option>
                                @foreach ($services as $s)
                                    <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="small mb-1 fw-bold">Note / Motif</label>
                            <input type="text" name="note" class="form-control"
                                placeholder="Ex : Perte, casse, consommation interne...">
                        </div>
                    </div>

                    {{-- Lignes produits --}}
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45%">Produit</th>
                                    <th style="width:20%" class="text-center">Stock disponible</th>
                                    <th style="width:25%">Quantité à sortir</th>
                                    <th style="width:10%" class="text-center">
                                        <button type="button" id="addSortieRow"
                                            class="btn btn-danger btn-sm"
                                            style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                            <i data-feather="plus" style="width:14px;height:14px;"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="sortieRows">
                                <tr class="sortie-row">
                                    <td>
                                        <select name="produit_id[]" class="form-select select2-sortie" style="width:100%" required>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach ($produits as $p)
                                                <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <span class="stock-actuel text-muted">—</span>
                                    </td>
                                    <td>
                                        <input type="number" name="quantite[]" class="form-control"
                                            min="0.001" step="0.001" placeholder="0" required>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm removeSortieRow"
                                            style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                            <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end align-items-center mt-2">
                        <span class="badge bg-danger" id="nbLignesSortie">1 ligne</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save me-1"></i> Enregistrer la sortie
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Ajustement manuel --}}
<div class="modal fade" id="modalAjustement" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('gestion_stock.ajustement') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajustement manuel du stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="small mb-1">Produit <span class="text-danger">*</span></label>
                        <select name="produit_id" class="form-select select2-ajust" required style="width:100%">
                            <option value="">-- Sélectionner --</option>
                            @foreach ($produits as $p)
                                <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small mb-1">Dépôt <span class="text-danger">*</span></label>
                        <select name="service_id" class="form-select" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($services as $s)
                                <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small mb-1">Nouvelle quantité réelle <span class="text-danger">*</span></label>
                        <input type="number" name="quantite" class="form-control" min="0" step="0.001" required>
                    </div>
                    <div class="mb-3">
                        <label class="small mb-1">Note / Motif</label>
                        <input type="text" name="note" class="form-control" placeholder="Inventaire physique, correction...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-1">
                        <i class="fas fa-save me-1"></i> Enregistrer l'ajustement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    const stocksData = @json(
        $stockQuantities
    );

    $(document).ready(function () {

        // ── Entrée en stock (multi-produits) ──────────────────────────
        function getEntreeDepotId() {
            return $('#modalEntree select[name="service_id"]').val();
        }

        function afficherStockEntree($select) {
            const produitId = $select.val();
            const serviceId = getEntreeDepotId();
            const $cellule  = $select.closest('.entree-row').find('.stock-actuel');

            if (produitId && serviceId) {
                const cle = produitId + '_' + serviceId;
                const qte = (stocksData[cle] !== undefined) ? stocksData[cle] : 0;
                $cellule
                    .text(qte.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 3 }))
                    .removeClass('text-muted').addClass('fw-bold');
            } else {
                $cellule.text('—').removeClass('fw-bold').addClass('text-muted');
            }
        }

        function initEntreeSelect2($sel) {
            $sel.select2({
                placeholder: 'Sélectionner un produit',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalEntree')
            }).on('change', function () {
                afficherStockEntree($(this));
            });
        }

        initEntreeSelect2($('#entreeRows .select2-entree'));

        $('#modalEntree select[name="service_id"]').on('change', function () {
            $('#entreeRows .select2-entree').each(function () {
                afficherStockEntree($(this));
            });
        });

        function updateNbLignesEntree() {
            const n = $('.entree-row').length;
            $('#nbLignesEntree').text(n + ' ligne' + (n > 1 ? 's' : ''));
        }

        const entreeRowTemplate = () => `
            <tr class="entree-row">
                <td>
                    <select name="produit_id[]" class="form-select select2-entree" style="width:100%" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($produits as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center"><span class="stock-actuel text-muted">—</span></td>
                <td><input type="number" name="quantite[]" class="form-control" min="0.001" step="0.001" placeholder="0" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm removeEntreeRow"
                        style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                        <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                </td>
            </tr>`;

        $('#addEntreeRow').on('click', function () {
            const $row = $(entreeRowTemplate());
            $('#entreeRows').append($row);
            initEntreeSelect2($row.find('.select2-entree'));
            if (typeof feather !== 'undefined') feather.replace();
            updateNbLignesEntree();
        });

        $(document).on('click', '.removeEntreeRow', function () {
            if ($('.entree-row').length > 1) {
                $(this).closest('.entree-row').remove();
                updateNbLignesEntree();
            } else {
                alert('Vous devez conserver au moins une ligne.');
            }
        });

        $('#modalEntree').on('hidden.bs.modal', function () {
            $('#entreeRows .entree-row:not(:first)').remove();
            $('#entreeRows .entree-row:first .select2-entree').val(null).trigger('change');
            $('#entreeRows .entree-row:first .stock-actuel').text('—').addClass('text-muted').removeClass('fw-bold');
            $('#entreeRows .entree-row:first input[name="quantite[]"]').val('');
            $('#modalEntree select[name="service_id"]').val('').trigger('change');
            $('#modalEntree input[name="note"]').val('');
            updateNbLignesEntree();
        });

        // ── Sortie de stock (multi-produits) ──────────────────────────
        function getSortieDepotId() {
            return $('#sortieServiceId').val();
        }

        function afficherStockSortie($select) {
            const produitId = $select.val();
            const serviceId = getSortieDepotId();
            const $cellule  = $select.closest('.sortie-row').find('.stock-actuel');

            if (produitId && serviceId) {
                const cle = produitId + '_' + serviceId;
                const qte = (stocksData[cle] !== undefined) ? stocksData[cle] : 0;
                $cellule
                    .text(qte.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 3 }))
                    .removeClass('text-muted text-danger')
                    .addClass(qte <= 0 ? 'text-danger fw-bold' : 'fw-bold');
            } else {
                $cellule.text('—').removeClass('fw-bold text-danger').addClass('text-muted');
            }
        }

        function initSortieSelect2($sel) {
            $sel.select2({
                placeholder: 'Sélectionner un produit',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalSortie')
            }).on('change', function () {
                afficherStockSortie($(this));
            });
        }

        initSortieSelect2($('#sortieRows .select2-sortie'));

        $('#sortieServiceId').on('change', function () {
            $('#sortieRows .select2-sortie').each(function () {
                afficherStockSortie($(this));
            });
        });

        function updateNbLignesSortie() {
            const n = $('.sortie-row').length;
            $('#nbLignesSortie').text(n + ' ligne' + (n > 1 ? 's' : ''));
        }

        const sortieRowTemplate = () => `
            <tr class="sortie-row">
                <td>
                    <select name="produit_id[]" class="form-select select2-sortie" style="width:100%" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($produits as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center"><span class="stock-actuel text-muted">—</span></td>
                <td><input type="number" name="quantite[]" class="form-control" min="0.001" step="0.001" placeholder="0" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm removeSortieRow"
                        style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                        <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                </td>
            </tr>`;

        $('#addSortieRow').on('click', function () {
            const $row = $(sortieRowTemplate());
            $('#sortieRows').append($row);
            initSortieSelect2($row.find('.select2-sortie'));
            if (typeof feather !== 'undefined') feather.replace();
            updateNbLignesSortie();
        });

        $(document).on('click', '.removeSortieRow', function () {
            if ($('.sortie-row').length > 1) {
                $(this).closest('.sortie-row').remove();
                updateNbLignesSortie();
            } else {
                alert('Vous devez conserver au moins une ligne.');
            }
        });

        $('#modalSortie').on('hidden.bs.modal', function () {
            $('#sortieRows .sortie-row:not(:first)').remove();
            $('#sortieRows .sortie-row:first .select2-sortie').val(null).trigger('change');
            $('#sortieRows .sortie-row:first .stock-actuel').text('—').addClass('text-muted').removeClass('fw-bold text-danger');
            $('#sortieRows .sortie-row:first input[name="quantite[]"]').val('');
            $('#sortieServiceId').val('').trigger('change');
            $('#modalSortie input[name="note"]').val('');
            updateNbLignesSortie();
        });

        // ── Ajustement unitaire ──────────────────────────────────────
        $('.select2-ajust').select2({
            placeholder: 'Sélectionner un produit',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#modalAjustement')
        });

        // ── Inventaire groupé ────────────────────────────────────────

        function getDepotId() {
            return $('#modalAjustementGroupe select[name="service_id"]').val();
        }

        function afficherStockActuel($select) {
            const produitId  = $select.val();
            const serviceId  = getDepotId();
            const $cellule   = $select.closest('.groupe-row').find('.stock-actuel');

            if (produitId && serviceId) {
                const cle = produitId + '_' + serviceId;
                const qte = (stocksData[cle] !== undefined) ? stocksData[cle] : 0;
                $cellule
                    .text(qte.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 3 }))
                    .removeClass('text-muted text-danger')
                    .addClass(qte <= 0 ? 'text-danger fw-bold' : 'fw-bold');
            } else {
                $cellule
                    .text('—')
                    .removeClass('fw-bold text-danger')
                    .addClass('text-muted');
            }
        }

        // Attacher le handler Select2 directement sur l'élément (évite les problèmes de délégation)
        function initGroupeSelect2($sel) {
            $sel.select2({
                placeholder: 'Sélectionner un produit',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modalAjustementGroupe')
            }).on('change', function () {
                afficherStockActuel($(this));
            });
        }

        initGroupeSelect2($('#groupeRows .select2-groupe'));

        // Quand le dépôt change, rafraîchir tous les stocks affichés
        $('#modalAjustementGroupe select[name="service_id"]').on('change', function () {
            $('#groupeRows .select2-groupe').each(function () {
                afficherStockActuel($(this));
            });
        });

        function updateNbLignes() {
            const n = $('.groupe-row').length;
            $('#nbLignes').text(n + ' ligne' + (n > 1 ? 's' : ''));
        }

        const rowTemplate = () => `
            <tr class="groupe-row">
                <td>
                    <select name="produit_id[]" class="form-select select2-groupe" style="width:100%" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach ($produits as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center"><span class="stock-actuel text-muted">—</span></td>
                <td><input type="number" name="quantite[]" class="form-control" min="0" step="0.001" placeholder="0" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm removeGroupeRow"
                        style="width:28px;height:28px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                        <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                    </button>
                </td>
            </tr>`;

        $('#addGroupeRow').on('click', function () {
            const $row = $(rowTemplate());
            $('#groupeRows').append($row);
            initGroupeSelect2($row.find('.select2-groupe'));
            if (typeof feather !== 'undefined') feather.replace();
            updateNbLignes();
        });

        $(document).on('click', '.removeGroupeRow', function () {
            if ($('.groupe-row').length > 1) {
                $(this).closest('.groupe-row').remove();
                updateNbLignes();
            } else {
                alert('Vous devez conserver au moins une ligne.');
            }
        });

        $('#modalAjustementGroupe').on('hidden.bs.modal', function () {
            $('#groupeRows .groupe-row:not(:first)').remove();
            const $firstSelect = $('#groupeRows .groupe-row:first .select2-groupe');
            $firstSelect.val(null).trigger('change');
            $('#groupeRows .groupe-row:first .stock-actuel').text('—').addClass('text-muted').removeClass('fw-bold text-danger');
            $('#groupeRows .groupe-row:first input[name="quantite[]"]').val('');
            $('#modalAjustementGroupe select[name="service_id"]').val('').trigger('change');
            updateNbLignes();
        });

        if (typeof feather !== 'undefined') feather.replace();

        // ── Ouverture automatique depuis le menu latéral (?open=entree|sortie) ──
        const ouverture = @json(request('open'));
        if (ouverture === 'entree') {
            new bootstrap.Modal(document.getElementById('modalEntree')).show();
        } else if (ouverture === 'sortie') {
            new bootstrap.Modal(document.getElementById('modalSortie')).show();
        }
    });
</script>
@endsection
