@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Fournisseurs</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="truck" title="Gestion des fournisseurs">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createFournisseurModal">
                <i class="fas fa-plus me-1"></i> Nouveau fournisseur
            </button>
        </x-page-header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i data-feather="list" style="width: 20px; height: 20px; margin-right: 0.5rem;"></i>
                        Liste des fournisseurs
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createFournisseurModal">
                        <i class="fas fa-plus me-1"></i> Nouveau fournisseur
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher un fournisseur..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Désignation</th>
                                        <th>Téléphone</th>
                                        <th>IFU</th>
                                        <th>Catégorie</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $fournisseur)
                                        <tr>
                                            <td>
                                                <span style="font-weight: 600; color: var(--primary-color);">
                                                    {{ $fournisseur->id }}
                                                </span>
                                            </td>
                                            <td>{{ $fournisseur->assujeti }}</td>
                                            <td>{{ $fournisseur->telephone ?? 'N/A' }}</td>
                                            <td>{{ $fournisseur->ifu ?? '—' }}</td>
                                            <td>
                                                @if ($fournisseur->Categorie)
                                                    <span class="badge badge-primary text-dark">
                                                        {{ $fournisseur->Categorie->libelle }}
                                                    </span>
                                                @else
                                                    <span class="badge"
                                                        style="background: var(--border-color); color: var(--text-dark);">
                                                        Non assignée
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('module_fournisseur.show', $fournisseur->id) }}"
                                                    class="btn btn-sm btn-success">
                                                    <i class="fa fa-eye"></i>
                                                </a>
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
                            <p>Aucun fournisseur trouvé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal création fournisseur --}}
        <div class="modal fade" id="createFournisseurModal" tabindex="-1" aria-labelledby="createFournisseurModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('module_fournisseur.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createFournisseurModalLabel">Ajouter un nouveau fournisseur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />

                            <fieldset class="mb-4"
                                style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                                <legend style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                    📋 Informations générales
                                </legend>

                                <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Désignation <span class="text-danger">*</span></label>
                                            <input class="form-control" name="assujeti" type="text"
                                                placeholder="Nom du fournisseur" value="{{ old('assujeti') }}" required />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Adresse postale</label>
                                            <input class="form-control" name="adresse" type="text"
                                                placeholder="Adresse physique" value="{{ old('adresse') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Adresse (localisation)</label>
                                            <input class="form-control" name="adresse_localisation" type="text"
                                                placeholder="Adresse (localisation)"
                                                value="{{ old('adresse_localisation') }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-lg-3 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">N° Téléphone</label>
                                            <input class="form-control" name="telephone" type="tel"
                                                placeholder="+229 XX XX XX XX" value="{{ old('telephone') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">N° IFU</label>
                                            <input class="form-control" name="ifu" type="text"
                                                placeholder="Identifiant fiscal unique" value="{{ old('ifu') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">RCCM</label>
                                            <input class="form-control" name="rccm" type="text"
                                                placeholder="Registre du commerce" value="{{ old('rccm') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                            <select name="categories_id" class="form-select" required>
                                                <option value="">Sélectionner une catégorie</option>
                                                @foreach ($categories as $categorie)
                                                    <option value="{{ $categorie->id }}"
                                                        {{ old('categories_id') == $categorie->id ? 'selected' : '' }}>
                                                        {{ $categorie->code }} - {{ $categorie->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-2"
                                style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                                <legend style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                    📑 Informations fiscales
                                </legend>
                                <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Division fiscale</label>
                                            <input class="form-control" name="division_fiscal" type="text"
                                                placeholder="Division" value="{{ old('division_fiscal') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Régime fiscal</label>
                                            <input class="form-control" name="regime_fiscal" type="text"
                                                placeholder="Régime" value="{{ old('regime_fiscal') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">Adresse cadastrale</label>
                                            <input class="form-control" name="adresse_cadas" type="text"
                                                placeholder="Adresse" value="{{ old('adresse_cadas') }}" />
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('createFournisseurModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
        });
        @endif
    </script>
@endsection
