@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Détails PSVBB</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="trending-up" title="Détails du PSVBB"></x-page-header>

        <div class="container-xl px-4 mt-n10">

            <!-- Informations PSVBB -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        Informations générales
                    </span>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                            data-bs-target="#editBicModal{{ $item->id }}"
                            style="background: var(--warning-color); border: none; color: white;">
                            <i class="fa fa-edit"></i>&nbsp; Modifier
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteBicModal{{ $item->id }}">
                            <i class="fa fa-trash"></i>&nbsp; Supprimer
                        </button>
                        <a href="{{ route('module_bic.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fa fa-arrow-left"></i>&nbsp; Retour à la liste
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label text-muted small">Code</label>
                                <div style="font-weight: 600; color: var(--primary-color); font-size: 1.1rem;">
                                    {{ $item->code }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="form-label text-muted small">Intitulé</label>
                                <div style="font-size: 1.1rem;">{{ $item->intitule }}</div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label text-muted small">Taux</label>
                                <div>
                                    <span class="badge bg-info" style="font-size: 1rem;">{{ $item->taux }} %</span>
                                </div>
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
        </div>

        <!-- Modal de modification -->
        <div class="modal fade" id="editBicModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel{{ $item->id }}">Modifier le PSVBB</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('module_bic.update', [$item->id]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="code{{ $item->id }}" class="form-label">Code</label>
                                <input type="text" class="form-control" id="code{{ $item->id }}" name="code"
                                    value="{{ $item->code }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="intitule{{ $item->id }}" class="form-label">Intitulé</label>
                                <input type="text" class="form-control" id="intitule{{ $item->id }}"
                                    name="intitule" value="{{ $item->intitule }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="taux{{ $item->id }}" class="form-label">Taux (%)</label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control"
                                    id="taux{{ $item->id }}" name="taux" value="{{ $item->taux }}" required>
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

        <!-- Modal de suppression -->
        <div class="modal fade" id="deleteBicModal{{ $item->id }}" tabindex="-1"
            aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="deleteModalLabel">
                            Confirmation de suppression</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                            aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-3">
                            <i class="fa fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p class="text-center">
                            Êtes-vous sûr de vouloir supprimer le PSVBB
                            <strong>{{ $item->intitule }}</strong> ?
                        </p>
                        <p class="text-center text-muted">
                            Cette action est irréversible et peut affecter les produits qui l'utilisent.
                        </p>
                    </div>
                    <div class="m-3">
                        <form action="{{ route('module_bic.destroy', [$item->id]) }}" method="POST"
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

        <!-- Produits qui appliquent ce PSVBB -->
        <div class="container-xl px-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        Produits appliquant ce PSVBB
                    </span>
                    <span class="badge bg-primary">{{ $produits->total() }} produit(s)</span>
                </div>
                <div class="card-body">
                    @if ($produits->count() > 0)
                        <div style="margin-bottom: 1rem;">
                            <input type="text" placeholder="Rechercher un produit..." class="form-control"
                                id="searchInput" onkeyup="searchTable()" />
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Désignation</th>
                                        <th>Unité</th>
                                        <th class="text-end">Prix unitaire</th>
                                        <th class="text-end">Quantité</th>
                                        <th class="text-end">Montant total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($produits as $produit)
                                        <tr>
                                            <td>
                                                <span style="font-weight: 600; color: var(--primary-color);">
                                                    {{ $produit->code }}
                                                </span>
                                            </td>
                                            <td>{{ $produit->designation }}</td>
                                            <td>{{ $produit->unite ?? '—' }}</td>
                                            <td class="text-end">
                                                {{ $produit->prix_unitaire !== null ? number_format($produit->prix_unitaire, 2, ',', ' ') : '—' }}
                                            </td>
                                            <td class="text-end">
                                                {{ $produit->qte !== null ? number_format($produit->qte, 2, ',', ' ') : '—' }}
                                            </td>
                                            <td class="text-end">
                                                {{ $produit->prix_unitaire !== null && $produit->qte !== null ? number_format($produit->prix_unitaire * $produit->qte, 2, ',', ' ') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $produits->links() }}
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
                            <i data-feather="inbox"
                                style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucun produit n'applique ce PSVBB</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection
