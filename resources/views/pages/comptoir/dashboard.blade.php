@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Tableau de bord — Comptoir</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        /* ================================================================
       DASHBOARD COMPTOIR — Styles
       ================================================================ */

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
            font-size: 1.55rem;
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

        .badge-statut {
            display: inline-flex;
            align-items: center;
            padding: 3px 11px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .3px;
            white-space: nowrap;
        }

        .badge-brouillon {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .badge-attente {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-paye {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-annule {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .badge-converti {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .badge-ouverte {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-en_arret {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-fermee {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

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

        .section-heading {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            color: #94a3b8;
            margin-bottom: .5rem;
        }

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

        .rank-pill {
            width: 26px;
            height: 26px;
            min-width: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
            font-weight: 800;
            background: #eff6ff;
            color: #1d4ed8;
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
                                &nbsp;Accueil &rsaquo; Facture comptoir &rsaquo; Tableau de bord
                            </p>
                            <h1 class="page-header-title">
                                <div class="page-header-icon" style="margin-right:.75rem;">
                                    <i data-feather="shopping-cart"></i>
                                </div>
                                Tableau de bord — Comptoir
                            </h1>
                            <p class="page-header-subtitle">
                                Vue d'ensemble des ventes, encaissements et sessions de caisse
                            </p>
                        </div>

                        <div class="col-12 col-xl-auto mt-4 d-flex flex-wrap gap-2 align-items-center">
                            <div
                                style="background:rgba(255,255,255,.15);backdrop-filter:blur(10px);
                                    padding:.65rem 1.2rem;border-radius:12px;color:#fff;
                                    font-weight:600;display:inline-flex;align-items:center;gap:.6rem;
                                    border:1px solid rgba(255,255,255,.3);font-size:.88rem;">
                                <i data-feather="calendar" style="width:15px;height:15px;"></i>
                                {{ Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                            </div>
                            <a href="{{ route('module_comptoir.pos') }}" class="btn btn-light btn-sm">
                                <i data-feather="shopping-cart" style="width:14px;height:14px;"></i>
                                Point de vente
                            </a>
                            <a href="{{ route('module_comptoir.tickets.index') }}" class="btn btn-light btn-sm">
                                <i data-feather="list" style="width:14px;height:14px;"></i>
                                État des ventes
                            </a>
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

                {{-- CA du jour --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">CA du jour</div>
                                    <div class="kpi-value mt-1">{!! number_format($caJour, 0, ',', '&nbsp;') !!}</div>
                                    <div class="kpi-sub">FCFA</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="trending-up" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div class="kpi-footer" style="color:rgba(255,255,255,.8);">
                                <i data-feather="bar-chart-2"></i>
                                CA du mois&nbsp;: {!! number_format($caMois, 0, ',', '&nbsp;') !!} FCFA
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panier moyen --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#065f46 0%,#10b981 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">Panier moyen</div>
                                    <div class="kpi-value mt-1">{!! number_format($panierMoyenJour, 0, ',', '&nbsp;') !!}</div>
                                    <div class="kpi-sub">FCFA / ticket</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="shopping-cart" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div class="kpi-footer" style="color:rgba(255,255,255,.8);">
                                <i data-feather="check-circle"></i>
                                {{ $nbTicketsPayesJour }} ticket{{ $nbTicketsPayesJour > 1 ? 's' : '' }} payé{{ $nbTicketsPayesJour > 1 ? 's' : '' }} aujourd'hui
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fond de caisse engagé --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#4c1d95 0%,#7c3aed 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">Fond de caisse engagé</div>
                                    <div class="kpi-value mt-1">{!! number_format($fondEngage, 0, ',', '&nbsp;') !!}</div>
                                    <div class="kpi-sub">FCFA</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="unlock" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div class="kpi-footer" style="color:rgba(255,255,255,.8);">
                                <i data-feather="box"></i>
                                {{ $sessionsActives }} session{{ $sessionsActives > 1 ? 's' : '' }} active{{ $sessionsActives > 1 ? 's' : '' }} sur {{ $nombreCaisses }} caisse{{ $nombreCaisses > 1 ? 's' : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Taux de finalisation --}}
                <div class="col-sm-6 col-xl-3 mb-3">
                    @php
                        $tauxFinalisation = $nbTicketsMois > 0 ? round((($nbPaye + $nbConverti) / $nbTicketsMois) * 100, 1) : 0;
                    @endphp
                    <div class="card kpi-card h-100" style="background:linear-gradient(135deg,#7f1d1d 0%,#ef4444 100%);">
                        <div class="card-body text-white p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="kpi-label" style="color:rgba(255,255,255,.8);">Taux de finalisation</div>
                                    <div class="kpi-value mt-1">{{ $tauxFinalisation }}<span
                                            style="font-size:1rem;">%</span></div>
                                    <div class="kpi-sub">tickets payés / total (mois)</div>
                                </div>
                                <div class="kpi-icon" style="background:rgba(255,255,255,.18);">
                                    <i data-feather="pie-chart" style="color:#fff;"></i>
                                </div>
                            </div>
                            <div style="border-top:1px solid rgba(255,255,255,.22);padding-top:.55rem;">
                                <div style="background:rgba(255,255,255,.25);border-radius:4px;height:6px;overflow:hidden;">
                                    <div
                                        style="background:#fff;height:6px;border-radius:4px;width:{{ min($tauxFinalisation, 100) }}%;transition:width .6s;">
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

                {{-- Tickets aujourd'hui --}}
                <div class="col-6 col-xl-3 mb-3">
                    @php $pctPayesJour = $nbTicketsJour > 0 ? round(($nbTicketsPayesJour / $nbTicketsJour) * 100) : 0; @endphp
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#eff6ff;">
                                    <i data-feather="shopping-bag" style="color:#2563eb;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#1e293b;line-height:1;">{{ $nbTicketsJour }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                Tickets aujourd'hui
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#16a34a;font-weight:700;">{{ $nbTicketsPayesJour }}</span> payés
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:{{ $pctPayesJour }}%;background:#2563eb;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- En attente / brouillon --}}
                <div class="col-6 col-xl-3 mb-3">
                    @php $pctAttente = $nbTicketsMois > 0 ? round((($nbEnAttente + $nbBrouillon) / $nbTicketsMois) * 100) : 0; @endphp
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#fffbeb;">
                                    <i data-feather="clock" style="color:#d97706;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#d97706;line-height:1;">{{ $nbEnAttente }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                En attente (mois)
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#64748b;font-weight:700;">{{ $nbBrouillon }}</span> brouillons
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:{{ $pctAttente }}%;background:#d97706;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Annulés --}}
                <div class="col-6 col-xl-3 mb-3">
                    @php $pctAnnule = $nbTicketsMois > 0 ? round(($nbAnnule / $nbTicketsMois) * 100) : 0; @endphp
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#fef2f2;">
                                    <i data-feather="x-circle" style="color:#dc2626;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#dc2626;line-height:1;">{{ $nbAnnule }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                Annulés (mois)
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#1d4ed8;font-weight:700;">{{ $nbConverti }}</span> convertis en facture
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:{{ $pctAnnule }}%;background:#ef4444;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sessions actives --}}
                <div class="col-6 col-xl-3 mb-3">
                    <div class="card akpi-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="akpi-icon" style="background:#f5f3ff;">
                                    <i data-feather="unlock" style="color:#7c3aed;"></i>
                                </div>
                                <span
                                    style="font-size:2rem;font-weight:800;color:#1e293b;line-height:1;">{{ $sessionsActives }}</span>
                            </div>
                            <div
                                style="font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#64748b;">
                                Sessions actives
                            </div>
                            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
                                <span style="color:#7c3aed;font-weight:700;">{{ $nombreCaisses }}</span> caisse(s) active(s)
                            </div>
                            <div class="prog-thin mt-2">
                                <div class="prog-fill" style="width:100%;background:#7c3aed;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /row KPI activité --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 3 — ÉVOLUTION CA + RÉPARTITION STATUTS
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">

                {{-- Line chart – Évolution CA & nb tickets --}}
                <div class="col-xl-8 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Évolution des ventes</div>
                                <div class="card-title">CA & nombre de tickets — 14 derniers jours</div>
                            </div>
                            <div class="d-flex gap-3">
                                <span class="legend-pill"><span style="background:#2563eb;"></span>CA (FCFA)</span>
                                <span class="legend-pill"><span style="background:#f59e0b;"></span>Tickets</span>
                            </div>
                        </div>
                        <div class="card-body px-3 pb-3 pt-0">
                            <div style="position:relative;height:275px;">
                                <canvas id="chartEvolution"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Doughnut – Répartition des tickets par statut --}}
                <div class="col-xl-4 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <div class="section-label">Répartition</div>
                            <div class="card-title">Tickets par statut (mois)</div>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center pb-3 pt-0">
                            @if ($nbTicketsMois > 0)
                                <div style="position:relative;height:230px;width:100%;">
                                    <canvas id="chartStatuts"></canvas>
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
             SECTION 4 — MODES DE PAIEMENT + CA PAR CAISSE
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">

                {{-- Modes de paiement du jour --}}
                <div class="col-xl-4 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <div class="section-label">Encaissements</div>
                            <div class="card-title">Modes de paiement (jour)</div>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center justify-content-center pb-3 pt-0">
                            @if (count($encaissementsParMode) > 0)
                                <div style="position:relative;height:230px;width:100%;">
                                    <canvas id="chartModes"></canvas>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <i data-feather="credit-card" style="width:38px;height:38px;opacity:.25;"></i>
                                    <p class="mt-2 small mb-0">Aucun encaissement aujourd'hui</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Horizontal bar – CA par caisse --}}
                <div class="col-xl-8 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <div class="section-label">Analyse par caisse</div>
                            <div class="card-title">Chiffre d'affaires par caisse (mois)</div>
                        </div>
                        <div class="card-body px-3 pb-3 pt-0">
                            @if (count($caParCaisse) > 0)
                                <div style="position:relative;height:280px;">
                                    <canvas id="chartCaisses"></canvas>
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

            </div>{{-- /row modes + caisses --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 5 — TOP PRODUITS & TICKETS RÉCENTS
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">

                {{-- Top produits vendus --}}
                <div class="col-xl-5 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <div class="section-label">Top ventes</div>
                            <div class="card-title">Produits les plus vendus (mois)</div>
                        </div>
                        <div class="card-body p-0">
                            @if ($topProduits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table dash-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="padding-left:1.5rem;">#</th>
                                                <th>Produit</th>
                                                <th class="text-end">Qté</th>
                                                <th class="text-end" style="padding-right:1.5rem;">CA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($topProduits as $i => $p)
                                                <tr>
                                                    <td style="padding-left:1.5rem;">
                                                        <span class="rank-pill">{{ $i + 1 }}</span>
                                                    </td>
                                                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                        {{ $p->designation ?? '—' }}
                                                    </td>
                                                    <td class="text-end">{{ rtrim(rtrim(number_format((float) $p->qte, 2, ',', ' '), '0'), ',') }}</td>
                                                    <td class="text-end" style="padding-right:1.5rem;">
                                                        <span style="font-weight:700;color:#1e293b;">
                                                            {!! number_format((float) $p->ca, 0, ',', '&nbsp;') !!}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i data-feather="package" style="width:40px;height:40px;opacity:.25;"></i>
                                    <p class="mt-2 small mb-0">Aucune vente ce mois-ci</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Derniers tickets --}}
                <div class="col-xl-7 mb-3 mb-xl-0">
                    <div class="card chart-card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <div class="section-label">Activité récente</div>
                                <div class="card-title">Derniers tickets</div>
                            </div>
                            <a href="{{ route('module_comptoir.tickets.index') }}"
                                style="background:#eff6ff;color:#2563eb;border-radius:8px;
                                  padding:5px 14px;font-size:.75rem;font-weight:700;
                                  text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                                Voir tout <i data-feather="arrow-right" style="width:13px;height:13px;"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            @if ($derniersTickets->count() > 0)
                                <div class="table-responsive">
                                    <table class="table dash-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="padding-left:1.5rem;">Code</th>
                                                <th>Client</th>
                                                <th class="text-end">TTC</th>
                                                <th class="text-center">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($derniersTickets as $t)
                                                <tr>
                                                    <td style="padding-left:1.5rem;">
                                                        <span style="font-weight:700;color:#2563eb;">{{ $t->code }}</span>
                                                    </td>
                                                    <td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                        {{ optional($t->Contribuable)->assujeti ?: ($t->client_libre ?: '—') }}
                                                    </td>
                                                    <td class="text-end">
                                                        <span style="font-weight:600;color:#1e293b;">
                                                            {!! number_format((float) $t->montant_ttc, 0, ',', '&nbsp;') !!}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        @php $bc = ['brouillon'=>'badge-brouillon','en_attente'=>'badge-attente','paye'=>'badge-paye','annule'=>'badge-annule','converti'=>'badge-converti'][$t->statut] ?? 'badge-brouillon'; @endphp
                                                        <span class="badge-statut {{ $bc }}">{{ $t->statut }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i data-feather="inbox" style="width:40px;height:40px;opacity:.25;"></i>
                                    <p class="mt-2 small mb-0">Aucun ticket enregistré</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>{{-- /row top produits + tickets --}}

            {{-- ──────────────────────────────────────────────────────
             SECTION 6 — SESSIONS DE CAISSE RÉCENTES
             ────────────────────────────────────────────────────── --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card chart-card">
                        <div class="card-header d-flex align-items-center gap-3">
                            <div
                                style="width:42px;height:42px;min-width:42px;border-radius:12px;
                                    background:linear-gradient(135deg,#133e9a,#2563eb);
                                    display:flex;align-items:center;justify-content:center;">
                                <i data-feather="box" style="width:18px;height:18px;color:#fff;"></i>
                            </div>
                            <div>
                                <div class="section-label">Caisses</div>
                                <div class="card-title">Dernières ouvertures de caisse</div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if ($sessionsRecentes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table dash-table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="padding-left:1.5rem;">Caisse</th>
                                                <th>Caissier</th>
                                                <th>Date métier</th>
                                                <th class="text-end">Fond initial</th>
                                                <th class="text-end">Ventes</th>
                                                <th>Tickets</th>
                                                <th class="text-center" style="padding-right:1.5rem;">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sessionsRecentes as $s)
                                                <tr>
                                                    <td style="padding-left:1.5rem;">
                                                        <span style="font-weight:700;color:#1e293b;">{{ optional($s->Caisse)->libelle ?? '—' }}</span>
                                                    </td>
                                                    <td>{{ trim((optional($s->OpenedBy)->prenom ?? '') . ' ' . (optional($s->OpenedBy)->nom ?? '')) ?: '—' }}</td>
                                                    <td>{{ optional($s->date_metier)->format('d/m/Y') }}</td>
                                                    <td class="text-end">{!! number_format((float) $s->fond_initial, 0, ',', '&nbsp;') !!}</td>
                                                    <td class="text-end">
                                                        <span style="font-weight:700;color:#16a34a;">
                                                            {!! number_format((float) $s->montant_total, 0, ',', '&nbsp;') !!}
                                                        </span>
                                                    </td>
                                                    <td>{{ $s->tickets_count }}</td>
                                                    <td class="text-center" style="padding-right:1.5rem;">
                                                        @php $sc = ['ouverte'=>'badge-ouverte','en_arret'=>'badge-en_arret','fermee'=>'badge-fermee'][$s->statut] ?? 'badge-fermee'; @endphp
                                                        <span class="badge-statut {{ $sc }}">{{ $s->statut }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i data-feather="inbox" style="width:48px;height:48px;opacity:.25;"></i>
                                    <p class="mt-2 mb-0">Aucune ouverture de caisse enregistrée</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>{{-- /row sessions --}}

        </div>{{-- /container-xl --}}
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') return;

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

            // ── 1. LINE CHART — Évolution CA & nombre de tickets ──────────
            var ctxEvo = document.getElementById('chartEvolution');
            if (ctxEvo) {
                new Chart(ctxEvo.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($labelsJours) !!},
                        datasets: [{
                                label: 'CA (FCFA)',
                                data: {!! json_encode($evolutionCaJour) !!},
                                borderColor: '#2563eb',
                                backgroundColor: 'rgba(37,99,235,0.07)',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#2563eb',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                fill: true,
                                lineTension: 0.35,
                                yAxisID: 'y-ca'
                            },
                            {
                                label: 'Tickets',
                                data: {!! json_encode($evolutionNbTickets) !!},
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245,158,11,0.07)',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#f59e0b',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                fill: false,
                                lineTension: 0.35,
                                yAxisID: 'y-nb'
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
                                id: 'y-ca',
                                position: 'left',
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
                            }, {
                                id: 'y-nb',
                                position: 'right',
                                gridLines: {
                                    display: false
                                },
                                ticks: {
                                    beginAtZero: true,
                                    fontSize: 11,
                                    fontColor: '#f59e0b',
                                    fontFamily: 'inherit',
                                    precision: 0
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
                                    if (item.datasetIndex === 0) {
                                        return ' ' + label + ' : ' +
                                            new Intl.NumberFormat('fr-FR').format(Math.round(item.yLabel)) +
                                            ' FCFA';
                                    }
                                    return ' ' + label + ' : ' + Math.round(item.yLabel);
                                }
                            }
                        }
                    }
                });
            }

            // ── 2. DOUGHNUT — Tickets par statut ───────────────────────────
            var ctxStatuts = document.getElementById('chartStatuts');
            if (ctxStatuts) {
                new Chart(ctxStatuts.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Payés', 'Convertis', 'En attente', 'Brouillons', 'Annulés'],
                        datasets: [{
                            data: [
                                {{ $nbPaye }},
                                {{ $nbConverti }},
                                {{ $nbEnAttente }},
                                {{ $nbBrouillon }},
                                {{ $nbAnnule }}
                            ],
                            backgroundColor: ['#16a34a', '#1d4ed8', '#d97706', '#94a3b8', '#dc2626'],
                            borderColor: '#fff',
                            borderWidth: 3
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
                                padding: 10,
                                fontColor: '#475569',
                                fontFamily: 'inherit'
                            }
                        },
                        tooltips: {
                            callbacks: {
                                label: function(item, data) {
                                    return ' ' + data.labels[item.index] + ' : ' +
                                        data.datasets[0].data[item.index] + ' ticket(s)';
                                }
                            }
                        }
                    }
                });
            }

            // ── 3. DOUGHNUT — Modes de paiement (jour) ─────────────────────
            var ctxModes = document.getElementById('chartModes');
            if (ctxModes) {
                var modeLabels = {!! json_encode(array_keys($encaissementsParMode)) !!};
                var modeData = {!! json_encode(array_values($encaissementsParMode)) !!};
                if (modeLabels.length > 0) {
                    new Chart(ctxModes.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: modeLabels,
                            datasets: [{
                                data: modeData,
                                backgroundColor: palette.slice(0, modeLabels.length),
                                borderColor: '#fff',
                                borderWidth: 3
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

            // ── 4. HORIZONTAL BAR — CA par caisse ──────────────────────────
            var ctxCaisses = document.getElementById('chartCaisses');
            if (ctxCaisses) {
                var caisseLabels = {!! json_encode(array_keys($caParCaisse)) !!};
                var caisseData = {!! json_encode(array_values($caParCaisse)) !!};
                if (caisseLabels.length > 0) {
                    new Chart(ctxCaisses.getContext('2d'), {
                        type: 'horizontalBar',
                        data: {
                            labels: caisseLabels,
                            datasets: [{
                                label: 'CA (FCFA)',
                                data: caisseData,
                                backgroundColor: palette.slice(0, caisseLabels.length),
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
