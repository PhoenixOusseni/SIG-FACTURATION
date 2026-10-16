@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Dépôt — {{ $service->libelle }}</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="archive" title="Détails du dépôt"></x-page-header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom: 8rem;">

            {{-- ─── Informations générales ─────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Informations générales</span>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-sm btn-warning" style="color:#fff;"
                            data-bs-toggle="modal" data-bs-target="#editServiceModal{{ $service->id }}">
                            <i class="fa fa-edit"></i>&nbsp; Modifier
                        </button>
                        <a href="{{ route('gestion_service.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fa fa-arrow-left"></i>&nbsp; Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label class="form-label text-muted small">Code</label>
                            <div style="font-weight: 600; color: var(--primary-color); font-size: 1.1rem;">
                                {{ $service->code ?: '—' }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Libellé</label>
                            <div style="font-size: 1.1rem; font-weight: 600;">{{ $service->libelle }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Description</label>
                            <div>{{ $service->description ?: '—' }}</div>
                        </div>
                    </div>

                    <hr>

                    {{-- KPI quantités --}}
                    <div class="row gx-0 text-center">
                        <div class="col-md-3 py-2">
                            <div class="text-muted small text-uppercase">Produits en stock</div>
                            <div style="font-size:1.6rem;font-weight:800;color:var(--primary-color);">{{ $totalProduits }}</div>
                        </div>
                        <div class="col-md-3 py-2">
                            <div class="text-muted small text-uppercase">Quantité totale</div>
                            <div style="font-size:1.6rem;font-weight:800;color:#047857;">
                                {{ number_format($totalQuantite, 2, ',', ' ') }}
                            </div>
                        </div>
                        <div class="col-md-3 py-2">
                            <div class="text-muted small text-uppercase">Stock faible</div>
                            <div style="font-size:1.6rem;font-weight:800;color:#b45309;">{{ $nbFaible }}</div>
                        </div>
                        <div class="col-md-3 py-2">
                            <div class="text-muted small text-uppercase">Ruptures</div>
                            <div style="font-size:1.6rem;font-weight:800;color:#c0392b;">{{ $nbRupture }}</div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Créé le :</small>
                            <span>{{ $service->created_at ? $service->created_at->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Dernière modification :</small>
                            <span>{{ $service->updated_at ? $service->updated_at->format('d/m/Y H:i') : '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Produits dans ce dépôt ─────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Produits dans ce dépôt</span>
                    <span class="badge bg-primary">{{ $totalProduits }} produit(s)</span>
                </div>
                <div class="card-body">
                    @if ($stocks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Désignation</th>
                                        <th>Unité</th>
                                        <th class="text-end">Quantité en stock</th>
                                        <th>Statut</th>
                                        <th>Dernière mise à jour</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stocks as $stock)
                                        @php
                                            $rowClass = '';
                                            if ($stock->quantite <= 0) $rowClass = 'table-danger';
                                            elseif ($stock->quantite < 5) $rowClass = 'table-warning';
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td><span class="fw-bold">{{ optional($stock->produit)->code }}</span></td>
                                            <td>{{ optional($stock->produit)->designation }}</td>
                                            <td>{{ optional($stock->produit)->unite }}</td>
                                            <td class="text-end fw-bold">{{ number_format($stock->quantite, 2, ',', ' ') }}</td>
                                            <td>
                                                @if ($stock->quantite <= 0)
                                                    <span class="badge bg-danger">Rupture</span>
                                                @elseif ($stock->quantite < 5)
                                                    <span class="badge bg-warning text-dark">Faible</span>
                                                @else
                                                    <span class="badge bg-success">OK</span>
                                                @endif
                                            </td>
                                            <td>{{ $stock->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Aucun produit enregistré dans ce dépôt pour le moment.
                        </div>
                    @endif
                </div>
            </div>

            {{-- ─── Mouvements de stock du dépôt ───────────────────── --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Mouvements de stock du dépôt</span>
                    <span class="badge bg-secondary">{{ $mouvements->total() }} mouvement(s)</span>
                </div>
                <div class="card-body">
                    @if ($mouvements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Produit</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-end">Quantité</th>
                                        <th>Référence</th>
                                        <th>Opérateur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($mouvements as $mv)
                                        <tr>
                                            <td>{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                            <td>{{ optional($mv->produit)->designation }}</td>
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $mouvements->links() }}
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Aucun mouvement enregistré pour ce dépôt.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ─── Modal de modification ──────────────────────────── --}}
        <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1"
            aria-labelledby="editServiceModalLabel{{ $service->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editServiceModalLabel{{ $service->id }}">
                            Modifier — {{ $service->libelle }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('gestion_service.update', $service->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="code{{ $service->id }}" class="form-label">Code</label>
                                <input type="text" class="form-control" id="code{{ $service->id }}" name="code"
                                    value="{{ $service->code }}">
                            </div>
                            <div class="mb-3">
                                <label for="libelle{{ $service->id }}" class="form-label">Libellé</label>
                                <input type="text" class="form-control" id="libelle{{ $service->id }}"
                                    name="libelle" value="{{ $service->libelle }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description{{ $service->id }}" class="form-label">Description</label>
                                <textarea class="form-control" id="description{{ $service->id }}"
                                    name="description" rows="3">{{ $service->description }}</textarea>
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
    </main>
@endsection
