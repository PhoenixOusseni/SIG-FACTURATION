@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Factures proforma</title>
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
                                <div class="page-header-icon"><i data-feather="activity"></i></div>
                                Gestion des factures proforma
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#createProformaModal">
                                <i class="fas fa-plus me-1"></i> Nouvelle proforma
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            @if (session('warning_stock'))
                <div class="alert alert-warning d-flex gap-2 align-items-start mb-3">
                    <i class="fas fa-exclamation-triangle mt-1"></i>
                    <div>
                        <strong>Proforma enregistrée — stock insuffisant pour certains produits :</strong>
                        <ul class="mb-0 mt-1">
                            @foreach (session('warning_stock') as $msg)
                                <li>{{ $msg }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Liste des factures proforma</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createProformaModal">
                        <i class="fas fa-plus me-1"></i> Nouvelle proforma
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher une facture..." class="form-control"
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
                                        <th>Date</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $facture)
                                        <tr>
                                            <td><span
                                                    style="font-weight: 600; color: var(--primary-color);">{{ $facture->code }}</span>
                                            </td>
                                            <td>{{ $facture->reference }}</td>
                                            <td>{{ $facture->Contribuable->assujeti ?? 'N/A' }}</td>
                                            <td>{{ $facture->date ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('module_facture_proforma.show', [$facture->id]) }}"
                                                    class="btn btn-sm btn-primary"
                                                    style="border-radius: 50%; width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align: center; padding: 3rem 1rem; color: var(--text-light);">
                            <i data-feather="inbox"
                                style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucune facture trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Fullscreen create modal --}}
        <div class="modal fade" id="createProformaModal" tabindex="-1" aria-labelledby="createProformaModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createProformaModalLabel">Créer une nouvelle facture proforma</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="container-xl">
                            <div id="invoiceFormAlerts" class="mb-3"></div>
                            <x-form-errors class="mb-3" />

                            <form id="proformaCreateForm" action="{{ route('module_facture_proforma.store') }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf

                                <fieldset class="mb-4"
                                    style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background:#fff;">
                                    <legend
                                        style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                        📋 Informations générales</legend>
                                    <input name="statut" type="hidden" value="en attente" />
                                    <input name="users_id" type="hidden" value="{{ Auth::user()->id }}" />

                                    <div class="row mb-3">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small d-flex justify-content-between align-items-center">
                                                    <span>Client <span class="text-danger">*</span></span>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary btn-quick-add"
                                                        data-bs-toggle="modal" data-bs-target="#quickCreateClientModal">
                                                        <i class="fas fa-plus"></i> Client
                                                    </button>
                                                </label>
                                                <select name="contribuables_id" id="contribuables_id"
                                                    class="form-select select2-input" required style="width:100%">
                                                    <option value="">Sélectionner le client</option>
                                                    @foreach ($contribuables as $contribuable)
                                                        <option value="{{ $contribuable->id }}"
                                                            {{ old('contribuables_id') == $contribuable->id ? 'selected' : '' }}>
                                                            {{ $contribuable->assujeti }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Objet</label>
                                                <input type="text" class="form-control" name="objet"
                                                    value="{{ old('objet') }}" placeholder="Objet" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-2 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Référence</label>
                                                <input class="form-control" name="reference" type="text"
                                                    value="{{ old('reference') }}" placeholder="Référence" />
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Remise 1 (XOF)</label>
                                                <input class="form-control" name="remise_1" type="number" step="0.01"
                                                    value="{{ old('remise_1') }}" placeholder="Remise 1" />
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Remise 2 (XOF)</label>
                                                <input class="form-control" name="remise_2" type="number" step="0.01"
                                                    value="{{ old('remise_2') }}" placeholder="Remise 2" />
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Date</label>
                                                <input class="form-control" name="date" type="date"
                                                    value="{{ old('date') }}" />
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Opérateur</label>
                                                <select name="operateur_id" id="operateur_id" class="form-select">
                                                    <option value="">Sélectionner l'opérateur</option>
                                                    @foreach (App\Models\User::all() as $operateur)
                                                        <option value="{{ $operateur->id }}"
                                                            {{ old('operateur_id', Auth::user()->id) == $operateur->id ? 'selected' : '' }}>
                                                            {{ $operateur->nom }} {{ $operateur->prenom }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="mb-4 elements-fieldset"
                                    style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background: rgba(49, 81, 146, 0.05);">
                                    <legend
                                        style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;"
                                        class="d-flex justify-content-between align-items-center w-100">
                                        <span>📦 Éléments de la facture</span>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#quickCreateProductModal">
                                            <i class="fas fa-plus"></i> Produit
                                        </button>
                                    </legend>
                                    <div class="elements-table-wrap">
                                        <div class="table-responsive">
                                            <table class="table table-elements align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center" style="width:40px">#</th>
                                                        <th style="min-width:280px">Produit</th>
                                                        <th style="width:130px" class="text-center">Quantité</th>
                                                        <th style="width:170px" class="text-center">Prix unitaire</th>
                                                        <th style="width:170px" class="text-end">Montant</th>
                                                        <th style="width:50px"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="elementsContainer">
                                                    <tr class="element-row">
                                                        <td class="text-center"><span class="row-index">1</span></td>
                                                        <td>
                                                            <select class="form-select base-taxable-select select2-input"
                                                                name="produits_id[]" style="width:100%">
                                                                <option value="">Sélectionner un produit</option>
                                                                @foreach ($produits as $item)
                                                                    <option value="{{ $item->id }}"
                                                                        data-stock="{{ $item->qte ?? 0 }}"
                                                                        @if (($item->qte ?? 0) <= 0) disabled @endif>
                                                                        {{ $item->code }} - {{ $item->designation }}
                                                                        (Stock: {{ $item->qte ?? 0 }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input class="form-control text-center qte-input"
                                                                name="quantite[]" type="number" step="0.01" min="0"
                                                                placeholder="1" />
                                                        </td>
                                                        <td>
                                                            <input class="form-control text-end prix-input"
                                                                name="prix_unitaire[]" type="number" step="0.01"
                                                                min="0" placeholder="0.00" />
                                                        </td>
                                                        <td class="text-end montant-cell">0</td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn-remove-row removeRowBtn">
                                                                <i data-feather="trash-2"
                                                                    style="width:15px;height:15px;"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4" class="text-end">Total général</td>
                                                        <td class="text-end fw-bold fs-5"
                                                            style="color:var(--primary-color);" id="grandTotal">0</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <button type="button" class="btn-add-line" id="addRowBtn">
                                        <i data-feather="plus-circle" style="width:16px;height:16px;"></i> Ajouter une
                                        ligne
                                    </button>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button class="btn btn-primary" type="submit" form="proformaCreateForm">
                            <i class="fas fa-check me-2"></i> Enregistrer la proforma
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.quick_create_modals')
    </main>
@endsection

@section('script')
    <script src="{{ asset('js/invoice-lines.js') }}"></script>
    <script src="{{ asset('js/quick-create.js') }}"></script>
    <script>
        const produitsData = {!! json_encode(
            $produits->keyBy('id')->map(function ($p) {
                return [
                    'code' => $p->code,
                    'designation' => $p->designation,
                    'prix_unitaire' => $p->prix_unitaire,
                    'qte' => $p->qte,
                ];
            })) !!};

        function initModalSelect2(scope) {
            $(scope).find('select.select2-input').each(function() {
                const $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                    placeholder: 'Sélectionner...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#createProformaModal')
                });
            });
        }

        $(document).ready(function() {
            const today = new Date().toISOString().split('T')[0];
            if (!$('input[name="date"]').val()) {
                $('input[name="date"]').val(today);
            }

            InvoiceLines.init({
                container: '#elementsContainer',
                products: produitsData,
                enforceStock: true,
                defaultQty: 1,
                mergeDuplicates: true,
                modalParent: '#createProformaModal',
                alertTarget: '#invoiceFormAlerts',
            });

            $('#createProformaModal').on('shown.bs.modal', function() {
                initModalSelect2(this);
                InvoiceLines.initSelect2($(this).find('.base-taxable-select'));
                if (typeof feather !== 'undefined') feather.replace();
            });

            $('.modal-quick-create').on('show.bs.modal', function() {
                const z = 1065;
                $(this).css('z-index', z);
                setTimeout(function() {
                    $('.modal-backdrop').not('.modal-stack').last().css('z-index', z - 1).addClass(
                        'modal-stack');
                }, 0);
            });

            QuickCreate.bind({
                formSelector: '#quickCreateClientForm',
                modalSelector: '#quickCreateClientModal',
                targetSelect: '#contribuables_id',
                errorsSelector: '#quickCreateClientModal .quick-create-errors',
                mapItem: function(data) {
                    const item = data.item || data.data || {};
                    return {
                        id: item.id,
                        text: item.assujeti || item.text || ('#' + item.id)
                    };
                }
            });

            QuickCreate.bind({
                formSelector: '#quickCreateProductForm',
                modalSelector: '#quickCreateProductModal',
                targetSelect: function() {
                    return $('#elementsContainer .base-taxable-select').last();
                },
                errorsSelector: '#quickCreateProductModal .quick-create-errors',
                mapItem: function(data) {
                    const item = data.item || data.data || {};
                    return {
                        id: item.id,
                        text: (item.code ? item.code + ' - ' : '') + (item.designation || item.text ||
                            ''),
                        code: item.code,
                        designation: item.designation,
                        prix_unitaire: item.prix_unitaire,
                        qte: item.qte,
                    };
                },
                onSuccess: function(item) {
                    InvoiceLines.registerProduct(item);
                    const $sel = $('#elementsContainer .base-taxable-select').last();
                    $sel.val(String(item.id)).trigger('change');
                }
            });

            @if ($errors->any() || session('form_error'))
                new bootstrap.Modal(document.getElementById('createProformaModal')).show();
            @endif

            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
@endsection
