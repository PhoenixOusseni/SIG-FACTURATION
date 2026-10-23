@extends('layouts.master')

@section('title')
    <title>SIG - FEC | Rapports Fiscaux & Financiers</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .stat-card {
            border-radius: 12px;
            border: 1px solid #e3e6ec;
            background: #ffffff;
            padding: 1.25rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 2rem 0 rgba(33, 40, 50, 0.1);
        }
        .stat-card .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-bottom: 0.35rem;
        }
        .stat-card .stat-value {
            font-size: 1.45rem;
            font-weight: 800;
            font-family: 'Consolas', 'Courier New', monospace;
            color: #1e293b;
        }
        .stat-card.featured {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #ffffff;
            border: none;
        }
        .stat-card.featured .stat-label {
            color: #93c5fd;
        }
        .stat-card.featured .stat-value {
            color: #38bdf8;
        }
        .table-custom th {
            background-color: #f8fafc;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            font-weight: 700;
        }
        .badge-z {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            font-weight: 700;
        }
        .badge-x {
            background-color: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            font-weight: 700;
        }
        .badge-a {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
<main>
    <div class="container-fluid px-4 py-4">

        {{-- En-tête exécutif & Boutons d'actions --}}
        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 12px;">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center justify-content-between g-3">
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center mb-2">
                            <div class="p-2 bg-primary bg-opacity-25 rounded-3 me-3 text-white">
                                <i data-feather="file-text" style="width: 24px; height: 24px;"></i>
                            </div>
                            <div>
                                <h2 class="h4 mb-0 fw-bold text-white">Rapports Fiscaux & Financiers (SECeF / DGI)</h2>
                                <p class="small text-white-50 mb-0">Clôtures journalières Z, lecture intermédiaire X et bilans périodiques conformes DGI</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                            <!-- Bouton Rapport X -->
                            <form action="{{ route('rapports.x') }}" method="POST" class="d-inline" onsubmit="return confirm('Générer une lecture journalière (Rapport X) ? Cette opération n\'efface pas les compteurs.')">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm shadow-sm">
                                    <i data-feather="eye" class="me-1" style="width: 15px; height: 15px;"></i> Rapport X (Lecture)
                                </button>
                            </form>

                            <!-- Bouton Clôture Z -->
                            <button type="button" class="btn btn-danger btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalClotureZ">
                                <i data-feather="lock" class="me-1" style="width: 15px; height: 15px;"></i> Clôture Fiscale Z
                            </button>

                            <!-- Bouton Rapport A (Articles) -->
                            <form action="{{ route('rapports.a') }}" method="POST" class="d-inline" onsubmit="return confirm('Générer le rapport officiel Articles (A) et clôturer la période des articles ?')">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm shadow-sm">
                                    <i data-feather="package" class="me-1" style="width: 15px; height: 15px;"></i> Clôture Articles A
                                </button>
                            </form>

                            <!-- Impression Bilan Périodique Financier -->
                            <a href="{{ route('rapports.bilan.imprimer', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" target="_blank" class="btn btn-light btn-sm shadow-sm">
                                <i data-feather="printer" class="me-1" style="width: 15px; height: 15px;"></i> Bilan Financier
                            </a>

                            <!-- Impression Bilan Articles -->
                            <a href="{{ route('rapports.articles.imprimer', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" target="_blank" class="btn btn-outline-light btn-sm shadow-sm">
                                <i data-feather="layers" class="me-1" style="width: 15px; height: 15px;"></i> Bilan Articles A4
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtres de période (Bien espacé, sans chevauchement) --}}
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 10px;">
            <div class="card-body py-3 px-4">
                <form action="{{ route('rapports.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Date début</label>
                        <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ $dateDebut }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted mb-1">Date fin</label>
                        <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ $dateFin }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2 shadow-sm">
                            <i data-feather="filter" class="me-1" style="width: 14px; height: 14px;"></i> Filtrer la période
                        </button>
                        <a href="{{ route('rapports.index', ['date_debut' => now()->toDateString(), 'date_fin' => now()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm">
                            Aujourd'hui
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Cartes KPI StatCards --}}
        @php $stats = $donnees['statistiques']; @endphp
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-2">
                <div class="stat-card">
                    <div class="stat-label">C.A. Hors Taxes</div>
                    <div class="stat-value text-dark">{{ number_format($stats['chiffre_affaires_ht'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="stat-card">
                    <div class="stat-label">TVA Collectée</div>
                    <div class="stat-value text-primary">{{ number_format($stats['tva_collectee'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="stat-card">
                    <div class="stat-label">Taxes Spécifiques (TS)</div>
                    <div class="stat-value text-info">{{ number_format($stats['taxes_specifiques'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="stat-card">
                    <div class="stat-label">PSVB Collecté</div>
                    <div class="stat-value text-warning">{{ number_format($stats['psvb_collecte'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="stat-card">
                    <div class="stat-label">Timbre Quittance</div>
                    <div class="stat-value text-secondary">{{ number_format($stats['timbre_quittance'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-2">
                <div class="stat-card featured">
                    <div class="stat-label">TOTAL TTC NET</div>
                    <div class="stat-value">{{ number_format($stats['total_ttc'], 0, ',', ' ') }} <small class="fs-6 text-white">F</small></div>
                </div>
            </div>
        </div>

        {{-- Détails fiscaux : Ventilation par Groupe de Taxe & Modes de règlement --}}
        <div class="row g-4 mb-4">
            {{-- Ventilation par Groupe TVA --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark"><i data-feather="pie-chart" class="me-2 text-primary"></i> Ventilation par Groupes de Taxes DGI</span>
                        <span class="badge bg-light text-dark border">{{ count($stats['groupes_taxes']) }} Groupes</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Groupe</th>
                                    <th class="text-center">Taux</th>
                                    <th class="text-end">Base HT</th>
                                    <th class="text-end">Montant TVA</th>
                                    <th class="text-end">Total TS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['groupes_taxes'] as $codeGrp => $grp)
                                <tr>
                                    <td class="fw-bold text-dark">
                                        <span class="badge bg-secondary me-1">{{ $codeGrp }}</span>
                                        {{ $grp['libelle'] }}
                                    </td>
                                    <td class="text-center">{{ $grp['taux'] > 0 ? $grp['taux'].'%' : '-' }}</td>
                                    <td class="text-end font-monospace">{{ number_format($grp['base_ht'], 0, ',', ' ') }} F</td>
                                    <td class="text-end font-monospace fw-bold text-primary">{{ number_format($grp['tva'], 0, ',', ' ') }} F</td>
                                    <td class="text-end font-monospace text-muted">{{ number_format($grp['ts'], 0, ',', ' ') }} F</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="2">TOTAL</td>
                                    <td class="text-end font-monospace">{{ number_format($stats['chiffre_affaires_ht'], 0, ',', ' ') }} F</td>
                                    <td class="text-end font-monospace text-primary">{{ number_format($stats['tva_collectee'], 0, ',', ' ') }} F</td>
                                    <td class="text-end font-monospace text-muted">{{ number_format($stats['taxes_specifiques'], 0, ',', ' ') }} F</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Modes de règlement et Réductions --}}
            <div class="col-lg-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark"><i data-feather="credit-card" class="me-2 text-success"></i> Règlements & Informations DGI</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($stats['modes_paiement'] as $modeNom => $modeTotal)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5">
                                <span class="text-secondary fw-semibold">
                                    <i data-feather="check-circle" class="me-2 text-success" style="width: 14px; height: 14px;"></i>
                                    {{ $modeNom }}
                                </span>
                                <span class="font-monospace fw-bold text-dark">{{ number_format($modeTotal, 0, ',', ' ') }} F</span>
                            </li>
                            @endforeach
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 bg-light">
                                <span class="text-muted"><i data-feather="tag" class="me-2" style="width: 14px; height: 14px;"></i> Réductions commerciales accordées</span>
                                <span class="font-monospace text-danger">- {{ number_format($stats['reductions_commerciales'], 0, ',', ' ') }} F</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5 bg-light">
                                <span class="text-muted"><i data-feather="corner-up-left" class="me-2" style="width: 14px; height: 14px;"></i> Total Factures d'Avoir (A déduire)</span>
                                <span class="font-monospace text-danger">- {{ number_format($stats['total_avoirs'], 0, ',', ' ') }} F</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2.5">
                                <span class="text-muted"><i data-feather="alert-circle" class="me-2" style="width: 14px; height: 14px;"></i> Ventes non fiscalisées / En cours</span>
                                <span class="badge bg-warning text-dark">{{ $stats['ventes_incompletes'] }} document(s)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Historique des Clôtures Z et Rapports X --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i data-feather="archive" class="me-2 text-dark"></i> Registre des Clôtures Fiscales Z et Rapports X</span>
                <span class="text-muted small">Dernier Z : {{ $dernierZ ? $dernierZ->created_at->format('d/m/Y H:i') . ' (N° ' . $dernierZ->numero_rapport . ')' : 'Aucune clôture enregistrée' }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Rapport</th>
                            <th>Type</th>
                            <th>Date / Heure</th>
                            <th>Période couverte</th>
                            <th class="text-end">CA HT</th>
                            <th class="text-end">TVA</th>
                            <th class="text-end">TTC</th>
                            <th>Ventes / Avoirs</th>
                            <th>Opérateur</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historiqueRapports as $rap)
                        <tr>
                            <td class="fw-bold font-monospace">{{ $rap->numero_rapport }}</td>
                            <td>
                                @if($rap->type_rapport === 'Z')
                                    <span class="badge badge-z">CLÔTURE Z</span>
                                @elseif($rap->type_rapport === 'A')
                                    <span class="badge badge-a">ARTICLES A</span>
                                @else
                                    <span class="badge badge-x">RAPPORT X</span>
                                @endif
                            </td>
                            <td>{{ $rap->date_rapport ? $rap->date_rapport->format('d/m/Y H:i:s') : $rap->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="small text-muted">
                                {{ $rap->date_debut ? $rap->date_debut->format('d/m/Y') : '—' }} au {{ $rap->date_fin ? $rap->date_fin->format('d/m/Y') : '—' }}
                            </td>
                            <td class="text-end font-monospace">{{ number_format($rap->chiffre_affaires_ht, 0, ',', ' ') }} F</td>
                            <td class="text-end font-monospace text-primary">{{ number_format($rap->total_tva, 0, ',', ' ') }} F</td>
                            <td class="text-end font-monospace fw-bold text-dark">{{ number_format($rap->total_ttc, 0, ',', ' ') }} F</td>
                            <td class="small">
                                @if($rap->type_rapport === 'A')
                                    {{ $rap->nb_factures_vente }} article(s)
                                @else
                                    {{ $rap->nb_factures_vente }} vente(s) · {{ $rap->nb_factures_avoir }} avoir(s)
                                @endif
                            </td>
                            <td class="small">{{ optional($rap->user)->name ?? 'Administrateur' }}</td>
                            <td class="text-center">
                                @if($rap->type_rapport === 'A')
                                    <a href="{{ route('rapports.articles.imprimer', ['rapport_id' => $rap->id]) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Imprimer le rapport officiel A (Articles)">
                                        <i data-feather="printer" style="width: 14px; height: 14px;"></i> Imprimer A
                                    </a>
                                @else
                                    <a href="{{ route('rapports.imprimer', $rap->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Imprimer le rapport fiscal">
                                        <i data-feather="printer" style="width: 14px; height: 14px;"></i> Imprimer
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i data-feather="inbox" class="d-block mx-auto mb-2" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                                Aucun rapport fiscal n'a encore été généré.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($historiqueRapports->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $historiqueRapports->links() }}
            </div>
            @endif
        </div>

    </div>
</main>

{{-- Modal Confirmation Clôture Z --}}
<div class="modal fade" id="modalClotureZ" tabindex="-1" aria-labelledby="modalClotureZLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalClotureZLabel">
                    <i data-feather="alert-triangle" class="me-2" style="width: 20px; height: 20px;"></i> Clôture Journalière Fiscale Z
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rapports.z') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <p class="text-dark fw-bold mb-2">
                        Attention : Vous êtes sur le point d'exécuter la clôture journalière fiscale (Rapport Z).
                    </p>
                    <div class="alert alert-warning small mb-3">
                        <ul class="mb-0 ps-3">
                            <li>Cette opération synchronise et valide les compteurs certifiés avec le boîtier physique MCF (Norme SECeF BF ST 1.0).</li>
                            <li>Les compteurs de ventes journaliers seront <strong>archivés et réinitialisés</strong> pour la nouvelle journée fiscale.</li>
                            <li>Un rapport fiscal officiel Z conforme DGI sera généré et conservé pour contrôle.</li>
                        </ul>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Total Ventes du jour (TTC) :</span>
                            <span class="fw-bold font-monospace">{{ number_format($donneesJour['statistiques']['total_ttc'], 0, ',', ' ') }} F</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Nombre de factures :</span>
                            <span class="fw-bold">{{ $donneesJour['statistiques']['nb_ventes'] }} ventes / {{ $donneesJour['statistiques']['nb_avoirs'] }} avoirs</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i data-feather="check-circle" class="me-1" style="width: 14px; height: 14px;"></i> Confirmer la clôture Z
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
