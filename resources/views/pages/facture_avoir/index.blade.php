@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Factures d'Avoir</title>
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
                                <div class="page-header-icon"><i data-feather="credit-card"></i></div>
                                Gestion des factures d'avoir
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <button type="button" class="btn btn-light" data-bs-toggle="modal"
                                data-bs-target="#createAvoirModal">
                                <i class="fas fa-plus me-1"></i> Nouvel avoir
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
                    <span>Liste des factures d'avoir</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createAvoirModal">
                        <i class="fas fa-plus me-1"></i> Nouvel avoir
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 1rem;">
                        <input type="text" placeholder="Rechercher une facture d'avoir..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>
                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Référence</th>
                                        <th>Motif</th>
                                        <th>Client</th>
                                        <th>Facture d'origine</th>
                                        <th>Date</th>
                                        <th style="width: 80px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $avoir)
                                        <tr>
                                            <td><span
                                                    style="font-weight: 600; color: var(--primary-color);">{{ $avoir->code }}</span>
                                            </td>
                                            <td>{{ $avoir->reference }}</td>
                                            <td>
                                                @switch($avoir->motif)
                                                    @case('retour')
                                                        <span class="badge bg-warning">Retour</span>
                                                    @break

                                                    @case('erreur')
                                                        <span class="badge bg-danger">Erreur</span>
                                                    @break

                                                    @case('annulation')
                                                        <span class="badge bg-dark">Annulation</span>
                                                    @break

                                                    @case('remise')
                                                        <span class="badge bg-info">Remise</span>
                                                    @break

                                                    @case('produit_defectueux')
                                                        <span class="badge bg-secondary">Défectueux</span>
                                                    @break

                                                    @default
                                                        <span class="badge bg-light">N/A</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $avoir->Contribuable->assujeti ?? 'N/A' }}</td>
                                            <td>{{ $avoir->Facture->code ?? 'N/A' }}</td>
                                            <td>{{ $avoir->date ?? 'N/A' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('module_facture_avoir.show', [$avoir->id]) }}"
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
                            <p>Aucune facture d'avoir trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Fullscreen create modal --}}
        <div class="modal fade" id="createAvoirModal" tabindex="-1" aria-labelledby="createAvoirModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createAvoirModalLabel">Créer une nouvelle facture d'avoir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="container-xl">
                            <div id="invoiceFormAlerts" class="mb-3"></div>
                            <x-form-errors class="mb-3" />

                            <form id="avoirCreateForm" action="{{ route('module_facture_avoir.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                @php
                                    $bridgeOk = !empty($sfeState['bridge']) && empty($sfeState['erreur']);
                                    $deviceOk = (bool) data_get($sfeState, 'device.succes', false);
                                    $dgiOk = (bool) data_get($sfeState, 'connexion.succes', false);
                                @endphp
                                <div class='alert {{ $bridgeOk && $deviceOk ? 'alert-success' : 'alert-warning' }} mb-3'>
                                    <strong>État SFE actualisé :</strong>
                                    Bridge {{ $bridgeOk ? 'en ligne' : 'indisponible' }} ·
                                    Boîtier {{ $deviceOk ? 'prêt' : 'non prêt' }} ·
                                    DGI {{ $dgiOk ? 'connectée' : 'non confirmée' }}.
                                    @if (!empty($sfeState['erreur']))
                                        <span class='d-block mt-1'>{{ $sfeState['erreur'] }}</span>
                                    @endif
                                </div>

                                <fieldset class="mb-4"
                                    style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background:#fff;">
                                    <legend
                                        style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                        📋 Informations générales</legend>
                                    <input name="statut" type="hidden" value="en attente" />

                                    <div class="row mb-3">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Facture d'origine <span
                                                        class="text-danger">*</span></label>
                                                <select name="facture_id" id="facture_id"
                                                    class="form-select select2-input" required style="width:100%">
                                                    <option value="">Sélectionner la facture</option>
                                                    @foreach ($factures as $facture)
                                                        <option value="{{ $facture->id }}"
                                                            {{ old('facture_id') == $facture->id ? 'selected' : '' }}>
                                                            {{ $facture->code }} -
                                                            {{ $facture->Contribuable->assujeti ?? 'N/A' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
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
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-lg-4 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Motif de l'avoir <span
                                                        class="text-danger">*</span></label>
                                                <select name="motif" id="motif_avoir" class="form-select" required>
                                                    <option value="">Sélectionner le motif</option>
                                                    <option value="retour"
                                                        {{ old('motif') == 'retour' ? 'selected' : '' }}>Retour de
                                                        marchandise → RAM</option>
                                                    <option value="erreur"
                                                        {{ old('motif') == 'erreur' ? 'selected' : '' }}>Erreur sur la
                                                        facture → COR</option>
                                                    <option value="annulation"
                                                        {{ old('motif') == 'annulation' ? 'selected' : '' }}>Annulation
                                                        de vente → RAN</option>
                                                    <option value="remise"
                                                        {{ old('motif') == 'remise' ? 'selected' : '' }}>Remise/Rabais
                                                        accordé → RRR</option>
                                                    <option value="produit_defectueux"
                                                        {{ old('motif') == 'produit_defectueux' ? 'selected' : '' }}>
                                                        Produit défectueux → RAM</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Nature DGI (FEC 1.25) <span
                                                        class="text-danger">*</span></label>
                                                <select name="nature_avoir" id="nature_avoir" class="form-select"
                                                    required>
                                                    <option value="">Sélectionner</option>
                                                    <option value="COR"
                                                        {{ old('nature_avoir') == 'COR' ? 'selected' : '' }}>COR —
                                                        Correction</option>
                                                    <option value="RAN"
                                                        {{ old('nature_avoir') == 'RAN' ? 'selected' : '' }}>RAN —
                                                        Annulation</option>
                                                    <option value="RAM"
                                                        {{ old('nature_avoir') == 'RAM' ? 'selected' : '' }}>RAM —
                                                        Reprise biens/services</option>
                                                    <option value="RRR"
                                                        {{ old('nature_avoir') == 'RRR' ? 'selected' : '' }}>RRR —
                                                        Remise / ristourne / rabais</option>
                                                </select>
                                                <small class="text-muted">Prérempli selon le motif ; modifiable.</small>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-12">
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
                                                <input class="form-control" name="remise_1" type="number"
                                                    step="0.01" value="{{ old('remise_1') }}"
                                                    placeholder="Remise 1" />
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Remise 2 (XOF)</label>
                                                <input class="form-control" name="remise_2" type="number"
                                                    step="0.01" value="{{ old('remise_2') }}"
                                                    placeholder="Remise 2" />
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Date</label>
                                                <input class="form-control" name="date" type="date"
                                                    value="{{ old('date') }}" />
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                <fieldset class="mb-4 elements-fieldset"
                                    style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background: rgba(49, 81, 146, 0.05);">
                                    <legend
                                        style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;"
                                        class="d-flex justify-content-between align-items-center w-100">
                                        <span>📦 Éléments de l'avoir</span>
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
                        <button class="btn btn-primary" type="submit" form="avoirCreateForm">
                            <i class="fas fa-check me-2"></i> Enregistrer l'avoir
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

        const facturesData = {!! $facturesDataJson !!};

        function showStickyFormAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
            const $box = $('#invoiceFormAlerts');
            const $alert = $('<div class=alert-validation role=alert><span></span></div>');
            $alert.addClass('alert ' + alertClass + ' alert-dismissible fade show');
            $alert.append('<button type=button class=btn-close data-bs-dismiss=alert aria-label=Fermer></button>');
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
                    dropdownParent: $('#createAvoirModal')
                });
            });
        }

        function loadFactureDetails(factureId) {
            const data = facturesData[factureId];
            if (data) {
                $('input[name=reference]').val(data.reference || '');
                $('input[name=remise_1]').val(data.remise_1 || 0);
                $('input[name=remise_2]').val(data.remise_2 || 0);
                InvoiceLines.replaceRows(data.lignes || []);
                $('select[name="contribuables_id"]').val(data.client_id).trigger('change');
                $('input[name="objet"]').val(data.objet || '');
            }
        }

        $(document).ready(function() {
            const today = new Date().toISOString().split('T')[0];
            if (!$('input[name="date"]').val()) {
                $('input[name="date"]').val(today);
            }

            const sourceFactureId = @json($sourceFactureId);
            const shouldLoadSource = sourceFactureId && facturesData[sourceFactureId];

            InvoiceLines.init({
                container: '#elementsContainer',
                products: produitsData,
                enforceStock: false,
                defaultQty: 1,
                mergeDuplicates: true,
                modalParent: '#createAvoirModal',
                alertTarget: '#invoiceFormAlerts',
            });

            if (shouldLoadSource) {
                $('#facture_id').val(sourceFactureId).trigger('change');
                loadFactureDetails(sourceFactureId);
                new bootstrap.Modal(document.getElementById('createAvoirModal')).show();
            }

            $('#createAvoirModal').on('shown.bs.modal', function() {
                initModalSelect2(this);
                InvoiceLines.initSelect2($(this).find('.base-taxable-select'));
                if (typeof feather !== 'undefined') feather.replace();
            });

            $(document).on('change', 'select[name="facture_id"]', function() {
                const factureId = $(this).val();
                if (factureId) {
                    loadFactureDetails(factureId);
                }
            });

            var motifToNature = {
                erreur: 'COR',
                annulation: 'RAN',
                retour: 'RAM',
                produit_defectueux: 'RAM',
                remise: 'RRR'
            };
            $('#motif_avoir').on('change', function() {
                var n = motifToNature[$(this).val()];
                if (n) {
                    $('#nature_avoir').val(n);
                }
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

            $('#avoirCreateForm').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const $submitBtn = $form.find('button[type=submit], input[type=submit]').add('button[form=avoirCreateForm]');
                const originalBtnText = $submitBtn.first().html();
                $('#invoiceFormAlerts').empty();
                $submitBtn.prop('disabled', true).html('<i class=\'fas fa-spinner fa-spin me-2\'></i> Validation en cours...');

                const restoreButton = function() {
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                };
                const showRequestError = function(xhr, fallback) {
                    let message = fallback;
                    if (xhr.responseJSON) {
                        message = xhr.responseJSON.message || message;
                        if (xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        }
                    }
                    showStickyFormAlert('error', message);
                    restoreButton();
                };

                if (!$form.find('[name=date]').val()) {
                    showStickyFormAlert('error', 'Veuillez sélectionner une date');
                    restoreButton();
                    return;
                }
                if (!$form.find('[name=contribuables_id]').val()) {
                    showStickyFormAlert('error', 'Veuillez sélectionner un client');
                    restoreButton();
                    return;
                }
                if (!$form.find('[name=motif]').val()) {
                    showStickyFormAlert('error', 'Veuillez sélectionner un motif');
                    restoreButton();
                    return;
                }

                if (!$form.find('[name=facture_id]').val()) {
                    showStickyFormAlert('error', 'Selectionnez la facture origine.');
                    restoreButton();
                    return;
                }
                if (!$form.find('[name=nature_avoir]').val()) {
                    showStickyFormAlert('error', 'Selectionnez la nature DGI de avoir.');
                    restoreButton();
                    return;
                }

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
                if (!lineError && validLines === 0) lineError = 'Ajoutez au moins une ligne complete a avoir.';
                if (!lineError && discounts < 0) lineError = 'Les remises ne peuvent pas etre negatives.';
                if (!lineError && discounts >= grossTotal) lineError = 'Le total des remises doit etre inferieur au total brut.';
                if (lineError) {
                    showStickyFormAlert('error', lineError);
                    restoreButton();
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: $form.attr('action'),
                    data: new FormData($form[0]),
                    contentType: false,
                    processData: false,
                    headers: { 'Accept': 'application/json' },
                    success: function(response) {
                        if (!response.success || !response.facture_avoir) {
                            showStickyFormAlert('error', 'Erreur lors de la création de la facture d\'avoir');
                            restoreButton();
                            return;
                        }

                        const avoirId = response.facture_avoir.id;
                        $.ajax({
                            type: 'POST',
                            url: '/api/v1/certifier',
                            headers: {
                                'X-CSRF-TOKEN': $form.find('[name=_token]').val(),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            data: JSON.stringify({ facture_avoir_id: avoirId }),
                            success: function(certResponse) {
                                if (certResponse.success) {
                                    showStickyFormAlert('success', 'Facture d\'avoir validée avec succès ! Redirection...');
                                    setTimeout(function() {
                                        window.location.href = '/module_facture_avoir/' + avoirId;
                                    }, 1200);
                                } else {
                                    showStickyFormAlert('error', certResponse.message || 'Erreur lors de la validation');
                                    restoreButton();
                                }
                            },
                            error: function(xhr) {
                                showRequestError(xhr, 'Erreur lors de la validation SFE Bridge (facture d\'avoir créée #' + avoirId + ')');
                            }
                        });
                    },
                    error: function(xhr) {
                        showRequestError(xhr, 'Erreur lors de la création de la facture d\'avoir');
                    }
                });
            });

            @if ($errors->any() || session('form_error'))
                new bootstrap.Modal(document.getElementById('createAvoirModal')).show();
            @endif

            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
@endsection
