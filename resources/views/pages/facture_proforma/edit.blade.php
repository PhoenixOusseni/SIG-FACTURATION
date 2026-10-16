@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Modifier facture proforma</title>
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
                                Modifier la facture proforma {{ $facture->code }}
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <div
                                style="background: rgba(255,255,255,0.2); padding: 0.75rem 1.5rem; border-radius: 0.5rem; color: white; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
                                <i data-feather="calendar" style="width: 18px; height: 18px;"></i>
                                {{ Carbon\Carbon::now()->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Modification de la facture proforma
                    <a href="{{ route('module_facture_proforma.show', $facture->id) }}" class="btn btn-light btn-sm">
                        <i data-feather="arrow-left"></i>&thinsp;&thinsp; Retour
                    </a>
                </div>
                <div class="card-body">
                    <x-form-errors class="mb-3" />
                    <div id="invoiceFormAlerts" class="mb-3"></div>

                    <form action="{{ route('module_facture_proforma.update', $facture->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Informations générales --}}
                        <fieldset class="mb-4"
                            style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                            <legend
                                style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                📋 Informations générales</legend>

                            <input class="form-control" name="users_id" type="hidden"
                                value="{{ $facture->users_id ?? Auth::user()->id }}" />

                            <div class="row mb-3">
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Client <span class="text-danger">*</span></label>
                                        <select name="contribuables_id" class="form-select select2-input" required
                                            style="width:100%">
                                            <option value="">Sélectionner le client</option>
                                            @foreach ($contribuables as $contribuable)
                                                <option value="{{ $contribuable->id }}"
                                                    {{ $facture->contribuables_id == $contribuable->id ? 'selected' : '' }}>
                                                    {{ $contribuable->assujeti }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Objet</label>
                                        <input class="form-control" name="objet" type="text"
                                            value="{{ $facture->objet }}" placeholder="Objet de la facture" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-2 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Référence</label>
                                        <input class="form-control" name="reference" type="text"
                                            value="{{ $facture->reference }}" placeholder="Référence" />
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Remise 1 (XOF)</label>
                                        <input class="form-control" name="remise_1" type="number" step="0.01"
                                            value="{{ $facture->remise_1 }}" placeholder="Remise 1" />
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Remise 2 (XOF)</label>
                                        <input class="form-control" name="remise_2" type="number" step="0.01"
                                            value="{{ $facture->remise_2 }}" placeholder="Remise 2" />
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Date</label>
                                        <input class="form-control" name="date" type="date"
                                            value="{{ $facture->date }}" />
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-12">
                                    <div class="form-group">
                                        <label class="small">Statut</label>
                                        <select name="statut" class="form-select">
                                            @foreach (['en attente', 'transformée', 'annulée'] as $st)
                                                <option value="{{ $st }}"
                                                    {{ $facture->statut == $st ? 'selected' : '' }}>
                                                    {{ ucfirst($st) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        {{-- Éléments de la facture --}}
                        <fieldset class="mb-4 elements-fieldset"
                            style="border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem; background: rgba(49, 81, 146, 0.05);">
                            <legend
                                style="font-weight: 600; color: var(--primary-color); padding: 0 1rem; margin-left: -1rem;">
                                📦 Éléments de la facture</legend>

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
                                            @forelse ($elements as $i => $element)
                                                <tr class="element-row">
                                                    <td class="text-center">
                                                        <input type="hidden" name="element_id[]"
                                                            value="{{ $element->id }}">
                                                        <span class="row-index">{{ $i + 1 }}</span>
                                                    </td>
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
                                                            value="{{ $element->quantite }}" />
                                                    </td>
                                                    <td>
                                                        <input class="form-control text-end prix-input"
                                                            name="prix_unitaire[]" type="number" step="0.01"
                                                            value="{{ $element->prix_unitaire }}" />
                                                    </td>
                                                    <td class="text-end montant-cell">0</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn-remove-row removeRowBtn">
                                                            <i data-feather="trash-2" style="width:15px;height:15px;"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="element-row">
                                                    <td class="text-center">
                                                        <input type="hidden" name="element_id[]" value="">
                                                        <span class="row-index">1</span>
                                                    </td>
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
                                                            <i data-feather="trash-2" style="width:15px;height:15px;"></i>
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
                            <a href="{{ route('module_facture_proforma.show', $facture->id) }}" class="btn btn-secondary">
                                Annuler
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

        $(document).ready(function() {
            InvoiceLines.init({
                container: '#elementsContainer',
                products: produitsData,
                enforceStock: true,
                defaultQty: 1,
                mergeDuplicates: true,
                includeElementId: true,
                alertTarget: '#invoiceFormAlerts',
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
