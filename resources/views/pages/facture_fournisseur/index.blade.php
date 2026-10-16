@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Factures fournisseurs</title>
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
                                Gestion des factures fournisseurs
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#createFournisseurFactureModal">
                                <i class="fas fa-plus me-1"></i> Nouvelle facture
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Liste des factures fournisseurs</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createFournisseurFactureModal">
                        <i class="fas fa-plus me-1"></i> Nouvelle facture
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
                                        <th>Fournisseur</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $facture)
                                        <tr>
                                            <td>
                                                <span style="font-weight: 600; color: var(--primary-color);">
                                                    {{ $facture->code }}
                                                </span>
                                            </td>
                                            <td>{{ $facture->reference }}</td>
                                            <td>{{ $facture->Fournisseur->assujeti ?? 'N/A' }}</td>
                                            <td>{{ $facture->date ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $facture->statut }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('module_facture_fournisseur.show', [$facture->id]) }}"
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
                            <p>Aucune facture fournisseur trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Fullscreen create modal --}}
        <div class="modal fade" id="createFournisseurFactureModal" tabindex="-1"
            aria-labelledby="createFournisseurFactureModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createFournisseurFactureModalLabel">Créer une nouvelle facture
                            fournisseur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="container-xl">
                            <div id="invoiceFormAlerts" class="mb-3"></div>
                            <x-form-errors class="mb-3" />

                            <form id="fournisseurFactureCreateForm"
                                action="{{ route('module_facture_fournisseur.store') }}" method="POST">
                                @csrf

                                <fieldset class="mb-4"
                                    style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background:#fff;">
                                    <legend
                                        style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                        📋 Informations générales
                                    </legend>

                                    <input name="statut" type="hidden" value="en attente" />
                                    <input name="users_id" type="hidden" value="{{ Auth::user()->id }}" />

                                    <div class="row mb-3">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small d-flex justify-content-between align-items-center">
                                                    <span>Fournisseur <span class="text-danger">*</span></span>
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary btn-quick-add"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#quickCreateFournisseurModal">
                                                        <i class="fas fa-plus"></i> Fournisseur
                                                    </button>
                                                </label>
                                                <select name="fournisseurs_id" id="fournisseurs_id"
                                                    class="form-select select2-input" required style="width:100%">
                                                    <option value="">Sélectionner le fournisseur</option>
                                                    @foreach ($fournisseurs as $fournisseur)
                                                        <option value="{{ $fournisseur->id }}"
                                                            {{ old('fournisseurs_id') == $fournisseur->id ? 'selected' : '' }}>
                                                            {{ $fournisseur->assujeti }}
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
                                        <div class="col-lg-4 col-md-12">
                                            <div class="form-group">
                                                <label class="small">N° Facture</label>
                                                <input class="form-control" id="generatedCode" name="code"
                                                    type="text" value="{{ old('code') }}"
                                                    placeholder="FF-00001/{{ date('Y') }}" readonly />
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Référence</label>
                                                <input class="form-control" name="reference" type="text"
                                                    value="{{ old('reference') }}" placeholder="Référence" />
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Date</label>
                                                <input class="form-control" name="date" type="date"
                                                    value="{{ old('date', date('Y-m-d')) }}" />
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
                                                                    <option value="{{ $item->id }}">
                                                                        {{ $item->code }} - {{ $item->designation }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input class="form-control text-center qte-input"
                                                                name="quantite[]" type="number" step="0.01"
                                                                min="0" placeholder="1" />
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
                        <button class="btn btn-primary" type="submit" form="fournisseurFactureCreateForm">
                            <i class="fas fa-check me-2"></i> Enregistrer la facture
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
                    dropdownParent: $('#createFournisseurFactureModal')
                });
            });
        }

        $(document).ready(function() {
            function generateFactureCode() {
                if ($('#generatedCode').val()) return;
                const currentYear = new Date().getFullYear();
                const randomNumber = Math.floor(Math.random() * 99999) + 1;
                const formattedNumber = String(randomNumber).padStart(5, '0');
                $('#generatedCode').val(`FF-${formattedNumber}/${currentYear}`);
            }

            InvoiceLines.init({
                container: '#elementsContainer',
                products: produitsData,
                enforceStock: false,
                defaultQty: 1,
                mergeDuplicates: true,
                modalParent: '#createFournisseurFactureModal',
                alertTarget: '#invoiceFormAlerts',
            });

            $('#createFournisseurFactureModal').on('shown.bs.modal', function() {
                generateFactureCode();
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
                formSelector: '#quickCreateFournisseurForm',
                modalSelector: '#quickCreateFournisseurModal',
                targetSelect: '#fournisseurs_id',
                errorsSelector: '#quickCreateFournisseurModal .quick-create-errors',
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
                new bootstrap.Modal(document.getElementById('createFournisseurFactureModal')).show();
            @endif

            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
@endsection
