@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Transferts de stock</title>
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
                            <div class="page-header-icon"><i data-feather="shuffle"></i></div>
                            Transferts de stock inter-dépôts
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mt-4">
                        <button type="button" class="btn btn-light" data-bs-toggle="modal"
                            data-bs-target="#createTransfertModal">
                            <i class="fas fa-plus me-1"></i> Nouveau transfert
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10" style="margin-bottom: 8rem;">
        <x-form-errors class="mb-3" />

        {{-- Liste des transferts --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    Liste des transferts
                    <span class="badge bg-secondary ms-2">{{ $collection->total() }}</span>
                </span>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#createTransfertModal">
                    <i class="fas fa-plus me-1"></i> Nouveau transfert
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Date</th>
                                <th>Source</th>
                                <th class="text-center">→</th>
                                <th>Destination</th>
                                <th class="text-center">Statut</th>
                                <th>Opérateur</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($collection as $t)
                                <tr>
                                    <td class="fw-bold" style="color:var(--primary-color)">{{ $t->code }}</td>
                                    <td>{{ $t->date ? \Carbon\Carbon::parse($t->date)->format('d/m/Y') : '—' }}</td>
                                    <td>{{ optional($t->serviceSource)->libelle }}</td>
                                    <td class="text-center text-muted"><i class="fas fa-arrow-right"></i></td>
                                    <td>{{ optional($t->serviceDestination)->libelle }}</td>
                                    <td class="text-center">
                                        @switch($t->statut)
                                            @case('en_attente')
                                                <span class="badge bg-warning text-dark">En attente</span>
                                                @break
                                            @case('valide')
                                                <span class="badge bg-success">Validé</span>
                                                @break
                                            @case('annule')
                                                <span class="badge bg-secondary">Annulé</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ optional($t->user)->nom }} {{ optional($t->user)->prenom }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('gestion_transfert.show', $t->id) }}"
                                            class="btn btn-sm btn-light"
                                            style="width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                            <i class="fa fa-eye text-success"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Aucun transfert enregistré
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $collection->links() }}
            </div>
        </div>
    </div>

    {{-- Modal création transfert --}}
    <div class="modal fade" id="createTransfertModal" tabindex="-1" aria-labelledby="createTransfertModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('gestion_transfert.store') }}" method="POST" id="transfertCreateForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createTransfertModalLabel">Nouveau transfert</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                    </div>
                    <div class="modal-body">
                        <x-form-errors />

                        <fieldset class="mb-4"
                            style="border:1px solid var(--border-color);border-radius:.75rem;padding:1.5rem;">
                            <legend style="font-weight:600;color:var(--primary-color);padding:0 1rem;margin-left:-1rem;">
                                📍 Informations du transfert
                            </legend>
                            <div class="row gx-3">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="small mb-1">Dépôt source <span class="text-danger">*</span></label>
                                        <select name="service_source_id" class="form-select" required>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach ($services as $s)
                                                <option value="{{ $s->id }}"
                                                    {{ old('service_source_id') == $s->id ? 'selected' : '' }}>
                                                    {{ $s->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="small mb-1">Dépôt destination <span class="text-danger">*</span></label>
                                        <select name="service_destination_id" class="form-select" required>
                                            <option value="">-- Sélectionner --</option>
                                            @foreach ($services as $s)
                                                <option value="{{ $s->id }}"
                                                    {{ old('service_destination_id') == $s->id ? 'selected' : '' }}>
                                                    {{ $s->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="small mb-1">Date</label>
                                        <input type="date" name="date" class="form-control"
                                            value="{{ old('date', date('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="small mb-1">Note / Motif</label>
                                        <input type="text" name="note" class="form-control"
                                            placeholder="Motif du transfert..." value="{{ old('note') }}">
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset
                            style="border:1px solid var(--border-color);border-radius:.75rem;padding:1.5rem;background:rgba(49,81,146,.05);">
                            <legend style="font-weight:600;color:var(--primary-color);padding:0 1rem;margin-left:-1rem;">
                                📦 Produits à transférer
                            </legend>
                            <div id="lignesContainer">
                                <div class="row mb-3 ligne-row">
                                    <div class="col-md-8">
                                        <label class="small mb-1">Produit</label>
                                        <select name="produits_id[]" class="form-select select2-produit" style="width:100%"
                                            required>
                                            <option value="">-- Sélectionner un produit --</option>
                                            @foreach ($produits as $p)
                                                <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small mb-1">Quantité</label>
                                        <input type="number" name="quantite[]" class="form-control" min="0.001"
                                            step="0.001" placeholder="0" required>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" id="addLigneBtn" class="btn btn-success btn-sm"
                                            style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;">
                                            <i data-feather="plus" style="width:16px;height:16px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Créer le transfert
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
        let baseOptions = `
            <option value="">-- Sélectionner un produit --</option>
            @foreach ($produits as $p)
                <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->designation }}</option>
            @endforeach
        `;

        function initSelect2($el) {
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                placeholder: 'Sélectionner...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#createTransfertModal')
            });
        }

        function initAllProductSelects() {
            $('#createTransfertModal .select2-produit').each(function() {
                initSelect2($(this));
            });
        }

        $('#createTransfertModal').on('shown.bs.modal', function() {
            initAllProductSelects();
            if (typeof feather !== 'undefined') feather.replace();
        });

        $('#addLigneBtn').on('click', function() {
            const newRow = `
                <div class="row mb-3 ligne-row">
                    <div class="col-md-8">
                        <select name="produits_id[]" class="form-select select2-produit" style="width:100%" required>
                            ${baseOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="quantite[]" class="form-control" min="0.001" step="0.001" placeholder="0" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm removeBtn"
                            style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;">
                            <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                        </button>
                    </div>
                </div>`;
            const $row = $(newRow);
            $('#lignesContainer').append($row);
            initSelect2($row.find('.select2-produit'));
            if (typeof feather !== 'undefined') feather.replace();
        });

        $(document).on('click', '.removeBtn', function() {
            if ($('.ligne-row').length > 1) {
                $(this).closest('.ligne-row').remove();
            }
        });

        @if ($errors->any())
            new bootstrap.Modal(document.getElementById('createTransfertModal')).show();
        @endif

        if (typeof feather !== 'undefined') feather.replace();
    });
</script>
@endsection
