@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Catalogue Produits</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="package" title="Catalogue des Produits & Services" subtitle="Gestion des articles, tarification PU HT / PU TTC, groupes de TVA et taxes spécifiques">
            <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createProduitModal">
                <i class="fas fa-plus-circle me-1"></i> Nouveau produit
            </button>
        </x-page-header>

        <div class="container-xl px-4">
            <x-form-errors class="mb-3" />

            {{-- Panneau de filtres --}}
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i data-feather="filter" style="width:16px;height:16px;" class="text-primary"></i>
                    <span class="fw-bold">Filtrer les produits</span>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('module_produit.index') }}" id="filterForm">
                        <div class="row gx-3 align-items-end">
                            <div class="col-md-4">
                                <label class="small mb-1">Famille produit DGI</label>
                                <select name="famille_produit" class="form-select">
                                    <option value="">-- Toutes --</option>
                                    <option value="LOCBIE" {{ request('famille_produit') === 'LOCBIE' ? 'selected' : '' }}>LOCBIE — Vente locale de biens</option>
                                    <option value="IMPBIE" {{ request('famille_produit') === 'IMPBIE' ? 'selected' : '' }}>IMPBIE — Vente de biens importés</option>
                                    <option value="LOCSER" {{ request('famille_produit') === 'LOCSER' ? 'selected' : '' }}>LOCSER — Prestation locale de services</option>
                                    <option value="IMPSER" {{ request('famille_produit') === 'IMPSER' ? 'selected' : '' }}>IMPSER — Prestation de services importés</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1">Groupe TVA</label>
                                <select name="tva_id" class="form-select">
                                    <option value="">-- Toutes --</option>
                                    @foreach ($tvas as $tva)
                                        <option value="{{ $tva->id }}" {{ request('tva_id') == $tva->id ? 'selected' : '' }}>
                                            Groupe {{ $tva->code }} — {{ $tva->intitule }} ({{ $tva->taux }} %)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i> Filtrer
                                </button>
                                <a href="{{ route('module_produit.index') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-times me-1"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-bold text-dark">
                        Liste des produits enregistrés
                        <span class="badge bg-secondary ms-2">{{ $collection->total() }}</span>
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createProduitModal">
                        <i class="fas fa-plus me-1"></i> Ajouter un article
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <input type="text" placeholder="Rechercher par code, désignation..." class="form-control"
                                id="searchInput" onkeyup="searchTable()">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="produitsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th>Unité</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-end">PU HT</th>
                                    <th class="text-end">PU TTC</th>
                                    <th class="text-center">TVA</th>
                                    <th class="text-center">Taxes spécifiques</th>
                                    <th class="text-center">Statut</th>
                                    <th style="min-width: 160px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($collection as $item)
                                    @php
                                        $isService = $item->isService();
                                        $qte = (float)($item->qte ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="fw-bold font-monospace text-primary">{{ $item->code }}</td>
                                        <td class="fw-bold">{{ $item->designation }}</td>
                                        <td>{{ $item->unite ?: 'U' }}</td>
                                        <td class="text-center" data-order="{{ $isService ? -1 : $qte }}">
                                            @if ($isService)
                                                <span class="badge bg-secondary text-white" title="Prestation de service : pas d'inventaire physique">
                                                    <i class="fas fa-concierge-bell me-1"></i> Prestation (N/A)
                                                </span>
                                            @elseif ($qte <= 0)
                                                <span class="badge bg-danger">{{ number_format($qte, 2, ',', ' ') }}</span>
                                            @elseif ($qte <= 5)
                                                <span class="badge bg-warning text-dark">{{ number_format($qte, 2, ',', ' ') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ number_format($qte, 2, ',', ' ') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace fw-bold" data-order="{{ (float)($item->prix_unitaire ?? 0) }}">{{ number_format($item->prix_unitaire ?? 0, 0, ',', ' ') }}</td>
                                        <td class="text-end font-monospace fw-bold text-primary" data-order="{{ (float)($item->prix_unitaire_ttc ?? 0) }}">{{ number_format($item->prix_unitaire_ttc ?? 0, 0, ',', ' ') }}</td>
                                        <td class="text-center">
                                            @if ($item->tva)
                                                <span class="badge bg-primary">Groupe {{ $item->tva->code }} ({{ $item->tva->taux }}%)</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($item->type_ts === 'POURCENTAGE' && (float)$item->pourcentage_ts > 0)
                                                <span class="badge bg-info text-dark">{{ $item->pourcentage_ts }} %</span>
                                                @if ($item->description_ts)
                                                    <small class="d-block text-muted">{{ $item->description_ts }}</small>
                                                @endif
                                            @elseif ($item->montant_ts > 0)
                                                <span class="badge bg-info text-dark">{{ number_format($item->montant_ts, 0, ',', ' ') }} (Fixe)</span>
                                                @if ($item->description_ts)
                                                    <small class="d-block text-muted">{{ $item->description_ts }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($item->is_active ?? true)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Actif</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Désactivé</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                {{-- Activer / Désactiver sans supprimer --}}
                                                <form action="{{ route('module_produit.toggle_status', [$item->id]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-secondary" title="{{ ($item->is_active ?? true) ? 'Désactiver (masquer des ventes)' : 'Activer (rendre disponible)' }}">
                                                        <i class="fas {{ ($item->is_active ?? true) ? 'fa-eye-slash text-warning' : 'fa-check text-success' }}"></i>
                                                    </button>
                                                </form>

                                                {{-- Voir fiche --}}
                                                <a href="{{ route('module_produit.show', [$item->id]) }}" class="btn btn-outline-info" title="Voir l'historique et stock">
                                                    <i class="fa fa-eye"></i>
                                                </a>

                                                {{-- Modifier --}}
                                                <button type="button" class="btn btn-outline-primary"
                                                    data-bs-toggle="modal" data-bs-target="#editProduitModal{{ $item->id }}"
                                                    title="Modifier l'article">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                {{-- Désactiver / Supprimer --}}
                                                <form action="{{ route('module_produit.destroy', [$item->id]) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver l\'article {{ $item->designation }} du catalogue ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Désactiver cet article du catalogue">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Aucun produit ne correspond aux critères sélectionnés.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if (method_exists($collection, 'links'))
                            <div class="mt-3">
                                {{ $collection->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modales d'édition extraites hors de tout conteneur mt-n10 (résout le bug du fond grisé) --}}
        @foreach ($collection as $item)
                @php
                    $itemIsService = in_array($item->famille_produit, ['LOCSER', 'IMPSER']);
                    $itemTypeTs = $item->type_ts ?? ($item->montant_ts > 0 ? 'MONTANT' : ($item->pourcentage_ts > 0 ? 'POURCENTAGE' : 'AUCUN'));
                @endphp
                <div class="modal fade text-start" id="editProduitModal{{ $item->id }}" tabindex="-1" aria-labelledby="editProduitModalLabel{{ $item->id }}" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <form action="{{ route('module_produit.update', $item->id) }}" method="POST" class="modal-content shadow-lg border-0">
                            @csrf
                            @method('PUT')
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="editProduitModalLabel{{ $item->id }}">
                                    <i class="fa fa-edit me-2"></i> Modifier l'article : {{ $item->designation }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row gx-3 mb-3">
                                    <div class="col-md-3">
                                        <label class="small mb-1 fw-bold">Code article <span class="text-danger">*</span></label>
                                        <input class="form-control font-monospace fw-bold" name="code" type="text" value="{{ old('code', $item->code) }}" required />
                                    </div>
                                    <div class="col-md-5">
                                        <label class="small mb-1 fw-bold">Désignation <span class="text-danger">*</span></label>
                                        <input class="form-control fw-bold" name="designation" type="text" value="{{ old('designation', $item->designation) }}" required />
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small mb-1">Unité</label>
                                        <input class="form-control" name="unite" type="text" value="{{ old('unite', $item->unite) }}" placeholder="Ex: kg, litre, pièce" />
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small mb-1 fw-bold">Prix unitaire HT <span class="text-danger">*</span></label>
                                        <input class="form-control font-monospace fw-bold text-end" name="prix_unitaire" type="number" min="0" step="0.01" value="{{ old('prix_unitaire', $item->prix_unitaire) }}" required />
                                    </div>
                                </div>

                                <div class="row gx-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="small mb-1 fw-bold">Groupe TVA <span class="text-danger">*</span></label>
                                        <select name="tva_id" class="form-select" required>
                                            <option value="">-- Sélectionner un groupe --</option>
                                            @foreach ($tvas as $tva)
                                                <option value="{{ $tva->id }}" {{ $item->tva_id == $tva->id ? 'selected' : '' }}>
                                                    Groupe {{ $tva->code }} — {{ $tva->intitule }} ({{ $tva->taux }}%)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small mb-1 fw-bold">Classification DGI <span class="text-danger">*</span></label>
                                        <select name="famille_produit" class="form-select produit-famille-select" data-target-id="edit_{{ $item->id }}" required>
                                            <option value="LOCBIE" {{ $item->famille_produit === 'LOCBIE' ? 'selected' : '' }}>LOCBIE — Vente locale de biens</option>
                                            <option value="IMPBIE" {{ $item->famille_produit === 'IMPBIE' ? 'selected' : '' }}>IMPBIE — Vente de biens importés</option>
                                            <option value="LOCSER" {{ $item->famille_produit === 'LOCSER' ? 'selected' : '' }}>LOCSER — Prestation locale de services</option>
                                            <option value="IMPSER" {{ $item->famille_produit === 'IMPSER' ? 'selected' : '' }}>IMPSER — Prestation de services importés</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small mb-1 fw-bold">Stock actuel</label>
                                        <input class="form-control font-monospace stock-input-field" id="stock_input_edit_{{ $item->id }}" name="qte" type="number" min="0" step="0.01"
                                            value="{{ $itemIsService ? 0 : old('qte', $item->qte) }}"
                                            {{ $itemIsService ? 'disabled' : '' }} />
                                        <small class="text-muted d-block mt-1 stock-service-notice" id="stock_notice_edit_{{ $item->id }}" style="{{ $itemIsService ? '' : 'display:none;' }}">
                                            <i class="fas fa-info-circle text-info"></i> Prestation : aucun suivi d'inventaire physique requis.
                                        </small>
                                    </div>
                                </div>

                                {{-- Taxes spécifiques TS dynamique --}}
                                <div class="card p-3 bg-light border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-coins me-1 text-warning"></i> Taxe Spécifique (TS)</h6>
                                        <span class="badge bg-secondary">Optionnel</span>
                                    </div>
                                    <div class="row gx-3">
                                        <div class="col-md-4">
                                            <label class="small mb-1 fw-bold">Type de calcul TS</label>
                                            <select name="type_ts" class="form-select ts-type-select" data-target-id="edit_{{ $item->id }}">
                                                <option value="AUCUN" {{ $itemTypeTs === 'AUCUN' ? 'selected' : '' }}>[Aucune TS] - Non applicable</option>
                                                <option value="MONTANT" {{ $itemTypeTs === 'MONTANT' ? 'selected' : '' }}>Montant Fixe (FCFA par unité)</option>
                                                <option value="POURCENTAGE" {{ $itemTypeTs === 'POURCENTAGE' ? 'selected' : '' }}>Pourcentage (%) sur le Prix Unitaire</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 ts-group-montant" id="ts_montant_edit_{{ $item->id }}" style="{{ $itemTypeTs === 'MONTANT' ? '' : 'display:none;' }}">
                                            <label class="small mb-1 fw-bold">Montant Fixe TS (FCFA)</label>
                                            <input class="form-control" name="montant_ts" type="number" min="0" step="0.01" value="{{ old('montant_ts', $item->montant_ts) }}" placeholder="Ex: 50" />
                                        </div>
                                        <div class="col-md-4 ts-group-pourcentage" id="ts_pourcentage_edit_{{ $item->id }}" style="{{ $itemTypeTs === 'POURCENTAGE' ? '' : 'display:none;' }}">
                                            <label class="small mb-1 fw-bold">Pourcentage TS (%)</label>
                                            <input class="form-control" name="pourcentage_ts" type="number" min="0" max="100" step="0.01" value="{{ old('pourcentage_ts', $item->pourcentage_ts) }}" placeholder="Ex: 10" />
                                        </div>
                                        <div class="col-md-4 ts-group-desc" id="ts_desc_edit_{{ $item->id }}" style="{{ $itemTypeTs !== 'AUCUN' ? '' : 'display:none;' }}">
                                            <label class="small mb-1">Libellé / Nature TS</label>
                                            <input class="form-control" name="description_ts" type="text" value="{{ old('description_ts', $item->description_ts) }}" placeholder="Ex: Boissons, Tabacs..." />
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="small mb-1">Description libre de l'article</label>
                                    <textarea class="form-control" name="description" rows="2">{{ old('description', $item->description) }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer bg-light" style="position: sticky; bottom: 0; z-index: 1050; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Annuler</button>
                                <button class="btn btn-primary px-4 fw-bold" type="submit">
                                    <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach

        {{-- Modal création produit --}}
        <div class="modal fade" id="createProduitModal" tabindex="-1" aria-labelledby="createProduitModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <form action="{{ route('module_produit.store') }}" method="POST" class="modal-content shadow-lg border-0">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="createProduitModalLabel">
                            <i class="fas fa-box-open me-2"></i> Ajouter un nouveau produit
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body p-4">
                        <x-form-errors />
                        <div class="row gx-3 mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1 fw-bold">Code article <span class="text-danger">*</span></label>
                                <input class="form-control font-monospace fw-bold" name="code" type="text" placeholder="Ex: ART001"
                                    value="{{ old('code') }}" required />
                            </div>
                            <div class="col-md-5">
                                <label class="small mb-1 fw-bold">Désignation <span class="text-danger">*</span></label>
                                <input class="form-control fw-bold" name="designation" type="text" placeholder="Nom de l'article"
                                    value="{{ old('designation') }}" required />
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1">Unité</label>
                                <input class="form-control" name="unite" type="text" placeholder="Ex: pièce, carton, kg"
                                    value="{{ old('unite', 'U') }}" />
                            </div>
                            <div class="col-md-2">
                                <label class="small mb-1 fw-bold text-dark">Prix unitaire HT <span class="text-danger">*</span></label>
                                <input class="form-control font-monospace fw-bold text-end" name="prix_unitaire" type="number" min="0"
                                    step="0.01" placeholder="0" value="{{ old('prix_unitaire') }}" required />
                            </div>
                        </div>
                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1 fw-bold">Groupe TVA <span class="text-danger">*</span></label>
                                <select name="tva_id" id="tva_id" class="form-select" required>
                                    <option value="">-- Sélectionner un groupe de TVA --</option>
                                    @foreach ($tvas as $tva)
                                        <option value="{{ $tva->id }}" {{ old('tva_id') == $tva->id ? 'selected' : '' }}>
                                            Groupe {{ $tva->code }} — {{ $tva->intitule }} ({{ $tva->taux }}%)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1 fw-bold">Classification DGI (Type d'article) <span class="text-danger">*</span></label>
                                <select name="famille_produit" class="form-select produit-famille-select" data-target-id="create" required>
                                    <option value="LOCBIE" {{ old('famille_produit', 'LOCBIE') == 'LOCBIE' ? 'selected' : '' }}>LOCBIE — Vente locale de biens</option>
                                    <option value="IMPBIE" {{ old('famille_produit') == 'IMPBIE' ? 'selected' : '' }}>IMPBIE — Vente de biens importés</option>
                                    <option value="LOCSER" {{ old('famille_produit') == 'LOCSER' ? 'selected' : '' }}>LOCSER — Prestation locale de services</option>
                                    <option value="IMPSER" {{ old('famille_produit') == 'IMPSER' ? 'selected' : '' }}>IMPSER — Prestation de services importés</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1 fw-bold">Stock initial</label>
                                <input class="form-control font-monospace stock-input-field" id="stock_input_create" name="qte" type="number" min="0" step="0.01"
                                    placeholder="0" value="{{ old('qte', 0) }}" />
                                <small class="text-muted d-block mt-1 stock-service-notice" id="stock_notice_create" style="display: none;">
                                    <i class="fas fa-info-circle text-info"></i> Prestation : aucun suivi d'inventaire physique requis.
                                </small>
                            </div>
                        </div>

                        {{-- Taxe Spécifique TS dynamique --}}
                        <div class="card p-3 bg-light border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-coins me-1 text-warning"></i> Taxe Spécifique (TS)</h6>
                                <span class="badge bg-secondary">Optionnel</span>
                            </div>
                            @php $createTsType = old('type_ts', 'AUCUN'); @endphp
                            <div class="row gx-3">
                                <div class="col-md-4">
                                    <label class="small mb-1 fw-bold">Type de calcul TS</label>
                                    <select name="type_ts" class="form-select ts-type-select" data-target-id="create">
                                        <option value="AUCUN" {{ $createTsType === 'AUCUN' ? 'selected' : '' }}>[Aucune TS] - Non applicable</option>
                                        <option value="MONTANT" {{ $createTsType === 'MONTANT' ? 'selected' : '' }}>Montant Fixe (FCFA par unité)</option>
                                        <option value="POURCENTAGE" {{ $createTsType === 'POURCENTAGE' ? 'selected' : '' }}>Pourcentage (%) sur le Prix Unitaire</option>
                                    </select>
                                </div>
                                <div class="col-md-4 ts-group-montant" id="ts_montant_create" style="{{ $createTsType === 'MONTANT' ? '' : 'display:none;' }}">
                                    <label class="small mb-1 fw-bold">Montant Fixe TS (FCFA)</label>
                                    <input class="form-control" name="montant_ts" type="number" min="0"
                                        step="0.01" placeholder="0" value="{{ old('montant_ts') }}" />
                                </div>
                                <div class="col-md-4 ts-group-pourcentage" id="ts_pourcentage_create" style="{{ $createTsType === 'POURCENTAGE' ? '' : 'display:none;' }}">
                                    <label class="small mb-1 fw-bold">Pourcentage TS (%)</label>
                                    <input class="form-control" name="pourcentage_ts" type="number" min="0" max="100"
                                        step="0.01" placeholder="0" value="{{ old('pourcentage_ts') }}" />
                                </div>
                                <div class="col-md-4 ts-group-desc" id="ts_desc_create" style="{{ $createTsType !== 'AUCUN' ? '' : 'display:none;' }}">
                                    <label class="small mb-1">Libellé / Nature TS</label>
                                    <input class="form-control" name="description_ts" type="text"
                                        placeholder="Ex: Boissons gazeuses, Taxe emballage" maxlength="50" value="{{ old('description_ts') }}" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="small mb-1">Description libre de l'article</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Informations complémentaires...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="position: sticky; bottom: 0; z-index: 1050; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-primary px-4 fw-bold" type="submit">
                            <i class="fas fa-save me-1"></i> Enregistrer le produit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
            });
        }

        // Bascule dynamique des champs de Taxe Spécifique (TS)
        function handleTsChange(selectElem) {
            const targetId = selectElem.getAttribute('data-target-id');
            const val = selectElem.value;
            const montantBox = document.getElementById('ts_montant_' + targetId);
            const pourcentBox = document.getElementById('ts_pourcentage_' + targetId);
            const descBox = document.getElementById('ts_desc_' + targetId);

            if (montantBox) montantBox.style.display = (val === 'MONTANT') ? 'block' : 'none';
            if (pourcentBox) pourcentBox.style.display = (val === 'POURCENTAGE') ? 'block' : 'none';
            if (descBox) descBox.style.display = (val !== 'AUCUN') ? 'block' : 'none';

            if (val === 'AUCUN') {
                if (montantBox) montantBox.querySelector('input').value = '';
                if (pourcentBox) pourcentBox.querySelector('input').value = '';
                if (descBox) descBox.querySelector('input').value = '';
            } else if (val === 'MONTANT') {
                if (pourcentBox) pourcentBox.querySelector('input').value = '';
            } else if (val === 'POURCENTAGE') {
                if (montantBox) montantBox.querySelector('input').value = '';
            }
        }

        // Bascule dynamique Stock vs Prestation de Services
        function handleFamilleChange(selectElem) {
            const targetId = selectElem.getAttribute('data-target-id');
            const val = selectElem.value;
            const isService = (val === 'LOCSER' || val === 'IMPSER');
            const stockInput = document.getElementById('stock_input_' + targetId);
            const stockNotice = document.getElementById('stock_notice_' + targetId);

            if (stockInput) {
                stockInput.disabled = isService;
                if (isService) {
                    stockInput.value = '0';
                }
            }
            if (stockNotice) {
                stockNotice.style.display = isService ? 'block' : 'none';
            }
        }

        let produitsDataTable = null;
        function initProduitsDataTable() {
            if ($.fn.DataTable && $('#produitsTable').length && !$.fn.DataTable.isDataTable('#produitsTable')) {
                produitsDataTable = $('#produitsTable').DataTable({
                    order: [[1, 'asc']], // Tri par Désignation
                    paging: false, // pagination gérée côté Laravel
                    info: false,
                    columnDefs: [
                        { targets: [9], orderable: false, searchable: false },
                        { targets: [3, 4, 5], type: 'num' }
                    ],
                    language: {
                        processing: "Traitement en cours...",
                        search: "Recherche :",
                        zeroRecords: "Aucun article trouvé",
                        emptyTable: "Aucun article disponible"
                    }
                });
            }
        }

        window.searchTable = function() {
            var val = document.getElementById("searchInput").value;
            if (produitsDataTable) {
                produitsDataTable.search(val).draw();
            } else {
                var filter = val.toUpperCase();
                $('#produitsTable tbody tr').each(function() {
                    $(this).toggle($(this).text().toUpperCase().indexOf(filter) > -1);
                });
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            initProduitsDataTable();

            // Écouteurs sur les sélecteurs TS
            document.querySelectorAll('.ts-type-select').forEach(function(sel) {
                sel.addEventListener('change', function() {
                    handleTsChange(this);
                });
                handleTsChange(sel);
            });

            // Écouteurs sur les sélecteurs Famille DGI (Services vs Biens)
            document.querySelectorAll('.produit-famille-select').forEach(function(sel) {
                sel.addEventListener('change', function() {
                    handleFamilleChange(this);
                });
                handleFamilleChange(sel);
            });

            @if ($errors->any())
                var el = document.getElementById('createProduitModal');
                if (el && window.bootstrap) new bootstrap.Modal(el).show();
            @endif
        });
    </script>
@endsection
