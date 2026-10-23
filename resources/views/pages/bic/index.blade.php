@extends('layouts.master')

@section('title')
    <title>SIG - CFE | PSVB</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="trending-up" title="Prélèvement Spécifique sur les Ventes de Biens (PSVB)"></x-page-header>
        <!-- Main page content-->

        <div class="container-xl px-4 mt-n10">
            <!-- Liste des catégories PSVB -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Catégories PSVB (DGI)</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createBicModal">
                        <i class="fa fa-plus"></i>&nbsp;Ajouter
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher une catégorie PSVB..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Intitulé</th>
                                        <th>Taux (%)</th>
                                        <th style="width: 100px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $item)
                                        <tr>
                                            <td>
                                                <span style="font-weight: 600; color: var(--primary-color);">{{ $item->code }}</span>
                                            </td>
                                            <td>{{ $item->intitule }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $item->taux }} %</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('module_bic.show', [$item->id]) }}" class="btn btn-outline-info" title="Voir les produits associés">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBicModal{{ $item->id }}" title="Modifier">
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('module_bic.destroy', [$item->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce taux BIC / PSVB ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                {{-- Modal d'édition BIC --}}
                                                <div class="modal fade text-start" id="editBicModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form action="{{ route('module_bic.update', [$item->id]) }}" method="POST">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header bg-primary text-white">
                                                                    <h5 class="modal-title"><i class="fa fa-edit me-2"></i> Modifier BIC / PSVB : {{ $item->code }}</h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body p-3">
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                                                        <input class="form-control" name="code" type="text" value="{{ old('code', $item->code) }}" required />
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Intitulé <span class="text-danger">*</span></label>
                                                                        <input class="form-control" name="intitule" type="text" value="{{ old('intitule', $item->intitule) }}" required />
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label fw-bold">Taux (%) <span class="text-danger">*</span></label>
                                                                        <input class="form-control" name="taux" type="number" step="0.01" min="0" max="100" value="{{ old('taux', $item->taux) }}" required />
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                                                                    <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-save me-1"></i> Enregistrer</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $collection->links() }}
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
                            <i data-feather="inbox"
                                style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucune catégorie PSVB trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal d'ajout -->
        <div class="modal fade" id="createBicModal" tabindex="-1" aria-labelledby="createBicModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('module_bic.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createBicModalLabel">Ajouter une nouvelle catégorie PSVB</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input class="form-control" name="code" type="text" placeholder="Ex: A"
                                            value="{{ Request::old('code') }}" required />
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label class="form-label">Intitulé <span class="text-danger">*</span></label>
                                        <input class="form-control" name="intitule" type="text"
                                            placeholder="Ex: Catégorie A — Droit commun" value="{{ Request::old('intitule') }}"
                                            required />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Taux (%) <span class="text-danger">*</span></label>
                                        <input class="form-control" name="taux" type="number" step="0.01" min="0"
                                            max="100" placeholder="2.0" value="{{ Request::old('taux') }}" required />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Annuler
                            </button>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('createBicModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
        });
        @endif
    </script>
@endsection
