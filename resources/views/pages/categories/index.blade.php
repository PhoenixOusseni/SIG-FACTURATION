@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Categories</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="layers" title="Gestion Type de Client"></x-page-header>
        <!-- Main page content-->

        <div class="container-xl px-4 mt-n10">
            <!-- Liste des catégories -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Liste des Types de Client</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createCategorieModal">
                        <i class="fa fa-plus"></i>&nbsp;Ajouter
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher un type de client..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Libellé</th>
                                        <th style="width: 100px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $item)
                                        <tr>
                                            <td><span
                                                    style="font-weight: 600; color: var(--primary-color);">{{ $item->code }}</span>
                                            </td>
                                            <td>{{ $item->libelle }}</td>
                                            <td class="text-center d-flex justify-content-center" style="gap: 0.5rem;">
                                                <button type="button" class="btn btn-sm btn-warning me-2"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editCategorieModal{{ $item->id }}"
                                                    style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background: var(--warning-color); border: none; color: white;">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteCategorieModal{{ $item->id }}"
                                                    style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal de modification -->
                                        <div class="modal fade" id="editCategorieModal{{ $item->id }}" tabindex="-1"
                                            aria-labelledby="editModalLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editModalLabel{{ $item->id }}">
                                                            Modifier le type de client</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="{{ route('module_categorie.update', [$item->id]) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="mb-3">
                                                                <label for="code{{ $item->id }}"
                                                                    class="form-label">Code</label>
                                                                <input type="text" class="form-control"
                                                                    id="code{{ $item->id }}" name="code"
                                                                    value="{{ $item->code }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="libelle{{ $item->id }}"
                                                                    class="form-label">Libellé</label>
                                                                <input type="text" class="form-control"
                                                                    id="libelle{{ $item->id }}" name="libelle"
                                                                    value="{{ $item->libelle }}" required>
                                                            </div>
                                                            <div class="d-flex justify-content-start">
                                                                <button type="submit" class="btn btn-1">
                                                                    <i class="fas fa-save"></i>&nbsp; Enregistrer les
                                                                    modifications
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal de suppression -->
                                        <div class="modal fade" id="deleteCategorieModal{{ $item->id }}"
                                            tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger">
                                                        <h5 class="modal-title text-white" id="deleteModalLabel">
                                                            Confirmation de suppression</h5>
                                                        <button type="button" class="btn-close text-white"
                                                            data-bs-dismiss="modal" aria-label="Close">X</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="text-center mb-3">
                                                            <i class="fa fa-exclamation-triangle text-danger"
                                                                style="font-size: 3rem;"></i>
                                                        </div>
                                                        <p class="text-center">
                                                            Êtes-vous sûr de vouloir supprimer Type de Client
                                                            <strong>{{ $item->libelle }}</strong> ?
                                                        </p>
                                                        <p class="text-center text-muted">
                                                            Cette action est irréversible et supprimera également tous les
                                                            éléments associés à ce type de client.
                                                        </p>
                                                    </div>
                                                    <div class="m-3">
                                                        <form
                                                            action="{{ route('module_categorie.destroy', [$item->id]) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-trash"></i>&nbsp; Supprimer
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">
                                                            <i class="fas fa-times"></i>&nbsp; Annuler
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $collection->links() }}
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
                            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucun type de client trouvé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal d'ajout -->
        <div class="modal fade" id="createCategorieModal" tabindex="-1" aria-labelledby="createCategorieModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('module_categorie.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createCategorieModalLabel">Ajouter un nouveau type de client</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Code <span class="text-danger">*</span></label>
                                        <input class="form-control" name="code" type="text" placeholder="CAT-001"
                                            value="{{ Request::old('code') }}" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Libellé <span class="text-danger">*</span></label>
                                        <input class="form-control" name="libelle" type="text"
                                            placeholder="Nom de la ligne métier" value="{{ Request::old('libelle') }}"
                                            required />
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
            var el = document.getElementById('createCategorieModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
        });
        @endif
    </script>
@endsection
