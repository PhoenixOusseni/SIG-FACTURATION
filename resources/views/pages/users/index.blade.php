@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Utilisateurs</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="users" title="Gestion des utilisateurs">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-plus me-1"></i> Nouvel utilisateur
            </button>
        </x-page-header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i data-feather="list" style="width: 20px; height: 20px; margin-right: 0.5rem;"></i>
                        Liste des utilisateurs
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createUserModal">
                        <i class="fas fa-plus me-1"></i> Nouvel utilisateur
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher un utilisateur..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Login</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->nom }}</td>
                                            <td>{{ $item->prenom }}</td>
                                            <td><span style="font-weight: 600; color: var(--primary-color);">{{ $item->login }}</span></td>
                                            <td>{{ $item->email }}</td>
                                            <td><span class="badge badge-primary">{{ $item->role }}</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('module_utilisateur.show', [$item->id]) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 2rem; color: var(--text-light);">
                            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucun utilisateur trouvé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal création utilisateur --}}
        <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('module_utilisateur.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createUserModalLabel">Ajouter un nouvel utilisateur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />
                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input class="form-control" name="nom" type="text" placeholder="Henry"
                                            value="{{ old('nom') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                        <input class="form-control" name="prenom" type="text" placeholder="Mitchel"
                                            value="{{ old('prenom') }}" required />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input class="form-control" name="email" type="email"
                                            placeholder="utilisateur@exemple.com" value="{{ old('email') }}" required />
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Rôle <span class="text-danger">*</span></label>
                                        <select class="form-select" name="role" required>
                                            <option value="">Sélectionner ici...</option>
                                            <option value="Administrateur" {{ old('role') == 'Administrateur' ? 'selected' : '' }}>Administrateur</option>
                                            <option value="Superviseur" {{ old('role') == 'Superviseur' ? 'selected' : '' }}>Superviseur</option>
                                            <option value="Manager" {{ old('role') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="Seniore" {{ old('role') == 'Seniore' ? 'selected' : '' }}>Sénior</option>
                                            <option value="Junior" {{ old('role') == 'Junior' ? 'selected' : '' }}>Junior</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Module <span class="text-danger">*</span></label>
                                        <select class="form-select" name="module" required>
                                            <option value="">Sélectionner ici...</option>
                                            <option value="facturation" {{ old('module') == 'facturation' ? 'selected' : '' }}>Facturation</option>
                                            <option value="tous" {{ old('module') == 'tous' ? 'selected' : '' }}>Tous</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Activation <span class="text-danger">*</span></label>
                                        <select class="form-select" name="activate" required>
                                            <option value="">Sélectionner ici...</option>
                                            <option value="1" {{ old('activate', '1') == '1' ? 'selected' : '' }}>Activer</option>
                                            <option value="0" {{ old('activate') === '0' ? 'selected' : '' }}>Désactiver</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
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
            var el = document.getElementById('createUserModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
        });
        @endif
    </script>
@endsection
