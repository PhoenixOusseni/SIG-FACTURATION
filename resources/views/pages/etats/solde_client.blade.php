@extends('layouts.master')

@section('title')
    <title>SIG - CFE | État du solde par client</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .filter-card {
            background: #fff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .filter-card .card-body {
            padding: 25px 30px;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            transition: all 0.2s;
        }

        .btn-search {
            background: #315192;
            border: none;
            padding: 10px 28px;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
        }

        .btn-search:hover {
            background: #1a2942;
            color: #fff;
        }

        .btn-reset {
            background: #6c757d;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
        }

        .result-card {
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
            border: none;
            margin-top: 25px;
        }

        .client-info-box {
            background: linear-gradient(135deg, #315192 0%, #1a2942 100%);
            color: #fff;
            padding: 22px 25px;
            border-radius: 12px;
            margin-bottom: 22px;
        }

        .client-info-box h4 {
            font-weight: 700;
            margin-bottom: 12px;
        }

        .client-detail {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 0.95rem;
        }

        .client-detail:last-child {
            border-bottom: none;
        }

        .stats-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border-left: 4px solid #315192;
        }

        .stats-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .stats-value {
            font-size: 1.6rem;
            font-weight: bold;
            color: #1a2942;
        }

        .stats-value.success { color: #28a745; }
        .stats-value.warning { color: #f39c12; }
        .stats-value.danger  { color: #dc3545; }

        .table-solde thead {
            background: #315192;
            color: #fff;
        }

        .table-solde th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            padding: 12px 10px;
            border: none;
            letter-spacing: 0.4px;
        }

        .table-solde td {
            padding: 11px 10px;
            vertical-align: middle;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .summary-row {
            background-color: #e9ecef;
            font-weight: 700;
            font-size: 1rem;
        }

        .no-data-message {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-data-message i {
            width: 60px;
            height: 60px;
            margin-bottom: 14px;
            opacity: 0.4;
        }

        .grand-total {
            background: linear-gradient(135deg, #315192 0%, #1a2942 100%);
            color: #fff;
            border-radius: 12px;
            padding: 22px 28px;
            margin-top: 25px;
        }

        .grand-total .lbl {
            text-transform: uppercase;
            font-size: 0.85rem;
            opacity: 0.9;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .grand-total .val {
            font-size: 1.5rem;
            font-weight: 800;
        }
    </style>
@endsection

@section('content')
    <header class="page-header page-header-dark header-gradient pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="users"></i></div>
                            État du solde par client
                        </h1>
                        <div class="page-header-subtitle">Visualisez le solde de chaque client (facturé, réglé, restant)</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10" style="margin-bottom: 6rem;">

        {{-- ========== Filtre ========== --}}
        <div class="card filter-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Critères de recherche</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('solde_client') }}" method="GET">
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label for="contribuable_id" class="form-label">Client</label>
                            <select name="contribuable_id" id="contribuable_id" class="form-select">
                                <option value="">-- Tous les clients --</option>
                                @foreach ($contribuables as $c)
                                    <option value="{{ $c->id }}" {{ $contribuableId == $c->id ? 'selected' : '' }}>
                                        {{ $c->assujeti }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control"
                                value="{{ $dateDebut }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control"
                                value="{{ $dateFin }}">
                        </div>
                        <div class="col-md-1 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i data-feather="search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========== Résultats ========== --}}
        @if ($resultats->count() > 0)
            <div class="result-card card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Résultats
                    </h5>
                    <a href="{{ route('print_solde_client', ['contribuable_id' => $contribuableId, 'date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}"
                       target="_blank" class="btn btn-primary btn-sm">
                        <i data-feather="printer"></i>&nbsp;Imprimer l'état
                    </a>
                </div>
                <div class="card-body">

                    @foreach ($resultats as $resultat)
                        <div class="client-info-box">
                            <h4 class="text-white">{{ optional($resultat->client)->assujeti ?? 'Client inconnu' }}</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="client-detail">
                                        <span>IFU :</span>
                                        <strong>{{ optional($resultat->client)->ifu ?? '—' }}</strong>
                                    </div>
                                    <div class="client-detail">
                                        <span>RCCM :</span>
                                        <strong>{{ optional($resultat->client)->rccm ?? '—' }}</strong>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="client-detail">
                                        <span>Téléphone :</span>
                                        <strong>{{ optional($resultat->client)->telephone ?? '—' }}</strong>
                                    </div>
                                    <div class="client-detail">
                                        <span>Adresse :</span>
                                        <strong>{{ optional($resultat->client)->adresse ?? '—' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-label">Nombre de factures</div>
                                    <div class="stats-value">{{ $resultat->factures->count() }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-label">Total facturé</div>
                                    <div class="stats-value success">
                                        {{ number_format($resultat->total_facture, 0, ',', ' ') }}
                                        <small class="text-muted" style="font-size:0.8rem;">FCFA</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive table-solde">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:5%;">#</th>
                                        <th style="width:12%;">Date</th>
                                        <th style="width:14%;">Code facture</th>
                                        <th style="width:14%;">Référence</th>
                                        <th style="width:13%;">Service</th>
                                        <th class="text-end" style="width:12%;">Montant TTC</th>
                                        <th class="text-end" style="width:11%;">Solde</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resultat->factures as $index => $facture)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}</td>
                                            <td><strong>{{ $facture->code }}</strong></td>
                                            <td>{{ $facture->reference ?? '—' }}</td>
                                            <td>{{ optional($facture->Service)->libelle ?? '—' }}</td>
                                            <td class="text-end">
                                                <strong>{{ number_format($facture->montant_ttc, 0, ',', ' ') }}</strong>
                                            </td>
                                            <td class="text-end {{ $facture->solde > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                                {{ number_format($facture->solde, 0, ',', ' ') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="summary-row">
                                        <td colspan="5" class="text-end"><strong>TOTAL CLIENT</strong></td>
                                        <td class="text-end">{{ number_format($resultat->total_facture, 0, ',', ' ') }}</td>
                                        <td class="text-end {{ $resultat->solde > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($resultat->solde, 0, ',', ' ') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        @if (!$loop->last)
                            <hr class="my-5">
                        @endif
                    @endforeach

                    {{-- Récapitulatif général --}}
                    @if ($resultats->count() > 1)
                        <div class="grand-total">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center">
                                    <div class="lbl">Total facturé global</div>
                                    <div class="val">{{ number_format($grandTotalFacture, 0, ',', ' ') }} FCFA</div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="lbl">Total réglé global</div>
                                    <div class="val">{{ number_format($grandTotalRegle, 0, ',', ' ') }} FCFA</div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="lbl">Solde global</div>
                                    <div class="val">{{ number_format($grandTotalSolde, 0, ',', ' ') }} FCFA</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif ($contribuableId || $dateDebut || $dateFin)
            <div class="result-card card">
                <div class="card-body">
                    <div class="no-data-message">
                        <i data-feather="inbox"></i>
                        <h4>Aucun résultat trouvé</h4>
                        <p>Aucune facture ne correspond aux critères sélectionnés.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="result-card card">
                <div class="card-body">
                    <div class="no-data-message">
                        <i data-feather="search"></i>
                        <h4>Veuillez effectuer une recherche</h4>
                        <p>Sélectionnez un client et/ou une période pour afficher le solde.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('script')
    @include('partials.script')
    <script>
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    </script>
@endsection
