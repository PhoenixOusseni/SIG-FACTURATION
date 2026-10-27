@extends('layouts.master')

@section('title')
    <title>SIG-FACTURATION | Tableau de bord</title>
@endsection

@section('style')
    <style>
        /* ================================================================
           DASHBOARD — DESIGN ÉLÉGANT & HARMONISÉ (Norme SaaS Pro)
           ================================================================ */

        /* ── Hero Banner ── */
        .dash-hero-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .date-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* ── KPI Financiers (Cartes blanches haut de gamme) ── */
        .dash-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            transition: transform .2s ease, box-shadow .2s ease;
            position: relative;
            overflow: hidden;
        }

        .dash-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
        }

        .dash-kpi-card.kpi-blue { border-left: 4px solid #1d3f7f; }
        .dash-kpi-card.kpi-green { border-left: 4px solid #059669; }
        .dash-kpi-card.kpi-red { border-left: 4px solid #dc2626; }
        .dash-kpi-card.kpi-purple { border-left: 4px solid #7c3aed; }

        .dash-kpi-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dash-kpi-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .dash-kpi-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.5px;
            color: #0f172a;
        }

        .dash-kpi-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 0.55rem;
            font-size: 0.78rem;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ── Cartes Activité ── */
        .dash-activity-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            transition: transform .2s ease;
        }

        .dash-activity-card:hover {
            transform: translateY(-2px);
        }

        /* ── Graphiques & Conteneurs ── */
        .chart-box-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .chart-box-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1rem 1.25rem;
        }

        .chart-box-card .section-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
        }

        .chart-box-card .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        /* ── Tables ── */
        .dash-table thead th {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
        }

        .dash-table tbody td {
            vertical-align: middle;
            font-size: 0.85rem;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.75rem 1rem;
            color: #334155;
        }

        .dash-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* ── Badges ── */
        .badge-soldee { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .badge-partielle { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-impayee { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        .prog-thin {
            height: 5px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
            margin-top: 5px;
        }

        .prog-fill {
            height: 100%;
            border-radius: 3px;
        }

        .legend-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
        }

        .legend-pill span {
            width: 12px;
            height: 3px;
            border-radius: 2px;
            display: inline-block;
        }
    </style>
@endsection

@section('content')
    <main class="py-4">
        <div class="container-xl px-4">

            {{-- =========================================================
                 1. HERO BANNER HARMONISÉ & ÉLÉGANT
                 ========================================================= --}}
            <div class="dash-hero-card mb-4 p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <h2 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">
                                Bonjour, {{ Auth::user()->prenom ?? 'Superviseur' }} {{ Auth::user()->nom ?? '' }} 👋
                            </h2>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="fas fa-shield-alt me-1"></i> Boîtier MCF Opérationnel (SECeF ST 1.0)
                            </span>
                        </div>
                        <p class="text-muted mb-0 small">
                            Vue synthétique de l'activité commerciale, trésorerie et conformité fiscale en temps réel.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="date-badge">
                            <i class="far fa-calendar-alt text-primary"></i>
                            {{ Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                        </div>
                        <a href="{{ route('module_ordre_facture.index') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                            <i class="fas fa-file-invoice me-1"></i> Hub Facturation Unifié
                        </a>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 2. INDICATEURS FINANCIERS CLÉS (4 CARTES DESIGN SAAS)
                 ========================================================= --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-uppercase fw-bold text-muted small mb-0" style="letter-spacing: 0.8px;">
                    Indicateurs Financiers &mdash; {{ Carbon\Carbon::now()->locale('fr')->isoFormat('MMMM YYYY') }}
                </h6>
            </div>

            <div class="row g-3 mb-4">
                {{-- CA du mois --}}
                <div class="col-sm-6 col-xl-3">
                    <div class="dash-kpi-card kpi-blue h-100 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="dash-kpi-label">CA du mois (Net)</div>
                                    <div class="dash-kpi-value text-primary">
                                        {{ number_format($totalCaMois, 0, ',', ' ') }}
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">FCFA</span>
                                    </div>
                                </div>
                                <div class="dash-kpi-icon" style="background: #eff6ff; color: #1d3f7f;">
                                    <i data-feather="trending-up"></i>
                                </div>
                            </div>
                        </div>
                        <div class="dash-kpi-footer">
                            <span>Total global :</span>
                            <strong class="font-monospace text-dark">{{ number_format($totalCaGlobal, 0, ',', ' ') }} F</strong>
                        </div>
                    </div>
                </div>

                {{-- Encaissements --}}
                <div class="col-sm-6 col-xl-3">
                    <div class="dash-kpi-card kpi-green h-100 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="dash-kpi-label">Encaissements</div>
                                    <div class="dash-kpi-value text-success">
                                        {{ number_format($totalEncaissementsMois, 0, ',', ' ') }}
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">FCFA</span>
                                    </div>
                                </div>
                                <div class="dash-kpi-icon" style="background: #ecfdf5; color: #059669;">
                                    <i data-feather="dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                        <div class="dash-kpi-footer">
                            <span>Solde caisse théorique :</span>
                            <strong class="font-monospace text-success" title="Total des numéraires réels en caisse">{{ number_format($soldeTheoriqueCaisse, 0, ',', ' ') }} F</strong>
                        </div>
                    </div>
                </div>

                {{-- Dépenses --}}
                <div class="col-sm-6 col-xl-3">
                    <div class="dash-kpi-card kpi-red h-100 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="dash-kpi-label">Dépenses du mois</div>
                                    <div class="dash-kpi-value text-danger">
                                        {{ number_format($totalDepensesMois, 0, ',', ' ') }}
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">FCFA</span>
                                    </div>
                                </div>
                                <div class="dash-kpi-icon" style="background: #fef2f2; color: #dc2626;">
                                    <i data-feather="arrow-down-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="dash-kpi-footer">
                            <span>Fournisseurs :</span>
                            <strong class="text-dark">{{ $nombreFournisseurs }} enregistrés</strong>
                        </div>
                    </div>
                </div>

                {{-- Taux de Recouvrement --}}
                <div class="col-sm-6 col-xl-3">
                    <div class="dash-kpi-card kpi-purple h-100 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="dash-kpi-label">Taux Recouvrement</div>
                                    <div class="dash-kpi-value" style="color: #7c3aed;">
                                        {{ $tauxRecouvrement }}%
                                    </div>
                                </div>
                                <div class="dash-kpi-icon" style="background: #f5f3ff; color: #7c3aed;">
                                    <i data-feather="pie-chart"></i>
                                </div>
                            </div>
                        </div>
                        <div class="dash-kpi-footer">
                            <span>Sur CA facturé global</span>
                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle">{{ $tauxRecouvrement >= 80 ? 'Optimal' : 'À suivre' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 3. INDICATEURS D'ACTIVITÉ OPÉRATIONNELLE
                 ========================================================= --}}
            <div class="row g-3 mb-4">
                {{-- Factures totales --}}
                <div class="col-6 col-xl-3">
                    <div class="dash-activity-card h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="dash-kpi-icon" style="background: #eff6ff; color: #2563eb; width: 40px; height: 40px;">
                                <i data-feather="file-text" style="width: 18px; height: 18px;"></i>
                            </div>
                            <span class="fs-4 fw-bold text-dark font-monospace">{{ $nombreFactures }}</span>
                        </div>
                        <div class="dash-kpi-label mb-1">Factures de Vente</div>
                        <div class="small text-muted d-flex justify-content-between align-items-center">
                            <span><strong class="text-success">{{ $nombreFacturesSoldees }}</strong> soldées</span>
                            <span class="badge bg-light text-muted border">{{ $nombreFacturesPartielles }} partielles</span>
                        </div>
                        <div class="prog-thin mt-2">
                            @php $pctS = $nombreFactures > 0 ? round(($nombreFacturesSoldees / $nombreFactures) * 100) : 0; @endphp
                            <div class="prog-fill bg-success" style="width: {{ $pctS }}%;"></div>
                        </div>
                    </div>
                </div>

                {{-- Factures impayées --}}
                <div class="col-6 col-xl-3">
                    <div class="dash-activity-card h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="dash-kpi-icon" style="background: #fef2f2; color: #dc2626; width: 40px; height: 40px;">
                                <i data-feather="alert-circle" style="width: 18px; height: 18px;"></i>
                            </div>
                            <div class="text-end">
                                <span class="fs-5 fw-bold text-danger font-monospace d-block">{{ number_format($totalCreancesGlobal, 0, ',', ' ') }} F</span>
                                <small class="text-muted" style="font-size: 11px;">Reste à recouvrer</small>
                            </div>
                        </div>
                        <div class="dash-kpi-label mb-1">Créances Clients</div>
                        <div class="small text-muted d-flex justify-content-between align-items-center">
                            <span><strong>{{ $nombreFacturesImpayees }}</strong> impayées · {{ $nombreFacturesPartielles }} partielles</span>
                            <a href="{{ route('module_ordre_facture.index') }}" class="text-danger fw-semibold small">Relancer</a>
                        </div>
                        <div class="prog-thin mt-2">
                            @php $pctI = $nombreFactures > 0 ? round(($nombreFacturesImpayees / $nombreFactures) * 100) : 0; @endphp
                            <div class="prog-fill bg-danger" style="width: {{ $pctI }}%;"></div>
                        </div>
                    </div>
                </div>

                {{-- Devis Proforma --}}
                <div class="col-6 col-xl-3">
                    <div class="dash-activity-card h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="dash-kpi-icon" style="background: #f5f3ff; color: #7c3aed; width: 40px; height: 40px;">
                                <i data-feather="file" style="width: 18px; height: 18px;"></i>
                            </div>
                            <span class="fs-4 fw-bold text-dark font-monospace">{{ $nombreProformas }}</span>
                        </div>
                        <div class="dash-kpi-label mb-1">Devis & Proformas</div>
                        <div class="small text-muted d-flex justify-content-between align-items-center">
                            <span><strong style="color: #7c3aed;">{{ $nombreBordereaux }}</strong> BL émis</span>
                            <a href="{{ route('module_ordre_facture.index') }}" class="fw-semibold small" style="color: #7c3aed;">Convertir</a>
                        </div>
                        <div class="prog-thin mt-2">
                            <div class="prog-fill" style="width: 100%; background: #7c3aed;"></div>
                        </div>
                    </div>
                </div>

                {{-- Clients Actifs --}}
                <div class="col-6 col-xl-3">
                    <div class="dash-activity-card h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="dash-kpi-icon" style="background: #ecfeff; color: #0891b2; width: 40px; height: 40px;">
                                <i data-feather="users" style="width: 18px; height: 18px;"></i>
                            </div>
                            <span class="fs-4 fw-bold text-dark font-monospace">{{ $nombreClientsActifs }}</span>
                        </div>
                        <div class="dash-kpi-label mb-1">Portefeuille Clients</div>
                        <div class="small text-muted d-flex justify-content-between align-items-center">
                            <span><strong style="color: #0891b2;">{{ $nombreFournisseurs }}</strong> fournisseurs</span>
                            <a href="{{ route('module_contribuable.index') }}" class="fw-semibold small" style="color: #0891b2;">Répertoire</a>
                        </div>
                        <div class="prog-thin mt-2">
                            <div class="prog-fill" style="width: 100%; background: #0891b2;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 4. GRAPHIQUE ÉVOLUTION FINANCIÈRE & RÉPARTITION
                 ========================================================= --}}
            <div class="row g-3 mb-4">
                {{-- Line Chart - CA & Encaissements --}}
                <div class="col-xl-8">
                    <div class="card chart-box-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Évolution financière</div>
                                <h6 class="card-title">Chiffre d'affaires & Encaissements &mdash; 6 derniers mois</h6>
                            </div>
                            <div class="d-flex gap-3">
                                <span class="legend-pill"><span style="background: #1d3f7f;"></span>CA HT</span>
                                <span class="legend-pill"><span style="background: #10b981;"></span>Encaissements</span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div style="position: relative; height: 280px;">
                                <canvas id="chartEvolution"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Doughnut - CA par Service --}}
                <div class="col-xl-4">
                    <div class="card chart-box-card h-100">
                        <div class="card-header">
                            <div class="section-label">Répartition</div>
                            <h6 class="card-title">CA par Ligne de Service</h6>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                            @if (count($statsService) > 0)
                                <div style="position: relative; height: 240px; width: 100%;">
                                    <canvas id="chartService"></canvas>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i data-feather="pie-chart" style="width: 38px; height: 38px; opacity: .25;"></i>
                                    <p class="mt-2 small mb-0">Aucune ventilation disponible pour le moment.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================================================
                 5. ACTIVITÉ RÉCENTE : DERNIÈRES FACTURES & DERNIERS RÈGLEMENTS
                 ========================================================= --}}
            <div class="row g-3 mb-4">
                {{-- Dernières Factures --}}
                <div class="col-xl-7">
                    <div class="card chart-box-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Activité Commerciale Récente</div>
                                <h6 class="card-title">Dernières Factures Émises</h6>
                            </div>
                            <a href="{{ route('module_ordre_facture.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                Voir Tout le Hub <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            @if ($collection->count() > 0)
                                <div class="table-responsive">
                                    <table class="table dash-table mb-0 align-middle">
                                        <thead>
                                            <tr>
                                                <th style="padding-left: 1.25rem;">N° Pièce</th>
                                                <th>Client</th>
                                                <th class="text-end">Montant TTC</th>
                                                <th>Date</th>
                                                <th class="text-center">Statut DGI</th>
                                                <th class="text-center" style="padding-right: 1.25rem;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($collection->take(6) as $facture)
                                                @php
                                                    $isCert = $facture->isCertified();
                                                    $tot = (float) $facture->montant_total;
                                                    $reste = (float) $facture->reste_a_payer;
                                                @endphp
                                                <tr>
                                                    <td style="padding-left: 1.25rem;">
                                                        <span class="fw-bold font-monospace text-primary">{{ $facture->code }}</span>
                                                    </td>
                                                    <td style="max-width: 160px;" class="text-truncate">
                                                        {{ optional($facture->Contribuable)->assujeti ?? '—' }}
                                                    </td>
                                                    <td class="text-end fw-bold font-monospace text-dark">
                                                        {{ number_format($tot, 0, ',', ' ') }} F
                                                    </td>
                                                    <td class="small text-muted">
                                                        {{ optional($facture->date)->format('d/m/Y') }}
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($isCert)
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                                <i class="fas fa-check-circle me-1"></i> Certifiée
                                                            </span>
                                                        @elseif ($facture->statut === 'Erreur MCF')
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                                <i class="fas fa-exclamation-triangle me-1"></i> Erreur
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                                                Brouillon
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center" style="padding-right: 1.25rem;">
                                                        <a href="{{ route('module_ordre_facture.index') }}" class="btn btn-sm btn-light border p-1" title="Consulter dans le Hub">
                                                            <i class="fas fa-external-link-alt text-primary"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fa-2x mb-2 text-muted opacity-50"></i>
                                    <p class="mb-0 small">Aucune facture enregistrée pour le moment.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Derniers Règlements & Répartition Statuts --}}
                <div class="col-xl-5">
                    <div class="card chart-box-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Trésorerie & Encaissements</div>
                                <h6 class="card-title">Derniers Règlements Reçus</h6>
                            </div>
                            <span class="badge bg-light text-muted border">{{ $derniersReglements->count() }} récents</span>
                        </div>
                        <div class="card-body p-3">
                            @if ($derniersReglements->count() > 0)
                                <div class="d-flex flex-column gap-2">
                                    @foreach ($derniersReglements as $rglt)
                                        <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded p-2 text-success bg-white border">
                                                    <i class="fas fa-arrow-down"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold small text-dark">{{ $rglt->code }} &bull; {{ $rglt->mode ?? 'Espèces' }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        Facture {{ optional($rglt->Facture)->code }} &mdash; {{ optional(optional($rglt->Facture)->Contribuable)->assujeti }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-success font-monospace">+{{ number_format($rglt->montant, 0, ',', ' ') }} F</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">{{ \Carbon\Carbon::parse($rglt->date)->format('d/m/Y') }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-hand-holding-usd fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0 small">Aucun encaissement récent.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
