@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Répertoire des Clients</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <x-page-header icon="users" title="Répertoire des Clients" subtitle="Gestion centralisée du portefeuille clients, assujettis fiscaux DGI et coordonnées">
            <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createClientModal">
                <i class="fas fa-plus-circle me-1"></i> Nouveau client
            </button>
        </x-page-header>

        <div class="container-fluid px-4">
            <x-form-errors class="mb-3" />

            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="fw-bold text-dark fs-6">
                        <i data-feather="users" style="width: 18px; height: 18px; margin-right: 0.5rem;" class="text-primary"></i>
                        Liste des Clients Enregistrés ({{ $collection->total() }})
                    </span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" placeholder="Rechercher par nom, IFU, RCCM, téléphone..." class="form-control"
                                id="searchInput" onkeyup="searchTable()" />
                        </div>
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100 mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom / Raison Sociale</th>
                                        <th>Type Client</th>
                                        <th>IFU / RCCM</th>
                                        <th>Téléphone</th>
                                        <th>Adresse</th>
                                        <th style="min-width: 140px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $client)
                                        @php
                                            $catCode = $client->Categorie ? $client->Categorie->code : ($client->type_client ?? 'CC');
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-bold font-monospace text-primary">
                                                    {{ $client->code ?? 'CLI-' . str_pad($client->id, 4, '0', STR_PAD_LEFT) }}
                                                </span>
                                            </td>
                                            <td class="fw-bold">{{ $client->assujeti }}</td>
                                            <td>
                                                @if ($catCode === 'PM')
                                                    <span class="badge bg-primary text-white">Société (PM)</span>
                                                @elseif ($catCode === 'PP')
                                                    <span class="badge bg-info text-dark">Particulier (PP)</span>
                                                @elseif ($catCode === 'PC')
                                                    <span class="badge bg-warning text-dark">Commerçant (PC)</span>
                                                @else
                                                    <span class="badge bg-secondary text-white">Comptant (CC)</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($client->ifu)
                                                    <span class="font-monospace fw-bold text-dark">{{ $client->ifu }}</span>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                                @if($client->rccm)
                                                    <br><small class="text-muted font-monospace">{{ $client->rccm }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $client->telephone ?? '—' }}</td>
                                            <td class="small text-muted">{{ Str::limit($client->adresse ?: '—', 25) }}</td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    {{-- Voir Fiche --}}
                                                    <a href="{{ route('module_contribuable.show', $client->id) }}"
                                                        class="btn btn-outline-info" title="Consulter la fiche client">
                                                        <i class="fa fa-eye"></i>
                                                    </a>

                                                    {{-- Modifier --}}
                                                    <button type="button" class="btn btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}"
                                                        title="Modifier les coordonnées du client">
                                                        <i class="fa fa-edit"></i>
                                                    </button>

                                                    {{-- Imprimer Fiche --}}
                                                    <a href="{{ route('print_fiche_contribuable', $client->id) }}"
                                                        target="_blank" class="btn btn-outline-secondary" title="Imprimer la fiche légale client">
                                                        <i class="fa fa-print"></i>
                                                    </a>

                                                    {{-- Nouvelle Facture --}}
                                                    <a href="{{ route('module_ordre_facture.index') }}?client_id={{ $client->id }}"
                                                        class="btn btn-outline-success" title="Facturer ce client">
                                                        <i class="fa fa-file-invoice"></i>
                                                    </a>

                                                    {{-- Supprimer --}}
                                                    <form action="{{ route('module_contribuable.destroy', $client->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le client {{ $client->assujeti }} ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer ce client">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if (method_exists($collection, 'links'))
                                <div class="mt-3">
                                    {{ $collection->links() }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
                            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucun client trouvé dans le répertoire.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal création client --}}
        <div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <form action="{{ route('module_contribuable.store') }}" method="POST" enctype="multipart/form-data" class="modal-content shadow-lg border-0">
                    @csrf
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title" id="createClientModalLabel">
                            <i class="fas fa-user-plus me-2"></i> Ajouter un nouveau client
                        </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body p-4">
                            <x-form-errors />

                            <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2" id="clientTypeHelp">
                                <i class="fas fa-info-circle fa-lg"></i>
                                <span id="clientTypeHelpText">Sélectionnez la catégorie du client pour ajuster les obligations fiscales DGI.</span>
                            </div>

                            <fieldset class="mb-4"
                                style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                                <legend style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                    📋 Classification DGI & Identité du Client</legend>

                                <div class="row">
                                    <div class="col-lg-12 col-md-12">
                                        {{-- UN SEUL SELECT UNIQUE POUR LA CATÉGORIE DU CLIENT --}}
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold">Type / Catégorie du Client <span class="text-danger">*</span></label>
                                            <select name="categories_id" id="client_type_select" class="form-select form-select-lg" required>
                                                @foreach ($categories as $categorie)
                                                    <option value="{{ $categorie->id }}" data-code="{{ $categorie->code }}"
                                                        {{ old('categories_id', 1) == $categorie->id ? 'selected' : '' }}>
                                                        {{ $categorie->code }} — {{ $categorie->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="type_client" id="hidden_type_client" value="CC">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold" id="label_assujeti">Nom / Raison Sociale <span class="text-danger">*</span></label>
                                            <input class="form-control" id="input_assujeti" name="assujeti" type="text"
                                                placeholder="Ex: SOCIETE ABC ou Nom Prénom" value="{{ old('assujeti', 'CLIENT COMPTANT') }}" required />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold" id="label_ifu">N° IFU <span class="req-star text-danger">*</span></label>
                                            <input class="form-control font-monospace" id="input_ifu" name="ifu" type="text"
                                                placeholder="Ex: 00012345A" value="{{ old('ifu') }}" />
                                            <small class="text-muted d-block" id="help_ifu">Obligatoire pour Personne Morale et Commerçant</small>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold" id="label_rccm">N° RCCM <span class="req-star text-danger">*</span></label>
                                            <input class="form-control font-monospace" id="input_rccm" name="rccm" type="text"
                                                placeholder="Ex: BF OUA 2024 B 1234" value="{{ old('rccm') }}" />
                                            <small class="text-muted d-block" id="help_rccm">Obligatoire pour Personne Morale et Commerçant</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold" id="label_telephone">N° Téléphone <span class="req-star text-danger">*</span></label>
                                            <input class="form-control" id="input_telephone" name="telephone" type="tel"
                                                placeholder="+226 XX XX XX XX" value="{{ old('telephone') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label fw-bold" id="label_adresse">Adresse géographique <span class="req-star text-danger">*</span></label>
                                            <input class="form-control" id="input_adresse" name="adresse" type="text"
                                                placeholder="Ville, Secteur, Rue" value="{{ old('adresse') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Email</label>
                                            <input class="form-control" name="email" type="email"
                                                placeholder="contact@exemple.bf" value="{{ old('email') }}" />
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <fieldset class="mb-2"
                                style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                                <legend style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                    📑 Informations fiscales & cadastrales</legend>
                                <div class="row">
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Division fiscale (Centre des impôts)</label>
                                            <input class="form-control" name="division_fiscal" type="text"
                                                placeholder="Ex: DME4, DGE, Kadiogo" value="{{ old('division_fiscal') }}" />
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Régime fiscal</label>
                                            <select name="regime_fiscal" class="form-select">
                                                <option value="">-- Non spécifié --</option>
                                                <option value="RNI" {{ old('regime_fiscal') == 'RNI' ? 'selected' : '' }}>RNI — Réel Normal</option>
                                                <option value="RSI" {{ old('regime_fiscal') == 'RSI' ? 'selected' : '' }}>RSI — Réel Simplifié</option>
                                                <option value="CME" {{ old('regime_fiscal') == 'CME' ? 'selected' : '' }}>CME — Micro-Entreprise</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-12">
                                        <div class="form-group mb-3">
                                            <label class="form-label">Adresse cadastrale</label>
                                            <input class="form-control font-monospace" name="adresse_cadas" type="text"
                                                placeholder="Ex: 0012-004-0018" value="{{ old('adresse_cadas') }}" />
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="modal-footer bg-light py-3" style="position: sticky; bottom: 0; z-index: 1050; border-top: 1px solid #dee2e6; background: #f8fafc;">
                            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Annuler</button>
                            <button class="btn btn-primary px-4 fw-bold" type="submit">
                                <i class="fas fa-save me-2"></i> Enregistrer le client
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        {{-- Modales d'édition extraites de tout conteneur mt-n10 (résout le bug du modal grisé/inaccessible) --}}
        @foreach ($collection as $client)
            <div class="modal fade text-start" id="editClientModal{{ $client->id }}" tabindex="-1" aria-labelledby="editClientModalLabel{{ $client->id }}" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <form action="{{ route('module_contribuable.update', $client->id) }}" method="POST" class="modal-content shadow-lg border-0">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-primary text-white py-3">
                            <h5 class="modal-title" id="editClientModalLabel{{ $client->id }}">
                                <i class="fa fa-edit me-2"></i> Modifier les coordonnées du client : {{ $client->assujeti }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body p-4" style="overflow-y: auto;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Catégorie / Type de Client <span class="text-danger">*</span></label>
                                    <select name="categories_id" class="form-select form-select-lg" required>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ $client->categories_id == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->code }} — {{ $cat->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nom / Raison Sociale <span class="text-danger">*</span></label>
                                    <input class="form-control form-control-lg" name="assujeti" type="text" value="{{ old('assujeti', $client->assujeti) }}" required />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">N° IFU</label>
                                    <input class="form-control font-monospace" name="ifu" type="text" value="{{ old('ifu', $client->ifu) }}" placeholder="Ex: 00012345A" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">N° RCCM</label>
                                    <input class="form-control font-monospace" name="rccm" type="text" value="{{ old('rccm', $client->rccm) }}" placeholder="Ex: BF OUA 2024 B 1234" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Téléphone</label>
                                    <input class="form-control" name="telephone" type="tel" value="{{ old('telephone', $client->telephone) }}" placeholder="+226 XX XX XX XX" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adresse Géographique</label>
                                    <input class="form-control" name="adresse" type="text" value="{{ old('adresse', $client->adresse) }}" placeholder="Ville, Secteur, Rue" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input class="form-control" name="email" type="email" value="{{ old('email', $client->email) }}" placeholder="contact@client.bf" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Division Fiscale / CDI</label>
                                    <input class="form-control" name="division_fiscal" type="text" value="{{ old('division_fiscal', $client->division_fiscal) }}" placeholder="Ex: DME, DGE, Kadiogo" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Régime Fiscal</label>
                                    <select name="regime_fiscal" class="form-select">
                                        <option value="">-- Non spécifié --</option>
                                        <option value="RNI" {{ $client->regime_fiscal == 'RNI' ? 'selected' : '' }}>RNI — Réel Normal</option>
                                        <option value="RSI" {{ $client->regime_fiscal == 'RSI' ? 'selected' : '' }}>RSI — Réel Simplifié</option>
                                        <option value="CME" {{ $client->regime_fiscal == 'CME' ? 'selected' : '' }}>CME — Micro-Entreprise</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Adresse Cadastrale (Format: XXXX-YYY-ZZZZ)</label>
                                    <input class="form-control font-monospace" name="adresse_cadas" type="text" value="{{ old('adresse_cadas', $client->adresse_cadas) }}" placeholder="XXXX-YYY-ZZZZ" pattern="^[A-Za-z0-9]{4}-[A-Za-z0-9]{3}-[A-Za-z0-9]{4}$" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3" style="position: sticky; bottom: 0; z-index: 1050; border-top: 1px solid #dee2e6;">
                            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Annuler</button>
                            <button class="btn btn-primary px-4 fw-bold" type="submit">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </main>
@endsection

@section('script')
    <script>
        function updateClientFormRules() {
            const select = document.getElementById('client_type_select');
            if (!select) return;
            const opt = select.options[select.selectedIndex];
            const type = opt ? (opt.getAttribute('data-code') || 'CC') : 'CC';

            const hiddenType = document.getElementById('hidden_type_client');
            if (hiddenType) hiddenType.value = type;

            const ifuInput = document.getElementById('input_ifu');
            const rccmInput = document.getElementById('input_rccm');
            const phoneInput = document.getElementById('input_telephone');
            const addrInput = document.getElementById('input_adresse');
            const nameInput = document.getElementById('input_assujeti');
            const helpText = document.getElementById('clientTypeHelpText');

            const stars = {
                ifu: document.querySelector('#label_ifu .req-star'),
                rccm: document.querySelector('#label_rccm .req-star'),
                phone: document.querySelector('#label_telephone .req-star'),
                addr: document.querySelector('#label_adresse .req-star'),
            };

            const helpIfu = document.getElementById('help_ifu');
            const helpRccm = document.getElementById('help_rccm');

            if (type === 'PM') {
                if (helpText) helpText.innerHTML = '<strong>Personne Morale (Entreprise)</strong> : Raison sociale, IFU, RCCM, Téléphone et Adresse géographique sont <strong>obligatoires</strong> (Norme DGI).';
                if (ifuInput) ifuInput.required = true;
                if (rccmInput) rccmInput.required = true;
                if (phoneInput) phoneInput.required = true;
                if (addrInput) addrInput.required = true;
                Object.values(stars).forEach(s => s && (s.style.display = 'inline'));
                if (helpIfu) helpIfu.textContent = 'Obligatoire (Personne Morale)';
                if (helpRccm) helpRccm.textContent = 'Obligatoire (Personne Morale)';
                if (nameInput && nameInput.value === 'CLIENT COMPTANT') nameInput.value = '';
            } else if (type === 'PC') {
                if (helpText) helpText.innerHTML = '<strong>Personne Physique Commerçante</strong> : Nom, IFU, RCCM, Téléphone et Adresse sont <strong>obligatoires</strong> (Norme DGI).';
                if (ifuInput) ifuInput.required = true;
                if (rccmInput) rccmInput.required = true;
                if (phoneInput) phoneInput.required = true;
                if (addrInput) addrInput.required = true;
                Object.values(stars).forEach(s => s && (s.style.display = 'inline'));
                if (helpIfu) helpIfu.textContent = 'Obligatoire (Commerçant)';
                if (helpRccm) helpRccm.textContent = 'Obligatoire (Commerçant)';
                if (nameInput && nameInput.value === 'CLIENT COMPTANT') nameInput.value = '';
            } else if (type === 'PP') {
                if (helpText) helpText.innerHTML = '<strong>Personne Physique (Particulier)</strong> : Nom, Téléphone et Adresse sont obligatoires. IFU et RCCM sont optionnels.';
                if (ifuInput) ifuInput.required = false;
                if (rccmInput) rccmInput.required = false;
                if (phoneInput) phoneInput.required = true;
                if (addrInput) addrInput.required = true;
                if (stars.ifu) stars.ifu.style.display = 'none';
                if (stars.rccm) stars.rccm.style.display = 'none';
                if (stars.phone) stars.phone.style.display = 'inline';
                if (stars.addr) stars.addr.style.display = 'inline';
                if (helpIfu) helpIfu.textContent = 'Optionnel (Particulier)';
                if (helpRccm) helpRccm.textContent = 'Optionnel (Particulier)';
                if (nameInput && nameInput.value === 'CLIENT COMPTANT') nameInput.value = '';
            } else {
                // CC
                if (helpText) helpText.innerHTML = '<strong>Client Comptant (Consommateur)</strong> : Vente au comptoir sans identification nominative. Seul le nom par défaut est requis.';
                if (ifuInput) ifuInput.required = false;
                if (rccmInput) rccmInput.required = false;
                if (phoneInput) phoneInput.required = false;
                if (addrInput) addrInput.required = false;
                Object.values(stars).forEach(s => s && (s.style.display = 'none'));
                if (helpIfu) helpIfu.textContent = 'Non requis (Client Comptant)';
                if (helpRccm) helpRccm.textContent = 'Non requis (Client Comptant)';
                if (nameInput && !nameInput.value.trim()) nameInput.value = 'CLIENT COMPTANT';
            }
        }

        function searchTable() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toUpperCase();
            var table = document.querySelector("table tbody");
            var tr = table.getElementsByTagName("tr");
            for (var i = 0; i < tr.length; i++) {
                var text = tr[i].textContent || tr[i].innerText;
                if (text.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('client_type_select');
            if (select) {
                select.addEventListener('change', updateClientFormRules);
                updateClientFormRules();
            }

            @if ($errors->any())
            var el = document.getElementById('createClientModal');
            if (el && window.bootstrap) new bootstrap.Modal(el).show();
            @endif
        });
    </script>
@endsection
