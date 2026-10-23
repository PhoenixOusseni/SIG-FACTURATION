@extends('layouts.master')

@section('title')
    <title>SIG-FACTURATION | Hub de Facturation Unifié</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        /* ── Hub Tabs & Badges ── */
        .hub-tab-btn {
            background: #fff;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .hub-tab-btn:hover {
            background: #f8fafc;
            color: #1d3f7f;
            border-color: #cbd5e1;
        }

        .hub-tab-btn.active {
            background: #1d3f7f;
            color: #fff;
            border-color: #1d3f7f;
            box-shadow: 0 2px 8px rgba(29, 63, 127, 0.25);
        }

        .badge-certifie {
            background-color: #15803d;
            color: #ffffff !important;
            border: 1px solid #166534;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 1px 3px rgba(21, 128, 61, 0.25);
        }

        .badge-brouillon {
            background-color: #475569;
            color: #ffffff !important;
            border: 1px solid #334155;
            font-weight: 600;
        }

        .badge-erreur {
            background-color: #dc2626;
            color: #ffffff !important;
            border: 1px solid #b91c1c;
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(220, 38, 38, 0.25);
        }

        .badge-proforma {
            background-color: #1d3f7f;
            color: #ffffff !important;
            border: 1px solid #152d5c;
            font-weight: 700;
        }

        .badge-remise {
            background-color: #0284c7 !important;
            color: #ffffff !important;
            border: 1px solid #0369a1 !important;
            font-weight: 700;
        }

        .badge-reprise {
            background-color: #d97706 !important;
            color: #ffffff !important;
            border: 1px solid #b45309 !important;
            font-weight: 700;
        }

        .badge-rectifie {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
            border: 1px solid #4338ca !important;
            font-weight: 700;
        }

        .badge-annulee {
            background-color: #b91c1c !important;
            color: #ffffff !important;
            border: 1px solid #991b1b !important;
            font-weight: 700;
        }

        .badge-avoir-total {
            background-color: #475569 !important;
            color: #ffffff !important;
            border: 1px solid #334155 !important;
            font-weight: 700;
        }

        .badge-avoir-partiel {
            background-color: #f59e0b !important;
            color: #1e293b !important;
            border: 1px solid #d97706 !important;
            font-weight: 700;
        }

        /* ── Live Synthesis Summary Box ── */
        .live-synthese-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }

        .live-synthese-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.88rem;
        }

        .live-synthese-row:last-child {
            border-bottom: none;
        }

        .live-synthese-total {
            background: #1d3f7f;
            color: #ffffff;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 1.25rem;
            font-weight: 800;
        }

        /* ── Header & Switcher de Prévisualisation Haute-Fidélité ── */
        .preview-header-bar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 20px;
        }

        .format-tab-btn {
            border: none !important;
            color: #94a3b8 !important;
            background: transparent !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            padding: 5px 14px !important;
            border-radius: 6px !important;
            transition: all 0.15s ease-in-out !important;
            cursor: pointer !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .format-tab-btn:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .format-tab-btn.active {
            background: #ffffff !important;
            color: #0f172a !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25) !important;
        }
    </style>
@endsection

@section('content')
    <main class="py-4">
        <div class="container-xl px-4">

            {{-- En-tête Principal du Hub --}}
            <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 14px; border: 1px solid #e2e8f0;">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <h2 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">
                                    Hub de Facturation Unifié
                                </h2>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fas fa-shield-alt me-1"></i> Boîtier MCF Opérationnel
                                </span>
                            </div>
                            <p class="text-muted mb-0 small">
                                Gestion centralisée des Ventes, Devis Proforma, Avoirs Fiscaux DGI et Encaissements.
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            {{-- Bouton 1 : Nouvelle Facture de Vente --}}
                            <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createFactureModal" onclick="resetFactureForm()">
                                <i class="fas fa-plus-circle me-1"></i> Nouvelle Facture
                            </button>

                            {{-- Bouton 2 : Nouveau Devis / Proforma --}}
                            <button type="button" class="btn fw-semibold shadow-sm text-white" style="background: #1d3f7f;" data-bs-toggle="modal" data-bs-target="#createProformaModal">
                                <i class="fas fa-file-invoice me-1"></i> Nouveau Devis
                            </button>

                            {{-- Bouton 3 : Recertification Globale Erreurs --}}
                            @if ($nbErreursMcf > 0)
                                <form action="{{ route('module_ordre_facture.recertifier_tout') }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Voulez-vous retenter la transmission MCF pour les {{ $nbErreursMcf }} facture(s) en erreur ?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger fw-bold shadow-sm">
                                        <i class="fas fa-sync me-1"></i> Recertifier Erreurs ({{ $nbErreursMcf }})
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Messages Flash --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif
            @if (session('error') || session('form_error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') ?? session('form_error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            {{-- 4 Mini-Cartes KPI d'état --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px; border-left: 4px solid #1d3f7f !important;">
                        <div class="text-muted small fw-bold text-uppercase">Factures Vente</div>
                        <div class="fs-4 fw-bold font-monospace text-primary">{{ $collection->count() }}</div>
                        <div class="small text-muted">{{ $facturesCertifiees->count() }} certifiées MCF</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px; border-left: 4px solid #059669 !important;">
                        <div class="text-muted small fw-bold text-uppercase">Devis Proforma</div>
                        <div class="fs-4 fw-bold font-monospace text-success">{{ $proformas->count() }}</div>
                        <div class="small text-muted">Prêts à convertir</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px; border-left: 4px solid #b45309 !important;">
                        <div class="text-muted small fw-bold text-uppercase">Avoirs Fiscaux</div>
                        <div class="fs-4 fw-bold font-monospace text-warning">{{ $avoirs->count() }}</div>
                        <div class="small text-muted">Corrections / Retours</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px; border-left: 4px solid #dc2626 !important;">
                        <div class="text-muted small fw-bold text-uppercase">Créances en Attente</div>
                        <div class="fs-4 fw-bold font-monospace text-danger">{{ $facturesImpayees->count() }}</div>
                        <div class="small text-muted">À encaisser</div>
                    </div>
                </div>
            </div>

            {{-- Carte Principale du Tableau Unifié --}}
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                {{-- Barre d'onglets de filtrage rapide (Zero Reload) --}}
                <div class="card-header bg-white border-bottom p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex flex-wrap gap-2" id="hubFilterTabs">
                            <button type="button" class="hub-tab-btn active" onclick="filtrerTableau('tous')">
                                <i class="fas fa-th-list me-1"></i> Toutes les pièces ({{ $collection->count() + $avoirs->count() + $proformas->count() }})
                            </button>
                            <button type="button" class="hub-tab-btn" onclick="filtrerTableau('FV')">
                                <i class="fas fa-file-invoice me-1"></i> Ventes ({{ $collection->count() }})
                            </button>
                            <button type="button" class="hub-tab-btn" onclick="filtrerTableau('FA')">
                                <i class="fas fa-undo me-1"></i> Avoirs Fiscaux ({{ $avoirs->count() }})
                            </button>
                            <button type="button" class="hub-tab-btn" onclick="filtrerTableau('PRO')">
                                <i class="fas fa-file-alt me-1"></i> Devis Proforma ({{ $proformas->count() }})
                            </button>
                            <button type="button" class="hub-tab-btn text-warning-emphasis" onclick="filtrerTableau('IMPAYE')">
                                <i class="fas fa-clock me-1"></i> Impayées ({{ $facturesImpayees->count() }})
                            </button>
                            @if ($nbErreursMcf > 0)
                                <button type="button" class="hub-tab-btn text-danger border-danger" onclick="filtrerTableau('ERREUR')">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Erreurs MCF ({{ $nbErreursMcf }})
                                </button>
                            @endif
                        </div>

                        {{-- Recherche instantanée --}}
                        <div style="min-width: 260px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0" id="hubSearchInput"
                                    placeholder="Rechercher N°, Client, Réf..." onkeyup="rechercherDansTableau()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="hubBillingTable">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Pièce & Type</th>
                                    <th>Date & Réf</th>
                                    <th>Client</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-center">Règlement</th>
                                    <th style="min-width: 150px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- 1. Factures de Vente (FV / EV / FT) --}}
                                @foreach ($collection as $facture)
                                    @php
                                        $isCertifiee = $facture->isCertified();
                                        $typeCode = $facture->type_code;
                                        $totTtc = (float) $facture->montant_total;
                                        $paye = (float) $facture->montant_paye;
                                        $reste = (float) $facture->reste_a_payer;
                                        $isErreur = ($facture->statut === 'Erreur MCF');
                                        $aAvoirTotal = $facture->aAvoirTotal();
                                        $aSubiAvoir = $facture->aSubiAvoir();
                                        $soldeAvoirDispo = $facture->soldeAvoirDisponible();
                                        $peutAvoir = $facture->peutSubirAvoir();
                                        $rowType = 'FV';
                                        $statusClass = $aAvoirTotal ? 'ANNULE' : ($isErreur ? 'ERREUR' : ($reste > 0.009 ? 'IMPAYE' : 'SOLDE'));
                                    @endphp
                                    <tr class="hub-row" data-type="FV" data-status="{{ $statusClass }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge {{ $typeCode === 'EV' ? 'bg-info text-dark' : 'bg-primary' }} font-monospace">{{ $typeCode }}</span>
                                                <a href="javascript:void(0)" onclick="openPreview({{ $facture->id }}, '{{ $facture->code }}', {{ $isCertifiee ? 'true' : 'false' }}, 'facture')" class="fw-bold font-monospace text-primary fs-6 text-decoration-none" title="Prévisualiser la facture">{{ $facture->code }}</a>
                                            </div>
                                        </td>
                                        <td data-order="{{ optional($facture->date)->format('Y-m-d') }}">
                                            <div>{{ optional($facture->date)->format('d/m/Y') }}</div>
                                            @if ($facture->reference)
                                                 <small class="text-muted font-monospace">{{ $facture->reference }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ optional($facture->Contribuable)->assujeti ?? 'Client Comptoir' }}</div>
                                            @if (optional($facture->Contribuable)->ifu)
                                                <small class="text-muted font-monospace">IFU: {{ $facture->Contribuable->ifu }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end font-monospace fw-bold fs-6" data-order="{{ $totTtc }}">
                                            {{ number_format($totTtc, 0, ',', ' ') }} F
                                        </td>
                                        <td class="text-center">
                                            @if ($facture->statut === 'Remise accordée' || $facture->statut === 'Remise appliquée' || str_contains(strtolower($facture->statut ?? ''), 'remise'))
                                                <span class="badge badge-remise px-2 py-1" title="Remise commerciale accordée par avoir fiscal">
                                                    <i class="fas fa-tag me-1"></i> Remise accordée
                                                </span>
                                            @elseif ($facture->statut === 'Reprise de biens' || $facture->statut === 'Retour marchandise' || str_contains(strtolower($facture->statut ?? ''), 'retour') || str_contains(strtolower($facture->statut ?? ''), 'reprise'))
                                                <span class="badge badge-reprise px-2 py-1" title="Reprise de biens / Retour de marchandises">
                                                    <i class="fas fa-box-open me-1"></i> Reprise de biens
                                                </span>
                                            @elseif ($facture->statut === 'Rectifiée par avoir' || str_contains(strtolower($facture->statut ?? ''), 'rectifi'))
                                                <span class="badge badge-rectifie px-2 py-1" title="Facture rectifiée par un avoir fiscal">
                                                    <i class="fas fa-pen-nib me-1"></i> Rectifiée par avoir
                                                </span>
                                            @elseif ($facture->statut === 'Annulée par Avoir' || $facture->statut === 'Annulée' || (str_contains(strtolower($facture->statut ?? ''), 'annul') && $aAvoirTotal))
                                                <span class="badge badge-annulee px-2 py-1" title="Facture intégralement annulée par un avoir fiscal">
                                                    <i class="fas fa-ban me-1"></i> Annulée par avoir
                                                </span>
                                            @elseif ($facture->statut === 'Avoir total appliqué' || $aAvoirTotal)
                                                <span class="badge badge-avoir-total px-2 py-1" title="Avoir fiscal total appliqué (Facture soldée/verrouillée)">
                                                    <i class="fas fa-check-double me-1"></i> Avoir total appliqué
                                                </span>
                                            @elseif ($aSubiAvoir)
                                                <span class="badge badge-avoir-partiel px-2 py-1" title="Avoir partiel émis (Solde restant : {{ number_format($soldeAvoirDispo, 0, ',', ' ') }} F)">
                                                    <i class="fas fa-undo me-1"></i> Avoir partiel
                                                </span>
                                            @elseif ($isCertifiee)
                                                <span class="badge badge-certifie px-2 py-1" title="Signée et sécurisée par le boîtier MCF">
                                                    <i class="fas fa-check-circle me-1"></i> Certifiée SFE
                                                </span>
                                                @if ($facture->sfe_compteur_total)
                                                    <small class="d-block text-muted font-monospace fw-bold">{{ $facture->sfe_compteur_total }}</small>
                                                @endif
                                            @elseif ($isErreur || in_array($facture->statut, ['Erreur SFE', 'Erreur MCF']) || !empty($facture->sfe_error))
                                                <span class="badge badge-erreur px-2 py-1" title="{{ $facture->sfe_error ?: 'Erreur communication MCF. Cliquez sur Recertifier.' }}">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Erreur SFE
                                                </span>
                                                @if ($facture->sfe_error)
                                                    <small class="d-block text-danger font-monospace mt-1" style="font-size:10px; max-width:130px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="{{ $facture->sfe_error }}">
                                                        {{ Str::limit($facture->sfe_error, 24) }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-brouillon px-2 py-1">
                                                    <i class="fas fa-file-alt me-1"></i> Brouillon
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($aAvoirTotal)
                                                <span class="badge bg-light text-muted border">Sans objet</span>
                                            @elseif ($reste <= 0.009)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Soldée</span>
                                            @elseif ($paye > 0)
                                                <span class="badge bg-info-subtle text-info border border-info-subtle">Partielle ({{ number_format($reste, 0, ',', ' ') }} F)</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Impayée</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm shadow-sm" role="group">
                                                {{-- 1. Bouton Aperçu Haute-Fidélité DGI (A4 / Ticket 80mm) --}}
                                                <button type="button" class="btn btn-outline-primary"
                                                    title="Prévisualiser et Imprimer la Facture (Format A4 ou Ticket 80mm)"
                                                    onclick="openPreview({{ $facture->id }}, '{{ $facture->code }}', {{ $isCertifiee ? 'true' : 'false' }}, 'facture')">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </button>

                                                @if ($isCertifiee)
                                                    {{-- FACTURE CERTIFIÉE DGI --}}
                                                    {{-- 2. Émettre un Avoir fiscal DGI si la facture est éligible --}}
                                                    @if ($peutAvoir)
                                                        <button type="button" class="btn btn-outline-warning text-dark"
                                                            title="Émettre un Avoir fiscal DGI sur cette facture (Solde éligible : {{ number_format($soldeAvoirDispo, 0, ',', ' ') }} FCFA)"
                                                            onclick="openAvoirModal({{ $facture->id }}, '{{ $facture->code }}', {{ $facture->contribuable_id ?? 'null' }}, '{{ addslashes(optional($facture->Contribuable)->assujeti ?? '') }}', {{ $totTtc }}, {{ $soldeAvoirDispo }}, {{ $paye }}, {{ $reste }})">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    @endif

                                                    {{-- 3. Règlement / Encaissement (UNIQUEMENT si reste à payer et facture non annulée) --}}
                                                    @if (!$aAvoirTotal && $reste > 0.009)
                                                        <button type="button" class="btn btn-outline-success"
                                                            title="Encaisser un règlement (Reste à payer : {{ number_format($reste, 0, ',', ' ') }} FCFA)"
                                                            onclick="openReglementModal({{ $facture->id }}, '{{ $facture->code }}', '{{ addslashes(optional($facture->Contribuable)->assujeti ?? '') }}', {{ $totTtc }}, {{ $paye }}, {{ $reste }})">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                        </button>
                                                    @endif

                                                    {{-- 4. Historique des Règlements & Reçus (si versements enregistrés) --}}
                                                    @if ($facture->Reglements->isNotEmpty())
                                                        <button type="button" class="btn btn-outline-info"
                                                            title="Historique des règlements & Télécharger les reçus ({{ $facture->Reglements->count() }} versement(s))"
                                                            onclick='openReglementsHistory({{ $facture->id }}, "{{ $facture->code }}", "{{ addslashes(optional($facture->Contribuable)->assujeti ?? '') }}", {{ $totTtc }}, {{ $paye }}, {{ $reste }}, @json($facture->Reglements))'>
                                                            <i class="fas fa-receipt"></i>
                                                        </button>
                                                    @endif
                                                @else
                                                    {{-- FACTURE BROUILLON OU EN ERREUR MCF --}}
                                                    {{-- Modifier le brouillon --}}
                                                    <a href="{{ route('module_ordre_facture.edit', $facture->id) }}" class="btn btn-outline-secondary" title="Modifier cette facture">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    {{-- Fiscalisation directe MCF --}}
                                                    @if ($sfeConnected)
                                                        <form action="{{ route('module_ordre_facture.recertifier', $facture->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-danger" title="{{ $isErreur ? 'Retenter la certification MCF' : 'Fiscaliser / Certifier par le boîtier MCF' }}">
                                                                <i class="fas {{ $isErreur ? 'fa-sync' : 'fa-bolt' }}"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" class="btn btn-outline-secondary opacity-50" title="Boîtier MCF déconnecté (Branchez le boîtier pour fiscaliser)" disabled>
                                                            <i class="fas fa-bolt"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- 2. Factures d'Avoir (FA / EA) --}}
                                @foreach ($avoirs as $avoir)
                                    @php
                                        $isCertifieeAvoir = (bool) ($avoir->statut === 'Fiscalisée' || $avoir->dgi_signature);
                                        $totAvoir = (float) ($avoir->montant_total ?? 0);
                                        $natureMap = [
                                            'RRR' => 'Remise commerciale',
                                            'RAM' => 'Retour marchandises',
                                            'COR' => 'Rectification',
                                            'RAN' => 'Annulation totale',
                                        ];
                                        $natureLabel = $natureMap[$avoir->nature_avoir] ?? ($avoir->nature_avoir ?: 'Avoir fiscal');
                                    @endphp
                                    <tr class="hub-row" data-type="FA" data-status="{{ $isCertifieeAvoir ? 'CERTIFIEE' : 'BROUILLON' }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-warning text-dark font-monospace">FA</span>
                                                <span class="fw-bold font-monospace text-warning-emphasis fs-6">{{ $avoir->code }}</span>
                                            </div>
                                        </td>
                                        <td data-order="{{ $avoir->date ? \Carbon\Carbon::parse($avoir->date)->format('Y-m-d') : '1970-01-01' }}">
                                            <div>{{ $avoir->date ? \Carbon\Carbon::parse($avoir->date)->format('d/m/Y') : 'N/A' }}</div>
                                            <small class="text-muted font-monospace">Sur : {{ optional($avoir->Facture)->code ?: 'Facture orig.' }}</small>
                                            <span class="badge bg-light text-secondary border px-1" style="font-size: 10px;">{{ $natureLabel }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ optional($avoir->Contribuable)->assujeti ?? optional(optional($avoir->Facture)->Contribuable)->assujeti ?? 'Client' }}</div>
                                        </td>
                                        <td class="text-end font-monospace fw-bold fs-6 text-danger" data-order="-{{ $totAvoir }}">
                                            - {{ number_format($totAvoir, 0, ',', ' ') }} F
                                        </td>
                                        <td class="text-center">
                                            @if ($isCertifieeAvoir)
                                                <span class="badge badge-certifie px-2 py-1">
                                                    <i class="fas fa-check-circle me-1"></i> Avoir Certifié
                                                </span>
                                            @elseif ($isErreurAvoir || str_contains(strtolower($avoir->statut ?? ''), 'erreur') || !empty($avoir->sfe_error))
                                                <span class="badge badge-erreur px-2 py-1" title="{{ $avoir->sfe_error ?: 'Erreur certification' }}">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Erreur
                                                </span>
                                                @if ($avoir->sfe_error)
                                                    <small class="d-block text-danger font-monospace mt-1" style="font-size:10px; max-width:130px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="{{ $avoir->sfe_error }}">
                                                        {{ Str::limit($avoir->sfe_error, 24) }}
                                                    </small>
                                                @endif
                                            @else
                                                <span class="badge badge-brouillon px-2 py-1">
                                                    <i class="fas fa-file-alt me-1"></i> Brouillon Avoir
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $natureLabel }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm shadow-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary"
                                                    title="Prévisualiser et Imprimer l'Avoir"
                                                    onclick="openPreview({{ $avoir->id }}, '{{ $avoir->code }}', {{ $isCertifieeAvoir ? 'true' : 'false' }}, 'avoir')">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </button>
                                                @if (!$isCertifieeAvoir)
                                                    <form action="{{ route('module_facture_avoir.certifier', $avoir->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger" title="Fiscaliser l'Avoir par le MCF">
                                                            <i class="fas fa-bolt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- 3. Devis / Factures Proforma --}}
                                @foreach ($proformas as $proforma)
                                    @php
                                        $totProforma = (float) ($proforma->total_ttc ?? 0);
                                    @endphp
                                    <tr class="hub-row" data-type="PRO" data-status="PROFORMA">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-info text-dark font-monospace">PRO</span>
                                                <span class="fw-bold font-monospace text-info-emphasis fs-6">{{ $proforma->code }}</span>
                                            </div>
                                        </td>
                                        <td data-order="{{ $proforma->date ? \Carbon\Carbon::parse($proforma->date)->format('Y-m-d') : '1970-01-01' }}">
                                            <div>{{ $proforma->date ? \Carbon\Carbon::parse($proforma->date)->format('d/m/Y') : 'N/A' }}</div>
                                            @if ($proforma->reference)
                                                <small class="text-muted font-monospace">{{ $proforma->reference }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ optional($proforma->Contribuable)->assujeti ?? 'Client' }}</div>
                                        </td>
                                        <td class="text-end font-monospace fw-bold fs-6" data-order="{{ $totProforma }}">
                                            {{ number_format($totProforma, 0, ',', ' ') }} F
                                        </td>
                                        <td class="text-center">
                                            @if ($proforma->estConvertie())
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                    <i class="fas fa-check-circle me-1"></i> Facturée
                                                </span>
                                            @else
                                                <span class="badge badge-proforma px-2 py-1">
                                                    <i class="fas fa-file-alt me-1"></i> {{ ucfirst($proforma->statut ?? 'Devis') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-muted border">Devis / Proforma</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($proforma->estConvertie())
                                                {{-- Règle 1 : Quand une facture proforma ou devis est convertie en facture, seul le bouton de détail s'affiche pour prévisualiser --}}
                                                <button type="button" class="btn btn-outline-primary btn-sm px-2.5"
                                                    title="Prévisualiser la proforma convertie"
                                                    onclick="openPreview({{ $proforma->id }}, '{{ $proforma->code }}', false, 'proforma')">
                                                    <i class="fas fa-eye me-1"></i> Détails
                                                </button>
                                            @else
                                                <div class="btn-group btn-group-sm shadow-sm" role="group">
                                                    {{-- Prévisualisation --}}
                                                    <button type="button" class="btn btn-outline-primary"
                                                        title="Prévisualiser / Imprimer la Proforma"
                                                        onclick="openPreview({{ $proforma->id }}, '{{ $proforma->code }}', false, 'proforma')">
                                                        <i class="fas fa-eye text-primary"></i>
                                                    </button>

                                                    {{-- Conversion Fluide dans le Formulaire Pré-rempli --}}
                                                    <button type="button" class="btn btn-outline-success"
                                                        title="Convertir en Facture de Vente"
                                                        onclick="convertirProformaViaForm({{ $proforma->id }})">
                                                        <i class="fas fa-file-invoice-dollar me-1"></i> Facturer
                                                    </button>

                                                    {{-- Supprimer le devis --}}
                                                    <form action="{{ route('module_facture_proforma.destroy', [$proforma->id]) }}" method="POST" class="d-inline"
                                                        onsubmit="return confirm('Confirmez-vous la suppression du devis {{ $proforma->code }} ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer ce devis">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================================================
             MODALE 1 : FORMULAIRE DE VENTE UNIFIÉ (AVEC MULTI-RÈGLEMENT & RÉCAP DGI)
             ==================================================================== --}}
        <div class="modal fade" id="createFactureModal" tabindex="-1" aria-labelledby="createFactureModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
                <div class="modal-content border-0 shadow-2xl" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header text-white d-flex justify-content-between align-items-center" style="background: #1d3f7f; border-bottom: 2px solid #152d5c;">
                        <div class="d-flex align-items-center gap-3">
                            <h5 class="modal-title fw-bold mb-0" id="createFactureModalLabel">
                                <i class="fas fa-file-invoice me-2"></i> Émission d'une Facture de Vente & Prestations
                            </h5>
                            @if ($sfeConnected)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem;">
                                    <i class="fas fa-shield-alt me-1"></i> Boîtier MCF Prêt
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 0.75rem;" title="Boîtier MCF déconnecté : certification directe indisponible">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Boîtier MCF Non Connecté (Mode Brouillon)
                                </span>
                            @endif
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>

                    <div class="modal-body p-4 bg-light">
                        {{-- Affichage visible des erreurs de validation serveur à l'intérieur du modal --}}
                        @if (isset($errors) && $errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                                    <h6 class="fw-bold mb-0">Veuillez corriger les informations suivantes :</h6>
                                </div>
                                <ul class="mb-0 ps-4 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif
                        @if (session('form_error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('form_error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <form action="{{ route('module_ordre_facture.store') }}" method="POST" id="mainFactureForm">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                            <input type="hidden" name="remise_2" value="0">
                            <input type="hidden" name="facture_proforma_id" id="facture_proforma_id" value="">

                            {{-- Bloc 1 : Client & En-tête --}}
                            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-id-card me-2"></i> 1. Données Générales & Client</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-lg-5 col-md-12">
                                            <label class="form-label d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold">Client Assujetti <span class="text-danger">*</span></span>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-0"
                                                    data-bs-toggle="modal" data-bs-target="#quickCreateClientModal">
                                                    <i class="fas fa-plus"></i> Nouveau Client
                                                </button>
                                            </label>
                                            <select name="contribuable_id" id="contribuable_id" class="form-select select2-input" required style="width:100%" onchange="validerChampsObligatoires()">
                                                <option value="">-- Sélectionner le client assujetti --</option>
                                                @foreach ($contribuables as $contribuable)
                                                    <option value="{{ $contribuable->id }}" {{ old('contribuable_id') == $contribuable->id ? 'selected' : '' }}>
                                                        {{ $contribuable->assujeti }} {{ $contribuable->ifu ? '(IFU: ' . $contribuable->ifu . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label fw-semibold">Type Facture <span class="text-danger">*</span></label>
                                            <select name="typefacture_id" id="typefacture_id" class="form-select" required onchange="validerChampsObligatoires()">
                                                <option value="">-- Sélectionner --</option>
                                                @foreach ($typefactures as $type)
                                                    <option value="{{ $type->id }}" {{ old('typefacture_id') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->code }} - {{ $type->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label fw-semibold">Date Vente <span class="text-danger">*</span></label>
                                            <input class="form-control" name="date" id="facture_date" type="date" value="{{ old('date', date('Y-m-d')) }}" required />
                                        </div>

                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label fw-semibold">Régime PSVB</label>
                                            <select name="bic_id" id="bic_id" class="form-select" onchange="calculerSyntheseLive()">
                                                <option value="" data-taux="0">-- Aucun PSVB applicable --</option>
                                                @foreach ($bics as $bic)
                                                    <option value="{{ $bic->id }}" data-taux="{{ $bic->taux }}" {{ old('bic_id') == $bic->id ? 'selected' : '' }}>
                                                        {{ $bic->code }} ({{ $bic->taux }}%) - {{ $bic->nom ?? $bic->designation }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">Prélèvement Spécifique applicable exclusivement sur les biens</small>
                                        </div>

                                        <div class="col-lg-4 col-md-6">
                                            <label class="form-label fw-semibold">Référence BC / Devis</label>
                                            <input class="form-control" name="reference" id="facture_reference" type="text" value="{{ old('reference') }}" placeholder="Ex: BC-2026-089 ou DEV-0012" />
                                        </div>

                                        <div class="col-lg-4 col-md-6">
                                            <label class="form-label fw-semibold">Magasin / Dépôt Stock</label>
                                            <select name="service_id" class="form-select">
                                                <option value="">-- Sélectionner un magasin / dépôt --</option>
                                                @foreach ($services as $srv)
                                                    <option value="{{ $srv->id }}" {{ old('service_id') == $srv->id ? 'selected' : '' }}>{{ $srv->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-4 col-md-6">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold mb-0">Remise Commerciale</label>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <input type="radio" class="btn-check" name="remise_type" id="remiseTypePct" value="POURCENTAGE" checked onchange="changerTypeRemise('POURCENTAGE')">
                                                    <label class="btn btn-outline-secondary py-0 px-2 font-monospace" for="remiseTypePct" style="font-size: 0.72rem;">% (Taux)</label>

                                                    <input type="radio" class="btn-check" name="remise_type" id="remiseTypeMnt" value="MONTANT" onchange="changerTypeRemise('MONTANT')">
                                                    <label class="btn btn-outline-secondary py-0 px-2 font-monospace" for="remiseTypeMnt" style="font-size: 0.72rem;">FCFA (Fixe)</label>
                                                </div>
                                            </div>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" name="remise_valeur" id="facture_remise_valeur" class="form-control font-monospace" value="{{ old('remise_valeur', 0) }}" placeholder="0" oninput="calculerSyntheseLive()">
                                                <span class="input-group-text bg-white fw-bold" id="remiseUnitLabel">%</span>
                                                <input type="hidden" name="remise_1" id="facture_remise_1" value="{{ old('remise_1', 0) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bloc 2 : Lignes de Facture (Biens + TS + Services) & Mode de Saisie Prix --}}
                            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-boxes me-2"></i> 2. Lignes d'Articles & Prestations de Services</h6>
                                        <small class="text-muted">Prestations de services exemptées d'inventaire physique et de PSVB</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        {{-- Sélecteur compact HT / TTC --}}
                                        <div class="d-inline-flex align-items-center bg-light border rounded-pill px-2 py-1 shadow-sm">
                                            <span class="small fw-bold text-muted me-2" style="font-size: 11px;"><i class="fas fa-calculator text-primary me-1"></i> Tarification :</span>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Mode de tarification">
                                                <input type="radio" class="btn-check" name="mode_prix" id="modePrixHt" value="HT" checked autocomplete="off" onchange="changerModePrix('HT')">
                                                <label class="btn btn-outline-primary btn-sm py-0.5 px-2.5 fw-bold" for="modePrixHt" style="font-size: 11px;" title="Prix saisis Hors Taxes, la TVA s'ajoute">
                                                    HT
                                                </label>

                                                <input type="radio" class="btn-check" name="mode_prix" id="modePrixTtc" value="TTC" autocomplete="off" onchange="changerModePrix('TTC')">
                                                <label class="btn btn-outline-primary btn-sm py-0.5 px-2.5 fw-bold" for="modePrixTtc" style="font-size: 11px;" title="Prix saisis Toutes Taxes Comprises, la TVA est déduite">
                                                    TTC
                                                </label>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm px-3" id="addRowBtn">
                                            <i class="fas fa-plus me-1"></i> Ajouter une ligne
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0" id="elementsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 45px;" class="text-center">#</th>
                                                    <th style="min-width: 380px;">Article / Prestation &bull; Classification & Groupe TVA</th>
                                                    <th style="width: 150px;" class="text-end" id="colPriceHeader">P.U. HT (FCFA)</th>
                                                    <th style="width: 110px;" class="text-center">Quantité</th>
                                                    <th style="width: 160px;" class="text-end" id="colTotalLineHeader">Total Ligne HT</th>
                                                    <th style="width: 50px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="elementsContainer">
                                                @if (old('produits_id') && is_array(old('produits_id')))
                                                    @foreach (old('produits_id') as $idx => $oldProdId)
                                                        <tr class="element-row">
                                                            <td class="text-center fw-bold text-muted row-index">{{ $idx + 1 }}</td>
                                                            <td>
                                                                <select name="produits_id[]" class="form-select base-taxable-select" required>
                                                                    <option value="">Sélectionner un produit...</option>
                                                                    @foreach ($produits as $prod)
                                                                        <option value="{{ $prod->id }}" {{ $oldProdId == $prod->id ? 'selected' : '' }}>
                                                                            {{ $prod->label_fiscal }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0" name="prix_unitaire[]" class="form-control prix-input text-end font-monospace" value="{{ old('prix_unitaire.' . $idx) }}" required>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" min="0.01" name="quantite[]" class="form-control qte-input text-center font-monospace" value="{{ old('quantite.' . $idx, 1) }}" required>
                                                            </td>
                                                            <td class="text-end">
                                                                <span class="montant-cell fw-bold font-monospace text-primary fs-6">0 FCFA</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-outline-danger btn-sm removeRowBtn">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr class="element-row">
                                                        <td class="text-center fw-bold text-muted row-index">1</td>
                                                        <td>
                                                            <select name="produits_id[]" class="form-select base-taxable-select" required>
                                                                <option value="">Sélectionner un produit...</option>
                                                                @foreach ($produits as $prod)
                                                                    <option value="{{ $prod->id }}">
                                                                        {{ $prod->label_fiscal }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" name="prix_unitaire[]" class="form-control prix-input text-end font-monospace" placeholder="0" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0.01" name="quantite[]" class="form-control qte-input text-center font-monospace" value="1" required>
                                                        </td>
                                                        <td class="text-end">
                                                            <span class="montant-cell fw-bold font-monospace text-primary fs-6">0 FCFA</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-outline-danger btn-sm removeRowBtn">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Bloc 3 : Mode de Règlement Légal & Conditions d'Encaissement (Différenciation Facturation vs Règlement) --}}
                            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 10px;">
                                <div class="card-header bg-white border-bottom py-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-success"><i class="fas fa-money-bill-wave me-2"></i> 3. Mode de Paiement Légal & Condition de Règlement</h6>
                                            <small class="text-muted">La facture porte le moyen convenu selon les règles DGI. Le client peut payer comptant, verser un acompte, ou ne rien payer (vente à terme / à crédit).</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-3">
                                        {{-- 1. Mode de paiement convenu pour la facture (exigé par la DGI) --}}
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold">
                                                Mode de Paiement Convenu <span class="text-danger">*</span>
                                                <small class="text-muted fw-normal">(Mention légale DGI sur facture)</small>
                                            </label>
                                            <select name="modepaie_id" id="modepaie_id" class="form-select fw-semibold" required onchange="onModePaiePrincipalChange()">
                                                <option value="">-- Sélectionner le mode de paiement --</option>
                                                @foreach ($modepaies as $mode)
                                                    <option value="{{ $mode->id }}" data-code="{{ $mode->code }}" {{ old('modepaie_id') == $mode->id ? 'selected' : '' }}>
                                                        {{ $mode->code }} - {{ $mode->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- 2. Condition de règlement immédiat --}}
                                        <div class="col-md-7">
                                            <label class="form-label fw-bold">Condition de Règlement à l'Émission</label>
                                            <div class="btn-group btn-group-sm w-100 shadow-sm" role="group">
                                                <input type="radio" class="btn-check" name="condition_reglement" id="cond_comptant" value="COMPTANT" checked onchange="onConditionReglementChange('COMPTANT')">
                                                <label class="btn btn-outline-success fw-bold" for="cond_comptant">
                                                    <i class="fas fa-check-circle me-1"></i> Au Comptant (100% réglé)
                                                </label>

                                                <input type="radio" class="btn-check" name="condition_reglement" id="cond_credit" value="CREDIT" onchange="onConditionReglementChange('CREDIT')">
                                                <label class="btn btn-outline-secondary fw-bold" for="cond_credit">
                                                    <i class="fas fa-clock me-1"></i> À Crédit / À Terme (0 F réglé)
                                                </label>

                                                <input type="radio" class="btn-check" name="condition_reglement" id="cond_acompte" value="ACOMPTE" onchange="onConditionReglementChange('ACOMPTE')">
                                                <label class="btn btn-outline-primary fw-bold" for="cond_acompte">
                                                    <i class="fas fa-hand-holding-usd me-1"></i> Acompte / Partiel
                                                </label>

                                                <input type="radio" class="btn-check" name="condition_reglement" id="cond_multi" value="MULTI" onchange="onConditionReglementChange('MULTI')">
                                                <label class="btn btn-outline-dark fw-bold" for="cond_multi">
                                                    <i class="fas fa-layer-group me-1"></i> Multi-Moyens
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Information explicative si Vente à Crédit --}}
                                    <div id="boxNoticeCredit" class="alert alert-info py-2 px-3 small mb-3 shadow-sm" style="display: none;">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Vente à crédit :</strong> Le client ne verse aucun montant à l'émission (0 FCFA). La facture sera certifiée avec un reste à payer égal à 100% du total TTC. Aucun reçu d'encaissement immédiat ne sera créé. Le mode convenu à l'échéance reste enregistré.
                                    </div>

                                    {{-- Tableau des versements / encaissements --}}
                                    <div id="boxVentilationPaiements" class="table-responsive border rounded bg-white">
                                        <table class="table table-sm align-middle mb-0" id="paiementsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 250px;">Moyen d'Encaissement</th>
                                                    <th style="width: 200px;">Montant Réglé Immédiat (FCFA)</th>
                                                    <th>Référence / N° Chèque / Transaction</th>
                                                    <th style="width: 60px;" class="text-center">
                                                        <button type="button" class="btn btn-outline-success btn-sm p-1" id="btnAddPaiementRow" onclick="ajouterLignePaiement()" title="Ajouter un autre moyen" style="display: none;">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="paiementsContainer">
                                                <tr class="paiement-row">
                                                    <td>
                                                        <select name="reglement_mode[]" class="form-select form-select-sm reglement-mode-select">
                                                            <option value="ESP">Espèces (Caisse)</option>
                                                            <option value="VIR">Virement Bancaire</option>
                                                            <option value="CHQ">Chèque Bancaire</option>
                                                            <option value="MOM">Mobile Money (Orange/Moov)</option>
                                                            <option value="CAR">Carte Bancaire</option>
                                                            <option value="AUT">Autre Moyen</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="1" min="0" name="reglement_montant[]" class="form-control form-control-sm font-monospace fw-bold reglement-montant-input" placeholder="0" oninput="calculerSyntheseLive()">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="reglement_reference[]" class="form-control form-control-sm" placeholder="Réf transaction, N° Chèque, Opérateur...">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-outline-danger btn-sm p-1 btn-del-paiement" onclick="supprimerLignePaiement(this)" style="display: none;"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- Bloc 4 : Mentions Spécifiques & Commentaires DGI (Lignes 1 à 9 libres) --}}
                            <div class="accordion mb-4 shadow-sm" id="accordionComments" style="border-radius: 10px; overflow:hidden;">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="headingComments">
                                        <button class="accordion-button collapsed bg-white fw-bold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseComments">
                                            <i class="fas fa-comment-dots me-2"></i> 4. Mentions Spécifiques & 9 Lignes de Commentaires DGI (Libres &bull; Minimum 64 caractères)
                                        </button>
                                    </h2>
                                    <div id="collapseComments" class="accordion-collapse collapse" data-bs-parent="#accordionComments">
                                        <div class="accordion-body bg-light">
                                            <p class="text-muted small mb-3">
                                                Saisie libre conforme à la norme SECeF DGI BF ST 1.0. Vous pouvez renseigner jusqu'à 9 lignes de mentions libres (Chauffeur, Référence Marché, Bon de Commande, Chantier, Remerciements, etc.). Chaque ligne accepte au moins 100 caractères.
                                            </p>
                                            <div class="row g-2">
                                                @for ($k = 1; $k <= 9; $k++)
                                                    <div class="col-md-4 col-sm-6">
                                                        <label class="form-label small text-muted mb-1 fw-semibold">Ligne {{ $k }}</label>
                                                        <input type="text" maxlength="150" name="comment_{{ $k }}" class="form-control form-control-sm font-monospace"
                                                            placeholder="Ligne {{ $k }} : Mention libre..." value="{{ old('comment_' . $k) }}">
                                                    </div>
                                                @endfor
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label small text-muted mb-1 fw-semibold">Mention Légale / Conditions Particulières</label>
                                                <input type="text" maxlength="255" name="mention_legale" class="form-control form-control-sm"
                                                    placeholder="Ex: Marché N°... / Vente soumise aux conditions générales..." value="{{ old('mention_legale') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bloc 5 : Synthèse Fiscale en Direct & Validation des Totaux --}}
                            <div class="live-synthese-card p-4 mb-4">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-7">
                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                            <i class="fas fa-calculator me-2 text-primary"></i> Synthèse de Taxation & Décomposition Fiscale (FCFA)
                                        </h6>
                                        <div class="live-synthese-row">
                                            <span class="text-muted">Total Lignes Brut HT :</span>
                                            <strong class="font-monospace" id="liveBrutHt">0 FCFA</strong>
                                        </div>
                                        <div class="live-synthese-row">
                                            <span class="text-muted">Remise Commerciale Cumulée :</span>
                                            <strong class="font-monospace text-danger" id="liveRemise">0 FCFA</strong>
                                        </div>
                                        <div class="live-synthese-row">
                                            <span class="text-muted">Base Imposable Nette HT :</span>
                                            <strong class="font-monospace" id="liveNetHt">0 FCFA</strong>
                                        </div>
                                        <div class="live-synthese-row">
                                            <span class="text-muted">Taxes Spécifiques (TS Fixe / %) :</span>
                                            <strong class="font-monospace text-info" id="liveTs">0 FCFA</strong>
                                        </div>
                                        <div class="live-synthese-row">
                                            <span class="text-muted">Montant TVA (Ventilation groupes légaux) :</span>
                                            <strong class="font-monospace" id="liveTva">0 FCFA</strong>
                                        </div>
                                        <div class="live-synthese-row">
                                            <span class="text-muted">
                                                Acompte PSVB :
                                                <small class="text-success fw-normal">(Biens physiques uniquement &bull; Prestations exonérées)</small>
                                            </span>
                                            <strong class="font-monospace text-warning-emphasis" id="livePsvb">0 FCFA</strong>
                                        </div>
                                    </div>

                                    <div class="col-lg-5">
                                        <div class="live-synthese-total text-center mb-3 shadow-sm">
                                            <div class="small text-white-50 text-uppercase fw-semibold mb-1">Net à Payer TTC (DGI SECeF)</div>
                                            <div class="font-monospace fs-2 fw-bolder" id="liveNetAPayer">0 FCFA</div>
                                        </div>

                                        <div class="bg-white p-3 rounded border">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted small">Total Réglé Immédiat :</span>
                                                <strong class="text-success font-monospace fs-6" id="liveTotalPaye">0 FCFA</strong>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Reste à Recouvrer :</span>
                                                <strong class="text-danger font-monospace fs-6" id="liveResteAPayer">0 FCFA</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Alerte si boîtier MCF déconnecté --}}
                            @if (!$sfeConnected)
                                <div class="alert alert-warning py-2 px-3 small mb-4 shadow-sm d-flex align-items-center gap-2">
                                    <i class="fas fa-exclamation-triangle fs-5 text-warning"></i>
                                    <div>
                                        <strong>Boîtier MCF déconnecté ou non détecté.</strong>
                                        La fiscalisation directe avec signature cryptographique DGI est désactivée. Vous pouvez enregistrer votre facture en <strong>Brouillon</strong> pour préserver vos données et rétablir la connexion MCF plus tard pour la certifier.
                                    </div>
                                </div>
                            @endif

                            {{-- Barre d'Action Finale Sticky --}}
                            <div class="d-flex flex-wrap justify-content-between align-items-center bg-white p-3 rounded shadow-sm border gap-2">
                                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Fermer sans Enregistrer
                                </button>

                                <div class="d-flex gap-2">
                                    {{-- 1. Enregistrer en Brouillon (Toujours disponible) --}}
                                    <button type="submit" name="action_type" value="brouillon" class="btn btn-outline-secondary px-4 fw-bold shadow-sm" id="btnSubmitBrouillon">
                                        <i class="fas fa-save me-1"></i> Enregistrer en Brouillon
                                    </button>

                                    {{-- 2. Enregistrer & Fiscaliser Directement via MCF (Grisé si MCF non connecté) --}}
                                    <button type="submit" name="action_type" value="certifier"
                                        class="btn btn-success fw-bold px-4 shadow-sm" id="btnSubmitCertifier"
                                        {{ $sfeConnected ? '' : 'disabled' }}
                                        style="{{ $sfeConnected ? '' : 'opacity: 0.55; cursor: not-allowed;' }}"
                                        title="{{ $sfeConnected ? 'Signer et sécuriser immédiatement par le boîtier MCF' : 'Boîtier MCF déconnecté : certification directe indisponible' }}">
                                        <i class="fas fa-bolt me-1"></i> Enregistrer & Fiscaliser (MCF)
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================================================
             MODALE 2 : CRÉATION FACTURE PROFORMA / DEVIS AVEC P.U. AUTOMATIQUE
             ==================================================================== --}}
        <div class="modal fade" id="createProformaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl">
                <form action="{{ route('module_facture_proforma.store') }}" method="POST" class="modal-content border-0 shadow-lg">
                    @csrf
                    <div class="modal-header text-white" style="background: #1d3f7f;">
                        <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i> Nouveau Devis / Facture Proforma</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Client Assujetti / Prospect <span class="text-danger">*</span></label>
                                <select name="contribuables_id" class="form-select select2-input" required style="width:100%">
                                    <option value="">-- Sélectionner le client --</option>
                                    @foreach ($contribuables as $contribuable)
                                        <option value="{{ $contribuable->id }}">{{ $contribuable->assujeti }} {{ $contribuable->ifu ? '(IFU: ' . $contribuable->ifu . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Date d'Émission</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Référence Devis</label>
                                <input type="text" name="reference" class="form-control" placeholder="Ex: DEV-2026-001">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Remise Commerciale (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="remise_1" id="proforma_remise_1" class="form-control font-monospace" value="0" placeholder="0" oninput="recalcProformaTotal()">
                                    <span class="input-group-text bg-white fw-bold">%</span>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <label class="form-label fw-semibold">Objet du Devis / Prestation / Projet</label>
                                <input type="text" name="objet" class="form-control" placeholder="Ex: Fourniture de matériel informatique ou réalisation de prestations">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Validité de l'Offre</label>
                                <input type="text" class="form-control bg-white" value="30 jours" readonly>
                            </div>
                        </div>

                        {{-- Lignes Proforma dynamiques --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-boxes me-2 text-primary"></i> Lignes d'Articles & Prestations du Devis</span>
                                <button type="button" class="btn btn-sm text-white fw-semibold" style="background:#1d3f7f;" id="addProformaRowBtn">
                                    <i class="fas fa-plus me-1"></i> Ajouter ligne
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Article / Prestation &bull; Classification & Groupe TVA</th>
                                            <th style="width: 160px;" class="text-end">P.U. HT (FCFA)</th>
                                            <th style="width: 120px;" class="text-center">Quantité</th>
                                            <th style="width: 180px;" class="text-end">Total Ligne HT</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="proformaElementsContainer">
                                        <tr class="proforma-row">
                                            <td>
                                                <select name="produits_id[]" class="form-select form-select-sm proforma-product-select" onchange="onProformaProductChange(this)" required>
                                                    <option value="">-- Sélectionner un article ou prestation --</option>
                                                    @foreach ($produits as $prod)
                                                        <option value="{{ $prod->id }}" data-price="{{ $prod->prix_unitaire }}">{{ $prod->label_fiscal }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.01" min="0" name="prix_unitaire[]" class="form-control form-control-sm text-end font-monospace proforma-price-input" placeholder="0" oninput="recalcProformaRow(this)" required></td>
                                            <td><input type="number" step="0.01" min="0.01" name="quantite[]" class="form-control form-control-sm text-center font-monospace proforma-qty-input" value="1" oninput="recalcProformaRow(this)" required></td>
                                            <td class="text-end font-monospace fw-bold text-primary proforma-line-total">0 FCFA</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="$(this).closest('tr').remove(); recalcProformaTotal();"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center bg-white p-3 rounded border">
                            <span class="text-muted me-3">Total Net Devis Brut HT :</span>
                            <span class="fs-5 fw-bold font-monospace text-primary" id="proformaTotalDisplay">0 FCFA</span>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn text-white fw-bold px-4" style="background: #1d3f7f;">
                            <i class="fas fa-save me-1"></i> Enregistrer le Devis
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ====================================================================
             MODALE 3 : ÉMISSION D'UN AVOIR FISCAL DIRECT (AVEC SÉLECTION DES LIGNES)
             ==================================================================== --}}
        <div class="modal fade" id="quickAvoirModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl">
                <form action="{{ route('module_facture_avoir.store') }}" method="POST" id="quickAvoirForm" class="modal-content border-0 shadow-lg">
                    @csrf
                    <input type="hidden" name="facture_id" id="avoir_facture_id">
                    <input type="hidden" name="contribuables_id" id="avoir_client_id">

                    <div class="modal-header text-white" style="background: #b45309;">
                        <h5 class="modal-title fw-bold"><i class="fas fa-undo me-2"></i> Émettre un Avoir Fiscal DGI (Norme SECeF)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        {{-- 1. Rappel synthétique de la Facture d'Origine --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid #b45309 !important; background: #fff;">
                            <div class="card-body py-2.5 px-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-2">
                                        <span class="text-muted d-block small" style="font-size: 11px;">Facture d'origine :</span>
                                        <span class="fw-bold font-monospace text-dark fs-6" id="avoir_origin_code">—</span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted d-block small" style="font-size: 11px;">Client destinataire :</span>
                                        <span class="fw-semibold text-dark" id="avoir_origin_client">—</span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted d-block small" style="font-size: 11px;">Total Facturé TTC :</span>
                                        <span class="fw-bold font-monospace text-primary fs-6" id="avoir_origin_total">—</span>
                                    </div>
                                    <div class="col-md-2">
                                        <span class="text-muted d-block small" style="font-size: 11px;">Total Déjà Réglé :</span>
                                        <span class="fw-bold font-monospace text-info fs-6" id="avoir_origin_paye">—</span>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="text-muted d-block small" style="font-size: 11px;">Reste Dû :</span>
                                                <span class="fw-bold font-monospace text-danger fs-6" id="avoir_origin_reste">—</span>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-muted d-block small" style="font-size: 11px;">Solde Avoir :</span>
                                                <span class="fw-bold font-monospace text-success fs-6" id="avoir_origin_solde">—</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Paramètres de l'Avoir (Nature & Motif Unifiés) --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Type & Motif de l'Avoir (Nature DGI) <span class="text-danger">*</span></label>
                                <select name="nature_avoir" id="avoir_nature" class="form-select fw-semibold" onchange="onAvoirNatureChange()" required>
                                    <option value="RAN" selected>RAN — Annulation totale de la vente (Reprise intégrale 100%)</option>
                                    <option value="RAM">RAM — Retour de marchandises (Réintègre le stock des biens)</option>
                                    <option value="COR">COR — Correction d'erreur matérielle sur facture</option>
                                    <option value="RRR">RRR — Rabais, Remise ou Ristourne accordé (Avoir financier)</option>
                                </select>
                                <input type="hidden" name="motif" id="avoir_motif" value="annulation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de l'Avoir <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-warning py-2 px-3 small mb-2 d-flex align-items-center" id="avoir_ran_blocked_alert" style="display: none;">
                                    <i class="fas fa-exclamation-triangle me-2 text-warning fs-5"></i>
                                    <div><strong>Annulation Totale (RAN) verrouillée :</strong> Cette facture a déjà fait l'objet d'un avoir partiel. L'annulation intégrale à 100% n'est plus autorisée. Veuillez utiliser un Retour (RAM), une Remise (RRR) ou une Correction (COR) sur les articles restants.</div>
                                </div>
                                <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-center" id="avoir_nature_notice">
                                    <i class="fas fa-ban me-2 text-danger fs-5"></i>
                                    <div><strong>Annulation Totale (RAN) :</strong> Toutes les quantités sont verrouillées à 100% de la facture d'origine. Les biens seront réintégrés au stock.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Contrôles Avancés RRR (Rabais, Remise, Ristourne) --}}
                        <div id="quickAvoirRrrControls" class="card border-info bg-light-subtle mb-3 shadow-sm" style="display: none;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="card-title text-info fw-bold mb-0">
                                        <i class="fas fa-tags me-1"></i> Paramétrage de la Remise Financière (RRR)
                                    </h6>
                                    <span class="badge bg-info-subtle text-info border border-info">Aucun mouvement de stock · PSVB = 0</span>
                                </div>
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Nature de la réduction</label>
                                        <select id="quick_rrr_type" class="form-select form-select-sm">
                                            <option value="Remise commerciale">Remise commerciale</option>
                                            <option value="Rabais">Rabais (défaut de conformité)</option>
                                            <option value="Ristourne">Ristourne (volume périodique)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold">Unité de calcul</label>
                                        <select id="quick_rrr_unite" class="form-select form-select-sm" onchange="onQuickRrrUnitChange()">
                                            <option value="pourcentage">Pourcentage (%)</option>
                                            <option value="montant">Montant fixe (FCFA)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold" id="quick_rrr_val_label">Valeur (%)</label>
                                        <input type="number" step="any" min="0" id="quick_rrr_val" class="form-control form-control-sm font-monospace fw-bold" placeholder="Ex: 10">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-sm btn-info text-white w-100 fw-bold" onclick="appliquerRemiseGlobaleAvoir()">
                                            <i class="fas fa-magic me-1"></i> Appliquer à la facture
                                        </button>
                                    </div>
                                </div>
                                <div class="form-text mt-1 text-muted" style="font-size: 11px;">
                                    <i class="fas fa-shield-alt text-success me-1"></i> Règle fiscale FEC/DGI : Pas de taxe PSVB, taxes spécifiques forfaitaires exclues, recalcul proportionnel de la TVA.
                                </div>
                            </div>
                        </div>

                        {{-- Tableau des Lignes de la Facture d'Origine à Rétrograder --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white py-2 fw-bold text-dark d-flex justify-content-between align-items-center">
                                <span>Articles & Prestations de la Facture d'Origine</span>
                                <small class="text-muted">Ventilation fiscale par article</small>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0" id="avoirItemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Article / Prestation</th>
                                                <th class="text-center" style="width: 100px;">Qté Init.</th>
                                                <th class="text-center" style="width: 140px;">Qté en Avoir</th>
                                                <th class="text-end" style="width: 140px;">P.U. HT</th>
                                                <th class="text-end" style="width: 170px;">Total Ligne TTC</th>
                                            </tr>
                                        </thead>
                                        <tbody id="avoirItemsContainer">
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="fas fa-spinner fa-spin me-2"></i> Chargement des lignes de la facture...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Synthèse Fiscale Complète de l'Avoir (Base HT, Taxes, PSVB, Net à Payer) --}}
                        <div class="card bg-light border p-3 rounded shadow-sm mb-3">
                            <div class="row g-2 text-center text-md-end align-items-center">
                                <div class="col-md-3 col-6 border-end">
                                    <span class="text-muted d-block small" style="font-size: 11px;">Base HT Total :</span>
                                    <span class="fw-bold font-monospace text-dark fs-6" id="avoirTotalHt">0 F</span>
                                </div>
                                <div class="col-md-3 col-6 border-end">
                                    <span class="text-muted d-block small" style="font-size: 11px;">Taxes (TVA + TS) :</span>
                                    <span class="fw-bold font-monospace text-dark fs-6" id="avoirTotalTaxes">0 F</span>
                                </div>
                                <div class="col-md-3 col-6 border-end">
                                    <span class="text-muted d-block small" style="font-size: 11px;">BIC (PSVB) :</span>
                                    <span class="fw-bold font-monospace text-dark fs-6" id="avoirTotalBic">0 F</span>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="text-muted d-block small fw-bold" style="font-size: 11px; color: #dc2626;">TOTAL AVOIR NET :</span>
                                    <span class="fs-5 fw-bold font-monospace text-danger" id="avoirTotalDisplay">0 F</span>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Impact Financier & Gestion de Trésorerie (Décaissement) --}}
                        <div class="card border-0 shadow-sm mt-3" id="quickAvoirTreasuryCard" style="border-radius: 8px;">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-coins text-warning fs-5"></i>
                                    <h6 class="mb-0 fw-bold text-dark">Impact Trésorerie & Règlement</h6>
                                </div>
                                <div id="quickAvoirTreasuryNotice" class="alert alert-light border small mb-0">
                                    Chargement de l'impact financier...
                                </div>
                                <div id="quickAvoirRefundSection" class="mt-3 pt-3 border-top" style="display: none;">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="effectuer_remboursement" id="quick_effectuer_remboursement" value="1" onchange="toggleQuickAvoirRefund(this.checked)">
                                        <label class="form-check-label fw-bold text-danger" for="quick_effectuer_remboursement">
                                            <i class="fas fa-hand-holding-usd me-1"></i> Rembourser immédiatement le client (Décaissement Caisse / Banque)
                                        </label>
                                    </div>
                                    <div id="quickAvoirRefundFields" style="display: none;" class="row g-2 mt-2 p-2 bg-light rounded border">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Mode de Décaissement</label>
                                            <select name="remboursement_mode" id="quick_remboursement_mode" class="form-select form-select-sm">
                                                <option value="Espèces" selected>Espèces (Caisse)</option>
                                                <option value="Chèque">Chèque</option>
                                                <option value="Virement Bancaire">Virement Bancaire</option>
                                                <option value="Mobile Money">Mobile Money</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Montant Décaissé (FCFA)</label>
                                            <input type="number" step="1" min="1" name="remboursement_montant" id="quick_remboursement_montant" class="form-control form-control-sm font-monospace fw-bold text-danger">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Réf. Transaction / Reçu</label>
                                            <input type="text" name="remboursement_ref" id="quick_remboursement_ref" class="form-control form-control-sm" placeholder="Ex: Décaissement Caisse...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <div class="d-flex align-items-center gap-2">
                            <div class="alert alert-danger py-1 px-2 small mb-0 d-flex align-items-center" id="avoir_solde_depasse_alert" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-1 text-danger"></i>
                                <span id="avoir_solde_depasse_text">Dépassement du solde éligible.</span>
                            </div>
                            <button type="submit" id="btnSubmitQuickAvoir" name="action_type" value="certifier" class="btn btn-warning fw-bold text-dark px-4 shadow-sm">
                                <i class="fas fa-check-circle me-1"></i> Valider l'Avoir
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ====================================================================
             MODALE 4 : ENCAISSER / RÈGLEMENT FINANCIER RAPIDE
             ==================================================================== --}}
        <div class="modal fade" id="quickReglementModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-md">
                <form action="{{ route('module_reglement.store') }}" method="POST" class="modal-content border-0 shadow-lg">
                    @csrf
                    <input type="hidden" name="facture_id" id="reglement_facture_id">

                    <div class="modal-header text-white" style="background: #15803d;">
                        <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave me-2"></i> Encaisser un Règlement Client</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Facture & Client</label>
                            <input type="text" id="reglement_facture_display" class="form-control fw-bold" readonly>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small text-muted">Montant Total TTC</label>
                                <input type="text" id="reglement_total_ttc" class="form-control font-monospace" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted">Solde Restant à Payer</label>
                                <input type="text" id="reglement_reste_display" class="form-control font-monospace text-danger fw-bold" readonly>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Montant du Paiement (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="1" name="montant" id="reglement_montant" class="form-control form-control-lg fw-bold text-success font-monospace" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mode de Paiement <span class="text-danger">*</span></label>
                                <select name="mode" class="form-select" required>
                                    <option value="Espèces">Espèces (Caisse)</option>
                                    <option value="Virement Bancaire">Virement Bancaire</option>
                                    <option value="Chèque">Chèque</option>
                                    <option value="Mobile Money (Orange/Moov)">Mobile Money</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Encaissement</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Référence Transaction / N° Chèque</label>
                                <input type="text" name="reference_paiement" class="form-control" placeholder="Ex: CHQ-89765 ou Ref Trans. Orange Money">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="fas fa-check-circle me-1"></i> Enregistrer le Paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ====================================================================
             MODALE 4 BIS : HISTORIQUE DES RÈGLEMENTS & REÇUS DE LA FACTURE
             ==================================================================== --}}
        <div class="modal fade" id="reglementsHistoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-2xl" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header text-white d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 14px 20px;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 32px; height: 32px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3);">
                                <i class="fas fa-receipt text-info"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0" id="historyModalTitle" style="font-size: 15px;">
                                    Historique des Règlements
                                </h5>
                                <small class="text-white-50" id="historyModalSubtitle">Facture</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm text-white-50 hover-text-white" data-bs-dismiss="modal" aria-label="Fermer" style="width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <i class="fas fa-times fs-6 text-white"></i>
                        </button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        {{-- KPI Recap --}}
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border text-center shadow-sm">
                                    <span class="text-muted d-block small fw-bold" style="font-size: 10px;">TOTAL FACTURE</span>
                                    <span class="font-monospace fw-bold text-dark fs-6" id="historyTotalFacture">0 F</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border text-center shadow-sm">
                                    <span class="text-muted d-block small fw-bold" style="font-size: 10px;">TOTAL ENCAISSÉ</span>
                                    <span class="font-monospace fw-bold text-success fs-6" id="historyTotalPaye">0 F</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded-3 border text-center shadow-sm">
                                    <span class="text-muted d-block small fw-bold" style="font-size: 10px;">RESTE À PAYER</span>
                                    <span class="font-monospace fw-bold fs-6" id="historyResteAPayer">0 F</span>
                                </div>
                            </div>
                        </div>

                        {{-- Table des règlements --}}
                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Réf. Règlement</th>
                                            <th>Date</th>
                                            <th>Mode</th>
                                            <th>Réf. Paiement</th>
                                            <th class="text-end">Montant</th>
                                            <th class="text-center" style="width: 140px;">Reçu Officiel</th>
                                        </tr>
                                    </thead>
                                    <tbody id="historyReglementsTableBody">
                                        {{-- Rempli par JavaScript --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
                        <div id="historyNewPaymentBtnContainer"></div>
                        <button type="button" class="btn btn-secondary btn-sm px-3 fw-semibold" data-bs-dismiss="modal">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================================================
             MODALE 5 : PRÉVISUALISATION HAUTE-FIDÉLITÉ DGI (A4 / TICKET 80MM)
             ==================================================================== --}}
        <div class="modal fade" id="previewFactureModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 960px;">
                <div class="modal-content shadow-2xl border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header preview-header-bar text-white d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3 overflow-hidden me-3" style="min-width: 0;">
                            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0" style="width: 36px; height: 36px; background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3);">
                                <i class="fas fa-file-invoice fs-6 text-info"></i>
                            </div>
                            <div class="d-flex align-items-center gap-2 overflow-hidden">
                                <h5 class="modal-title mb-0 fw-bold text-white text-nowrap" id="previewModalTitle" style="font-size: 15px; letter-spacing: -0.2px;">
                                    Prévisualisation de la Facture
                                </h5>
                                <span id="previewModalBadge" class="badge rounded-pill fw-semibold font-monospace" style="font-size: 11px; padding: 4px 10px;"></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            {{-- Switcher Format A4 vs Ticket 80mm --}}
                            <div class="preview-format-switcher p-1 rounded-3 d-inline-flex align-items-center" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12);">
                                <button type="button" class="btn btn-sm format-tab-btn active" id="btnPreviewA4" onclick="switchPreviewFormat('a4')">
                                    <i class="fas fa-file-alt me-1.5 opacity-75"></i> Format A4 DGI
                                </button>
                                <button type="button" class="btn btn-sm format-tab-btn" id="btnPreviewTicket" onclick="switchPreviewFormat('ticket')">
                                    <i class="fas fa-receipt me-1.5 opacity-75"></i> Ticket 80mm
                                </button>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-1.5 shadow-sm d-inline-flex align-items-center gap-1.5 ms-1" onclick="imprimerPreview()" style="font-size: 12px; border-radius: 6px;">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                            <button type="button" class="btn btn-sm text-white-50 hover-text-white ms-1" data-bs-dismiss="modal" aria-label="Fermer" title="Fermer" style="width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1);">
                                <i class="fas fa-times fs-6 text-white"></i>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body p-0" style="background-color: #f1f5f9; height: 75vh;">
                        <div class="d-flex justify-content-center align-items-center h-100 p-2">
                            <iframe id="previewIframe" src="about:blank" style="width: 100%; height: 100%; border: none; border-radius: 6px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.08);"></iframe>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="fas fa-shield-alt text-primary me-1"></i> Prévisualisation conforme aux spécifications DGI / SECeF</small>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm px-3 fw-semibold" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Fermer
                            </button>
                            <button type="button" class="btn btn-primary btn-sm px-3 fw-bold" onclick="imprimerPreview()">
                                <i class="fas fa-print me-1"></i> Imprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modales d'ajout rapide --}}
        @include('partials.quick_create_modals')

    </main>
@endsection

@section('script')
    <script src="{{ asset('js/invoice-lines.js') }}"></script>
    <script>
        const productsCatalog = @json($produitsData);

        // Variables globales pour le moteur de prévisualisation
        let currentPreviewId = null;
        let currentPreviewType = 'facture';
        let currentPreviewFormat = 'a4';

        let hubBillingDataTable = null;
        let currentTabFilter = 'tous';

        function initHubBillingDataTable() {
            if ($.fn.DataTable && $('#hubBillingTable').length && !$.fn.DataTable.isDataTable('#hubBillingTable')) {
                // Enregistrement du filtre personnalisé par onglets
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'hubBillingTable') return true;
                    if (currentTabFilter === 'tous') return true;
                    const node = hubBillingDataTable ? hubBillingDataTable.row(dataIndex).node() : null;
                    if (!node) return true;
                    const rowType = $(node).attr('data-type');
                    const rowStatus = $(node).attr('data-status');
                    if (currentTabFilter === 'ERREUR') return rowStatus === 'ERREUR';
                    if (currentTabFilter === 'IMPAYE') return rowStatus === 'IMPAYE';
                    return rowType === currentTabFilter;
                });

                hubBillingDataTable = $('#hubBillingTable').DataTable({
                    order: [[1, 'desc']], // Tri par défaut : Date la plus récente
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
                    columnDefs: [
                        { targets: [6], orderable: false, searchable: false }, // Colonne Actions non triable
                        { targets: [3], type: 'num' } // Colonne Total triée numériquement
                    ],
                    language: {
                        processing: "Traitement en cours...",
                        search: "Recherche :",
                        lengthMenu: "Afficher _MENU_ pièces",
                        info: "Affichage de _START_ à _END_ sur _TOTAL_ pièces",
                        infoEmpty: "Affichage de 0 à 0 sur 0 pièce",
                        infoFiltered: "(filtré sur _MAX_ pièces)",
                        loadingRecords: "Chargement...",
                        zeroRecords: "Aucune pièce trouvée",
                        emptyTable: "Aucune pièce disponible",
                        paginate: {
                            first: "Premier",
                            previous: "Précédent",
                            next: "Suivant",
                            last: "Dernier"
                        }
                    },
                    dom: "<'row p-3 align-items-center'<'col-sm-6'l><'col-sm-6 text-end'<'d-none'f>>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row p-3 align-items-center border-top'<'col-sm-6 text-muted small'i><'col-sm-6'p>>"
                });
            }
        }

        // 1. Filtrer le tableau sans recharger la page
        function filtrerTableau(type) {
            $('.hub-tab-btn').removeClass('active');
            if (window.event && window.event.target) {
                $(window.event.target).closest('.hub-tab-btn').addClass('active');
            }
            currentTabFilter = type;
            if (hubBillingDataTable) {
                hubBillingDataTable.draw();
            } else {
                $('.hub-row').each(function() {
                    const rowType = $(this).attr('data-type');
                    const rowStatus = $(this).attr('data-status');
                    if (type === 'tous') $(this).show();
                    else if (type === 'ERREUR') $(this).toggle(rowStatus === 'ERREUR');
                    else if (type === 'IMPAYE') $(this).toggle(rowStatus === 'IMPAYE');
                    else $(this).toggle(rowType === type);
                });
            }
        }

        // 2. Recherche en temps réel dans le tableau
        function rechercherDansTableau() {
            const val = document.getElementById('hubSearchInput').value;
            if (hubBillingDataTable) {
                hubBillingDataTable.search(val).draw();
            } else {
                const filter = val.toLowerCase();
                $('.hub-row').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(filter) > -1);
                });
            }
        }

        // 3. Ouvrir la modale de prévisualisation haute-fidélité DGI
        function openPreview(id, code, isCertifiee, type) {
            currentPreviewId = id;
            currentPreviewType = type || 'facture';
            currentPreviewFormat = 'a4';

            let titlePrefix = 'Facture de Vente';
            if (currentPreviewType === 'avoir') titlePrefix = 'Facture d\'Avoir';
            if (currentPreviewType === 'proforma') titlePrefix = 'Facture Proforma';

            $('#previewModalTitle').text(titlePrefix + ' ' + code);

            if (isCertifiee) {
                $('#previewModalBadge')
                    .html('<i class="fas fa-circle me-1" style="font-size: 6px; vertical-align: middle;"></i> Certifiée SFE / DGI')
                    .attr('style', 'font-size: 11px; padding: 4px 10px; background: rgba(16, 185, 129, 0.15) !important; color: #34d399 !important; border: 1px solid rgba(16, 185, 129, 0.3) !important;');
            } else {
                $('#previewModalBadge')
                    .html('<i class="fas fa-circle me-1" style="font-size: 6px; vertical-align: middle;"></i> Brouillon')
                    .attr('style', 'font-size: 11px; padding: 4px 10px; background: rgba(245, 158, 11, 0.15) !important; color: #fbbf24 !important; border: 1px solid rgba(245, 158, 11, 0.3) !important;');
            }

            if (currentPreviewType === 'proforma') {
                $('#btnPreviewTicket').hide();
            } else {
                $('#btnPreviewTicket').show();
            }

            switchPreviewFormat('a4');
            const modal = new bootstrap.Modal(document.getElementById('previewFactureModal'));
            modal.show();
        }

        // 4. Basculer entre format A4 et Ticket de caisse 80mm
        function switchPreviewFormat(format) {
            currentPreviewFormat = format;
            if (format === 'a4') {
                $('#btnPreviewA4').addClass('active');
                $('#btnPreviewTicket').removeClass('active');
                let url = '/print_ordre_facture/' + currentPreviewId + '?iframe=1';
                if (currentPreviewType === 'avoir') url = '/print_facture_avoir/' + currentPreviewId + '?iframe=1';
                if (currentPreviewType === 'proforma') url = '/print_facture_proforma/' + currentPreviewId + '?iframe=1';
                document.getElementById('previewIframe').src = url;
            } else {
                $('#btnPreviewTicket').addClass('active');
                $('#btnPreviewA4').removeClass('active');
                let url = '/print_ticket_facture/' + currentPreviewId + '?iframe=1';
                document.getElementById('previewIframe').src = url;
            }
        }

        // 5. Lancer l'impression directe sans quitter la modale
        function imprimerPreview() {
            const iframe = document.getElementById('previewIframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();

                // Enregistrer immédiatement l'impression pour afficher la mention DUPLICATA dès les prochaines impressions
                if (currentPreviewId) {
                    $.post('/module_ordre_facture/' + currentPreviewId + '/marquer-imprimee', {
                        _token: '{{ csrf_token() }}'
                    }).done(function() {
                        setTimeout(function() {
                            switchPreviewFormat(currentPreviewFormat);
                        }, 800);
                    });
                }
            }
        }

        // 6. Ouvrir la modale d'avoir pré-remplie avec chargement dynamique et calcul fiscal complet
        let quickAvoirFacturePaye = 0;
        let quickAvoirFactureReste = 0;
        let quickAvoirFactureTotal = 0;
        let quickAvoirSoldeDispo = 0;
        let currentInvoiceData = null;

        function openAvoirModal(factureId, code, clientId, clientName, totalTtc, soldeAvoirDispo, montantPaye, resteAPayer) {
            quickAvoirFactureTotal = parseFloat(totalTtc) || 0;
            quickAvoirSoldeDispo = parseFloat(soldeAvoirDispo) || 0;
            quickAvoirFacturePaye = parseFloat(montantPaye) || 0;
            quickAvoirFactureReste = parseFloat(resteAPayer) || 0;

            $('#avoir_facture_id').val(factureId);
            $('#avoir_facture_display').val(code + ' (' + clientName + ') - ' + Number(totalTtc).toLocaleString('fr-FR') + ' F TTC');
            $('#avoir_client_id').val(clientId);

            $('#avoir_origin_code').text(code);
            $('#avoir_origin_client').text(clientName || 'Client');
            $('#avoir_origin_total').text(Number(totalTtc).toLocaleString('fr-FR') + ' F');
            $('#avoir_origin_paye').text(Number(quickAvoirFacturePaye).toLocaleString('fr-FR') + ' F');
            $('#avoir_origin_reste').text(Number(quickAvoirFactureReste).toLocaleString('fr-FR') + ' F');
            $('#avoir_origin_solde').text(Number(quickAvoirSoldeDispo).toLocaleString('fr-FR') + ' F');

            $('#quick_effectuer_remboursement').prop('checked', false);
            $('#quickAvoirRefundFields').hide();

            $('#avoir_ran_blocked_alert').hide();
            $('#avoir_solde_depasse_alert').hide();
            $('#btnSubmitQuickAvoir').prop('disabled', false);

            $('#avoir_nature').val('RAN');
            $('#quick_rrr_val').val('');
            $('#quickAvoirRrrControls').hide();

            $('#avoirItemsContainer').html(`
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin me-2"></i> Chargement des articles de la facture...
                    </td>
                </tr>
            `);

            const modal = new bootstrap.Modal(document.getElementById('quickAvoirModal'));
            modal.show();

            // Fetch invoice lines with complete fiscal breakdown from JSON endpoint
            fetch('/module_ordre_facture/' + factureId + '/json')
                .then(res => res.json())
                .then(data => {
                    currentInvoiceData = data;
                    renderAvoirRows();
                    onAvoirNatureChange();
                    recalcAvoirTotal();
                })
                .catch(err => {
                    $('#avoirItemsContainer').html('<tr><td colspan="5" class="text-center text-danger py-3">Erreur lors du chargement des lignes.</td></tr>');
                });
        }

        function renderAvoirRows() {
            let html = '';
            if (currentInvoiceData && currentInvoiceData.lignes && currentInvoiceData.lignes.length > 0) {
                currentInvoiceData.lignes.forEach((el, idx) => {
                    const qteInit = el.quantite_initiale !== undefined ? el.quantite_initiale : el.quantite;
                    const qteDeja = el.quantite_deja_avoir || 0;
                    const qteRest = el.quantite_restante !== undefined ? el.quantite_restante : el.quantite;
                    const pu = el.prix_unitaire;
                    const tot = (el.ttc_total !== undefined) ? el.ttc_total : (el.montant_ht + (el.tva_total || 0) + (el.ts_total || 0));
                    const tvaInfo = el.taux_tva > 0 ? `TVA ${el.taux_tva}%` : `Exonéré (Gr. ${el.groupe_tva})`;
                    const tsInfo = el.ts_total > 0 ? ` · TS: ${Number(el.ts_total).toLocaleString('fr-FR')} F` : '';

                    html += `
                        <tr class="avoir-item-row" data-idx="${idx}" data-rest="${qteRest}">
                            <td>
                                <input type="hidden" name="produits_id[]" value="${el.produit_id || ''}">
                                <div class="fw-bold text-dark">${el.designation}</div>
                                <small class="text-muted" style="font-size: 11px;">
                                    ${tvaInfo}${tsInfo}
                                    ${el.is_service ? ' · (Prestation)' : ''}
                                    ${qteDeja > 0 ? ` · <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Déjà retourné: ${qteDeja}</span>` : ''}
                                </small>
                            </td>
                            <td class="text-center font-monospace">${qteInit}</td>
                            <td class="text-center">
                                ${qteRest <= 0 ? `
                                    <span class="badge bg-secondary">Épuisé (0)</span>
                                    <input type="hidden" name="quantite[]" class="avoir-qte-input" value="0" max="0">
                                ` : `
                                    <input type="number" step="0.01" min="0" max="${qteRest}" name="quantite[]"
                                        class="form-control form-control-sm font-monospace avoir-qte-input text-center fw-bold"
                                        value="${qteRest}" oninput="recalcAvoirRow(this)">
                                    <small class="text-muted d-block" style="font-size: 10px;">Max restant: ${qteRest}</small>
                                `}
                            </td>
                            <td class="text-end font-monospace">
                                <input type="hidden" name="prix_unitaire[]" value="${pu}" class="avoir-pu-input">
                                <span class="avoir-pu-display">${Number(pu).toLocaleString('fr-FR')} F</span>
                            </td>
                            <td class="text-end font-monospace fw-bold text-danger avoir-line-total">
                                ${Number(tot).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} F
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="5" class="text-center text-muted py-3">Aucune ligne trouvée sur cette facture.</td></tr>';
            }
            $('#avoirItemsContainer').html(html);
        }

        function onAvoirNatureChange() {
            let nature = $('#avoir_nature').val();

            // Règle critique anti-fraude & cohérence : si la facture a déjà subi un avoir partiel, interdire RAN
            if (currentInvoiceData && currentInvoiceData.can_ran === false) {
                $('#avoir_nature option[value="RAN"]').prop('disabled', true);
                $('#avoir_ran_blocked_alert').show();
                if (nature === 'RAN') {
                    $('#avoir_nature').val('RAM');
                    nature = 'RAM';
                }
            } else {
                $('#avoir_nature option[value="RAN"]').prop('disabled', false);
                $('#avoir_ran_blocked_alert').hide();
            }

            const motifMap = {
                'RAN': 'annulation',
                'COR': 'erreur',
                'RAM': 'retour',
                'RRR': 'remise'
            };
            $('#avoir_motif').val(motifMap[nature] || 'annulation');

            const notices = {
                'RAN': '<i class="fas fa-ban me-2 text-danger fs-5"></i><div><strong>Annulation Totale (RAN) :</strong> Toutes les quantités sont verrouillées à 100% de la facture d\'origine. Les articles physiques seront intégralement réintégrés au stock.</div>',
                'RAM': '<i class="fas fa-box-open me-2 text-warning fs-5"></i><div><strong>Retour de Marchandises (RAM) :</strong> Ajustez les quantités d\'articles physiques retournés dans la limite du stock restant. Les articles sélectionnés seront réintégrés au stock.</div>',
                'RRR': '<i class="fas fa-percent me-2 text-info fs-5"></i><div><strong>Avoir Financier (RRR) :</strong> Rabais ou Remise accordé après facturation. Aucun mouvement de stock physique, PSVB = 0, TS fixes exclues.</div>',
                'COR': '<i class="fas fa-tools me-2 text-primary fs-5"></i><div><strong>Correction d\'Erreur (COR) :</strong> Rectification matérielle des lignes de la facture certifiée sur le reliquat restant.</div>'
            };
            $('#avoir_nature_notice').html(notices[nature] || notices['RAN']);

            if (nature === 'RRR') {
                $('#quickAvoirRrrControls').slideDown();
            } else {
                $('#quickAvoirRrrControls').slideUp();
                // Reset unit prices to original when leaving RRR
                if (currentInvoiceData && currentInvoiceData.lignes) {
                    $('.avoir-item-row').each(function() {
                        const idx = $(this).data('idx');
                        const el = currentInvoiceData.lignes[idx];
                        if (el) {
                            $(this).find('.avoir-pu-input').val(el.prix_unitaire);
                            $(this).find('.avoir-pu-display').text(Number(el.prix_unitaire).toLocaleString('fr-FR') + ' F');
                        }
                    });
                }
            }

            if (nature === 'RAN') {
                $('.avoir-item-row').each(function() {
                    const maxQte = $(this).find('.avoir-qte-input').attr('max');
                    $(this).find('.avoir-qte-input').val(maxQte).prop('readonly', true).addClass('bg-light');
                });
            } else if (nature === 'RRR') {
                $('.avoir-item-row .avoir-qte-input').prop('readonly', true).addClass('bg-light');
            } else {
                $('.avoir-item-row .avoir-qte-input').prop('readonly', false).removeClass('bg-light');
            }

            recalcAvoirTotal();
        }

        function onQuickRrrUnitChange() {
            const u = $('#quick_rrr_unite').val();
            if (u === 'pourcentage') {
                $('#quick_rrr_val_label').text('Valeur (%)');
                $('#quick_rrr_val').attr('placeholder', 'Ex: 10').attr('max', 100);
            } else {
                $('#quick_rrr_val_label').text('Valeur (FCFA)');
                $('#quick_rrr_val').attr('placeholder', 'Ex: 5000').removeAttr('max');
            }
        }

        function appliquerRemiseGlobaleAvoir() {
            if (!currentInvoiceData || !currentInvoiceData.lignes || currentInvoiceData.lignes.length === 0) {
                alert("Aucun article disponible sur la facture originale.");
                return;
            }
            const unit = $('#quick_rrr_unite').val();
            const val = parseFloat($('#quick_rrr_val').val()) || 0;
            if (val <= 0) {
                alert("Veuillez saisir une valeur supérieure à 0.");
                return;
            }

            let pct = 0;
            // Base HT résiduelle sur les quantités encore disponibles
            const totalOrigHt = currentInvoiceData.lignes.reduce((acc, l) => {
                const qRest = l.quantite_restante !== undefined ? l.quantite_restante : l.quantite;
                return acc + (qRest * l.prix_unitaire);
            }, 0);

            if (unit === 'pourcentage') {
                pct = Math.min(100, val);
            } else {
                pct = totalOrigHt > 0 ? Math.min(100, (val / totalOrigHt) * 100) : 0;
            }

            $('.avoir-item-row').each(function() {
                const idx = $(this).data('idx');
                const origLigne = currentInvoiceData.lignes[idx];
                const origPu = origLigne.prix_unitaire;
                const rabaisPu = Math.round((origPu * (pct / 100)) * 100) / 100;
                const qRest = origLigne.quantite_restante !== undefined ? origLigne.quantite_restante : origLigne.quantite;

                $(this).find('.avoir-pu-input').val(rabaisPu);
                $(this).find('.avoir-pu-display').text(Number(rabaisPu).toLocaleString('fr-FR') + ' F');
                $(this).find('.avoir-qte-input').val(qRest).prop('readonly', true).addClass('bg-light');
            });

            recalcAvoirTotal();
        }

        function computeQuickAvoirLine(rowEl) {
            const $row = $(rowEl);
            const idx = $row.data('idx');
            const el = currentInvoiceData && currentInvoiceData.lignes ? currentInvoiceData.lignes[idx] : null;
            if (!el) return 0;

            const nature = $('#avoir_nature').val();
            const isRrr = (nature === 'RRR');
            const qte = parseFloat($row.find('.avoir-qte-input').val()) || 0;
            const pu = parseFloat($row.find('.avoir-pu-input').val()) || 0;
            const montantHt = qte * pu;

            // TS : sur RRR, les TS forfaitaires par unité ne s'appliquent pas. Seules les TS proportionnelles en % s'appliquent.
            let tsUnit = 0;
            if (el.type_ts === 'POURCENTAGE' && el.pourcentage_ts > 0) {
                tsUnit = Math.round((pu * (el.pourcentage_ts / 100)) * 100) / 100;
            } else if (!isRrr && el.montant_ts > 0) {
                tsUnit = el.montant_ts;
            }
            const tsTotal = Math.round((tsUnit * qte) * 100) / 100;

            // TVA
            const baseTva = montantHt + tsTotal;
            const tvaTotal = Math.round((baseTva * (el.taux_tva / 100)) * 100) / 100;
            const ttcTotal = baseTva + tvaTotal;

            // BIC (PSVB) : strictement 0 sur les avoirs financiers RRR, sinon sur les biens uniquement
            const tauxBic = (currentInvoiceData && currentInvoiceData.taux_bic) ? currentInvoiceData.taux_bic : 0;
            const bicTotal = (el.is_bien && tauxBic > 0 && !isRrr)
                ? Math.round((ttcTotal * (tauxBic / 100)) * 100) / 100
                : 0;

            // La ligne affiche STRICTEMENT le Total TTC (sans BIC)
            const totalLigneTtc = ttcTotal;

            $row.data('line-ht', montantHt);
            $row.data('line-ts', tsTotal);
            $row.data('line-tva', tvaTotal);
            $row.data('line-bic', bicTotal);
            $row.data('line-total', totalLigneTtc);

            $row.find('.avoir-line-total').text(Number(totalLigneTtc).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' F');
            return totalLigneTtc;
        }

        function recalcAvoirRow(input) {
            computeQuickAvoirLine($(input).closest('tr')[0]);
            recalcAvoirTotal();
        }

        function recalcAvoirTotal() {
            let sumHt = 0;
            let sumTaxes = 0;
            let sumBic = 0;
            let sumTotal = 0;

            const nature = $('#avoir_nature').val();

            $('.avoir-item-row').each(function() {
                computeQuickAvoirLine(this);
                sumHt += (parseFloat($(this).data('line-ht')) || 0);
                sumTaxes += ((parseFloat($(this).data('line-ts')) || 0) + (parseFloat($(this).data('line-tva')) || 0));
                sumBic += (parseFloat($(this).data('line-bic')) || 0);
            });

            sumTotal = sumHt + sumTaxes + sumBic;

            // En annulation totale RAN : égalité stricte au centime avec la facture originale
            if (nature === 'RAN' && currentInvoiceData && currentInvoiceData.montant_total) {
                sumTotal = currentInvoiceData.montant_total;
            }

            $('#avoirTotalHt').text(Number(sumHt).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' F');
            $('#avoirTotalTaxes').text(Number(sumTaxes).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' F');
            $('#avoirTotalBic').text(Number(sumBic).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' F');
            $('#avoirTotalDisplay').text(Number(sumTotal).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' F');

            // Plafonnement strict sur le solde disponible pour avoir
            const soldeDispo = (currentInvoiceData && currentInvoiceData.solde_avoir_disponible !== undefined)
                ? currentInvoiceData.solde_avoir_disponible
                : quickAvoirSoldeDispo;

            if (sumTotal > (soldeDispo + 0.009)) {
                $('#avoir_solde_depasse_text').text('Dépassement du solde éligible (' + Number(soldeDispo).toLocaleString('fr-FR') + ' F). Réduisez les quantités.');
                $('#avoir_solde_depasse_alert').show();
                $('#btnSubmitQuickAvoir').prop('disabled', true);
            } else if (sumTotal <= 0.009) {
                $('#avoir_solde_depasse_alert').hide();
                $('#btnSubmitQuickAvoir').prop('disabled', true);
            } else {
                $('#avoir_solde_depasse_alert').hide();
                $('#btnSubmitQuickAvoir').prop('disabled', false);
            }

            // Analyse Trésorerie en Temps Réel
            const totalAvoir = Math.round(sumTotal);
            const paye = quickAvoirFacturePaye;
            const reste = quickAvoirFactureReste;

            if (paye <= 0.009) {
                // Facture 100% impayée
                $('#quickAvoirTreasuryNotice').html(`
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-info-circle me-2 fs-5 text-secondary"></i>
                        <div><strong>Créance non encaissée :</strong> La facture d'origine n'avait fait l'objet d'aucun règlement (0 FCFA). Cet avoir annule ou diminue directement la créance due par le client. <strong>Aucun mouvement de caisse ni décaissement requis.</strong></div>
                    </div>
                `).removeClass('alert-warning alert-info alert-primary').addClass('alert-light');
                $('#quickAvoirRefundSection').hide();
                $('#quick_effectuer_remboursement').prop('checked', false);
                $('#quickAvoirRefundFields').hide();
            } else if (totalAvoir <= (reste + 0.009)) {
                // Avoir partiel couvert par le reste à payer
                const nouvReste = Math.max(0, reste - totalAvoir);
                $('#quickAvoirTreasuryNotice').html(`
                    <div class="d-flex align-items-center text-info">
                        <i class="fas fa-info-circle me-2 fs-5"></i>
                        <div><strong>Imputation sur créance :</strong> L'avoir (${Number(totalAvoir).toLocaleString('fr-FR')} F) est inférieur au reste dû (${Number(reste).toLocaleString('fr-FR')} F). Le reste à payer du client sera désormais de <strong>${Number(nouvReste).toLocaleString('fr-FR')} FCFA</strong>. Aucun remboursement immédiat nécessaire.</div>
                    </div>
                `).removeClass('alert-warning alert-light alert-primary').addClass('alert-info');
                $('#quickAvoirRefundSection').hide();
                $('#quick_effectuer_remboursement').prop('checked', false);
                $('#quickAvoirRefundFields').hide();
            } else {
                // Solde en faveur du client suite à l'avoir
                const soldeClient = Math.min(paye, Math.max(0, totalAvoir - reste));
                $('#quickAvoirTreasuryNotice').html(`
                    <div class="d-flex align-items-center text-primary-emphasis">
                        <i class="fas fa-wallet me-2 fs-5 text-primary"></i>
                        <div>
                            <strong>Solde en faveur du client : ${Number(soldeClient).toLocaleString('fr-FR')} FCFA.</strong><br>
                            La facture d'origine ayant déjà été réglée (${Number(paye).toLocaleString('fr-FR')} F), cet avoir dégage un crédit de <strong>${Number(soldeClient).toLocaleString('fr-FR')} FCFA</strong> en faveur du client.<br>
                            <span class="text-muted" style="font-size: 11px;">
                                <i class="fas fa-info-circle me-1"></i> Par défaut, ce montant est conservé en compte client pour ses futurs achats. Vous pouvez activer l'option ci-dessous pour effectuer un remboursement immédiat.
                            </span>
                        </div>
                    </div>
                `).removeClass('alert-light alert-info alert-warning').addClass('alert-primary');
                $('#quickAvoirRefundSection').slideDown();
                // Décoché par défaut : l'avoir est conservé en compte client sauf demande expresse
                $('#quick_effectuer_remboursement').prop('checked', false);
                $('#quickAvoirRefundFields').hide();
                $('#quick_remboursement_montant').val(Math.round(soldeClient)).attr('max', Math.round(soldeClient));
                $('#quick_remboursement_ref').val('Remboursement suite à Avoir ' + $('#avoir_origin_code').text());
            }
        }

        function toggleQuickAvoirRefund(checked) {
            if (checked) {
                $('#quickAvoirRefundFields').slideDown();
            } else {
                $('#quickAvoirRefundFields').slideUp();
            }
        }

        // 7. Ouvrir la modale de règlement pré-remplie
        function openReglementModal(factureId, code, clientName, totalTtc, montantPaye, resteAPayer) {
            $('#reglement_facture_id').val(factureId);
            $('#reglement_facture_display').val(code + ' - ' + clientName);
            $('#reglement_total_ttc').val(Number(totalTtc).toLocaleString('fr-FR') + ' F');
            $('#reglement_reste_display').val(Number(resteAPayer).toLocaleString('fr-FR') + ' F');
            $('#reglement_montant').val(resteAPayer).attr('max', resteAPayer);
            const modal = new bootstrap.Modal(document.getElementById('quickReglementModal'));
            modal.show();
        }

        // 7 BIS. Ouvrir l'historique complet des règlements d'une facture & télécharger les reçus
        function openReglementsHistory(factureId, code, clientName, totalTtc, paye, reste, reglements) {
            $('#historyModalTitle').html('<i class="fas fa-receipt me-2 text-info"></i> Règlements de la Facture ' + code);
            $('#historyModalSubtitle').text('Client : ' + (clientName || 'Client Comptoir'));
            $('#historyTotalFacture').text(Number(totalTtc).toLocaleString('fr-FR') + ' F');
            $('#historyTotalPaye').text(Number(paye).toLocaleString('fr-FR') + ' F');

            if (reste <= 0.009) {
                $('#historyResteAPayer').text('0 F (Soldée)').attr('class', 'font-monospace fw-bold text-success fs-6');
            } else {
                $('#historyResteAPayer').text(Number(reste).toLocaleString('fr-FR') + ' F').attr('class', 'font-monospace fw-bold text-danger fs-6');
            }

            let tbody = '';
            if (!reglements || reglements.length === 0) {
                tbody = '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-info-circle me-1"></i> Aucun règlement enregistré pour cette facture.</td></tr>';
            } else {
                reglements.forEach(function(r) {
                    const dateStr = r.date ? new Date(r.date).toLocaleDateString('fr-FR') : '—';
                    const mntFormatted = Number(r.montant).toLocaleString('fr-FR') + ' F';
                    const printUrl = '/print_reglement/' + r.id;
                    tbody += `
                        <tr>
                            <td>
                                <span class="badge bg-light text-primary border font-monospace fw-bold">${r.code || 'REG-N/A'}</span>
                            </td>
                            <td>${dateStr}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark border">${r.mode || 'Espèces'}</span>
                            </td>
                            <td class="text-muted small">${r.reference_paiement || '—'}</td>
                            <td class="text-end font-monospace fw-bold text-success">${mntFormatted}</td>
                            <td class="text-center">
                                <a href="${printUrl}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5 fw-semibold" style="font-size: 11px;">
                                    <i class="fas fa-print me-1"></i> Reçu
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }
            $('#historyReglementsTableBody').html(tbody);

            // Si la facture n'est pas soldée, proposer un bouton d'encaissement direct
            if (reste > 0.009) {
                const safeClient = (clientName || '').replace(/'/g, "\\'");
                $('#historyNewPaymentBtnContainer').html(`
                    <button type="button" class="btn btn-success btn-sm fw-bold px-3" onclick="bootstrap.Modal.getInstance(document.getElementById('reglementsHistoryModal')).hide(); openReglementModal(${factureId}, '${code}', '${safeClient}', ${totalTtc}, ${paye}, ${reste});">
                        <i class="fas fa-plus-circle me-1"></i> Encaisser un nouveau règlement
                    </button>
                `);
            } else {
                $('#historyNewPaymentBtnContainer').html('<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i> Facture entièrement soldée</span>');
            }

            const modal = new bootstrap.Modal(document.getElementById('reglementsHistoryModal'));
            modal.show();
        }

        // 8. Conversion Proforma -> Facture de Vente dans le Formulaire Pré-rempli
        // Variables globales
        let currentModePrix = 'HT';
        window.currentModePrix = 'HT';
        let currentConditionReglement = 'COMPTANT';
        let currentNetAPayer = 0;

        // Basculer entre Saisie HT et Saisie TTC avec conversion dynamique et déterministe (sans dérive)
        function changerModePrix(mode) {
            if (currentModePrix === mode) return;
            currentModePrix = mode;
            window.currentModePrix = mode;

            if (mode === 'TTC') {
                $('#colPriceHeader').text('P.U. TTC (FCFA)');
                $('#colTotalLineHeader').text('Total Ligne TTC');
            } else {
                $('#colPriceHeader').text('P.U. HT (FCFA)');
                $('#colTotalLineHeader').text('Total Ligne HT');
            }

            // Basculer chaque ligne de manière déterministe
            $('#elementsContainer .element-row').each(function() {
                const prodId = $(this).find('.base-taxable-select').val();
                const $puInput = $(this).find('.prix-input');
                if (!prodId) return;

                const prodInfo = productsCatalog[String(prodId)] || {};
                const tauxTva = parseFloat(prodInfo.taux_tva) || 0;
                let tsUnit = 0;
                if (prodInfo.type_ts === 'MONTANT') tsUnit = parseFloat(prodInfo.montant_ts) || 0;
                else if (prodInfo.type_ts === 'POURCENTAGE') {
                    const base = parseFloat(prodInfo.prix_unitaire) || 0;
                    tsUnit = base * ((parseFloat(prodInfo.pourcentage_ts) || 0) / 100);
                }

                let puHt = $(this).data('pu-ht');
                if (puHt === undefined || isNaN(puHt) || puHt <= 0) {
                    puHt = parseFloat(prodInfo.prix_unitaire) || parseFloat($puInput.val()) || 0;
                    $(this).data('pu-ht', puHt);
                }

                let puTtc = $(this).data('pu-ttc');
                if (puTtc === undefined || isNaN(puTtc) || puTtc <= 0) {
                    puTtc = prodInfo.prix_unitaire_ttc !== undefined
                        ? parseFloat(prodInfo.prix_unitaire_ttc)
                        : Math.round(((puHt + tsUnit) * (1 + tauxTva / 100)) * 100) / 100;
                    $(this).data('pu-ttc', puTtc);
                }

                if (mode === 'TTC') {
                    $puInput.val(puTtc);
                } else {
                    $puInput.val(puHt);
                }
            });

            calculerSyntheseLive();
        }

        // Basculer entre Remise en Pourcentage et Remise en Montant Fixe
        function changerTypeRemise(type) {
            if (type === 'MONTANT') {
                $('#remiseUnitLabel').text('FCFA');
                $('#facture_remise_valeur').attr('placeholder', 'Ex: 5000');
            } else {
                $('#remiseUnitLabel').text('%');
                $('#facture_remise_valeur').attr('placeholder', 'Ex: 10');
            }
            calculerSyntheseLive();
        }

        // Gestion de la condition de règlement légal (Facturation vs Règlement)
        function onConditionReglementChange(cond) {
            currentConditionReglement = cond;
            const $pContainer = $('#paiementsContainer');

            if (cond === 'COMPTANT') {
                $('#boxNoticeCredit').slideUp();
                $('#boxVentilationPaiements').slideDown();
                $('#btnAddPaiementRow').hide();
                $('.btn-del-paiement').hide();
                // Conserver 1 ligne de paiement égale à 100% du net à payer
                $pContainer.find('tr.paiement-row').slice(1).remove();
                $pContainer.find('tr.paiement-row:first .reglement-montant-input')
                    .val(currentNetAPayer)
                    .prop('readonly', false);
            } else if (cond === 'CREDIT') {
                $('#boxNoticeCredit').slideDown();
                $('#boxVentilationPaiements').slideUp();
                $('#btnAddPaiementRow').hide();
                $('.btn-del-paiement').hide();
                // 0 FCFA versé à l'émission
                $pContainer.find('tr.paiement-row').slice(1).remove();
                $pContainer.find('tr.paiement-row:first .reglement-montant-input').val(0);
            } else if (cond === 'ACOMPTE') {
                $('#boxNoticeCredit').slideUp();
                $('#boxVentilationPaiements').slideDown();
                $('#btnAddPaiementRow').hide();
                $('.btn-del-paiement').hide();
                $pContainer.find('tr.paiement-row').slice(1).remove();
                const $firstInput = $pContainer.find('tr.paiement-row:first .reglement-montant-input');
                $firstInput.prop('readonly', false);
                if (parseFloat($firstInput.val()) >= currentNetAPayer) {
                    $firstInput.val(Math.round(currentNetAPayer / 2));
                }
            } else if (cond === 'MULTI') {
                $('#boxNoticeCredit').slideUp();
                $('#boxVentilationPaiements').slideDown();
                $('#btnAddPaiementRow').show();
                $('.btn-del-paiement').show();
                $pContainer.find('tr.paiement-row .reglement-montant-input').prop('readonly', false);
            }
            calculerSyntheseLive();
        }

        // Synchroniser le premier moyen de paiement avec le mode convenu légal
        function onModePaiePrincipalChange() {
            const selectedCode = $('#modepaie_id option:selected').data('code');
            if (selectedCode) {
                const $firstSelect = $('#paiementsContainer tr.paiement-row:first .reglement-mode-select');
                if ($firstSelect.find('option[value="' + selectedCode + '"]').length) {
                    $firstSelect.val(selectedCode);
                }
            }
        }

        // 8. Conversion Proforma -> Facture de Vente dans le Formulaire Pré-rempli
        function convertirProformaViaForm(proformaId) {
            fetch('/module_facture_proforma/' + proformaId + '/json')
                .then(res => res.json())
                .then(data => {
                    resetFactureForm();
                    $('#facture_proforma_id').val(proformaId);

                    if (data.contribuable_id) {
                        $('#contribuable_id').val(data.contribuable_id).trigger('change');
                    }
                    if (data.reference) {
                        $('#facture_reference').val('Devis ' + data.reference);
                    }
                    if (data.remise_1) {
                        $('#facture_remise_1').val(data.remise_1);
                    }

                    // S'assurer qu'un mode légal est bien sélectionné
                    if (!$('#modepaie_id').val()) {
                        $('#modepaie_id').val($('#modepaie_id option:first').val());
                    }

                    // Vider les lignes existantes
                    $('#elementsContainer').empty();

                    if (data.lignes && data.lignes.length > 0) {
                        data.lignes.forEach((item, idx) => {
                            const pu = parseFloat(item.prix_unitaire) || 0;
                            const qte = parseFloat(item.quantite) || 1;
                            const tot = Math.round(pu * qte);
                            const rowHtml = `
                                <tr class="element-row">
                                    <td class="text-center fw-bold text-muted row-index">${idx + 1}</td>
                                    <td>
                                        <select name="produits_id[]" class="form-select base-taxable-select" required>
                                            ${InvoiceLines.buildOptionsHtml(item.produit_id)}
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="prix_unitaire[]" class="form-control prix-input text-end font-monospace" value="${pu}" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="quantite[]" class="form-control qte-input text-center font-monospace" value="${qte}" required>
                                    </td>
                                    <td class="text-end">
                                        <span class="montant-cell fw-bold font-monospace text-primary fs-6">${tot.toLocaleString('fr-FR')} FCFA</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm removeRowBtn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                            $('#elementsContainer').append(rowHtml);
                        });
                    }

                    // Ouvrir la modale
                    const modal = new bootstrap.Modal(document.getElementById('createFactureModal'));
                    modal.show();

                    InvoiceLines._bind();
                    InvoiceLines._initExistingSelects();
                    calculerSyntheseLive();
                })
                .catch(err => {
                    alert('Erreur lors du chargement de la proforma : ' + err.message);
                });
        }

        // 9. Reset complet du formulaire de facturation
        function resetFactureForm() {
            const form = document.getElementById('mainFactureForm');
            if (form) form.reset();
            $('#facture_proforma_id').val('');
            $('#facture_date').val(new Date().toISOString().slice(0, 10));
            $('#cond_comptant').prop('checked', true);
            currentConditionReglement = 'COMPTANT';
            $('#modePrixHt').prop('checked', true);
            currentModePrix = 'HT';
            $('#colPriceHeader').text('P.U. HT (FCFA)');
            $('#boxNoticeCredit').hide();
            $('#boxVentilationPaiements').show();
            $('#btnAddPaiementRow').hide();

            $('#remiseTypePct').prop('checked', true);
            $('#remiseUnitLabel').text('%');
            $('#facture_remise_valeur').val(0);
            $('#facture_remise_1').val(0);
            $('#modepaie_id').val('');
            $('#contribuable_id').val('');
            $('#typefacture_id').val('');

            $('#elementsContainer').html(`
                <tr class="element-row">
                    <td class="text-center fw-bold text-muted row-index">1</td>
                    <td>
                        <select name="produits_id[]" class="form-select base-taxable-select" required>
                            ${InvoiceLines.buildOptionsHtml()}
                        </select>
                    </td>
                    <td><input type="number" step="0.01" min="0" name="prix_unitaire[]" class="form-control prix-input text-end font-monospace" placeholder="0" required></td>
                    <td><input type="number" step="0.01" min="0.01" name="quantite[]" class="form-control qte-input text-center font-monospace" value="1" required></td>
                    <td class="text-end"><span class="montant-cell fw-bold font-monospace text-primary fs-6">0 FCFA</span></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm removeRowBtn"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
            `);

            $('#paiementsContainer').html(`
                <tr class="paiement-row">
                    <td>
                        <select name="reglement_mode[]" class="form-select form-select-sm reglement-mode-select">
                            <option value="ESP" selected>Espèces (Caisse)</option>
                            <option value="VIR">Virement Bancaire</option>
                            <option value="CHQ">Chèque Bancaire</option>
                            <option value="MOM">Mobile Money (Orange/Moov)</option>
                            <option value="CAR">Carte Bancaire</option>
                            <option value="AUT">Autre Moyen</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="1" min="0" name="reglement_montant[]" class="form-control form-control-sm font-monospace fw-bold reglement-montant-input" placeholder="0" oninput="calculerSyntheseLive()">
                    </td>
                    <td>
                        <input type="text" name="reglement_reference[]" class="form-control form-control-sm" placeholder="Réf transaction, N° Chèque...">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm p-1 btn-del-paiement" onclick="supprimerLignePaiement(this)" style="display: none;"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `);
            InvoiceLines._bind();
            InvoiceLines._initExistingSelects();
            calculerSyntheseLive();
            validerChampsObligatoires();
        }

        // 10. Multi-règlements dynamiques
        function ajouterLignePaiement() {
            const rowHtml = `
                <tr class="paiement-row">
                    <td>
                        <select name="reglement_mode[]" class="form-select form-select-sm reglement-mode-select">
                            <option value="ESP">Espèces (Caisse)</option>
                            <option value="VIR">Virement Bancaire</option>
                            <option value="CHQ">Chèque Bancaire</option>
                            <option value="MOM">Mobile Money (Orange/Moov)</option>
                            <option value="CAR">Carte Bancaire</option>
                            <option value="AUT">Autre Moyen</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="1" min="0" name="reglement_montant[]" class="form-control form-control-sm font-monospace fw-bold reglement-montant-input" placeholder="0" oninput="calculerSyntheseLive()">
                    </td>
                    <td>
                        <input type="text" name="reglement_reference[]" class="form-control form-control-sm" placeholder="Réf transaction, N° Chèque...">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm p-1 btn-del-paiement" onclick="supprimerLignePaiement(this)"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#paiementsContainer').append(rowHtml);
            calculerSyntheseLive();
        }

        function supprimerLignePaiement(btn) {
            if ($('.paiement-row').length > 1) {
                $(btn).closest('.paiement-row').remove();
            } else {
                $(btn).closest('.paiement-row').find('.reglement-montant-input').val('');
            }
            calculerSyntheseLive();
        }

        const isMcfConnected = @json($sfeConnected);

        // 11. Validation dynamique des champs obligatoires & désactivation intelligente des boutons
        function validerChampsObligatoires() {
            const hasClient = !!$('#contribuable_id').val();
            const hasType = !!$('#typefacture_id').val();
            const hasMode = !!$('#modepaie_id').val();

            let hasValidLine = false;
            $('.element-row').each(function() {
                const prod = $(this).find('.base-taxable-select').val();
                const qte = parseFloat($(this).find('.qte-input').val()) || 0;
                const pu = parseFloat($(this).find('.prix-input').val());
                if (prod && qte > 0 && !isNaN(pu) && pu >= 0) {
                    hasValidLine = true;
                }
            });

            const formValid = hasClient && hasType && hasMode && hasValidLine;

            // 1. Bouton Brouillon : actif dès que les champs obligatoires sont renseignés
            const $btnBrouillon = $('#btnSubmitBrouillon');
            if ($btnBrouillon.length) {
                $btnBrouillon.prop('disabled', !formValid);
                $btnBrouillon.css({
                    'opacity': formValid ? '1' : '0.55',
                    'cursor': formValid ? 'pointer' : 'not-allowed'
                });
                if (!formValid) {
                    $btnBrouillon.attr('title', 'Veuillez renseigner le client, le type de facture, le mode de paiement et au moins un article valide (Qté > 0 et P.U. ≥ 0).');
                } else {
                    $btnBrouillon.attr('title', 'Enregistrer cette facture en brouillon');
                }
            }

            // 2. Bouton Fiscaliser MCF : actif SEULEMENT si formulaire valide ET boîtier MCF connecté
            const $btnCertifier = $('#btnSubmitCertifier');
            if ($btnCertifier.length) {
                const canCertify = formValid && isMcfConnected;
                $btnCertifier.prop('disabled', !canCertify);
                $btnCertifier.css({
                    'opacity': canCertify ? '1' : '0.55',
                    'cursor': canCertify ? 'pointer' : 'not-allowed'
                });
                if (!isMcfConnected) {
                    $btnCertifier.attr('title', 'Boîtier physique MCF déconnecté ou non détecté. Seul l\'enregistrement en brouillon est disponible.');
                } else if (!formValid) {
                    $btnCertifier.attr('title', 'Veuillez renseigner les champs obligatoires (client, type, mode, article valide) pour pouvoir fiscaliser.');
                } else {
                    $btnCertifier.attr('title', 'Signer et fiscaliser immédiatement par le boîtier physique MCF');
                }
            }
        }

        // 12. CALCUL DE SYNTHÈSE FISCALE EN TEMPS RÉEL (Conforme DGI SECeF ST 1.0)
        function calculerSyntheseLive() {
            let totalBrutHt = 0;
            let totalTs = 0;
            let totalTva = 0;
            let basePsvbBiens = 0;

            const remiseType = $('input[name="remise_type"]:checked').val() || 'POURCENTAGE';
            const remiseVal = parseFloat($('#facture_remise_valeur').val()) || 0;
            const bicSelected = $('#bic_id option:selected');
            const bicTaux = parseFloat(bicSelected.data('taux')) || 0;

            const lineItems = [];
            $('.element-row').each(function() {
                const prodId = $(this).find('.base-taxable-select').val();
                const qte = parseFloat($(this).find('.qte-input').val()) || 0;
                const puSaisi = parseFloat($(this).find('.prix-input').val()) || 0;

                if (qte > 0 && puSaisi >= 0) {
                    const prodInfo = productsCatalog[String(prodId)] || {};
                    const isService = !!prodInfo.is_service;
                    const tauxTva = parseFloat(prodInfo.taux_tva) || 0;
                    const typeTs = prodInfo.type_ts || 'AUCUN';
                    const mntTs = parseFloat(prodInfo.montant_ts) || 0;
                    const pctTs = parseFloat(prodInfo.pourcentage_ts) || 0;

                    let puHt = $(this).data('pu-ht');
                    if (puHt === undefined || isNaN(puHt) || puHt <= 0) {
                        let puHorsTs = puSaisi;
                        if (typeTs === 'MONTANT') {
                            puHorsTs = Math.max(0, puSaisi - mntTs);
                        }
                        const diviseur = 1 + (tauxTva / 100) + (typeTs === 'POURCENTAGE' ? (pctTs / 100) : 0);
                        puHt = currentModePrix === 'TTC' ? (diviseur > 0 ? (puHorsTs / diviseur) : puSaisi) : puSaisi;
                        $(this).data('pu-ht', puHt);
                    }
                    const rowHt = puHt * qte;

                    totalBrutHt += rowHt;
                    const displayLineMnt = currentModePrix === 'TTC' ? (puSaisi * qte) : rowHt;
                    $(this).find('.montant-cell').text(Math.round(displayLineMnt).toLocaleString('fr-FR') + ' FCFA');

                    lineItems.push({
                        prodId: prodId,
                        qte: qte,
                        puSaisi: puSaisi,
                        rowHt: rowHt,
                        is_service: isService,
                        taux_tva: tauxTva,
                        type_ts: typeTs,
                        montant_ts: mntTs,
                        pourcentage_ts: pctTs,
                    });
                }
            });

            // Remise commerciale globale (pourcentage ou montant fixe)
            let totalRemise = 0;
            if (remiseType === 'MONTANT') {
                totalRemise = Math.min(totalBrutHt, Math.max(0, remiseVal));
            } else {
                const pct = Math.min(100, Math.max(0, remiseVal));
                totalRemise = totalBrutHt * (pct / 100);
            }
            $('#facture_remise_1').val(totalRemise.toFixed(2));
            const netHt = Math.max(0, totalBrutHt - totalRemise);
            const discountRatio = totalBrutHt > 0 ? (totalRemise / totalBrutHt) : 0;

            // Calcul TS, TVA et Base PSVB par ligne
            lineItems.forEach(item => {
                const itemNetHt = item.rowHt * (1 - discountRatio);

                // TS (Taxe Spécifique)
                let itemTs = 0;
                if (item.type_ts === 'MONTANT') {
                    itemTs = item.qte * item.montant_ts;
                } else if (item.type_ts === 'POURCENTAGE') {
                    itemTs = itemNetHt * (item.pourcentage_ts / 100);
                }
                totalTs += itemTs;

                // TVA légale (sur base HT nette + TS)
                const baseTva = itemNetHt + itemTs;
                const itemTva = baseTva * (item.taux_tva / 100);
                totalTva += itemTva;

                // Acompte PSVB : STRICTEMENT sur les biens matériels, prestations EXONÉRÉES
                if (!item.is_service) {
                    basePsvbBiens += (itemNetHt + itemTs + itemTva);
                }
            });

            const totalPsvb = basePsvbBiens * (bicTaux / 100);
            const netAPayer = Math.round(netHt + totalTs + totalTva + totalPsvb);
            currentNetAPayer = netAPayer;

            // Affichage dans la synthèse (sans décimales inutiles en FCFA)
            $('#liveBrutHt').text(Math.round(totalBrutHt).toLocaleString('fr-FR') + ' FCFA');
            $('#liveRemise').text(totalRemise > 0 ? '- ' + Math.round(totalRemise).toLocaleString('fr-FR') + ' FCFA' : '0 FCFA');
            $('#liveNetHt').text(Math.round(netHt).toLocaleString('fr-FR') + ' FCFA');
            $('#liveTs').text(Math.round(totalTs).toLocaleString('fr-FR') + ' FCFA');
            $('#liveTva').text(Math.round(totalTva).toLocaleString('fr-FR') + ' FCFA');
            $('#livePsvb').text(Math.round(totalPsvb).toLocaleString('fr-FR') + ' FCFA');
            $('#liveNetAPayer').text(Math.round(netAPayer).toLocaleString('fr-FR') + ' FCFA');

            // Ajustement des règlements selon la condition choisie
            let totalRegle = 0;
            if (currentConditionReglement === 'COMPTANT') {
                $('#paiementsContainer tr.paiement-row:first .reglement-montant-input').val(netAPayer);
                totalRegle = netAPayer;
            } else if (currentConditionReglement === 'CREDIT') {
                $('#paiementsContainer tr.paiement-row:first .reglement-montant-input').val(0);
                totalRegle = 0;
            } else {
                $('.reglement-montant-input').each(function() {
                    totalRegle += parseFloat($(this).val()) || 0;
                });
            }

            const reste = Math.max(0, netAPayer - Math.round(totalRegle));
            $('#liveTotalPaye').text(Math.round(totalRegle).toLocaleString('fr-FR') + ' FCFA');
            $('#liveResteAPayer').text(Math.round(reste).toLocaleString('fr-FR') + ' FCFA');

            validerChampsObligatoires();
        }

        // 13. Fonctions Devis Proforma
        function onProformaProductChange(select) {
            const price = parseFloat($(select).find('option:selected').data('price')) || 0;
            const row = $(select).closest('tr');
            row.find('.proforma-price-input').val(price);
            recalcProformaRow(row.find('.proforma-qty-input')[0]);
        }

        function recalcProformaRow(input) {
            const row = $(input).closest('tr');
            const qte = parseFloat(row.find('.proforma-qty-input').val()) || 0;
            const pu = parseFloat(row.find('.proforma-price-input').val()) || 0;
            const tot = Math.round(qte * pu);
            row.find('.proforma-line-total').text(tot.toLocaleString('fr-FR') + ' FCFA');
            recalcProformaTotal();
        }

        function recalcProformaTotal() {
            let totalBrut = 0;
            $('.proforma-row').each(function() {
                const qte = parseFloat($(this).find('.proforma-qty-input').val()) || 0;
                const pu = parseFloat($(this).find('.proforma-price-input').val()) || 0;
                totalBrut += (qte * pu);
            });
            const r1 = parseFloat($('#proforma_remise_1').val()) || 0;
            const netDevis = Math.round(Math.max(0, totalBrut * (1 - r1 / 100)));
            $('#proformaTotalDisplay').text(netDevis.toLocaleString('fr-FR') + ' FCFA');
        }

        // Initialisation globale
        $(document).ready(function() {
            if (typeof InvoiceLines !== 'undefined') {
                InvoiceLines.init({
                    container: '#elementsContainer',
                    rowSelector: '.element-row',
                    productSelect: '.base-taxable-select',
                    qtyInput: '.qte-input',
                    priceInput: '.prix-input',
                    montantCell: '.montant-cell',
                    grandTotal: '#grandTotal',
                    addRowBtn: '#addRowBtn',
                    removeRowBtn: '.removeRowBtn',
                    products: productsCatalog,
                    enforceStock: true,
                    modalParent: '#createFactureModal',
                });
            }

            // Écouter les changements pour recalculer la synthèse fiscale et valider les boutons
            $(document).on('input change', '.qte-input, .prix-input, .base-taxable-select, #facture_remise_valeur, input[name="remise_type"], #bic_id, #contribuable_id, #typefacture_id, #modepaie_id', function() {
                calculerSyntheseLive();
                validerChampsObligatoires();
            });

            // Gestion de la saisie manuelle de prix pour maintenir pu-ht et pu-ttc synchronisés sans dérive
            $(document).on('input', '#elementsContainer .prix-input', function() {
                const $row = $(this).closest('.element-row');
                const prodId = $row.find('.base-taxable-select').val();
                const prodInfo = productsCatalog[String(prodId)] || {};
                const tauxTva = parseFloat(prodInfo.taux_tva) || 0;
                const val = parseFloat($(this).val()) || 0;

                let tsUnit = 0;
                if (prodInfo.type_ts === 'MONTANT') tsUnit = parseFloat(prodInfo.montant_ts) || 0;
                else if (prodInfo.type_ts === 'POURCENTAGE') tsUnit = val * ((parseFloat(prodInfo.pourcentage_ts) || 0) / 100);

                if (currentModePrix === 'HT') {
                    $row.data('pu-ht', val).attr('data-pu-ht', val);
                    const ttc = Math.round(((val + tsUnit) * (1 + tauxTva / 100)) * 100) / 100;
                    $row.data('pu-ttc', ttc).attr('data-pu-ttc', ttc);
                } else {
                    $row.data('pu-ttc', val).attr('data-pu-ttc', val);
                    const div = 1 + tauxTva / 100;
                    const ht = div > 0 ? Math.round(((val - tsUnit) / div) * 100) / 100 : val;
                    $row.data('pu-ht', ht).attr('data-pu-ht', ht);
                }
            });

            // À la soumission de la facture, s'assurer que le backend reçoit toujours les P.U. HT normalisés
            $('#mainFactureForm').on('submit', function() {
                if (currentModePrix === 'TTC') {
                    $('#elementsContainer .element-row').each(function() {
                        const puHt = $(this).data('pu-ht');
                        if (puHt !== undefined && !isNaN(puHt) && puHt > 0) {
                            $(this).find('.prix-input').val(puHt);
                        }
                    });
                }
            });

            // Revalidation après ajout ou suppression de ligne
            $(document).on('click', '.removeRowBtn, #addRowBtn', function() {
                setTimeout(function() {
                    calculerSyntheseLive();
                    validerChampsObligatoires();
                }, 80);
            });

            // Gestion de l'ajout de ligne pour Proforma
            $('#addProformaRowBtn').on('click', function() {
                const rowHtml = `
                    <tr class="proforma-row">
                        <td>
                            <select name="produits_id[]" class="form-select form-select-sm proforma-product-select" onchange="onProformaProductChange(this)" required>
                                <option value="">Sélectionner un produit...</option>
                                @foreach ($produits as $prod)
                                    <option value="{{ $prod->id }}" data-price="{{ $prod->prix_unitaire }}">{{ $prod->label_fiscal }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" step="0.01" min="0" name="prix_unitaire[]" class="form-control form-control-sm text-end font-monospace proforma-price-input" placeholder="0" oninput="recalcProformaRow(this)" required></td>
                        <td><input type="number" step="0.01" min="0.01" name="quantite[]" class="form-control form-control-sm text-center font-monospace proforma-qty-input" value="1" oninput="recalcProformaRow(this)" required></td>
                        <td class="text-end font-monospace fw-bold text-primary proforma-line-total">0 FCFA</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="$(this).closest('tr').remove(); recalcProformaTotal();"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                $('#proformaElementsContainer').append(rowHtml);
            });

            @if ((isset($errors) && $errors->any()) || session('form_error'))
                new bootstrap.Modal(document.getElementById('createFactureModal')).show();
            @endif

            initHubBillingDataTable();
            if (typeof feather !== 'undefined') feather.replace();
            calculerSyntheseLive();
        });
    </script>
@endsection
