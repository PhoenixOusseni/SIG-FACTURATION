@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Bordereaux de livraison</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <header class="page-header pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="truck"></i></div>
                                Gestion des bordereaux de livraison
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <div style="background: rgba(255,255,255,0.2); padding: 0.75rem 1.5rem; border-radius: 0.5rem; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
                                <i data-feather="calendar" style="width: 18px; height: 18px;"></i>
                                {{ Carbon\Carbon::now()->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            {{-- Ligne d'actions rapides --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <strong>Workflow :</strong>
                            <span class="badge bg-primary">1. Proforma</span>
                            <i data-feather="arrow-right" style="width:16px;height:16px;"></i>
                            <span class="badge bg-warning text-dark">2. Bordereau de livraison</span>
                            <i data-feather="arrow-right" style="width:16px;height:16px;"></i>
                            <span class="badge bg-success">3. Facture définitive</span>
                        </div>
                        {{-- <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newDirectBLModal">
                            <i data-feather="plus"></i> Créer un BL direct (sans proforma)
                        </button> --}}
                    </div>
                </div>
            </div>

            {{-- Section : transformer une proforma --}}
            @if ($proformas->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        Transformer une facture proforma en bordereau de livraison
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Référence</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($proformas->where('statut', '!=', 'transformée')->take(10) as $p)
                                        <tr>
                                            <td><strong>{{ $p->code }}</strong></td>
                                            <td>{{ $p->reference }}</td>
                                            <td>{{ optional($p->Contribuable)->assujeti }}</td>
                                            <td>{{ $p->date }}</td>
                                            <td><span class="badge bg-secondary">{{ $p->statut }}</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('bordereau_from_proforma', $p->id) }}"
                                                    class="btn btn-sm btn-warning"
                                                    onclick="return confirm('Transformer cette proforma en bordereau de livraison ?')">
                                                    <i data-feather="repeat" style="width:14px;height:14px;"></i>
                                                    Transformer
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Liste des BL --}}
        <div class="container-xl px-4">
            <div class="card mb-4">
                <div class="card-header">Liste des bordereaux de livraison</div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher un bordereau..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Référence</th>
                                        <th>Client</th>
                                        <th>Depot</th>
                                        <th>Origine</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $bl)
                                        <tr>
                                            <td><span style="font-weight:600; color:var(--primary-color);">{{ $bl->code }}</span></td>
                                            <td>{{ $bl->reference }}</td>
                                            <td>{{ optional($bl->Contribuable)->assujeti }}</td>
                                            <td>{{ optional($bl->Service)->libelle ?? 'N/A' }}</td>
                                            <td>
                                                @if ($bl->FactureProforma)
                                                    <span class="badge bg-info">Proforma {{ $bl->FactureProforma->code }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Direct</span>
                                                @endif
                                            </td>
                                            <td>{{ $bl->date }}</td>
                                            <td>
                                                @if ($bl->statut === 'facturé')
                                                    <span class="badge bg-success">{{ $bl->statut }}</span>
                                                @elseif ($bl->statut === 'livré')
                                                    <span class="badge bg-primary">{{ $bl->statut }}</span>
                                                @elseif ($bl->statut === 'annulé')
                                                    <span class="badge bg-danger">{{ $bl->statut }}</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ $bl->statut }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('module_bordereau_livraison.show', $bl->id) }}"
                                                    class="btn btn-sm btn-primary"
                                                    style="border-radius:50%; width:32px; height:32px; padding:0; display:inline-flex; align-items:center; justify-content:center;">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align:center; padding:3rem 1rem; color:var(--text-light);">
                            <i data-feather="inbox" style="width:48px; height:48px; margin-bottom:1rem; opacity:0.5;"></i>
                            <p>Aucun bordereau de livraison enregistré</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Modal : création directe ===== --}}
        <div class="modal fade" id="newDirectBLModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouveau bordereau de livraison (direct)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                    </div>
                    <form action="{{ route('module_bordereau_livraison.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="small">Client <span class="text-danger">*</span></label>
                                    <select name="contribuables_id" class="form-select" required style="width:100%">
                                        <option value="">Sélectionner le client</option>
                                        @foreach ($contribuables as $c)
                                            <option value="{{ $c->id }}">{{ $c->assujeti }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="small">Ligne métier</label>
                                    <select name="categorie_id" class="form-select">
                                        <option value="">Sélectionner</option>
                                        @foreach (App\Models\Categorie::all() as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="small">Ligne de service <span class="text-danger">*</span></label>
                                    <select name="service_id" class="form-select" required>
                                        <option value="">Sélectionner</option>
                                        @foreach (App\Models\Service::all() as $s)
                                            <option value="{{ $s->id }}">{{ $s->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="small">Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Référence</label>
                                    <input type="text" name="reference" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Transporteur</label>
                                    <input type="text" name="transporteur" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Livreur</label>
                                    <input type="text" name="livreur" class="form-control">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="small">Remise 1</label>
                                    <input type="number" step="0.01" min="0" name="remise_1" class="form-control" value="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="small">Remise 2</label>
                                    <input type="number" step="0.01" min="0" name="remise_2" class="form-control" value="0">
                                </div>
                            </div>

                            <fieldset class="mb-3" style="border:1px solid var(--border-color); border-radius:.5rem; padding:1rem;">
                                <legend style="font-weight:600; color:var(--primary-color); padding:0 .5rem;">Éléments</legend>
                                <div id="blElementsContainer">
                                    <div class="row mb-2 bl-element-row">
                                        <div class="col-md-4">
                                            <label class="small">Produit</label>
                                            <select name="produits_id[]" class="form-select" style="width:100%">
                                                <option value="">Sélectionner</option>
                                                @foreach ($produits as $p)
                                                    <option value="{{ $p->id }}">{{ $p->code }} - {{ $p->designation }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="small">Désignation</label>
                                            <input type="text" name="designation[]" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small">Qté</label>
                                            <input type="number" step="0.01" name="quantite[]" class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="small">Prix U.</label>
                                            <input type="number" step="0.01" name="prix_unitaire[]" class="form-control">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-success btn-sm" id="blAddRow">
                                                <i data-feather="plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="mb-3">
                                <label class="small">Observation</label>
                                <textarea name="observation" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="m-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
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
        $(document).ready(function() {
            const $modal = $('#newDirectBLModal');

            let baseOptions = `
                <option value="">Sélectionner un produit</option>
                @foreach ($produits as $p)
                    <option value="{{ $p->id }}">{{ $p->code }} - {{ $p->designation }}</option>
                @endforeach
            `;

            function refreshIcons() {
                if (typeof feather !== 'undefined') feather.replace();
            }

            // Initialise les Select2 d'un conteneur en s'ancrant au modal si concerné
            function initSelect2(scope) {
                const $scope = $(scope);
                const inModal = $scope.closest('.modal').length > 0;
                $scope.find('.select2-input').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) return;
                    $(this).select2({
                        placeholder: 'Sélectionner...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: inModal ? $(this).closest('.modal') : $(document.body)
                    });
                });
            }

            $(document).on('click', '#blAddRow', function() {
                let row = `
                    <div class="row mb-2 bl-element-row">
                        <div class="col-md-4">
                            <label class="small">Produit</label>
                            <select name="produits_id[]" class="form-select select2-input" style="width:100%">${baseOptions}</select>
                        </div>
                        <div class="col-md-3">
                            <label class="small">Désignation</label>
                            <input type="text" name="designation[]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="small">Qté</label>
                            <input type="number" step="0.01" name="quantite[]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="small">Prix U.</label>
                            <input type="number" step="0.01" name="prix_unitaire[]" class="form-control">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm blRemoveRow">
                                <i data-feather="trash-2"></i>
                            </button>
                        </div>
                    </div>`;
                $('#blElementsContainer').append(row);
                initSelect2($('#blElementsContainer .bl-element-row').last());
                refreshIcons();
            });

            $(document).on('click', '.blRemoveRow', function() {
                if ($('.bl-element-row').length > 1) $(this).closest('.bl-element-row').remove();
            });

            // Initialiser uniquement les selects hors modal au chargement
            initSelect2($('main'));

            // Initialiser les selects du modal seulement quand il est affiché
            $modal.on('shown.bs.modal', function() {
                initSelect2($modal);
            });

            refreshIcons();
        });
    </script>
@endsection
