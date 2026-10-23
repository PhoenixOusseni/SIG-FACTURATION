@extends('layouts.master')

@section('title')
    <title>SIG - FEC | Journal de Piste d'Audit</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .badge-action {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-CREATE { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-UPDATE { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-DELETE { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-FISCALISATION { background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
        .badge-CLOTURE_Z { background-color: #ffe4e6; color: #e11d48; border: 1px solid #fecdd3; }
        .badge-RAPPORT_X { background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-RAPPORT_A { background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
        .badge-STOCK { background-color: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
        .badge-CONNEXION { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .table-custom th {
            background-color: #f8fafc;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
<main>
    <header class="page-header page-header-dark header-gradient pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="activity"></i></div>
                            Piste d'Audit & Journal des Événements
                        </h1>
                        <div class="page-header-subtitle">
                            Journal inaltérable de traçabilité des opérations, modifications et fiscalisations conforme aux normes DGI Burkina Faso
                        </div>
                    </div>
                    <div class="col-12 col-xl-auto mt-4">
                        <a href="{{ route('audit.imprimer', ['entite' => $entite, 'action' => $action, 'date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" target="_blank" class="btn btn-outline-light shadow-sm">
                            <i data-feather="printer" class="me-1" style="width: 16px; height: 16px;"></i> Imprimer le Journal d'Audit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10 mb-5">
        {{-- Onglets de filtrage rapide DGI (Factures, Avoirs, Rapports, Boîtier MCF) --}}
        <div class="card mb-3 border-0 shadow-sm bg-white">
            <div class="card-body py-2 px-3">
                <ul class="nav nav-pills nav-fill gap-2">
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ $tab === 'all' ? 'active bg-primary text-white fw-bold' : 'text-dark' }}" href="{{ route('audit.index', ['tab' => 'all']) }}">
                            <i class="fas fa-list me-1"></i> Toutes les opérations ({{ number_format($totalLogs, 0, ',', ' ') }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ $tab === 'factures' ? 'active bg-primary text-white fw-bold' : 'text-dark' }}" href="{{ route('audit.index', ['tab' => 'factures']) }}">
                            <i class="fas fa-file-invoice me-1"></i> Factures de Vente ({{ number_format($countFactures, 0, ',', ' ') }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ $tab === 'avoirs' ? 'active bg-primary text-white fw-bold' : 'text-dark' }}" href="{{ route('audit.index', ['tab' => 'avoirs']) }}">
                            <i class="fas fa-undo me-1"></i> Avoirs Fiscaux ({{ number_format($countAvoirs, 0, ',', ' ') }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ $tab === 'rapports' ? 'active bg-primary text-white fw-bold' : 'text-dark' }}" href="{{ route('audit.index', ['tab' => 'rapports']) }}">
                            <i class="fas fa-chart-bar me-1"></i> Rapports X, Z & A ({{ number_format($countRapports, 0, ',', ' ') }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-2 {{ $tab === 'mcf' ? 'active bg-primary text-white fw-bold' : 'text-dark' }}" href="{{ route('audit.index', ['tab' => 'mcf']) }}">
                            <i class="fas fa-microchip me-1"></i> Boîtier MCF ({{ number_format($countMcf, 0, ',', ' ') }})
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body py-3">
                <form action="{{ route('audit.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Entité</label>
                        <select name="entite" class="form-select form-select-sm">
                            <option value="">Toutes les entités</option>
                            <option value="FACTURE" {{ $entite === 'FACTURE' ? 'selected' : '' }}>Factures Ventes</option>
                            <option value="AVOIR" {{ $entite === 'AVOIR' ? 'selected' : '' }}>Factures d'Avoir</option>
                            <option value="CLIENT" {{ $entite === 'CLIENT' ? 'selected' : '' }}>Clients</option>
                            <option value="ARTICLE" {{ $entite === 'ARTICLE' ? 'selected' : '' }}>Produits & Articles</option>
                            <option value="STOCK" {{ $entite === 'STOCK' ? 'selected' : '' }}>Mouvements de Stock</option>
                            <option value="MCF" {{ $entite === 'MCF' ? 'selected' : '' }}>Boîtier MCF / SFE</option>
                            <option value="RAPPORT" {{ $entite === 'RAPPORT' ? 'selected' : '' }}>Rapports & Clôtures Z</option>
                            <option value="PARAMETRES" {{ $entite === 'PARAMETRES' ? 'selected' : '' }}>Paramètres Société</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Action</label>
                        <select name="action" class="form-select form-select-sm">
                            <option value="">Toutes les actions</option>
                            <option value="CREATE" {{ $action === 'CREATE' ? 'selected' : '' }}>Création</option>
                            <option value="UPDATE" {{ $action === 'UPDATE' ? 'selected' : '' }}>Modification</option>
                            <option value="DELETE" {{ $action === 'DELETE' ? 'selected' : '' }}>Suppression</option>
                            <option value="FISCALISATION" {{ $action === 'FISCALISATION' ? 'selected' : '' }}>Fiscalisation MCF</option>
                            <option value="CLOTURE_Z" {{ $action === 'CLOTURE_Z' ? 'selected' : '' }}>Clôture Z</option>
                            <option value="RAPPORT_X" {{ $action === 'RAPPORT_X' ? 'selected' : '' }}>Rapport X</option>
                            <option value="RAPPORT_A" {{ $action === 'RAPPORT_A' ? 'selected' : '' }}>Rapport A (Articles)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Date début</label>
                        <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ $dateDebut }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Date fin</label>
                        <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ $dateFin }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i data-feather="filter" class="me-1" style="width: 14px; height: 14px;"></i> Filtrer
                        </button>
                        <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary btn-sm">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tableau Piste d'audit --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i data-feather="list" class="me-2 text-primary"></i> Événements Enregistrés (Total : {{ number_format($totalLogs, 0, ',', ' ') }})</span>
                <span class="text-muted small">Piste inaltérable horodatée</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Heure</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Entité</th>
                            <th>Réf. Entité</th>
                            <th>Description / Détails de l'opération</th>
                            <th>Adresse IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="small font-monospace text-nowrap text-dark fw-bold">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="small">
                                <span class="fw-semibold text-dark">{{ $log->nom_utilisateur }}</span>
                            </td>
                            <td>
                                @php $act = strtoupper($log->action); @endphp
                                <span class="badge-action badge-{{ $act }}">{{ $act }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $log->entite }}</span>
                            </td>
                            <td class="small font-monospace">
                                {{ $log->entite_id ? '#'.$log->entite_id : '—' }}
                            </td>
                            <td class="small text-secondary" style="max-width: 400px;">
                                {{ $log->details }}
                            </td>
                            <td class="small font-monospace text-muted text-nowrap">
                                {{ $log->ip_address ?: '127.0.0.1' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i data-feather="shield-off" class="d-block mx-auto mb-2" style="width: 32px; height: 32px; opacity: 0.4;"></i>
                                Aucun événement d'audit enregistré pour ces filtres.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="card-footer bg-white py-2">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</main>
@endsection
