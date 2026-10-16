@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Modifier Facture d'Avoir</title>
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
                                <div class="page-header-icon"><i data-feather="edit"></i></div>
                                Modifier la facture d'avoir
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <div class="card mb-4">
                <div class="card-body">
                    <x-form-errors class="mb-3" />
                    <div id="invoiceFormAlerts" class="mb-3"></div>

                    <form action="{{ route('module_facture_avoir.update', $facture_avoir->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Informations générales -->
                        <fieldset class="mb-4"
                            style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                            <legend
                                style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                📋 Informations générales</legend>

                            <div class="row mb-3">
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Facture d'origine <span class="text-danger">*</span></label>
                                        <select name="facture_id" class="form-select select2-input" required
                                            style="width:100%">
                                            <option value="">Sélectionner la facture</option>
                                            @foreach ($factures as $facture)
                                                <option value="{{ $facture->id }}"
                                                    {{ $facture_avoir->facture_id == $facture->id ? 'selected' : '' }}>
                                                    {{ $facture->code }} -
                                                    {{ $facture->Contribuable->assujeti ?? 'N/A' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Client <span class="text-danger">*</span></label>
                                        <select name="contribuables_id" class="form-select select2-input" required
                                            style="width:100%">
                                            <option value="">Sélectionner le client</option>
                                            @foreach ($contribuables as $contribuable)
                                                <option value="{{ $contribuable->id }}"
                                                    {{ $facture_avoir->contribuables_id == $contribuable->id ? 'selected' : '' }}>
                                                    {{ $contribuable->assujeti }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Motif de l'avoir <span class="text-danger">*</span></label>
                                        <select name="motif" id="motif_avoir" class="form-select" required>
                                            <option value="">Sélectionner le motif</option>
                                            <option value="retour"
                                                {{ $facture_avoir->motif == 'retour' ? 'selected' : '' }}>Retour de
                                                marchandise</option>
                                            <option value="erreur"
                                                {{ $facture_avoir->motif == 'erreur' ? 'selected' : '' }}>Erreur sur la
                                                facture</option>
                                            <option value="annulation"
                                                {{ $facture_avoir->motif == 'annulation' ? 'selected' : '' }}>Annulation
                                                de vente</option>
                                            <option value="remise"
                                                {{ $facture_avoir->motif == 'remise' ? 'selected' : '' }}>Remise/Rabais
                                                accordé</option>
                                            <option value="produit_defectueux"
                                                {{ $facture_avoir->motif == 'produit_defectueux' ? 'selected' : '' }}>
                                                Produit défectueux</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Nature DGI <span class="text-danger">*</span></label>
                                        <select name="nature_avoir" id="nature_avoir" class="form-select" required>
                                            @foreach (['COR' => 'COR — Correction', 'RAN' => 'RAN — Annulation', 'RAM' => 'RAM — Reprise', 'RRR' => 'RRR — Remise/ristourne'] as $code => $label)
                                                <option value="{{ $code }}"
                                                    {{ $facture_avoir->nature_avoir == $code ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Objet</label>
                                        <input type="text" class="form-control" name="objet"
                                            value="{{ $facture_avoir->objet }}" placeholder="Objet" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-2 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Référence</label>
                                        <input class="form-control" name="reference" type="text"
                                            value="{{ $facture_avoir->reference }}" placeholder="Référence" />
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Remise 1 (XOF)</label>
                                        <input class="form-control" name="remise_1" type="number" step="0.01"
                                            value="{{ $facture_avoir->remise_1 }}" placeholder="Remise 1" />
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Remise 2 (XOF)</label>
                                        <input class="form-control" name="remise_2" type="number" step="0.01"
                                            value="{{ $facture_avoir->remise_2 }}" placeholder="Remise 2" />
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Date</label>
                                        <input class="form-control" name="date" type="date"
                                            value="{{ $facture_avoir->date }}" />
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Éléments de la facture d'avoir -->
                        <fieldset class="mb-4 elements-fieldset"
                            style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background: rgba(49, 81, 146, 0.05);">
                            <legend
                                style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                📦 Éléments de l'avoir</legend>

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
                                            @forelse($elements as $index => $element)
                                                <tr class="element-row">
                                                    <td class="text-center"><span
                                                            class="row-index">{{ $index + 1 }}</span></td>
                                                    <td>
                                                        <select class="form-select base-taxable-select select2-input"
                                                            name="produits_id[]" style="width:100%">
                                                            <option value="">Sélectionner un produit</option>
                                                            @foreach ($produits as $item)
                                                                <option value="{{ $item->id }}"
                                                                    {{ $element->produits_id == $item->id ? 'selected' : '' }}>
                                                                    {{ $item->code }} - {{ $item->designation }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input class="form-control text-center qte-input"
                                                            name="quantite[]" type="number" step="0.01"
                                                            value="{{ $element->quantite }}" placeholder="1" />
                                                    </td>
                                                    <td>
                                                        <input class="form-control text-end prix-input"
                                                            name="prix_unitaire[]" type="number" step="0.01"
                                                            value="{{ $element->prix_unitaire }}"
                                                            placeholder="0.00" />
                                                    </td>
                                                    <td class="text-end montant-cell">
                                                        {{ number_format($element->montant_total ?? ($element->quantite * $element->prix_unitaire), 2, '.', '') }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn-remove-row removeRowBtn">
                                                            <i data-feather="trash-2"
                                                                style="width:15px;height:15px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="element-row">
                                                    <td class="text-center"><span class="row-index">1</span></td>
                                                    <td>
                                                        <select class="form-select base-taxable-select select2-input"
                                                            name="produits_id[]" style="width:100%">
                                                            <option value="">Sélectionner un produit</option>
                                                            @foreach ($produits as $item)
                                                                <option value="{{ $item->id }}">{{ $item->code }} -
                                                                    {{ $item->designation }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input class="form-control text-center qte-input"
                                                            name="quantite[]" type="number" step="0.01"
                                                            placeholder="1" />
                                                    </td>
                                                    <td>
                                                        <input class="form-control text-end prix-input"
                                                            name="prix_unitaire[]" type="number" step="0.01"
                                                            placeholder="0.00" />
                                                    </td>
                                                    <td class="text-end montant-cell">0</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn-remove-row removeRowBtn">
                                                            <i data-feather="trash-2"
                                                                style="width:15px;height:15px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforelse
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

                        <div style="display: flex; gap: 0.75rem; margin-top: 2rem;">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-save me-2"></i> Enregistrer les modifications
                            </button>
                            <a href="{{ route('module_facture_avoir.show', $facture_avoir->id) }}"
                                class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script src="{{ asset('js/invoice-lines.js') }}"></script>
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

        const facturesData = {!! $facturesDataJson !!};

        function loadFactureDetails(factureId) {
            const data = facturesData[factureId];
            if (data) {
                $('select[name="contribuables_id"]').val(data.client_id).trigger('change');
                $('input[name="objet"]').val(data.objet || '');
            }
        }

        $(document).ready(function() {
            InvoiceLines.init({
                container: '#elementsContainer',
                products: produitsData,
                enforceStock: false,
                defaultQty: 1,
                mergeDuplicates: true,
                includeElementId: false,
                alertTarget: '#invoiceFormAlerts',
            });

            $('select[name="facture_id"]').on('change', function() {
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

            $('.select2-input').not('.base-taxable-select').select2({
                placeholder: 'Sélectionner...',
                allowClear: true,
                width: '100%'
            });

            if (typeof feather !== 'undefined') feather.replace();
        });
    </script>
@endsection
