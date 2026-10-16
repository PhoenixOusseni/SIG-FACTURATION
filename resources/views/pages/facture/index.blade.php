@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Facturation</title>
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
                                Gestion des factures
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#createFactureModal">
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
                    <span>Liste des factures</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createFactureModal">
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
                                        <th>N° Facture</th>
                                        <th>Référence</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $facture)
                                        <tr>
                                            <td><span style="font-weight: 600; color: var(--primary-color);">{{ $facture->code }}</span></td>
                                            <td>{{ $facture->reference }}</td>
                                            <td>{{ $facture->Contribuable->assujeti ?? 'N/A' }}</td>
                                            <td>{{ $facture->date ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('module_ordre_facture.show', [$facture->id]) }}"
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
                            <i data-feather="inbox" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Aucune facture trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Fullscreen create modal --}}
        <div class="modal fade" id="createFactureModal" tabindex="-1" aria-labelledby="createFactureModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createFactureModalLabel">Créer une nouvelle facture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="container-xl">
                            <div id="invoiceFormAlerts" class="mb-3"></div>
                            <x-form-errors class="mb-3" />

                            <form id="factureCreateForm" action="{{ route('module_ordre_facture.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <fieldset class="mb-4"
                                    style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background:#fff;">
                                    <legend style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                        📋 Informations générales</legend>
                                    <input name="statut" type="hidden" value="Brouillon" />
                                    <input name="users_id" type="hidden" value="{{ Auth::user()->id }}" />
                                    <input name="user_id" type="hidden" value="{{ Auth::user()->id }}" />

                                    <div class="row mb-3">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small d-flex justify-content-between align-items-center">
                                                    <span>Client <span class="text-danger">*</span></span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-quick-add"
                                                        data-bs-toggle="modal" data-bs-target="#quickCreateClientModal">
                                                        <i class="fas fa-plus"></i> Client
                                                    </button>
                                                </label>
                                                <select name="contribuable_id" id="contribuable_id"
                                                    class="form-select select2-input" required style="width:100%">
                                                    <option value="">Sélectionner le client</option>
                                                    @foreach ($contribuables as $contribuable)
                                                        <option value="{{ $contribuable->id }}"
                                                            {{ old('contribuable_id') == $contribuable->id ? 'selected' : '' }}>
                                                            {{ $contribuable->assujeti }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Type de Facture <span class="text-danger">*</span></label>
                                                <select name="typefacture_id" id="typefacture_id" class="form-select" required>
                                                    <option value="">Sélectionner un type de facture</option>
                                                    @if (isset($typefactures))
                                                        @foreach ($typefactures as $type)
                                                            <option value="{{ $type->id }}"
                                                                {{ old('typefacture_id') == $type->id ? 'selected' : '' }}>
                                                                {{ $type->code }} - {{ $type->libelle }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
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
                                        <div class="col-lg-3 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Opérateur</label>
                                                <input class="form-control" type="text"
                                                    value="{{ Auth::user()->nom }} {{ Auth::user()->prenom }}" disabled />
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Date <span class="text-danger">*</span></label>
                                                <input class="form-control" name="date" type="date"
                                                    value="{{ old('date') }}" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-3 col-md-12">
                                            <div class="form-group">
                                                <label class="small d-flex justify-content-between align-items-center">
                                                    <span>Mode de Paiement</span>
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-quick-add"
                                                        data-bs-toggle="modal" data-bs-target="#quickCreateModePaieModal">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </label>
                                                <select name="modepaie_id" id="modepaie_id" class="form-select">
                                                    <option value="">Sélectionner</option>
                                                    @if (isset($modepaies))
                                                        @foreach ($modepaies as $mode)
                                                            <option value="{{ $mode->id }}"
                                                                {{ old('modepaie_id') == $mode->id ? 'selected' : '' }}>
                                                                {{ $mode->code }} - {{ $mode->libelle ?? '' }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-12">
                                            <div class="form-group">
                                                <label class="small">PSVB</label>
                                                <select name="bic_id" id="bic_id" class="form-select">
                                                    <option value="">Sélectionner le PSVB</option>
                                                    @if (isset($bics))
                                                        @foreach ($bics as $bic)
                                                            <option value="{{ $bic->id }}"
                                                                {{ old('bic_id') == $bic->id ? 'selected' : '' }}>
                                                                {{ $bic->code }} - {{ $bic->intitule ?? 'N/A' }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="mb-4 elements-fieldset"
                                    style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background: rgba(49, 81, 146, 0.05);">
                                    <legend style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;"
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
                                                            <input class="form-control text-center qte-input" name="quantite[]"
                                                                type="number" step="0.01" min="0" placeholder="1" />
                                                        </td>
                                                        <td>
                                                            <input class="form-control text-end prix-input"
                                                                name="prix_unitaire[]" type="number" step="0.01"
                                                                min="0" placeholder="0.00" />
                                                        </td>
                                                        <td class="text-end montant-cell">0</td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn-remove-row removeRowBtn">
                                                                <i data-feather="trash-2" style="width:15px;height:15px;"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4" class="text-end">Total général</td>
                                                        <td class="text-end fw-bold fs-5" style="color:var(--primary-color);"
                                                            id="grandTotal">0</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                    <button type="button" class="btn-add-line" id="addRowBtn">
                                        <i data-feather="plus-circle" style="width:16px;height:16px;"></i> Ajouter une ligne
                                    </button>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button class="btn btn-primary" id="submitFactureBtn" type="submit" form="factureCreateForm">
                            <i class="fas fa-check me-2"></i> Valider la facture
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
        const produitsData = @json($produitsData);

        function showStickyFormAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
            const $box = $('#invoiceFormAlerts');
            const $alert = $(
                '<div class="alert ' + alertClass + ' alert-validation alert-dismissible fade show" role="alert">' +
                '<span></span>' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>' +
                '</div>'
            );
            $alert.find('span').text(message);
            $box.append($alert);
        }

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
                    dropdownParent: $('#createFactureModal')
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
                modalParent: '#createFactureModal',
                alertTarget: '#invoiceFormAlerts',
            });

            $('#createFactureModal').on('shown.bs.modal', function() {
                initModalSelect2(this);
                InvoiceLines.initSelect2($(this).find('.base-taxable-select'));
                if (typeof feather !== 'undefined') feather.replace();
            });

            // Nested modals: keep parent open
            $('.modal-quick-create').on('show.bs.modal', function() {
                const z = 1065;
                $(this).css('z-index', z);
                setTimeout(function() {
                    $('.modal-backdrop').not('.modal-stack').last().css('z-index', z - 1).addClass('modal-stack');
                }, 0);
            });

            QuickCreate.bind({
                formSelector: '#quickCreateClientForm',
                modalSelector: '#quickCreateClientModal',
                targetSelect: '#contribuable_id',
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
                        text: (item.code ? item.code + ' - ' : '') + (item.designation || item.text || ''),
                        code: item.code,
                        designation: item.designation,
                        prix_unitaire: item.prix_unitaire,
                        qte: item.qte,
                    };
                },
                onSuccess: function(item) {
                    InvoiceLines.registerProduct(item);
                    // Select on last row and trigger fill
                    const $sel = $('#elementsContainer .base-taxable-select').last();
                    $sel.val(String(item.id)).trigger('change');
                }
            });

            QuickCreate.bind({
                formSelector: '#quickCreateModePaieForm',
                modalSelector: '#quickCreateModePaieModal',
                targetSelect: '#modepaie_id',
                errorsSelector: '#quickCreateModePaieModal .quick-create-errors',
                mapItem: function(data) {
                    const item = data.item || data.data || {};
                    return {
                        id: item.id,
                        text: (item.code || '') + (item.libelle ? ' - ' + item.libelle : '')
                    };
                }
            });

            $('#factureCreateForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $submitBtn = $('#submitFactureBtn');
                const originalBtnText = $submitBtn.html();
                $('#invoiceFormAlerts').empty();

                $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Validation en cours...');

                if (!$form.find('[name="typefacture_id"]').val()) {
                    showStickyFormAlert('error', 'Veuillez sélectionner un type de facture');
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                    return;
                }
                if (!$form.find('[name="date"]').val()) {
                    showStickyFormAlert('error', 'Veuillez sélectionner une date');
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                    return;
                }
                if (!$form.find('[name="contribuable_id"]').val()) {
                    showStickyFormAlert('error', 'Veuillez sélectionner un client');
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                    return;
                }

                // Immediate feedback; Laravel enforces the same rules again.
                let lineError = '';
                let grossTotal = 0;
                let validLines = 0;
                $form.find('.element-row').each(function(index) {
                    const product = $(this).find('[name=produits_id\\[\\]]').val();
                    const quantity = Number($(this).find('[name=quantite\\[\\]]').val());
                    const price = Number($(this).find('[name=prix_unitaire\\[\\]]').val());
                    if (!product && !quantity && !price) return;
                    if (!product) lineError = 'Ligne ' + (index + 1) + ' : selectionnez un produit.';
                    else if (!Number.isFinite(quantity) || quantity <= 0) lineError = 'Ligne ' + (index + 1) + ' : la quantite doit etre superieure a zero.';
                    else if (!Number.isFinite(price) || price <= 0) lineError = 'Ligne ' + (index + 1) + ' : le prix doit etre superieur a zero.';
                    else { validLines++; grossTotal += quantity * price; }
                    if (lineError) return false;
                });
                const discounts = Number($form.find('[name=remise_1]').val() || 0) + Number($form.find('[name=remise_2]').val() || 0);
                if (!lineError && validLines === 0) lineError = 'Ajoutez au moins une ligne complete a la facture.';
                if (!lineError && discounts < 0) lineError = 'Les remises ne peuvent pas etre negatives.';
                if (!lineError && discounts >= grossTotal) lineError = 'Le total des remises doit etre inferieur au total brut.';
                if (lineError) {
                    showStickyFormAlert('error', lineError);
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                    return;
                }

                const formData = new FormData($form[0]);

                $.ajax({
                    type: 'POST',
                    url: $form.attr('action'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: { 'Accept': 'application/json' },
                    success: function(response) {
                        if (response.success && response.facture) {
                            const factureId = response.facture.id;
                            $.ajax({
                                type: 'POST',
                                url: '/api/v1/certifier',
                                headers: {
                                    'X-CSRF-TOKEN': $form.find('[name="_token"]').val(),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                data: JSON.stringify({ facture_id: factureId }),
                                success: function(certResponse) {
                                    if (certResponse.success) {
                                        showStickyFormAlert('success', 'Facture validée avec succès ! Redirection...');
                                        setTimeout(function() {
                                            window.location.href = '/module_ordre_facture/' + factureId;
                                        }, 1200);
                                    } else {
                                        showStickyFormAlert('error', certResponse.message || 'Erreur lors de la validation');
                                        $submitBtn.prop('disabled', false).html(originalBtnText);
                                    }
                                },
                                error: function(xhr) {
                                    let errorMsg = 'Erreur lors de la validation SFE Bridge';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        errorMsg = xhr.responseJSON.message;
                                    }
                                    // Facture créée mais certif échouée — encore rediriger possible
                                    showStickyFormAlert('error', errorMsg + ' (facture créée #' + factureId + ')');
                                    $submitBtn.prop('disabled', false).html(originalBtnText);
                                }
                            });
                        } else {
                            showStickyFormAlert('error', 'Erreur lors de la création de la facture');
                            $submitBtn.prop('disabled', false).html(originalBtnText);
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Erreur lors de la création de la facture';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showStickyFormAlert('error', errorMsg);
                        $submitBtn.prop('disabled', false).html(originalBtnText);
                    }
                });
            });

            @if ($errors->any() || session('form_error'))
                new bootstrap.Modal(document.getElementById('createFactureModal')).show();
            @endif

            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
@endsection
