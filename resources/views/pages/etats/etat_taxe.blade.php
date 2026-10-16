@extends('layouts.master')

@section('title')
    <title>SIG - CFE | État des taxes</title>
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

        .filter-card .card-body { padding: 25px 30px; }

        .form-select, .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }

        .result-card {
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
            border: none;
            margin-top: 25px;
        }

        .kpi-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 16px;
            border-left: 4px solid #315192;
        }

        .kpi-card.tva    { border-left-color: #2980b9; }
        .kpi-card.bic    { border-left-color: #e67e22; }
        .kpi-card.total  { border-left-color: #27ae60; }

        .kpi-label {
            font-size: 0.78rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .kpi-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1a2942;
        }

        .kpi-value small { font-size: 0.75rem; color: #6c757d; font-weight: 500; }

        .taxe-table thead {
            background: #315192;
            color: #fff;
        }

        .taxe-table.bic thead { background: #c2410c; }

        .taxe-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            padding: 11px 10px;
            letter-spacing: 0.4px;
            border: none;
        }

        .taxe-table td {
            padding: 10px;
            vertical-align: middle;
        }

        .taxe-table tfoot td {
            background: #e9ecef;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .section-title {
            font-weight: 700;
            color: #1a2942;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid #315192;
            display: inline-block;
        }

        .section-title.bic { border-bottom-color: #c2410c; }

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

        .badge-taux {
            background: #315192;
            color: #fff;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.78rem;
        }

        .badge-taux.bic { background: #c2410c; }
    </style>
@endsection

@section('content')
    <header class="page-header page-header-dark header-gradient pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="percent"></i></div>
                            État des taxes (TVA &amp; PSVBB)
                        </h1>
                        <div class="page-header-subtitle">Récapitulatif des taxes collectées sur la période</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10" style="margin-bottom: 6rem;">

        {{-- ========== Filtres ========== --}}
        <div class="card filter-card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Critères de recherche</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('etat_taxe') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="tva_id" class="form-label">TVA</label>
                            <select name="tva_id" id="tva_id" class="form-select">
                                <option value="">-- Toutes les TVA --</option>
                                @foreach ($tvas as $t)
                                    <option value="{{ $t->id }}" {{ $tvaId == $t->id ? 'selected' : '' }}>
                                        {{ $t->intitule }} ({{ rtrim(rtrim(number_format($t->taux, 2, ',', ''), '0'), ',') }} %)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="bic_id" class="form-label">PSVBB</label>
                            <select name="bic_id" id="bic_id" class="form-select">
                                <option value="">-- Tous les PSVBB --</option>
                                @foreach ($bics as $b)
                                    <option value="{{ $b->id }}" {{ $bicId == $b->id ? 'selected' : '' }}>
                                        {{ $b->intitule }} ({{ rtrim(rtrim(number_format($b->taux, 2, ',', ''), '0'), ',') }} %)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control"
                                value="{{ $dateDebut }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control"
                                value="{{ $dateFin }}">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i data-feather="search"></i>&nbsp;Rechercher
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========== Résultats ========== --}}
        @if ($factures->count() > 0)

            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('print_etat_taxe', ['tva_id' => $tvaId, 'bic_id' => $bicId, 'date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}"
                   target="_blank" class="btn btn-primary btn-sm">
                    <i data-feather="printer"></i>&nbsp;Imprimer l'état
                </a>
            </div>

            {{-- KPI ----------------------------------------------------------------- --}}
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card">
                        <div class="kpi-label">Total HT</div>
                        <div class="kpi-value">{{ number_format($totalHt, 0, ',', ' ') }} <small>FCFA</small></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card tva">
                        <div class="kpi-label">Total TVA collectée</div>
                        <div class="kpi-value" style="color:#2980b9;">{{ number_format($totalTva, 0, ',', ' ') }} <small>FCFA</small></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card bic">
                        <div class="kpi-label">Total PSVBB collecté</div>
                        <div class="kpi-value" style="color:#e67e22;">{{ number_format($totalBic, 0, ',', ' ') }} <small>FCFA</small></div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="kpi-card total">
                        <div class="kpi-label">Total taxes (TVA + PSVBB)</div>
                        <div class="kpi-value" style="color:#27ae60;">{{ number_format($totalTva + $totalBic, 0, ',', ' ') }} <small>FCFA</small></div>
                    </div>
                </div>
            </div>

            {{-- Récap par taux ------------------------------------------------------ --}}
            <div class="row">
                <div class="col-lg-6">
                    <div class="result-card card">
                        <div class="card-body">
                            <h5 class="section-title">Récapitulatif TVA par taux</h5>
                            @if (count($tvaGroups) > 0)
                                <table class="table taxe-table">
                                    <thead>
                                        <tr>
                                            <th>Intitulé</th>
                                            <th class="text-center">Taux</th>
                                            <th class="text-end">Base HT</th>
                                            <th class="text-end">Montant TVA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tvaGroups as $g)
                                            <tr>
                                                <td>{{ $g['intitule'] }}</td>
                                                <td class="text-center"><span class="badge-taux">{{ rtrim(rtrim(number_format($g['taux'], 2, ',', ''), '0'), ',') }} %</span></td>
                                                <td class="text-end">{{ number_format($g['base_ht'], 0, ',', ' ') }}</td>
                                                <td class="text-end"><strong>{{ number_format($g['montant'], 0, ',', ' ') }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-end">TOTAL TVA</td>
                                            <td class="text-end">{{ number_format(array_sum(array_column($tvaGroups, 'base_ht')), 0, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($totalTva, 0, ',', ' ') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @else
                                <p class="text-muted mb-0">Aucune TVA collectée sur cette période.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="result-card card">
                        <div class="card-body">
                            <h5 class="section-title bic">Récapitulatif PSVBB par taux</h5>
                            @if (count($bicGroups) > 0)
                                <table class="table taxe-table bic">
                                    <thead>
                                        <tr>
                                            <th>Intitulé</th>
                                            <th class="text-center">Taux</th>
                                            <th class="text-end">Base TTC</th>
                                            <th class="text-end">Montant PSVBB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bicGroups as $g)
                                            <tr>
                                                <td>{{ $g['intitule'] }}</td>
                                                <td class="text-center"><span class="badge-taux bic">{{ rtrim(rtrim(number_format($g['taux'], 2, ',', ''), '0'), ',') }} %</span></td>
                                                <td class="text-end">{{ number_format($g['base_ttc'], 0, ',', ' ') }}</td>
                                                <td class="text-end"><strong>{{ number_format($g['montant'], 0, ',', ' ') }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-end">TOTAL PSVBB</td>
                                            <td class="text-end">{{ number_format(array_sum(array_column($bicGroups, 'base_ttc')), 0, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($totalBic, 0, ',', ' ') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @else
                                <p class="text-muted mb-0">Aucun PSVBB collecté sur cette période.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Détail par facture -------------------------------------------------- --}}
            <div class="result-card card">
                <div class="card-body">
                    <h5 class="section-title">Détail par facture</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover taxe-table">
                            <thead>
                                <tr>
                                    <th style="width:4%;">#</th>
                                    <th style="width:11%;">Date</th>
                                    <th style="width:13%;">N° facture</th>
                                    <th style="width:24%;">Client</th>
                                    <th class="text-end" style="width:12%;">Total HT</th>
                                    <th class="text-end" style="width:12%;">TVA</th>
                                    <th class="text-end" style="width:12%;">PSVBB</th>
                                    <th class="text-end" style="width:12%;">Total taxes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($factures as $i => $f)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($f->date)->format('d/m/Y') }}</td>
                                        <td><strong>{{ $f->code }}</strong></td>
                                        <td>{{ optional($f->Contribuable)->assujeti ?? '—' }}</td>
                                        <td class="text-end">{{ number_format($f->total_ht, 0, ',', ' ') }}</td>
                                        <td class="text-end" style="color:#2980b9;"><strong>{{ number_format($f->total_tva, 0, ',', ' ') }}</strong></td>
                                        <td class="text-end" style="color:#e67e22;"><strong>{{ number_format($f->total_bic, 0, ',', ' ') }}</strong></td>
                                        <td class="text-end"><strong>{{ number_format($f->total_tva + $f->total_bic, 0, ',', ' ') }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end">TOTAL GÉNÉRAL</td>
                                    <td class="text-end">{{ number_format($totalHt, 0, ',', ' ') }}</td>
                                    <td class="text-end" style="color:#2980b9;">{{ number_format($totalTva, 0, ',', ' ') }}</td>
                                    <td class="text-end" style="color:#e67e22;">{{ number_format($totalBic, 0, ',', ' ') }}</td>
                                    <td class="text-end" style="color:#27ae60;">{{ number_format($totalTva + $totalBic, 0, ',', ' ') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        @elseif ($dateDebut || $dateFin || $tvaId || $bicId)
            <div class="result-card card">
                <div class="card-body">
                    <div class="no-data-message">
                        <i data-feather="inbox"></i>
                        <h4>Aucun résultat trouvé</h4>
                        <p>Aucune taxe ne correspond aux critères sélectionnés.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="result-card card">
                <div class="card-body">
                    <div class="no-data-message">
                        <i data-feather="search"></i>
                        <h4>Veuillez effectuer une recherche</h4>
                        <p>Sélectionnez une période et/ou un type de taxe pour afficher l'état.</p>
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