@endsection

@push('scripts')
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Palette de couleurs raffinées & harmonisées
            var palette = [
                '#1d3f7f', '#059669', '#d97706', '#7c3aed',
                '#0891b2', '#e11d48', '#4b5563', '#ea580c'
            ];

            function fmt(v) {
                if (v >= 1000000) return (v / 1000000).toFixed(1) + ' M';
                if (v >= 1000) return (v / 1000).toFixed(0) + ' k';
                return v;
            }

            // ── 1. Graphique Évolution CA & Encaissements ────────────────
            var ctxEvolution = document.getElementById('chartEvolution');
            if (ctxEvolution) {
                new Chart(ctxEvolution.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($labels) !!},
                        datasets: [{
                            label: 'Chiffre d\'Affaires (HT)',
                            data: {!! json_encode($evolutionCa) !!},
                            borderColor: '#1d3f7f',
                            backgroundColor: 'rgba(29, 63, 127, 0.05)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#1d3f7f',
                            pointRadius: 4,
                            fill: true,
                            tension: 0.3
                        }, {
                            label: 'Encaissements',
                            data: {!! json_encode($evolutionEncaissements) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointBackgroundColor: '#10b981',
                            pointRadius: 4,
                            borderDash: [4, 4],
                            fill: false,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: { display: false },
                        scales: {
                            xAxes: [{
                                gridLines: { display: false },
                                ticks: { fontColor: '#64748b', fontSize: 11 }
                            }],
                            yAxes: [{
                                gridLines: { color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: {
                                    beginAtZero: true,
                                    fontColor: '#64748b',
                                    fontSize: 11,
                                    callback: function(v) { return fmt(v); }
                                }
                            }]
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#ffffff',
                            titleFontColor: '#0f172a',
                            bodyFontColor: '#475569',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            callbacks: {
                                label: function(item, data) {
                                    var label = data.datasets[item.datasetIndex].label;
                                    return ' ' + label + ' : ' +
                                        new Intl.NumberFormat('fr-FR').format(Math.round(item.yLabel)) + ' FCFA';
                                }
                            }
                        }
                    }
                });
            }

            // ── 2. Graphique Répartition CA par Service ─────────────────
            var ctxService = document.getElementById('chartService');
            if (ctxService) {
                var svcLabels = {!! json_encode(array_keys($statsService)) !!};
                var svcData = {!! json_encode(array_values($statsService)) !!};
                if (svcLabels.length > 0) {
                    new Chart(ctxService.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: svcLabels,
                            datasets: [{
                                data: svcData,
                                backgroundColor: palette.slice(0, svcLabels.length),
                                borderColor: '#ffffff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutoutPercentage: 68,
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, fontSize: 11, fontColor: '#475569', padding: 10 }
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(item, data) {
                                        var val = data.datasets[0].data[item.index];
                                        return ' ' + data.labels[item.index] + ' : ' +
                                            new Intl.NumberFormat('fr-FR').format(Math.round(val)) + ' FCFA';
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
@endpush
