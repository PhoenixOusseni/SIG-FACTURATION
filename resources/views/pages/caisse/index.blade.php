@extends('layouts.master')

@section('title')
    <title>SIG-FACTURATION | Gestion de Caisse & Numéraires</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .kpi-card-caisse {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: all 0.2s ease-in-out;
        }
        .kpi-card-caisse:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .solde-caisse-badge {
            background: linear-gradient(135deg, #1d3f7f 0%, #0f172a 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 16px 20px;
        }
    </style>
@endsection

@section('content')
<main class="py-4">
    <div class="container-xl px-4" style="margin-bottom: 6rem;">

        {{-- En-tête Principal Executive (Style Hub Facturation) --}}
        <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 14px; border: 1px solid #e2e8f0;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h2 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">
                                <i class="fas fa-cash-register me-2 text-primary"></i>Gestion de Caisse & Numéraires
                            </h2>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="fas fa-shield-alt me-1"></i> Conforme Exigence FEC / DGI
                            </span>
                        </div>
                        <p class="text-muted mb-0 small">
                            Enregistrement réglementaire des dépôts et retraits de numéraires, contrôle de trésorerie et bons de caisse.
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        {{-- Bouton Dépôt de Numéraire --}}
                        <button type="button" class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDepot">
                            <i class="fas fa-arrow-down me-1"></i> Nouveau Dépôt (Entrée)
                        </button>

                        {{-- Bouton Retrait de Numéraire --}}
                        <button type="button" class="btn btn-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRetrait">
                            <i class="fas fa-arrow-up me-1"></i> Nouveau Retrait (Sortie)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cartes d'indicateurs de trésorerie en direct --}}
        <div class="row g-3 mb-4">
            {{-- Solde théorique global --}}
            <div class="col-12 col-md-4">
                <div class="solde-caisse-badge shadow-sm">
                    <div class="text-white-50 small fw-bold text-uppercase mb-1">Solde Théorique en Caisse</div>
                    <div class="fs-2 fw-bold font-monospace">{{ number_format($soldeTheoriqueCaisse, 0, ',', ' ') }} <small class="fs-6">FCFA</small></div>
                    <div class="small text-white-50 mt-1">
                        <i class="fas fa-info-circle me-1"></i> Dépôts + Ventes Espèces − Retraits
                    </div>
                </div>
            </div>

            {{-- Dépôts du jour --}}
            <div class="col-6 col-md-4">
                <div class="card kpi-card-caisse p-3 h-100" style="border-left: 4px solid #10b981 !important;">
                    <div class="text-muted small fw-bold text-uppercase">Dépôts (Aujourd'hui)</div>
                    <div class="fs-4 fw-bold font-monospace text-success mt-1">
                        + {{ number_format($totalDepotsJour, 0, ',', ' ') }} <small class="fs-6">FCFA</small>
                    </div>
                    <div class="small text-muted mt-1">
                        Total cumulé : <strong>{{ number_format($totalDepotsGlobal, 0, ',', ' ') }} FCFA</strong>
                    </div>
                </div>
            </div>

            {{-- Retraits du jour --}}
            <div class="col-6 col-md-4">
                <div class="card kpi-card-caisse p-3 h-100" style="border-left: 4px solid #ef4444 !important;">
                    <div class="text-muted small fw-bold text-uppercase">Retraits (Aujourd'hui)</div>
                    <div class="fs-4 fw-bold font-monospace text-danger mt-1">
                        - {{ number_format($totalRetraitsJour, 0, ',', ' ') }} <small class="fs-6">FCFA</small>
                    </div>
                    <div class="small text-muted mt-1">
                        Total cumulé : <strong>{{ number_format($totalRetraitsGlobal, 0, ',', ' ') }} FCFA</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtres du Journal --}}
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white border-bottom p-3">
                <form method="GET" action="{{ route('module_caisse.index') }}" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted mb-1">Type de mouvement</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">-- Tous les types --</option>
                            <option value="DEPOT" {{ request('type') === 'DEPOT' ? 'selected' : '' }}>Dépôts uniquement (Entrées)</option>
                            <option value="RETRAIT" {{ request('type') === 'RETRAIT' ? 'selected' : '' }}>Retraits uniquement (Sorties)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted mb-1">Caisse</label>
                        <select name="caisse_id" class="form-select form-select-sm">
                            <option value="">-- Toutes les caisses --</option>
                            @foreach ($caisses as $c)
                                <option value="{{ $c->id }}" {{ request('caisse_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->libelle }} ({{ $c->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted mb-1">Du</label>
                        <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted mb-1">Au</label>
                        <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                        <a href="{{ route('module_caisse.index') }}" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>

            {{-- Table du journal de caisse --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tableMouvementsCaisse">
                        <thead class="table-light">
                            <tr>
                                <th>Code Pièce</th>
                                <th>Date & Heure</th>
                                <th>Type</th>
                                <th>Motif / Catégorie</th>
                                <th>Bénéficiaire / Justificatif</th>
                                <th>Caisse</th>
                                <th class="text-end">Montant</th>
                                <th>Opérateur</th>
                                <th class="text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mouvements as $m)
                                <tr>
                                    <td>
                                        <span class="fw-bold font-monospace text-dark">{{ $m->code }}</span>
                                    </td>
                                    <td>
                                        <div>{{ optional($m->date_mouvement)->format('d/m/Y') }}</div>
                                        <small class="text-muted font-monospace">{{ optional($m->date_mouvement)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        {!! $m->type_badge !!}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $m->motif }}</div>
                                        <small class="text-muted">{{ $m->type_motif }}</small>
                                    </td>
                                    <td>
                                        @if ($m->beneficiaire)
                                            <div><i class="fas fa-user me-1 text-muted"></i>{{ $m->beneficiaire }}</div>
                                        @endif
                                        @if ($m->piece_justificative)
                                            <small class="text-muted font-monospace"><i class="fas fa-file-alt me-1"></i>{{ $m->piece_justificative }}</small>
                                        @elseif (!$m->beneficiaire)
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($m->Caisse)
                                            <span class="badge bg-light text-dark border">{{ $m->Caisse->libelle }}</span>
                                        @else
                                            <span class="badge bg-light text-muted border">Générale</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="font-monospace fw-bold {{ $m->isDepot() ? 'text-success' : 'text-danger' }} fs-6">
                                            {{ $m->isDepot() ? '+' : '-' }} {{ number_format($m->montant, 0, ',', ' ') }} FCFA
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ optional($m->User)->prenom }} {{ optional($m->User)->nom ?? 'Système' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('module_caisse.print_recu', $m->id) }}" target="_blank"
                                           class="btn btn-outline-primary btn-sm" title="Imprimer le reçu de caisse">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-cash-register fa-2x mb-2 d-block text-muted"></i>
                                        Aucun mouvement de caisse enregistré pour le moment.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>

{{-- ==================== MODAL 1 : NOUVEAU DÉPÔT DE NUMÉRAIRE ==================== --}}
<div class="modal fade" id="modalDepot" tabindex="-1" aria-labelledby="modalDepotLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('module_caisse.store') }}" method="POST" class="modal-content shadow-lg border-0">
            @csrf
            <input type="hidden" name="type" value="DEPOT">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalDepotLabel">
                    <i class="fas fa-arrow-down me-2"></i> Enregistrer un Dépôt de Numéraire (Entrée)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-success-subtle border border-success-subtle text-success small mb-3">
                    <i class="fas fa-check-circle me-1"></i>
                    Conforme à la prescription FEC / SFE : enregistrement d'apport de monnaie, renflouement ou fonds en espèces.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Montant du Dépôt (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" name="montant" class="form-control form-control-lg font-monospace fw-bold text-success text-end"
                               placeholder="Ex: 50 000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date & Heure <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="date_mouvement" class="form-control"
                               value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Catégorie de Dépôt <span class="text-danger">*</span></label>
                        <select name="type_motif" class="form-select" required>
                            <option value="APPORT_MONNAIE">Apport de monnaie</option>
                            <option value="RENFLOUEMENT">Renflouement de caisse</option>
                            <option value="APPORT_EXPLOITANT">Apport gérant / exploitant</option>
                            <option value="REMBOURSEMENT_AVANCE">Remboursement d'avance</option>
                            <option value="AUTRE">Autre entrée de trésorerie</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Caisse de destination</label>
                        <select name="caisse_id" class="form-select">
                            <option value="">-- Caisse principale / Générale --</option>
                            @foreach ($caisses as $c)
                                <option value="{{ $c->id }}">{{ $c->libelle }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Motif / Libellé de l'opération <span class="text-danger">*</span></label>
                        <input type="text" name="motif" class="form-control" placeholder="Ex: Apport de monnaie pour ouverture de caisse matinale" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Déposant / Provenance des fonds</label>
                        <input type="text" name="beneficiaire" class="form-control" placeholder="Ex: Gérant, Banque, Caissier principal">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">N° de Pièce justificative</label>
                        <input type="text" name="piece_justificative" class="form-control" placeholder="Ex: Bordereau retrait banque N°...">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Observations complémentaires</label>
                        <textarea name="observation" rows="2" class="form-control" placeholder="Remarques éventuelles..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-success fw-bold">
                    <i class="fas fa-check me-1"></i> Valider le Dépôt
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL 2 : NOUVEAU RETRAIT DE NUMÉRAIRE ==================== --}}
<div class="modal fade" id="modalRetrait" tabindex="-1" aria-labelledby="modalRetraitLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('module_caisse.store') }}" method="POST" class="modal-content shadow-lg border-0">
            @csrf
            <input type="hidden" name="type" value="RETRAIT">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalRetraitLabel">
                    <i class="fas fa-arrow-up me-2"></i> Enregistrer un Retrait de Numéraire (Sortie)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger-subtle border border-danger-subtle text-danger small mb-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Conforme à la prescription FEC / SFE : enregistrement des sorties d'espèces, versements bancaires ou décaissements.
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Montant du Retrait (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" name="montant" class="form-control form-control-lg font-monospace fw-bold text-danger text-end"
                               placeholder="Ex: 25 000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date & Heure <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="date_mouvement" class="form-control"
                               value="{{ date('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Catégorie de Retrait <span class="text-danger">*</span></label>
                        <select name="type_motif" class="form-select" required>
                            <option value="VERSEMENT_BANQUE">Versement bancaire d'espèces</option>
                            <option value="ACHAT_URGENT">Achat / Fournitures urgentes</option>
                            <option value="DEPENSE_FONCTIONNEMENT">Dépense de fonctionnement</option>
                            <option value="PRELEVEMENT_GERANT">Prélèvement exploitant / gérant</option>
                            <option value="AUTRE">Autre sortie de caisse</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Caisse source</label>
                        <select name="caisse_id" class="form-select">
                            <option value="">-- Caisse principale / Générale --</option>
                            @foreach ($caisses as $c)
                                <option value="{{ $c->id }}">{{ $c->libelle }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Motif / Libellé de l'opération <span class="text-danger">*</span></label>
                        <input type="text" name="motif" class="form-control" placeholder="Ex: Versement des recettes de la veille à la banque" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nom du Bénéficiaire <span class="text-danger">*</span></label>
                        <input type="text" name="beneficiaire" class="form-control" placeholder="Ex: Jean Dupont (Coursier), Coris Bank..." required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">N° de Pièce justificative</label>
                        <input type="text" name="piece_justificative" class="form-control" placeholder="Ex: Reçu N°..., Facturette N°...">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Observations complémentaires</label>
                        <textarea name="observation" rows="2" class="form-control" placeholder="Remarques éventuelles..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-danger fw-bold">
                    <i class="fas fa-check me-1"></i> Valider le Retrait
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable && $('#tableMouvementsCaisse').length) {
            $('#tableMouvementsCaisse').DataTable({
                order: [[1, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
                language: {
                    processing: "Traitement en cours...",
                    search: "Recherche :",
                    lengthMenu: "Afficher _MENU_ mouvements",
                    info: "Affichage de _START_ à _END_ sur _TOTAL_ mouvements",
                    infoEmpty: "Affichage de 0 à 0 sur 0 mouvement",
                    infoFiltered: "(filtré sur _MAX_ mouvements)",
                    loadingRecords: "Chargement...",
                    zeroRecords: "Aucun mouvement trouvé",
                    emptyTable: "Aucun mouvement enregistré",
                    paginate: {
                        first: "Premier",
                        previous: "<i class='fas fa-chevron-left me-1'></i> Précédent",
                        next: "Suivant <i class='fas fa-chevron-right ms-1'></i>",
                        last: "Dernier"
                    }
                }
            });
        }
    });
</script>
@endsection
