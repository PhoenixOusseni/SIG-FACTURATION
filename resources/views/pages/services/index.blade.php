@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Gestion des dépôts</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="list" title="Gestion des dépôts">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createServiceModal">
                <i class="fas fa-plus me-1"></i> Ajouter un dépôt
            </button>
        </x-page-header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            @if (session('success'))
                <div class="alert alert-success alert-validation alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Liste des dépôts</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createServiceModal">
                        <i class="fas fa-plus me-1"></i> Ajouter un dépôt
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Libellé</th>
                                    <th>Description</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($services as $service)
                                    <tr>
                                        <td>{{ $service->id }}</td>
                                        <td>{{ $service->code }}</td>
                                        <td>{{ $service->libelle }}</td>
                                        <td>{{ $service->description }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('gestion_service.show', $service->id) }}"
                                                class="btn btn-sm btn-light"><i data-feather="eye"></i></a>
                                            <form action="{{ route('gestion_service.destroy', $service->id) }}"
                                                method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce dépôt ?')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Aucun dépôt trouvé</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal création dépôt --}}
        <div class="modal fade" id="createServiceModal" tabindex="-1" aria-labelledby="createServiceModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('gestion_service.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createServiceModalLabel">Ajouter un dépôt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />
                            <div class="mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code"
                                    value="{{ old('code') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="libelle" name="libelle"
                                    value="{{ old('libelle') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
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
            var el = document.getElementById('createServiceModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
        });
        @endif
        if (typeof feather !== 'undefined') feather.replace();
    </script>
@endsection
