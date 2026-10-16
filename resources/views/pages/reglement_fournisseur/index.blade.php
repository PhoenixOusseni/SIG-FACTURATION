@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Règlements fournisseurs</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        :root {
            --primary-color: #315192;
            --text-light: #6c757d;
        }

        .stat-card {
            border: 0;
            border-radius: 10px;
            color: #fff;
        }

        .stat-card .label {
            font-size: .8rem;
            text-transform: uppercase;
            opacity: .85;
        }

        .stat-card .value {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .badge-mode {
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 6px;
        }

        .badge-soldée    { background:#d1fae5; color:#065f46; }
        .badge-partielle { background:#fef3c7; color:#92400e; }
        .badge-impayée   { background:#fee2e2; color:#991b1b; }
    </style>
@endsection

@section('content')
    <main>
        <x-page-header icon="dollar-sign" title="Règlements des factures fournisseurs"></x-page-header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom:6rem;">

            {{-- Stats --}}
            @php
                $totalDecaisse = $collection->sum('montant');
                $nb = $collection->count();
                $nbFacturesOuvertes = $factures->count();
            @endphp
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card" style="background:linear-gradient(135deg,#315192,#1a2942);">
                        <div class="card-body">
                            <div class="label">Total décaissé</div>
                            <div class="value">{{ number_format($totalDecaisse, 0, ',', ' ') }} FCFA</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card" style="background:linear-gradient(135deg,#10b981,#047857);">
                        <div class="card-body">
                            <div class="label">Nombre de règlements</div>
                            <div class="value">{{ $nb }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card" style="background:linear-gradient(135deg,#f59e0b,#b45309);">
                        <div class="card-body">
                            <div class="label">Factures en cours</div>
                            <div class="value">{{ $nbFacturesOuvertes }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i data-feather="list" style="width:18px;height:18px;"></i> Liste des règlements fournisseurs</span>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newReglementModal">
                        <i class="fa fa-plus"></i>&nbsp;Nouveau règlement
                    </button>
                </div>
                <div class="card-body">
                    <div style="margin-bottom:1rem;">
                        <input type="text" placeholder="Rechercher un règlement..." class="form-control"
                            id="searchInput" onkeyup="searchTable()" />
                    </div>

                    @if ($collection->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="searchTable">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Facture</th>
                                        <th>Fournisseur</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Réf. paiement</th>
                                        <th style="width:120px; text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $r)
                                        <tr>
                                            <td><strong style="color:var(--primary-color);">{{ $r->code }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('module_facture_fournisseur.show', $r->facture_fournisseur_id) }}">
                                                    {{ optional($r->FactureFournisseur)->code ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td>{{ optional(optional($r->FactureFournisseur)->Fournisseur)->assujeti ?? '—' }}</td>
                                            <td><span class="badge-mode" style="background:#e0e7ff;color:#3730a3;">{{ $r->mode }}</span></td>
                                            <td class="text-end"><strong>{{ number_format($r->montant, 0, ',', ' ') }} FCFA</strong></td>
                                            <td>{{ $r->reference_paiement ?? '—' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('module_reglement_fournisseur.show', $r->id) }}" class="btn btn-sm btn-success">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('print_reglement_fournisseur', $r->id) }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        {{-- Modal édition --}}
                                        <div class="modal fade" id="editReglementModal{{ $r->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form action="{{ route('module_reglement_fournisseur.update', $r->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Modifier le règlement {{ $r->code }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="small">Date</label>
                                                                    <input type="date" name="date" class="form-control"
                                                                        value="{{ $r->date }}" required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="small">Mode</label>
                                                                    <select name="mode" class="form-select" required>
                                                                        @foreach (['Espèces','Chèque','Virement','Mobile Money','Carte bancaire'] as $m)
                                                                            <option value="{{ $m }}" {{ $r->mode == $m ? 'selected' : '' }}>{{ $m }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="small">Montant (FCFA)</label>
                                                                    <input type="number" step="0.01" min="0.01" name="montant"
                                                                        class="form-control" value="{{ $r->montant }}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="small">Référence paiement</label>
                                                                    <input type="text" name="reference_paiement"
                                                                        class="form-control" value="{{ $r->reference_paiement }}">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="small">Observation</label>
                                                                    <textarea name="observation" class="form-control" rows="2">{{ $r->observation }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="m-3">
                                                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align:center; padding:3rem 1rem; color:var(--text-light);">
                            <i data-feather="inbox" style="width:48px;height:48px;opacity:.5;"></i>
                            <p class="mt-2">Aucun règlement fournisseur enregistré</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ==================== MODAL : NOUVEAU RÈGLEMENT ==================== --}}
        <div class="modal fade" id="newReglementModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('module_reglement_fournisseur.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau règlement fournisseur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                        </div>
                        <div class="modal-body">
                            @if ($factures->isEmpty())
                                <div class="alert alert-info mb-0">
                                    Toutes les factures fournisseurs sont soldées. Aucun règlement à enregistrer.
                                </div>
                            @else
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="small">Facture fournisseur <span class="text-danger">*</span></label>
                                        <select name="facture_fournisseur_id" id="factureSelect" class="form-select select2-input" required style="width:100%">
                                            <option value="">Sélectionner une facture fournisseur</option>
                                            @foreach ($factures as $f)
                                                <option value="{{ $f->id }}"
                                                    data-total="{{ $f->montant_total }}"
                                                    data-paye="{{ $f->montant_paye }}"
                                                    data-reste="{{ $f->reste_a_payer }}">
                                                    {{ $f->code }} — {{ optional($f->Fournisseur)->assujeti }}
                                                    (reste : {{ number_format($f->reste_a_payer, 0, ',', ' ') }} FCFA)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small">Total facture</label>
                                        <input type="text" id="totalFacture" class="form-control bg-light" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Déjà payé</label>
                                        <input type="text" id="payeFacture" class="form-control bg-light" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Reste à payer</label>
                                        <input type="text" id="resteFacture" class="form-control bg-light" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="small">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Mode <span class="text-danger">*</span></label>
                                        <select name="mode" class="form-select" required>
                                            @foreach (['Espèces','Chèque','Virement','Mobile Money','Carte bancaire'] as $m)
                                                <option value="{{ $m }}">{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Montant (FCFA) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0.01" name="montant" id="montantInput" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="small">Référence paiement</label>
                                        <input type="text" name="reference_paiement" class="form-control" placeholder="N° chèque / transaction...">
                                    </div>
                                    <div class="col-12">
                                        <label class="small">Observation</label>
                                        <textarea name="observation" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="m-3">
                            @if (!$factures->isEmpty())
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        $(function () {
            function initSelect2(scope) {
                $(scope).find('select.select2-input').each(function () {
                    if ($(this).hasClass('select2-hidden-accessible')) return;
                    var $modal = $(this).closest('.modal');
                    var options = { width: '100%' };
                    if ($modal.length) options.dropdownParent = $modal;
                    $(this).select2(options);
                });
            }

            initSelect2(document);

            $('#newReglementModal').on('shown.bs.modal', function () {
                initSelect2(this);
            });
            $('[id^="editReglementModal"]').on('shown.bs.modal', function () {
                initSelect2(this);
            });

            $('#factureSelect').on('change', function () {
                var opt = $(this).find(':selected');
                var fmt = function (v) {
                    if (!v) return '';
                    return Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' FCFA';
                };
                $('#totalFacture').val(fmt(opt.data('total')));
                $('#payeFacture').val(fmt(opt.data('paye')));
                $('#resteFacture').val(fmt(opt.data('reste')));
                $('#montantInput').attr('max', opt.data('reste') || '').val(opt.data('reste') || '');
            });
        });
    </script>
@endsection
