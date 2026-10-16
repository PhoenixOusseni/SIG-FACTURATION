@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Règlements des dépenses</title>
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

        .badge-soldée {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-partielle {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-impayée {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
@endsection

@section('content')
    <main>
        <x-page-header icon="credit-card" title="Règlements des dépenses"></x-page-header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom:6rem;">

            {{-- Stats --}}
            @php
                $totalDecaisse = $collection->sum('montant');
                $nb = $collection->count();
                $nbDepensesOuvertes = $depenses->count();
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
                            <div class="label">Dépenses en cours</div>
                            <div class="value">{{ $nbDepensesOuvertes }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Liste des dépenses non soldées --}}
            @if ($depenses->count() > 0)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i data-feather="alert-circle" style="width:18px;height:18px;"></i>
                            Dépenses en attente de règlement ({{ $depenses->count() }})
                        </span>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newReglementModal">
                            <i class="fa fa-plus"></i>&nbsp;Nouveau règlement
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Motif</th>
                                        <th>Fournisseur</th>
                                        <th class="text-end">Montant total</th>
                                        <th class="text-end">Déjà payé</th>
                                        <th class="text-end">Reste à payer</th>
                                        <th>Statut</th>
                                        <th class="text-center" style="width:90px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($depenses as $d)
                                        <tr>
                                            <td><strong style="color:var(--primary-color);">{{ $d->code ?? '—' }}</strong>
                                            </td>
                                            <td>{{ $d->date ? \Carbon\Carbon::parse($d->date)->format('d/m/Y') : '—' }}</td>
                                            <td>{{ $d->motif ?? '—' }}</td>
                                            <td>{{ optional($d->fournisseur)->assujeti ?? '—' }}</td>
                                            <td class="text-end">{{ number_format($d->montant, 0, ',', ' ') }}</td>
                                            <td class="text-end text-success">
                                                {{ number_format($d->montant_paye, 0, ',', ' ') }}</td>
                                            <td class="text-end text-danger">
                                                <strong>{{ number_format($d->reste_a_payer, 0, ',', ' ') }}</strong>
                                            </td>
                                            <td>
                                                @if ($d->statut_paiement === 'partielle')
                                                    <span class="badge-partielle"
                                                        style="padding:4px 10px;border-radius:6px;font-size:.8rem;">Partielle</span>
                                                @else
                                                    <span class="badge-impayée"
                                                        style="padding:4px 10px;border-radius:6px;font-size:.8rem;">Impayée</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#reglDepenseModal{{ $d->id }}" title="Régler">
                                                    <i class="fa fa-dollar-sign"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        {{-- Modal règlement rapide par dépense --}}
                                        <div class="modal fade" id="reglDepenseModal{{ $d->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('reglement_depense.store', $d->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Règlement — {{ $d->motif ?? $d->code }}
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal">X</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-info py-2 mb-3">
                                                                Reste à payer :
                                                                <strong>{{ number_format($d->reste_a_payer, 0, ',', ' ') }}</strong>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="small">Date <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="date" name="date"
                                                                        class="form-control" value="{{ date('Y-m-d') }}"
                                                                        required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="small">Mode <span
                                                                            class="text-danger">*</span></label>
                                                                    <select name="mode" class="form-select" required>
                                                                        @foreach (['Espèces', 'Chèque', 'Virement', 'Mobile Money', 'Carte bancaire'] as $m)
                                                                            <option value="{{ $m }}">
                                                                                {{ $m }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="small">Montant (FCFA) <span
                                                                            class="text-danger">*</span></label>
                                                                    <input type="number" step="0.01" min="0.01"
                                                                        max="{{ $d->reste_a_payer }}" name="montant"
                                                                        class="form-control"
                                                                        value="{{ $d->reste_a_payer }}" required>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="small">Référence paiement</label>
                                                                    <input type="text" name="reference_paiement"
                                                                        class="form-control"
                                                                        placeholder="N° chèque / transaction...">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="small">Observation</label>
                                                                    <textarea name="observation" class="form-control" rows="2"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-light"
                                                                data-bs-dismiss="modal">Annuler</button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fa fa-save"></i>&nbsp; Enregistrer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i data-feather="list" style="width:18px;height:18px;"></i> Liste des règlements de
                        dépenses</span>
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
                                        <th>Dépense (motif)</th>
                                        <th>Fournisseur</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Réf. paiement</th>
                                        <th style="width:100px; text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($collection as $r)
                                        <tr>
                                            <td><strong style="color:var(--primary-color);">{{ $r->code }}</strong>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('module_depense.show', $r->depense_id) }}">
                                                    {{ optional($r->depense)->motif ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td>{{ optional(optional($r->depense)->fournisseur)->assujeti ?? '—' }}</td>
                                            <td><span class="badge-mode"
                                                    style="background:#e0e7ff;color:#3730a3;">{{ $r->mode }}</span>
                                            </td>
                                            <td class="text-end"><strong>{{ number_format($r->montant, 0, ',', ' ') }}</strong></td>
                                            <td>{{ $r->reference_paiement ?? '—' }}</td>
                                            <td class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('print_reglement_depense', $r->id) }}" target="_blank"
                                                    class="btn btn-sm btn-primary" title="Imprimer">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a href="{{ route('supp_reglement_depense', $r->id) }}"
                                                    class="btn btn-sm btn-danger" title="Supprimer"
                                                    onclick="return confirm('Supprimer ce règlement ?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div style="text-align:center; padding:3rem 1rem; color:var(--text-light);">
                            <i data-feather="inbox" style="width:48px;height:48px;opacity:.5;"></i>
                            <p class="mt-2">Aucun règlement de dépense enregistré</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ==================== MODAL : NOUVEAU RÈGLEMENT (sélection libre) ==================== --}}
        <div class="modal fade" id="newReglementModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouveau règlement de dépense</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                    </div>
                    <div class="modal-body">
                        @if ($depenses->isEmpty())
                            <div class="alert alert-info mb-0">
                                Toutes les dépenses sont soldées. Aucun règlement à enregistrer.
                            </div>
                        @else
                            <p class="text-muted small mb-3">
                                Sélectionnez une dépense dans le tableau ci-dessous pour la régler.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Motif</th>
                                            <th>Fournisseur</th>
                                            <th class="text-end">Reste à payer</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($depenses as $d)
                                            <tr>
                                                <td>{{ $d->motif ?? ($d->code ?? '—') }}</td>
                                                <td>{{ optional($d->fournisseur)->assujeti ?? '—' }}</td>
                                                <td class="text-end text-danger">
                                                    <strong>{{ number_format($d->reste_a_payer, 0, ',', ' ') }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        data-bs-dismiss="modal" data-bs-toggle="modal"
                                                        data-bs-target="#reglDepenseModal{{ $d->id }}">
                                                        <i class="fa fa-dollar-sign"></i> Régler
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    <script>
        function searchTable() {
            var input = document.getElementById('searchInput').value.toLowerCase();
            var rows = document.querySelectorAll('#searchTable tbody tr');
            rows.forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
            });
        }
    </script>
@endsection
