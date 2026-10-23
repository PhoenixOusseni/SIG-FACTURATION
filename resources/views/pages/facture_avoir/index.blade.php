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
                            <table class="table table-hover align-middle mb-0" id="avoirsTable">
                                <thead class="table-light">
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
                                            <td data-order="{{ $avoir->date ? \Carbon\Carbon::parse($avoir->date)->format('Y-m-d') : '1970-01-01' }}">{{ $avoir->date ? \Carbon\Carbon::parse($avoir->date)->format('d/m/Y') : 'N/A' }}</td>
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
                                                <label class="small fw-bold">Facture d'origine <span
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

                                    {{-- Carte de rappel synthétique de la Facture d'origine --}}
                                    <div id="avoirOrigineDetailsCard" class="card border-primary bg-light mb-3" style="display:none;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-title text-primary fw-bold mb-0">
                                                    <i class="fas fa-file-invoice me-1"></i> Synthèse Facture d'Origine : <span id="cardOrigineRef"></span>
                                                </h6>
                                                <span id="cardOrigineStatut" class="badge bg-secondary"></span>
                                            </div>
                                            <div class="row g-2 small">
                                                <div class="col-md-2"><strong>Client :</strong> <span id="cardOrigineClientNom">-</span></div>
                                                <div class="col-md-2"><strong>IFU :</strong> <span id="cardOrigineClientIfu">-</span></div>
                                                <div class="col-md-2"><strong>Total Facture :</strong> <span id="cardOrigineMontant" class="fw-bold text-dark">-</span></div>
                                                <div class="col-md-2"><strong>Déjà Réglé :</strong> <span id="cardOriginePaye" class="fw-bold text-info">-</span></div>
                                                <div class="col-md-2"><strong>Reste Dû :</strong> <span id="cardOrigineReste" class="fw-bold text-danger">-</span></div>
                                                <div class="col-md-2"><strong>Solde Avoir :</strong> <span id="cardOrigineSoldeDispo" class="badge bg-success fs-6">-</span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small fw-bold">Nature / Motif de l'avoir (Norme FEC DGI) <span
                                                        class="text-danger">*</span></label>
                                                <input type="hidden" name="motif" id="motif_avoir" value="{{ old('motif', 'annulation') }}">
                                                <select name="nature_avoir" id="nature_avoir" class="form-select border-primary"
                                                    required onchange="onNatureAvoirChange(this.value)">
                                                    <option value="RAN" {{ old('nature_avoir', 'RAN') == 'RAN' ? 'selected' : '' }}>
                                                        RAN — Annulation totale de la vente (100% de la facture)
                                                    </option>
                                                    <option value="RAM" {{ old('nature_avoir') == 'RAM' ? 'selected' : '' }}>
                                                        RAM — Retour de marchandises (réintégration en stock)
                                                    </option>
                                                    <option value="COR" {{ old('nature_avoir') == 'COR' ? 'selected' : '' }}>
                                                        COR — Correction d'erreur matérielle (ajustement prix/qté)
                                                    </option>
                                                    <option value="RRR" {{ old('nature_avoir') == 'RRR' ? 'selected' : '' }}>
                                                        RRR — Remise / rabais / ristourne accordé (sans mouvement de stock)
                                                    </option>
                                                </select>
                                                <div id="natureAvoirAlert" class="form-text mt-1 text-primary"></div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-12">
                                            <div class="form-group">
                                                <label class="small">Objet / Précisions</label>
                                                <input type="text" class="form-control" name="objet" id="avoir_objet"
                                                    value="{{ old('objet') }}" placeholder="Précisez la raison ou le contexte de l'avoir..." />
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Contrôles Avancés RRR (Rabais, Remise, Ristourne) --}}
                                    <div id="mainAvoirRrrControls" class="card border-info bg-light-subtle mb-3 shadow-sm" style="display: none;">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="card-title text-info fw-bold mb-0">
                                                    <i class="fas fa-tags me-1"></i> Paramétrage de la Remise Financière (RRR)
                                                </h6>
                                                <span class="badge bg-info-subtle text-info border border-info">Aucun mouvement de stock · PSVB = 0</span>
                                            </div>
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold">Nature de la réduction</label>
                                                    <select id="main_rrr_type" class="form-select form-select-sm">
                                                        <option value="Remise commerciale">Remise commerciale</option>
                                                        <option value="Rabais">Rabais (défaut de conformité)</option>
                                                        <option value="Ristourne">Ristourne (volume périodique)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold">Unité de calcul</label>
                                                    <select id="main_rrr_unite" class="form-select form-select-sm" onchange="onMainRrrUnitChange()">
                                                        <option value="pourcentage">Pourcentage (%)</option>
                                                        <option value="montant">Montant fixe (FCFA)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold" id="main_rrr_val_label">Valeur (%)</label>
                                                    <input type="number" step="any" min="0" id="main_rrr_val" class="form-control form-control-sm font-monospace fw-bold" placeholder="Ex: 10">
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button" class="btn btn-sm btn-info text-white w-100 fw-bold" onclick="appliquerMainRemiseGlobaleAvoir()">
                                                        <i class="fas fa-magic me-1"></i> Appliquer à la facture
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="form-text mt-1 text-muted" style="font-size: 11px;">
                                                <i class="fas fa-shield-alt text-success me-1"></i> Règle fiscale FEC/DGI : Pas de taxe PSVB, taxes spécifiques forfaitaires exclues, recalcul proportionnel de la TVA.
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

                                    {{-- Section Trésorerie & Décaissement --}}
                                    <div class="card border-0 shadow-sm mt-4" id="mainAvoirTreasuryCard" style="border-radius: 8px; border: 1px solid #e2e8f0 !important;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="fas fa-coins text-warning fs-5"></i>
                                                <h6 class="mb-0 fw-bold text-dark">Impact Trésorerie & Règlement Client</h6>
                                            </div>
                                            <div id="mainAvoirTreasuryNotice" class="alert alert-light border small mb-0">
                                                Sélectionnez une facture d'origine pour afficher l'analyse financière.
                                            </div>
                                            <div id="mainAvoirRefundSection" class="mt-3 pt-3 border-top" style="display: none;">
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" name="effectuer_remboursement" id="main_effectuer_remboursement" value="1" onchange="toggleMainAvoirRefund(this.checked)">
                                                    <label class="form-check-label fw-bold text-danger" for="main_effectuer_remboursement">
                                                        <i class="fas fa-hand-holding-usd me-1"></i> Rembourser immédiatement le client (Décaissement Caisse / Banque)
                                                    </label>
                                                </div>
                                                <div id="mainAvoirRefundFields" style="display: none;" class="row g-2 mt-2 p-2 bg-light rounded border">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Mode de Décaissement</label>
                                                        <select name="remboursement_mode" id="main_remboursement_mode" class="form-select form-select-sm">
                                                            <option value="Espèces" selected>Espèces (Caisse)</option>
                                                            <option value="Chèque">Chèque</option>
                                                            <option value="Virement Bancaire">Virement Bancaire</option>
                                                            <option value="Mobile Money">Mobile Money</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Montant Décaissé (FCFA)</label>
                                                        <input type="number" step="1" min="1" name="remboursement_montant" id="main_remboursement_montant" class="form-control form-control-sm font-monospace fw-bold text-danger">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Réf. Transaction / Reçu</label>
                                                        <input type="text" name="remboursement_ref" id="main_remboursement_ref" class="form-control form-control-sm" placeholder="Ex: Décaissement Caisse...">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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

        function onNatureAvoirChange(val) {
            const $motif = $('#motif_avoir');
            const $alert = $('#natureAvoirAlert');
            const $addRowBtn = $('#addRowBtn');
            const $removeBtns = $('.btn-remove-row, .removeRowBtn');
            const $qtyInputs = $('.qte-input');
            const $prixInputs = $('.prix-input');

            if (val === 'RAN') {
                $motif.val('annulation');
                $alert.html('<i class="fas fa-lock me-1 text-danger"></i> <strong>Annulation totale (RAN)</strong> : La facture est reprise à 100%. Quantités et montants verrouillés.').removeClass('text-primary text-info text-success').addClass('text-danger');
                $addRowBtn.prop('disabled', true).hide();
                $removeBtns.prop('disabled', true).hide();
                $qtyInputs.prop('readonly', true).addClass('bg-light');
                $prixInputs.prop('readonly', true).addClass('bg-light');
                $('#mainAvoirRrrControls').slideUp();
            } else if (val === 'RAM') {
                $motif.val('retour');
                $alert.html('<i class="fas fa-boxes me-1 text-primary"></i> <strong>Retour marchandises (RAM)</strong> : Spécifiez les quantités restituées. Les biens physiques seront réintégrés en stock.').removeClass('text-danger text-info text-success').addClass('text-primary');
                $addRowBtn.prop('disabled', false).show();
                $removeBtns.prop('disabled', false).show();
                $qtyInputs.prop('readonly', false).removeClass('bg-light');
                $prixInputs.prop('readonly', false).removeClass('bg-light');
                $('#mainAvoirRrrControls').slideUp();
            } else if (val === 'COR') {
                $motif.val('erreur');
                $alert.html('<i class="fas fa-edit me-1 text-info"></i> <strong>Correction d\'erreur (COR)</strong> : Ajustez les lignes ou montants erronés.').removeClass('text-danger text-primary text-success').addClass('text-info');
                $addRowBtn.prop('disabled', false).show();
                $removeBtns.prop('disabled', false).show();
                $qtyInputs.prop('readonly', false).removeClass('bg-light');
                $prixInputs.prop('readonly', false).removeClass('bg-light');
                $('#mainAvoirRrrControls').slideUp();
            } else if (val === 'RRR') {
                $motif.val('remise');
                $alert.html('<i class="fas fa-percent me-1 text-success"></i> <strong>Rabais / Remise / Ristourne (RRR)</strong> : Avoir financier accordé sans mouvement de stock, PSVB = 0, TS fixes exclues.').removeClass('text-danger text-primary text-info').addClass('text-success');
                $addRowBtn.prop('disabled', true).hide();
                $removeBtns.prop('disabled', true).hide();
                $qtyInputs.prop('readonly', true).addClass('bg-light');
                $prixInputs.prop('readonly', false).removeClass('bg-light');
                $('#mainAvoirRrrControls').slideDown();
            } else {
                $motif.val('annulation');
                $alert.text('');
                $addRowBtn.prop('disabled', false).show();
                $removeBtns.prop('disabled', false).show();
                $qtyInputs.prop('readonly', false).removeClass('bg-light');
                $prixInputs.prop('readonly', false).removeClass('bg-light');
                $('#mainAvoirRrrControls').slideUp();
            }
            evaluateMainAvoirTreasury();
        }

        function onMainRrrUnitChange() {
            const u = $('#main_rrr_unite').val();
            if (u === 'pourcentage') {
                $('#main_rrr_val_label').text('Valeur (%)');
                $('#main_rrr_val').attr('placeholder', 'Ex: 10').attr('max', 100);
            } else {
                $('#main_rrr_val_label').text('Valeur (FCFA)');
                $('#main_rrr_val').attr('placeholder', 'Ex: 5000').removeAttr('max');
            }
        }

        function appliquerMainRemiseGlobaleAvoir() {
            const fId = $('#facture_id').val();
            const fact = facturesData[fId];
            if (!fact || !fact.lignes || fact.lignes.length === 0) {
                alert("Veuillez sélectionner une facture d'origine valide.");
                return;
            }
            const unit = $('#main_rrr_unite').val();
            const val = parseFloat($('#main_rrr_val').val()) || 0;
            if (val <= 0) {
                alert("Veuillez saisir une valeur supérieure à 0.");
                return;
            }

            let pct = 0;
            const totalOrigHt = fact.lignes.reduce((acc, l) => {
                const qRest = l.quantite_restante !== undefined ? parseFloat(l.quantite_restante) : parseFloat(l.quantite);
                return acc + (qRest * (parseFloat(l.prix_unitaire) || 0));
            }, 0);

            if (unit === 'pourcentage') {
                pct = Math.min(100, val);
            } else {
                pct = totalOrigHt > 0 ? Math.min(100, (val / totalOrigHt) * 100) : 0;
            }

            $('#elementsContainer .element-row').each(function(idx) {
                const origLigne = fact.lignes[idx];
                if (origLigne) {
                    const origPu = parseFloat(origLigne.prix_unitaire) || 0;
                    const rabaisPu = Math.round((origPu * (pct / 100)) * 100) / 100;
                    const qRest = origLigne.quantite_restante !== undefined ? origLigne.quantite_restante : origLigne.quantite;
                    $(this).find('.prix-input').val(rabaisPu).trigger('input');
                    $(this).find('.qte-input').val(qRest).prop('readonly', true).addClass('bg-light');
                }
            });

            setTimeout(evaluateMainAvoirTreasury, 150);
        }

        function calculateFiscalTotalForMainAvoir() {
            const nature = $('#nature_avoir').val();
            const fId = $('#facture_id').val();
            const fact = facturesData[fId];
            if (!fact) return 0;

            if (nature === 'RAN') {
                return parseFloat(fact.total_facture) || 0;
            }

            const isRrr = (nature === 'RRR');
            const tauxBic = isRrr ? 0 : (parseFloat(fact.taux_bic) || 0);

            let totalHt = 0;
            let totalTs = 0;
            let totalTva = 0;
            let totalBic = 0;

            $('#elementsContainer .element-row').each(function() {
                const prodId = $(this).find('select[name="produits_id[]"]').val();
                const qte = parseFloat($(this).find('.qte-input').val()) || 0;
                const pu = parseFloat($(this).find('.prix-input').val()) || 0;
                const prod = produitsData[prodId];
                if (!prod || qte <= 0) return;

                const ligneHt = qte * pu;
                totalHt += ligneHt;

                let tsUnit = 0;
                if (prod.type_ts === 'POURCENTAGE' && prod.pourcentage_ts > 0) {
                    tsUnit = Math.round((pu * (prod.pourcentage_ts / 100)) * 100) / 100;
                } else if (!isRrr && prod.montant_ts > 0) {
                    tsUnit = prod.montant_ts;
                }
                const ligneTs = Math.round((tsUnit * qte) * 100) / 100;
                totalTs += ligneTs;

                const baseTva = ligneHt + ligneTs;
                const ligneTva = Math.round((baseTva * (prod.taux_tva / 100)) * 100) / 100;
                totalTva += ligneTva;

                const ligneTtc = baseTva + ligneTva;
                if (prod.is_bien && tauxBic > 0 && !isRrr) {
                    totalBic += Math.round((ligneTtc * (tauxBic / 100)) * 100) / 100;
                }
            });

            const remise1 = parseFloat($('input[name=remise_1]').val()) || 0;
            const remise2 = parseFloat($('input[name=remise_2]').val()) || 0;
            const netCom = Math.max(0, totalHt - remise1 - remise2);
            const ratio = totalHt > 0 ? (netCom / totalHt) : 1;

            const totalFinal = (totalHt * ratio) + totalTs + totalTva + totalBic;
            return Math.round(totalFinal);
        }

        let activeFacturePaye = 0;
        let activeFactureReste = 0;
        let activeFactureTotal = 0;

        function evaluateMainAvoirTreasury() {
            const totalAvoir = calculateFiscalTotalForMainAvoir();
            const paye = activeFacturePaye;
            const reste = activeFactureReste;

            if (!activeFactureTotal) {
                $('#mainAvoirTreasuryNotice').html('Sélectionnez une facture d\'origine pour afficher l\'analyse financière.').removeClass('alert-warning alert-info alert-danger').addClass('alert-light');
                $('#mainAvoirRefundSection').hide();
                $('#main_effectuer_remboursement').prop('checked', false);
                $('#mainAvoirRefundFields').hide();
                return;
            }

            // Vérification de dépassement du solde disponible
            const fId = $('#facture_id').val();
            const fact = facturesData[fId];
            if (fact) {
                const soldeDispo = parseFloat(fact.solde_disponible) || 0;
                if (totalAvoir > (soldeDispo + 0.009)) {
                    $('#mainAvoirTreasuryNotice').html(`
                        <div class="d-flex align-items-center text-danger">
                            <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                            <div><strong>Dépassement du solde éligible :</strong> Le montant de l'avoir (${new Intl.NumberFormat('fr-FR').format(totalAvoir)} F) dépasse le solde disponible sur cette facture (${new Intl.NumberFormat('fr-FR').format(soldeDispo)} F). Veuillez réduire les quantités.</div>
                        </div>
                    `).removeClass('alert-warning alert-info alert-light alert-primary').addClass('alert-danger');
                    $('#avoirCreateForm button[type=submit], button[form=avoirCreateForm]').prop('disabled', true);
                    return;
                } else if (totalAvoir > 0) {
                    $('#avoirCreateForm button[type=submit], button[form=avoirCreateForm]').prop('disabled', false);
                }
            }

            if (paye <= 0.009) {
                $('#mainAvoirTreasuryNotice').html(`
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-info-circle me-2 fs-5 text-secondary"></i>
                        <div><strong>Créance non encaissée :</strong> Le client n'avait versé aucun paiement (0 FCFA). Cet avoir annule ou diminue simplement la créance due par le client. <strong>Aucun mouvement de caisse ni décaissement requis.</strong></div>
                    </div>
                `).removeClass('alert-warning alert-info').addClass('alert-light');
                $('#mainAvoirRefundSection').hide();
                $('#main_effectuer_remboursement').prop('checked', false);
                $('#mainAvoirRefundFields').hide();
            } else if (totalAvoir <= (reste + 0.009)) {
                const nouvReste = Math.max(0, reste - totalAvoir);
                $('#mainAvoirTreasuryNotice').html(`
                    <div class="d-flex align-items-center text-info">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div><strong>Absorption sur créance :</strong> L'avoir (${new Intl.NumberFormat('fr-FR').format(totalAvoir)} F) est inférieur au reste dû (${new Intl.NumberFormat('fr-FR').format(reste)} F). Le nouveau reste à payer sera de <strong>${new Intl.NumberFormat('fr-FR').format(nouvReste)} FCFA</strong>. Aucun remboursement immédiat nécessaire.</div>
                    </div>
                `).removeClass('alert-warning alert-light').addClass('alert-info');
                $('#mainAvoirRefundSection').hide();
                $('#main_effectuer_remboursement').prop('checked', false);
                $('#mainAvoirRefundFields').hide();
            } else {
                const soldeClient = Math.min(paye, Math.max(0, totalAvoir - reste));
                $('#mainAvoirTreasuryNotice').html(`
                    <div class="d-flex align-items-center text-primary-emphasis">
                        <i class="fas fa-wallet me-2 fs-5 text-primary"></i>
                        <div>
                            <strong>Solde en faveur du client : ${new Intl.NumberFormat('fr-FR').format(soldeClient)} FCFA.</strong><br>
                            La facture d'origine ayant déjà été réglée (${new Intl.NumberFormat('fr-FR').format(paye)} F), cet avoir dégage un crédit de <strong>${new Intl.NumberFormat('fr-FR').format(soldeClient)} FCFA</strong> en faveur du client.<br>
                            <span class="text-muted" style="font-size: 11px;">
                                <i class="fas fa-info-circle me-1"></i> Par défaut, ce montant est conservé en compte client pour ses futurs achats. Vous pouvez activer l'option ci-dessous pour effectuer un remboursement immédiat.
                            </span>
                        </div>
                    </div>
                `).removeClass('alert-light alert-info alert-warning').addClass('alert-primary');
                $('#mainAvoirRefundSection').slideDown();
                // Décoché par défaut : l'avoir est conservé en compte client sauf demande expresse
                $('#main_effectuer_remboursement').prop('checked', false);
                $('#mainAvoirRefundFields').hide();
                $('#main_remboursement_montant').val(Math.round(soldeClient)).attr('max', Math.round(soldeClient));
                $('#main_remboursement_ref').val('Remboursement suite à Avoir sur ' + $('#cardOrigineRef').text());
            }
        }

        function toggleMainAvoirRefund(checked) {
            if (checked) {
                $('#mainAvoirRefundFields').slideDown();
            } else {
                $('#mainAvoirRefundFields').slideUp();
            }
        }

        function loadFactureDetails(factureId) {
            const data = facturesData[factureId];
            if (data) {
                activeFactureTotal = parseFloat(data.total_facture) || 0;
                activeFacturePaye = parseFloat(data.montant_paye) || 0;
                activeFactureReste = parseFloat(data.reste_a_payer) || 0;

                $('#cardOrigineRef').text(data.code || ('#' + data.id));
                $('#cardOrigineStatut').text(data.statut_paiement || 'Non payée');
                $('#cardOrigineClientNom').text(data.client_name || data.client_nom || 'Client');
                $('#cardOrigineClientIfu').text(data.client_ifu || 'Non renseigné');
                $('#cardOrigineMontant').text(new Intl.NumberFormat('fr-FR').format(data.total_facture || 0) + ' XOF');
                $('#cardOriginePaye').text(new Intl.NumberFormat('fr-FR').format(data.montant_paye || 0) + ' XOF');
                $('#cardOrigineReste').text(new Intl.NumberFormat('fr-FR').format(data.reste_a_payer || 0) + ' XOF');
                $('#cardOrigineSoldeDispo').text(new Intl.NumberFormat('fr-FR').format(data.solde_disponible || 0) + ' XOF');
                $('#avoirOrigineDetailsCard').slideDown();

                // Gestion RAN si la facture a déjà subi un avoir
                $('#havingRanAlert').remove();
                if (data.can_ran === false) {
                    $('#nature_avoir option[value="RAN"]').prop('disabled', true);
                    if ($('#nature_avoir').val() === 'RAN') {
                        $('#nature_avoir').val('RAM');
                    }
                    $('#nature_avoir').after(`
                        <div id="havingRanAlert" class="alert alert-warning py-1.5 px-2.5 small mt-1.5 mb-0 d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                            <div><strong>Annulation Totale (RAN) verrouillée :</strong> Cette facture a déjà fait l'objet d'un avoir partiel. Seuls les avoirs partiels (RAM, RRR ou COR) sur le reliquat restant sont autorisés.</div>
                        </div>
                    `);
                } else {
                    $('#nature_avoir option[value="RAN"]').prop('disabled', false);
                }

                // Vérification du solde disponible
                if (parseFloat(data.solde_disponible) <= 0.009) {
                    showStickyFormAlert('error', 'Cette facture est déjà intégralement annulée ou couverte par des avoirs (Solde disponible : 0 FCFA).');
                    $('#avoirCreateForm button[type=submit], button[form=avoirCreateForm]').prop('disabled', true);
                } else {
                    $('#avoirCreateForm button[type=submit], button[form=avoirCreateForm]').prop('disabled', false);
                }

                $('input[name=reference]').val(data.reference || ('Facture ' + data.code));
                $('input[name=remise_1]').val(data.remise_1 || 0);
                $('input[name=remise_2]').val(data.remise_2 || 0);
                InvoiceLines.replaceRows(data.lignes || []);
                $('select[name="contribuables_id"]').val(data.client_id).trigger('change');
                $('input[name="objet"]').val('Avoir sur facture ' + data.code + (data.objet ? ' - ' + data.objet : ''));

                onNatureAvoirChange($('#nature_avoir').val());
                setTimeout(evaluateMainAvoirTreasury, 150);
            } else {
                activeFactureTotal = 0;
                activeFacturePaye = 0;
                activeFactureReste = 0;
                $('#havingRanAlert').remove();
                $('#nature_avoir option[value="RAN"]').prop('disabled', false);
                $('#avoirOrigineDetailsCard').slideUp();
                evaluateMainAvoirTreasury();
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
                onNatureAvoirChange($('#nature_avoir').val());
            });

            $(document).on('change', 'select[name="facture_id"]', function() {
                const factureId = $(this).val();
                if (factureId) {
                    loadFactureDetails(factureId);
                } else {
                    $('#avoirOrigineDetailsCard').slideUp();
                }
            });

            $(document).on('input change', '#elementsContainer input, #elementsContainer select, #addRowBtn, .removeRowBtn', function() {
                setTimeout(evaluateMainAvoirTreasury, 100);
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

            // Initialisation de DataTable sur la liste des factures d'avoir
            let avoirsDataTable = null;
            if ($.fn.DataTable && $('#avoirsTable').length) {
                avoirsDataTable = $('#avoirsTable').DataTable({
                    order: [[5, 'desc']], // Tri par date décroissante par défaut
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
                    columnDefs: [
                        { targets: [6], orderable: false, searchable: false }
                    ],
                    language: {
                        processing: "Traitement en cours...",
                        search: "Recherche :",
                        lengthMenu: "Afficher _MENU_ avoirs",
                        info: "Affichage de _START_ à _END_ sur _TOTAL_ avoirs",
                        infoEmpty: "Affichage de 0 à 0 sur 0 avoir",
                        infoFiltered: "(filtré sur _MAX_ avoirs)",
                        zeroRecords: "Aucun avoir trouvé",
                        paginate: { first: "Premier", previous: "Précédent", next: "Suivant", last: "Dernier" }
                    }
                });
            }

            window.searchTable = function() {
                const val = document.getElementById("searchInput").value;
                if (avoirsDataTable) {
                    avoirsDataTable.search(val).draw();
                } else {
                    const filter = val.toUpperCase();
                    $('#avoirsTable tbody tr').each(function() {
                        $(this).toggle($(this).text().toUpperCase().indexOf(filter) > -1);
                    });
                }
            };

            @if ($errors->any() || session('form_error'))
                new bootstrap.Modal(document.getElementById('createAvoirModal')).show();
            @endif

            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
@endsection
