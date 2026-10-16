@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Produits</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
    <main>
        <header class="page-header page-header-dark header-gradient pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="package"></i></div>
                                Liste des produits
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#createProduitModal">
                                <i class="fas fa-plus me-1"></i> Nouveau produit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            {{-- Panneau de filtres --}}
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center gap-2">
                    <i data-feather="filter" style="width:16px;height:16px;"></i>
                    Filtrer les produits
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('module_produit.index') }}" id="filterForm">
                        <div class="row gx-3 align-items-end">
                            <div class="col-md-4">
                                <label class="small mb-1">Famille produit</label>
                                <select name="famille_produit" class="form-select select2-filter">
                                    <option value="">-- Toutes --</option>
                                    <option value="LOCBIE" {{ request('famille_produit') === 'LOCBIE' ? 'selected' : '' }}>LOCBIE — Bien Local</option>
                                    <option value="IMPBIE" {{ request('famille_produit') === 'IMPBIE' ? 'selected' : '' }}>IMPBIE — Bien Importé</option>
                                    <option value="LOCSER" {{ request('famille_produit') === 'LOCSER' ? 'selected' : '' }}>LOCSER — Service Local</option>
                                    <option value="IMPSER" {{ request('famille_produit') === 'IMPSER' ? 'selected' : '' }}>IMPSER — Service Importé</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="small mb-1">Groupe TVA</label>
                                <select name="tva_id" class="form-select select2-filter">
                                    <option value="">-- Toutes --</option>
                                    @foreach ($tvas as $tva)
                                        <option value="{{ $tva->id }}" {{ request('tva_id') == $tva->id ? 'selected' : '' }}>
                                            {{ $tva->intitule }} — {{ $tva->taux }} %
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-1 w-100">
                                    <i class="fas fa-search me-1"></i> Filtrer
                                </button>
                                <a href="{{ route('module_produit.index') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-times me-1"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        Liste des produits
                        <span class="badge bg-secondary ms-2">{{ $collection->total() }}</span>
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createProduitModal">
                        <i class="fas fa-plus me-1"></i> Nouveau produit
                    </button>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end mb-3">
                        <input type="text" placeholder="Rechercher..." class="form-control" style="max-width: 280px;"
                            id="searchInput" onkeyup="searchTable()">
                    </div>

                    @if (request()->hasAny(['famille_produit', 'tva_id']))
                        <div class="alert alert-info py-2 mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-filter"></i>
                            <span>Filtres actifs :</span>
                            @if (request('famille_produit'))
                                <span class="badge bg-primary">Famille : {{ request('famille_produit') }}</span>
                            @endif
                            @if (request('tva_id'))
                                @php $activeTva = $tvas->find(request('tva_id')); @endphp
                                <span class="badge bg-primary">TVA : {{ $activeTva ? $activeTva->intitule . ' ' . $activeTva->taux . '%' : request('tva_id') }}</span>
                            @endif
                        </div>
                    @endif

                    <div class="sbp-preview-content table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Désignation</th>
                                    <th class="text-center">Unité</th>
                                    <th class="text-center">Stock disponible</th>
                                    <th class="text-center">Prix unitaire</th>
                                    <th class="text-center">TVA (%)</th>
                                    <th class="text-center">PSVBB (%)</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($collection as $item)
                                    <tr>
                                        <td>{{ $item->code }}</td>
                                        <td>{{ $item->designation }}</td>
                                        <td>{{ $item->unite }}</td>
                                        <td class="text-center">
                                            @php $qte = (float) ($item->qte ?? 0); @endphp
                                            @if ($qte <= 0)
                                                <span class="badge bg-danger">{{ number_format($qte, 2, ',', ' ') }}</span>
                                            @elseif ($qte <= 5)
                                                <span class="badge bg-warning text-dark">{{ number_format($qte, 2, ',', ' ') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ number_format($qte, 2, ',', ' ') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->prix_unitaire ?? 0, 2, ',', ' ') }}</td>
                                        <td>{{ $item->tva_id ? optional($item->tva)->taux : '' }}%</td>
                                        <td>{{ $item->bic_id ? optional($item->bic)->taux : '' }}%</td>
                                        <td class="d-flex align-items-center justify-content-center">
                                            <a href="{{ route('module_produit.show', [$item->id]) }}" class="btn btn-sm btn-light">
                                                <i class="fa fa-eye text-success mx-2" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Aucun produit ne correspond aux critères sélectionnés.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $collection->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal création produit --}}
        <div class="modal fade" id="createProduitModal" tabindex="-1" aria-labelledby="createProduitModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form action="{{ route('module_produit.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="createProduitModalLabel">Ajouter un nouveau produit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                        </div>
                        <div class="modal-body">
                            <x-form-errors />
                            <div class="row gx-3 mb-3">
                                <div class="col-md-2">
                                    <label class="small mb-1">Code <span class="text-danger">*</span></label>
                                    <input class="form-control" name="code" type="text" placeholder="Code du produit"
                                        value="{{ old('code') }}" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="small mb-1">Désignation <span class="text-danger">*</span></label>
                                    <input class="form-control" name="designation" type="text" placeholder="Nom du produit"
                                        value="{{ old('designation') }}" required />
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1">Unité</label>
                                    <input class="form-control" name="unite" type="text" placeholder="Ex: kg, litre, pièce"
                                        value="{{ old('unite') }}" />
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1">Prix unitaire</label>
                                    <input class="form-control" name="prix_unitaire" type="number" min="0"
                                        step="0.01" placeholder="0" value="{{ old('prix_unitaire') }}" />
                                </div>
                                <div class="col-md-2">
                                    <label class="small mb-1">Stock initial</label>
                                    <input class="form-control" name="qte" type="number" min="0" step="0.01"
                                        placeholder="0" value="{{ old('qte') }}" />
                                </div>
                            </div>
                            <div class="row gx-3 mb-3">
                                <div class="col-md-3">
                                    <label class="small mb-1">TVA (%)</label>
                                    <select name="tva_id" id="tva_id" class="form-select select2-tva" style="width:100%">
                                        <option value="">-- Sélectionner une TVA --</option>
                                        @foreach ($tvas as $tva)
                                            <option value="{{ $tva->id }}" {{ old('tva_id') == $tva->id ? 'selected' : '' }}>
                                                {{ $tva->intitule }} - {{ $tva->taux }} %
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small mb-1">PSVBB (%)</label>
                                    <select name="bic_id" id="bic_id" class="form-select select2-bic" style="width:100%">
                                        <option value="">-- Sélectionner un PSVBB --</option>
                                        @foreach ($bics as $bic)
                                            <option value="{{ $bic->id }}" {{ old('bic_id') == $bic->id ? 'selected' : '' }}>
                                                {{ $bic->intitule }} - {{ $bic->taux }} %
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small mb-1">Montant TS</label>
                                    <input class="form-control" name="montant_ts" type="number" min="0"
                                        step="0.01" placeholder="0" value="{{ old('montant_ts') }}" />
                                </div>
                                <div class="col-md-3">
                                    <label class="small mb-1">Type produit</label>
                                    <select name="famille_produit" class="form-select">
                                        <option value="">-- Sélectionner --</option>
                                        <option value="LOCBIE" {{ old('famille_produit') == 'LOCBIE' ? 'selected' : '' }}>LOCBIE — Bien Local</option>
                                        <option value="IMPBIE" {{ old('famille_produit') == 'IMPBIE' ? 'selected' : '' }}>IMPBIE — Bien importé</option>
                                        <option value="LOCSER" {{ old('famille_produit') == 'LOCSER' ? 'selected' : '' }}>LOCSER — Service Local</option>
                                        <option value="IMPSER" {{ old('famille_produit') == 'IMPSER' ? 'selected' : '' }}>IMPSER — Service Import</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row gx-3 mb-3">
                                <div class="col-md-6">
                                    <label class="small mb-1">Description TS</label>
                                    <input class="form-control" name="description_ts" type="text"
                                        placeholder="Description de la taxe spécifique" value="{{ old('description_ts') }}" />
                                </div>
                                <div class="col-md-6">
                                    <label class="small mb-1">Description</label>
                                    <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
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
        $(document).ready(function() {
            $('.select2-filter').select2({
                allowClear: true,
                width: '100%'
            });

            function initModalSelect2() {
                $('#createProduitModal .select2-tva, #createProduitModal .select2-bic').each(function() {
                    const $el = $(this);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                    $el.select2({
                        placeholder: 'Sélectionner...',
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#createProduitModal')
                    });
                });
            }

            $('#createProduitModal').on('shown.bs.modal', function() {
                initModalSelect2();
            });

            @if ($errors->any())
                new bootstrap.Modal(document.getElementById('createProduitModal')).show();
            @endif
        });

        function searchTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.sbp-preview-content tbody tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
            });
        }
    </script>
@endsection
