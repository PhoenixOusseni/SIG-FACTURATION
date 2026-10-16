@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Profil Utilisateur</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="user" title="Mon Profil"></x-page-header>
        <!-- Main page content-->

        <div class="container-xl px-4 mt-4">
            <div class="row">
                <div class="col-xl-4">
                    <!-- Profile picture card-->
                    <div class="card mb-4 mb-xl-0">
                        <div class="card-header">Photo de Profil</div>
                        <div class="card-body">
                            <!-- Profile picture image-->
                            <div class="d-flex justify-content-center">
                                <img class="img-account-profile rounded-circle mb-2"
                                        src="{{ asset('images/auth-bg.jpg') }}" alt="logo user" />
                            </div>
                            <!-- Profile picture help block-->
                            <div class="small font-italic text-muted mb-4 text-center">JPG ou PNG pas plus de 5 MB</div>
                            <!-- Profile picture upload button-->
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <!-- Account details card-->
                    <div class="card mb-4">
                        <div class="card-header">Détails du Compte</div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('users.update', $finds->id) }}">
                                @csrf
                                @method('PUT')
                                <!-- Form Row-->
                                <div class="row gx-3 mb-3">
                                    <!-- Form Group (first name)-->
                                    <div class="col-md-6">
                                        <label class="small mb-1" for="inputFirstName">Nom</label>
                                        <input class="form-control" id="inputFirstName" name="nom" type="text" value="{{ $finds->nom }}" />
                                    </div>
                                    <!-- Form Group (last name)-->
                                    <div class="col-md-6">
                                        <label class="small mb-1" for="inputLastName">Prénom</label>
                                        <input class="form-control" id="inputLastName" name="prenom" type="text" value="{{ $finds->prenom }}" />
                                    </div>
                                </div>
                                <div class="row gx-3 mb-3">
                                    <!-- Form Group (username - read only)-->
                                    <div class="col-md-4">
                                        <label class="small mb-1" for="inputUsername">Nom utilisateur</label>
                                        <input class="form-control" id="inputUsername" type="text" value="{{ $finds->login }}" disabled />
                                    </div>
                                    <!-- Form Group (role - read only)-->
                                    <div class="col-md-4">
                                        <label class="small mb-1" for="inputRole">Rôle</label>
                                        <input class="form-control" id="inputRole" type="text" value="{{ $finds->role }}" disabled />
                                    </div>
                                    <!-- Form Group (email)-->
                                    <div class="col-md-4">
                                        <label class="small mb-1" for="inputEmail">Email</label>
                                        <input class="form-control" id="inputEmail" name="email" type="email" value="{{ $finds->email }}" />
                                    </div>
                                </div>
                                <!-- Form Actions-->
                                <div class="row">
                                    <div class="col-md-12">
                                        <button class="btn btn-1" type="submit">
                                            <i class="fas fa-save"></i>
                                            &nbsp; &nbsp;Enregistrer les modifications
                                        </button>
                                        <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#formPasswordBackdrop">
                                            <i class="fas fa-lock"></i>
                                            &nbsp; &nbsp;Changer mon mot de passe
                                        </button>
                                        @if(auth()->user()->role === 'admin')
                                            <a href="{{ url('supp_user/' . $finds->id) }}" class="btn btn-danger" type="button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <i class="fas fa-trash"></i>
                                                &nbsp; &nbsp;Supprimer l'utilisateur
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity History Card -->
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Historique des Activités</span>
                            <small class="text-muted">Dernières actions sur votre compte</small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Action</th>
                                            <th>Description</th>
                                            <th>Adresse IP</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activityTableBody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Chargement...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->
                            <nav id="activityPagination"></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal password -->
        <div class="modal fade" id="formPasswordBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-default">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="staticBackdropLabel">Changer mon mot de passe</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <form method="POST" action="{{ url('change_password/' . $finds->id) }}">
                                @csrf
                                <div class="p-2 m-1">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12">
                                            <div class="mb-3">
                                                <label>Nouveau mot de passe<span class="text-danger">*</span></label>
                                                <input class="form-control" name="password" type="password" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-1">
                                        <i class="fas fa-edit"></i>
                                        &nbsp; &nbsp;Changer
                                    </button>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        <i class="fas fa-close"></i>
                                        &nbsp; &nbsp;Fermer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        // Load user activities on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadActivityHistory(1);
        });

        function loadActivityHistory(page = 1) {
            const userId = {{ $finds->id }};
            fetch(`/api/users/${userId}/activities?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    displayActivities(data);
                })
                .catch(error => {
                    console.error('Error loading activities:', error);
                    document.getElementById('activityTableBody').innerHTML = 
                        '<tr><td colspan="4" class="text-center text-danger">Erreur lors du chargement des activités</td></tr>';
                });
        }

        function displayActivities(data) {
            const tableBody = document.getElementById('activityTableBody');
            const pagination = document.getElementById('activityPagination');

            if (data.data && data.data.length > 0) {
                tableBody.innerHTML = data.data.map(activity => `
                    <tr>
                        <td><small>${new Date(activity.created_at).toLocaleString('fr-FR')}</small></td>
                        <td>
                            <span class="badge bg-info">${getActionBadge(activity.action)}</span>
                        </td>
                        <td>${activity.description || '-'}</td>
                        <td><small>${activity.ip_address || '-'}</small></td>
                    </tr>
                `).join('');
            } else {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Aucune activité enregistrée</td></tr>';
            }

            // Build pagination
            if (data.last_page > 1) {
                let paginationHtml = '<ul class="pagination justify-content-center">';
                
                if (data.current_page > 1) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadActivityHistory(1); return false;">Première</a></li>`;
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadActivityHistory(${data.current_page - 1}); return false;">Précédent</a></li>`;
                }

                for (let i = 1; i <= data.last_page; i++) {
                    if (i === data.current_page) {
                        paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadActivityHistory(${i}); return false;">${i}</a></li>`;
                    }
                }

                if (data.current_page < data.last_page) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadActivityHistory(${data.current_page + 1}); return false;">Suivant</a></li>`;
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadActivityHistory(${data.last_page}); return false;">Dernière</a></li>`;
                }

                paginationHtml += '</ul>';
                pagination.innerHTML = paginationHtml;
            }
        }

        function getActionBadge(action) {
            const actions = {
                'login': 'Connexion',
                'logout': 'Déconnexion',
                'update_profile': 'Modification Profil',
                'change_password': 'Changement Mot de passe',
                'account_deleted': 'Compte Supprimé'
            };
            return actions[action] || action;
        }
    </script>
@endsection
