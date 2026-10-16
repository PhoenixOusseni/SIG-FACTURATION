@extends('layouts.master')

@section('title')
    <title>SIG-FACTURATION | Tableau de bord</title>
@endsection

@section('style')
    <style>
        /* ================================================================
       DASHBOARD — Styles
       ================================================================ */

        /* ── KPI Cards ── */
        .kpi-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.09);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.13);
        }

        .kpi-icon {
            width: 52px;
            height: 52px;
            min-width: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .kpi-icon svg {
            width: 24px;
            height: 24px;
        }

        .kpi-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .9px;
        }

        .kpi-value {
            font-size: 1.65rem;
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.4px;
        }

        .kpi-sub {
            font-size: .75rem;
            opacity: .75;
            margin-top: 2px;
        }

        .kpi-footer {
            border-top: 1px solid rgba(255, 255, 255, .22);
            padding-top: .6rem;
            font-size: .75rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .kpi-footer svg {
            width: 13px;
            height: 13px;
        }

        /* ── Activity KPI Cards ── */
        .akpi-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.07);
            transition: transform .2s, box-shadow .2s;
        }

        .akpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.11);
        }

        .akpi-icon {
            width: 46px;
            height: 46px;
            min-width: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .akpi-icon svg {
            width: 20px;
            height: 20px;
        }

        /* ── Chart Cards ── */
        .chart-card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.07);
        }

        .chart-card .card-header {
            background: transparent;
            border-bottom: none;
            padding: 1.25rem 1.5rem .25rem;
        }

        .chart-card .card-header .section-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
        }

        .chart-card .card-header .card-title {
            font-size: .98rem;
            font-weight: 700;
            color: #1e293b;
            margin: 2px 0 0;
        }

        /* ── Tables ── */
        .dash-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .dash-table thead th {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .85px;
            color: #94a3b8;
            background: #f8fafc;
            border-top: none;
            border-bottom: 2px solid #e2e8f0;
            padding: .7rem 1rem;
        }

        .dash-table tbody td {
            vertical-align: middle;
            font-size: .855rem;
            border-bottom: 1px solid #f1f5f9;
            padding: .72rem 1rem;
            color: #334155;
        }

        .dash-table tbody tr:last-child td {
            border-bottom: none;
        }

        .dash-table tbody tr:hover td {
            background: #f8fafc;
        }

        /* ── Status Badges ── */
        .badge-statut {
            display: inline-flex;
            align-items: center;
            padding: 3px 11px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .badge-soldee {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-partielle {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-impayee {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* ── Stat progress items ── */
        .stat-item {
            display: flex;
            align-items: center;
            padding: .65rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-right: 10px;
        }

        /* ── Progress thin ── */
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

        /* ── Reglement item ── */
        .rglt-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: .6rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .rglt-item:last-child {
            border-bottom: none;
        }

        .rglt-icon {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rglt-icon svg {
            width: 16px;
            height: 16px;
        }

        /* ── Section title ── */
        .section-heading {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            color: #94a3b8;
            margin-bottom: .5rem;
        }

        /* ── Legend pill ── */
        .legend-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
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
    <main>

        {{-- =========================================================
         PAGE HEADER
         ========================================================= --}}
        <header class="page-header pb-10">
            <div class="container-xl px-4">
                <div class="page-header-pattern"></div>
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">

                        <div class="col-auto mt-4">
                            <p class="page-header-breadcrumb mb-1">
                                <i data-feather="home" style="width:11px;height:11px;"></i>
                                &nbsp;Accueil &rsaquo; Tableau de bord
                            </p>
                            <h1 class="page-header-title">
                                <div class="page-header-icon" style="margin-right:.75rem;">
                                    <i data-feather="activity"></i>
                                </div>
                                Tableau de bord
                            </h1>
                            <p class="page-header-subtitle">
                                Bienvenue {{ Auth::user()->prenom }} {{ Auth::user()->nom }} &mdash; vue d'ensemble de
                                l'activité
                            </p>
                        </div>

                        <div class="col-12 col-xl-auto mt-4">
                            <div
                                style="background:rgba(255,255,255,.15);backdrop-filter:blur(10px);
                                    padding:.65rem 1.2rem;border-radius:12px;color:#fff;
                                    font-weight:600;display:inline-flex;align-items:center;gap:.6rem;
                                    border:1px solid rgba(255,255,255,.3);font-size:.88rem;">
                                <i data-feather="calendar" style="width:15px;height:15px;"></i>
                                {{ Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        {{-- =========================================================
         MAIN CONTENT
         ========================================================= --}}
        <div class="container-xl px-4 mt-n10">

            {{-- ──────────────────────────────────────────────────────
             SECTION 1 — KPI FINANCIERS (4 cartes dégradées)
             ────────────────────────────────────────────────────── --}}
            <p class="section-heading">Indicateurs financiers —
                {{ Carbon\Carbon::now()->locale('fr')->isoFormat('MMMM YYYY') }}</p>
            <div class="row mb-4">

                {{-- CA du mois --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">CA du mois (Net)</div>
                                    <div class="kpi-value mt-1">{!! number_format($totalCaMois, 0, ',', '&nbsp;') !!}</div>
                                    <div class="kpi-sub">FCFA</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="trending-up" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div class="kpi-footer" style="color:rgba(255,255,255,.8);">
                                <i data-feather="bar-chart-2"></i>
                                CA global&nbsp;: {!! number_format($totalCaGlobal, 0, ',', '&nbsp;') !!} FCFA
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Encaissements --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#065f46 0%,#10b981 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">Encaissements</div>
                                    <div class="kpi-value mt-1">{!! number_format($totalEncaissementsMois, 0, ',', '&nbsp;') !!}</div>
                                    <div class="kpi-sub">FCFA ce mois</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="dollar-sign" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div class="kpi-footer" style="color:rgba(255,255,255,.8);">
                                <i data-feather="credit-card"></i>
                                Total&nbsp;: {!! number_format($totalEncaissements, 0, ',', '&nbsp;') !!} FCFA
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dépenses --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#7f1d1d 0%,#ef4444 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">Dépenses</div>
                                    <div class="kpi-value mt-1">{!! number_format($totalDepensesMois, 0, ',', '&nbsp;') !!}</div>
                                    <div class="kpi-sub">FCFA ce mois</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="arrow-down-circle" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div class="kpi-footer" style="color:rgba(255,255,255,.8);">
                                <i data-feather="info"></i>
                                Fournisseurs enregistrés&nbsp;: {{ $nombreFournisseurs }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Taux de recouvrement --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">Taux de recouvrement</div>
                                    <div class="kpi-value mt-1">{{ $tauxRecouvrement }}<span
                                            style="font-size:1rem;">%</span></div>
                                    <div class="kpi-sub">sur CA global</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="pie-chart" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div style="border-top:1px solid rgba(255,255,255,.22);padding-top:.55rem;">
                                <div style="background:rgba(255,255,255,.25);border-radius:4px;height:6px;overflow:hidden;">
                                    <div
                                        style="background:#fff;height:6px;border-radius:4px;width:{{ min($tauxRecouvrement, 100) }}%;transition:width .6s;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>{{-- /row KPI financiers --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 2 — KPI ACTIVITÉ (4 cartes blanches)
             ────────────────────────────────────────────────────── --}}
            <p class="section-heading">Indicateurs d'activité</p>
            <div class="row mb-4">

                {{-- Total factures --}}
                <div class="col-6 col-xl-3 mb-3">
                    @php $pctSoldees = $nombreFactures > 0 ? round(($nombreFacturesSoldees/$nombreFactures)*100) : 0; @endphp
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#eff6ff;">
                                    <i data-feather="file-text" style="color:#2563eb;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#1e293b;line-height:1;">{{ $nombreFactures }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                Factures totales
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#16a34a;font-weight:700;">{{ $nombreFacturesSoldees }}</span> soldées
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:{{ $pctSoldees }}%;background:#2563eb;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Impayées --}}
                <div class="col-6 col-xl-3 mb-3">
                    @php $pctImpayees = $nombreFactures > 0 ? round(($nombreFacturesImpayees/$nombreFactures)*100) : 0; @endphp
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#fef2f2;">
                                    <i data-feather="alert-circle" style="color:#dc2626;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#dc2626;line-height:1;">{{ $nombreFacturesImpayees }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                Impayées
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#d97706;font-weight:700;">{{ $nombreFacturesPartielles }}</span>
                                paiements partiels
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:{{ $pctImpayees }}%;background:#ef4444;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Proformas --}}
                <div class="col-6 col-xl-3 mb-3">
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#f5f3ff;">
                                    <i data-feather="clipboard" style="color:#7c3aed;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#1e293b;line-height:1;">{{ $nombreProformas }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                Proformas
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#7c3aed;font-weight:700;">{{ $nombreBordereaux }}</span> bons de
                                livraison
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:100%;background:#7c3aed;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clients actifs --}}
                <div class="col-6 col-xl-3 mb-3">
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#ecfeff;">
                                    <i data-feather="users" style="color:#0891b2;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#1e293b;line-height:1;">{{ $nombreClientsActifs }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                Clients actifs
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#0891b2;font-weight:700;">{{ $nombreFournisseurs }}</span> fournisseurs
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:100%;background:#0891b2;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /row KPI activité --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 3 — GRAPHIQUE ÉVOLUTION + DOUGHNUT SERVICE
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">

                {{-- Line chart – Évolution CA & Encaissements --}}
                <div class="col-xl-8 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Évolution financière</div>
                                <div class="card-title">CA & Encaissements — 6 derniers mois</div>
                            </div>
                            <div class="d-flex gap-3">
                                <span class="legend-pill"><span style="background:#2563eb;"></span>CA HT</span>
                                <span class="legend-pill"><span style="background:#10b981;"></span>Encaissements</span>
                            </div>
                        </div>
                        <div class="card-body px-3 pb-3 pt-0">
                            <div style="position:relative;height:275px;">
                                <canvas id="chartEvolution"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Doughnut – CA par Ligne de Service --}}
                <div class="col-xl-4 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <div class="section-label">Répartition</div>
                            <div class="card-title">CA par Ligne de Service</div>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center pb-3 pt-0">
                            @if (count($statsService) > 0)
                                <div style="position:relative;height:230px;width:100%;">
                                    <canvas id="chartService"></canvas>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i data-feather="pie-chart" style="width:38px;height:38px;opacity:.25;"></i>
                                    <p class="mt-2 small mb-0">Aucune donnée</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>{{-- /row évolution --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 4 — RÉPARTITION STATUTS + CA PAR MÉTIER
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">

                {{-- Répartition statuts --}}
                <div class="col-xl-4 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <div class="section-label">Suivi paiements</div>
                            <div class="card-title">Répartition des factures</div>
                        </div>
                        <div class="card-body px-4 pb-4 pt-0">
                            @php
                                $totalF = max($nombreFactures, 1);
                                $pctSoldeesD = round(($nombreFacturesSoldees / $totalF) * 100, 1);
                                $pctPartielles = round(($nombreFacturesPartielles / $totalF) * 100, 1);
                                $pctImpayeesD = round(($nombreFacturesImpayees / $totalF) * 100, 1);
                            @endphp

                            @if ($nombreFactures > 0)
                                <div style="position:relative;height:170px;margin-bottom:.75rem;">
                                    <canvas id="chartStatuts"></canvas>
                                </div>
                            @endif

                            {{-- Légende + barres de progression --}}
                            <div class="stat-item">
                                <span class="stat-dot" style="background:#16a34a;"></span>
                                <div style="flex:1;">
                                    <div style="font-size:.82rem;font-weight:600;color:#1e293b;">Soldées</div>
                                    <div class="prog-thin">
                                        <div class="prog-fill" style="width:{{ $pctSoldeesD }}%;background:#16a34a;">
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-left:14px;text-align:right;">
                                    <div style="font-size:.9rem;font-weight:800;color:#16a34a;">
                                        {{ $nombreFacturesSoldees }}</div>
                                    <div style="font-size:.68rem;color:#94a3b8;">{{ $pctSoldeesD }}%</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-dot" style="background:#d97706;"></span>
                                <div style="flex:1;">
                                    <div style="font-size:.82rem;font-weight:600;color:#1e293b;">Paiements partiels</div>
                                    <div class="prog-thin">
                                        <div class="prog-fill" style="width:{{ $pctPartielles }}%;background:#d97706;">
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-left:14px;text-align:right;">
                                    <div style="font-size:.9rem;font-weight:800;color:#d97706;">
                                        {{ $nombreFacturesPartielles }}</div>
                                    <div style="font-size:.68rem;color:#94a3b8;">{{ $pctPartielles }}%</div>
                                </div>
                            </div>
                            <div class="stat-item">
                                <span class="stat-dot" style="background:#dc2626;"></span>
                                <div style="flex:1;">
                                    <div style="font-size:.82rem;font-weight:600;color:#1e293b;">Impayées</div>
                                    <div class="prog-thin">
                                        <div class="prog-fill" style="width:{{ $pctImpayeesD }}%;background:#dc2626;">
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-left:14px;text-align:right;">
                                    <div style="font-size:.9rem;font-weight:800;color:#dc2626;">
                                        {{ $nombreFacturesImpayees }}</div>
                                    <div style="font-size:.68rem;color:#94a3b8;">{{ $pctImpayeesD }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Horizontal bar – CA par Catégorie Métier --}}
                <div class="col-xl-8 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <div class="section-label">Analyse par métier</div>
                            <div class="card-title">Chiffre d'affaires par Catégorie Métier</div>
                        </div>
                        <div class="card-body px-3 pb-3 pt-0">
                            @if (count($statsMetier) > 0)
                                <div style="position:relative;height:280px;">
                                    <canvas id="chartMetier"></canvas>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i data-feather="bar-chart-2" style="width:38px;height:38px;opacity:.25;"></i>
                                    <p class="mt-2 small mb-0">Aucune donnée disponible</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>{{-- /row statuts + métier --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 5 — FACTURES RÉCENTES & DERNIERS RÈGLEMENTS
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">

                {{-- Tableau des dernières factures --}}
                <div class="col-xl-7 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Activité récente</div>
                                <div class="card-title">Dernières factures</div>
                            </div>
                            <a href="{{ route('module_ordre_facture.index') }}"
                                style="background:#eff6ff;color:#2563eb;border-radius:8px;
                                  padding:5px 14px;font-size:.75rem;font-weight:700;
                                  text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                Voir tout <i data-feather="arrow-right" style="width:13px;height:13px;"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            @if ($collection->count() > 0)
                                <div class="table-responsive">
                                    <table class="table dash-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="padding-left:1.5rem;">Code</th>
                                                <th>Client</th>
                                                <th>Montant TTC</th>
                                                <th>Date</th>
                                                <th class="text-center" style="padding-right:1.5rem;">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($collection->take(7) as $facture)
                                                <tr>
                                                    <td style="padding-left:1.5rem;">
                                                        <span
                                                            style="font-weight:700;color:#2563eb;">{{ $facture->code }}</span>
                                                    </td>
                                                    <td
                                                        style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                        {{ optional($facture->Contribuable)->assujeti ?? '—' }}
                                                    </td>
                                                    <td>
                                                        <span style="font-weight:600;color:#1e293b;">
                                                            {!! number_format($facture->montant_total, 0, ',', '&nbsp;') !!}
                                                        </span>
                                                        <span style="font-size:.68rem;color:#94a3b8;"> FCFA</span>
                                                    </td>
                                                    <td>
                                                        {{ $facture->date ? \Carbon\Carbon::parse($facture->date)->format('d/m/Y') : '—' }}
                                                    </td>
                                                    <td class="text-center" style="padding-right:1.5rem;">
                                                        @php $sp = $facture->statut_paiement; @endphp
                                                        @if ($sp === 'soldée')
                                                            <span class="badge-statut badge-soldee">Soldée</span>
                                                        @elseif($sp === 'partielle')
                                                            <span class="badge-statut badge-partielle">Partielle</span>
                                                        @else
                                                            <span class="badge-statut badge-impayee">Impayée</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i data-feather="inbox" style="width:40px;height:40px;opacity:.25;"></i>
                                    <p class="mt-2 small mb-0">Aucune facture enregistrée</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Derniers règlements --}}
                <div class="col-xl-5 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Encaissements</div>
                                <div class="card-title">Derniers règlements</div>
                            </div>
                            <a href="{{ route('module_reglement.index') }}"
                                style="background:#f0fdf4;color:#16a34a;border-radius:8px;
                                  padding:5px 14px;font-size:.75rem;font-weight:700;
                                  text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                Voir tout <i data-feather="arrow-right" style="width:13px;height:13px;"></i>
                            </a>
                        </div>
                        <div class="card-body px-4 pb-4 pt-2">
                            @if ($derniersReglements->count() > 0)
                                @foreach ($derniersReglements as $reglement)
                                    <div class="rglt-item">
                                        <div class="rglt-icon" style="background:#f0fdf4;">
                                            @php $mode = strtolower($reglement->mode ?? ''); @endphp
                                            @if (str_contains($mode, 'mobile'))
                                                <i data-feather="smartphone" style="color:#16a34a;"></i>
                                            @elseif(str_contains($mode, 'ch'))
                                                <i data-feather="edit-3" style="color:#16a34a;"></i>
                                            @elseif(str_contains($mode, 'virement'))
                                                <i data-feather="refresh-cw" style="color:#16a34a;"></i>
                                            @else
                                                <i data-feather="dollar-sign" style="color:#16a34a;"></i>
                                            @endif
                                        </div>
                                        <div style="flex:1;min-width:0;">
                                            <div
                                                style="font-size:.83rem;font-weight:600;color:#1e293b;
                                                overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                {{ optional(optional($reglement->Facture)->Contribuable)->assujeti ?? ($reglement->code ?? '—') }}
                                            </div>
                                            <div style="font-size:.72rem;color:#94a3b8;">
                                                {{ $reglement->mode }}
                                                @if ($reglement->date)
                                                    &middot; {{ \Carbon\Carbon::parse($reglement->date)->format('d/m/Y') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div style="text-align:right;flex-shrink:0;">
                                            <div style="font-size:.88rem;font-weight:800;color:#16a34a;">
                                                +{!! number_format($reglement->montant, 0, ',', '&nbsp;') !!}
                                            </div>
                                            <div style="font-size:.68rem;color:#94a3b8;">FCFA</div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i data-feather="credit-card" style="width:38px;height:38px;opacity:.25;"></i>
                                    <p class="mt-2 small mb-0">Aucun règlement enregistré</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>{{-- /row factures + règlements --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 6 — FACTURES DU JOUR
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card chart-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <div
                                style="width:42px;height:42px;min-width:42px;border-radius:12px;
                                    background:linear-gradient(135deg,#133e9a,#2563eb);
                                    display:flex;align-items:center;justify-content:center;">
                                <i data-feather="sun" style="width:18px;height:18px;color:#fff;"></i>
                            </div>
                            <div>
                                <div class="section-label">Aujourd'hui</div>
                                <div class="card-title d-flex align-items-center gap-2">
                                    Factures du {{ Carbon\Carbon::today()->format('d/m/Y') }}
                                    <span
                                        style="background:#eff6ff;color:#2563eb;padding:2px 10px;
                                             border-radius:20px;font-size:.7rem;font-weight:700;">
                                        {{ $recettes->count() }} facture{{ $recettes->count() > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if ($recettes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table dash-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="padding-left:1.5rem;">Code</th>
                                                <th>Référence</th>
                                                <th>Client</th>
                                                <th>Ligne de service</th>
                                                <th>Date</th>
                                                <th class="text-center" style="padding-right:1.5rem;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recettes as $recette)
                                                <tr>
                                                    <td style="padding-left:1.5rem;">
                                                        <span
                                                            style="font-weight:700;color:#2563eb;">{{ $recette->code }}</span>
                                                    </td>
                                                    <td>{{ $recette->reference ?? '—' }}</td>
                                                    <td>{{ optional($recette->Contribuable)->assujeti ?? '—' }}</td>
                                                    <td>{{ optional($recette->Service)->libelle ?? '—' }}</td>
                                                    <td>{{ $recette->date ?? '—' }}</td>
                                                    <td class="text-center" style="padding-right:1.5rem;">
                                                        <a href="{{ route('module_ordre_facture.show', $recette->id) }}"
                                                            style="background:linear-gradient(135deg,#133e9a,#2563eb);
                                                          color:#fff;border-radius:8px;padding:5px 14px;
                                                          font-size:.75rem;font-weight:700;text-decoration:none;
                                                          display:inline-flex;align-items:center;gap:5px;">
                                                            <i data-feather="eye" style="width:13px;height:13px;"></i>
                                                            Voir
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i data-feather="inbox" style="width:48px;height:48px;opacity:.25;"></i>
                                    <p class="mt-2 mb-0">Aucune facture émise aujourd'hui</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>{{-- /row factures du jour --}}

        </div>{{-- /container-xl --}}
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') feather.replace();

            if (typeof Chart === 'undefined') return;

            // ── Palette de couleurs ────────────────────────────────────────
            var palette = [
                '#2563eb', '#10b981', '#f59e0b', '#ef4444', '#7c3aed',
                '#0891b2', '#059669', '#dc2626', '#d97706', '#4f46e5',
                '#0284c7', '#16a34a', '#9333ea', '#0f766e'
            ];

            function fmt(v) {
                if (v >= 1e6) return (v / 1e6).toFixed(1).replace('.', ',') + ' M';
                if (v >= 1e3) return (v / 1e3).toFixed(0) + ' K';
                return new Intl.NumberFormat('fr-FR').format(Math.round(v));
            }

            // ── 1. LINE CHART — Évolution CA & Encaissements ──────────────
            var ctxEvo = document.getElementById('chartEvolution');
            if (ctxEvo) {
                new Chart(ctxEvo.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($labels) !!},
                        datasets: [{
                                label: 'CA (HT)',
                                data: {!! json_encode($evolutionCa) !!},
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37,99,235,0.07)',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#2563eb',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                lineTension: 0.35
                            },
                            {
                                label: 'Encaissements',
                                data: {!! json_encode($evolutionEncaissements) !!},
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.07)',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                fill: true,
                                lineTension: 0.35
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            display: false
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false
                                },
                                ticks: {
                                    fontSize: 11,
                                    fontColor: '#94a3b8',
                                    fontFamily: 'inherit'
                                }
                            }],
                            yAxes: [{
                                gridLines: {
                                    color: 'rgba(0,0,0,0.05)',
                                    zeroLineColor: 'rgba(0,0,0,0.08)'
                                },
                                ticks: {
                                    beginAtZero: true,
                                    fontSize: 11,
                                    fontColor: '#94a3b8',
                                    fontFamily: 'inherit',
                                    callback: function(v) {
                                        return fmt(v);
                                    }
                                }
                            }]
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: '#fff',
                            titleFontColor: '#1e293b',
                            bodyFontColor: '#475569',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            callbacks: {
                                label: function(item, data) {
                                    var label = data.datasets[item.datasetIndex].label;
                                    return ' ' + label + ' : ' +
                                        new Intl.NumberFormat('fr-FR').format(Math.round(item.yLabel)) +
                                        ' FCFA';
                                }
                            }
                        }
                    }
                });
            }

            // ── 2. DOUGHNUT — CA par Ligne de Service ─────────────────────
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
                                borderColor: '#fff',
                                borderWidth: 3,
                                hoverBorderWidth: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutoutPercentage: 65,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 10,
                                    fontSize: 11,
                                    padding: 12,
                                    fontColor: '#475569',
                                    fontFamily: 'inherit'
                                }
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(item, data) {
                                        var val = data.datasets[0].data[item.index];
                                        var total = data.datasets[0].data.reduce(function(a, b) {
                                            return a + b;
                                        }, 0);
                                        var pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                        return ' ' + data.labels[item.index] + ' : ' +
                                            new Intl.NumberFormat('fr-FR').format(Math.round(val)) +
                                            ' FCFA (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    });
                }
            }

            // ── 3. DOUGHNUT — Statuts des factures ────────────────────────
            var ctxStatuts = document.getElementById('chartStatuts');
            if (ctxStatuts) {
                new Chart(ctxStatuts.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Soldées', 'Partielles', 'Impayées'],
                        datasets: [{
                            data: [
                                {{ $nombreFacturesSoldees }},
                                {{ $nombreFacturesPartielles }},
                                {{ $nombreFacturesImpayees }}
                            ],
                            backgroundColor: ['#16a34a', '#d97706', '#dc2626'],
                            borderColor: '#fff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutoutPercentage: 72,
                        legend: {
                            display: false
                        },
                        tooltips: {
                            callbacks: {
                                label: function(item, data) {
                                    return ' ' + data.labels[item.index] + ' : ' +
                                        data.datasets[0].data[item.index] + ' facture(s)';
                                }
                            }
                        }
                    }
                });
            }

            // ── 4. HORIZONTAL BAR — CA par Catégorie Métier ───────────────
            var ctxMetier = document.getElementById('chartMetier');
            if (ctxMetier) {
                var metLabels = {!! json_encode(array_keys($statsMetier)) !!};
                var metData = {!! json_encode(array_values($statsMetier)) !!};
                if (metLabels.length > 0) {
                    new Chart(ctxMetier.getContext('2d'), {
                        type: 'horizontalBar',
                        data: {
                            labels: metLabels,
                            datasets: [{
                                label: 'CA (HT)',
                                data: metData,
                                backgroundColor: palette.slice(0, metLabels.length),
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                xAxes: [{
                                    gridLines: {
                                        color: 'rgba(0,0,0,0.05)'
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                        fontSize: 11,
                                        fontColor: '#94a3b8',
                                        fontFamily: 'inherit',
                                        callback: function(v) {
                                            return fmt(v);
                                        }
                                    }
                                }],
                                yAxes: [{
                                    gridLines: {
                                        display: false
                                    },
                                    ticks: {
                                        fontSize: 11,
                                        fontColor: '#475569',
                                        fontFamily: 'inherit'
                                    }
                                }]
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(item, data) {
                                        return ' CA : ' +
                                            new Intl.NumberFormat('fr-FR').format(Math.round(item
                                                .xLabel)) + ' FCFA';
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
