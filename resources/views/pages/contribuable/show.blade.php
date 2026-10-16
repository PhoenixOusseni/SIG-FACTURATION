@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Détails Contribuable</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .stat-card {
            border: none;
            border-radius: 12px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        .stat-card .label {
            font-size: .8rem;
            opacity: .9;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .stat-card .value {
            font-size: 1.6rem;
            font-weight: 700;
            margin-top: .25rem;
        }

        .stat-card .sub {
            font-size: .8rem;
            opacity: .9;
        }

        .bg-gradient-blue {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }

        .bg-gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        }

        .bg-gradient-amber {
            background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
        }

        .bg-gradient-red {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        }

        .bg-gradient-slate {
            background: linear-gradient(135deg, #475569 0%, #1e293b 100%);
        }

        .info-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }

        .info-label {
            font-size: .75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #111827;
        }

        .badge-statut {
            padding: .35rem .7rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
        }

        .badge-soldee {
            background: #dcfce7;
            color: #166534;
        }

        .badge-partielle {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-impayee {
            background: #fee2e2;
            color: #991b1b;
        }

        .progress-thin {
            height: 6px;
            border-radius: 999px;
        }

        .avatar-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: #fff;
            font-weight: 700;
            font-size: 1.5rem;
        }

        /* ---- PRINT ---- */
        @media print {
            body {
                font-size: 12px !important;
            }

            .no-print,
            nav,
            header,
            footer,
            x-page-header,
            [data-no-print] {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                break-inside: avoid;
            }

            .stat-card {
                color: #000 !important;
                border: 1px solid #ccc !important;
                background: #f9f9f9 !important;
            }

            .print-header {
                display: block !important;
            }

            a[href]:after {
                content: none !important;
            }

            .container-xl {
                padding: 0 !important;
            }
        }

        .print-header {
            display: none;
        }
    </style>
@endsection

@section('content')
    <main>
        <x-page-header icon="user" title="Fiche client"></x-page-header>

        <div class="container-xl px-4 mt-n10">

            {{-- En-tête visible uniquement à l'impression --}}
            <div class="print-header text-center mb-4">
                <h4 class="fw-bold">FICHE CLIENT</h4>
                <div class="text-muted small">Imprimé le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</div>
            </div>

            {{-- Bandeau identité --}}
            <div class="card mb-4 info-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="avatar-circle no-print">
                            {{ strtoupper(substr($contribuable->assujeti ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{ $contribuable->assujeti ?? '—' }}</h4>
                            <div class="text-muted">
                                <i class="fa fa-tag"></i>
                                {{ optional($contribuable->Categorie)->libelle ?? 'Sans catégorie' }}
                                @if ($contribuable->ifu)
                                    &nbsp;·&nbsp; <strong>IFU :</strong> {{ $contribuable->ifu }}
                                @endif
                                @if ($contribuable->rccm)
                                    &nbsp;·&nbsp; <strong>RCCM :</strong> {{ $contribuable->rccm }}
                                @endif
                            </div>
                        </div>
                        <div class="no-print">
                            <a href="{{ route('print_fiche_contribuable', $contribuable->id) }}" target="_blank"
                                class="btn btn-sm btn-success" title="Imprimer la fiche">
                                <i class="fa fa-print"></i>
                            </a>
                            {{-- Delete button --}}
                            <form action="{{ route('module_contribuable.destroy', $contribuable->id) }}" method="POST"
                                class="d-inline-block"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contribuable ? Cette action est irréversible.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            <a href="{{ route('module_contribuable.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistiques --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-gradient-slate p-3">
                        <div class="label">Factures</div>
                        <div class="value">{{ $stats['nb_factures'] }}</div>
                        <div class="sub">
                            {{ $stats['nb_soldees'] }} soldée(s) · {{ $stats['nb_partielles'] }} partielle(s) ·
                            {{ $stats['nb_impayees'] }} impayée(s)
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-gradient-blue p-3">
                        <div class="label">Montant facturé</div>
                        <div class="value">{{ number_format($stats['montant_total'], 0, ',', ' ') }}</div>
                        <div class="sub">FCFA (TTC + PSVBB)</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-gradient-green p-3">
                        <div class="label">Total soldé</div>
                        <div class="value">{{ number_format($stats['montant_paye'], 0, ',', ' ') }}</div>
                        <div class="sub">FCFA encaissés</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div
                        class="stat-card {{ $stats['reste_a_payer'] > 0.009 ? 'bg-gradient-red' : 'bg-gradient-green' }} p-3">
                        <div class="label">Reste à solder</div>
                        <div class="value">{{ number_format($stats['reste_a_payer'], 0, ',', ' ') }}</div>
                        <div class="sub">FCFA</div>
                    </div>
                </div>
            </div>

            {{-- Progression de paiement globale --}}
            @if ($stats['montant_total'] > 0)
                @php
                    $pct = min(100, round(($stats['montant_paye'] * 100) / $stats['montant_total'], 1));
                @endphp
                <div class="card mb-4 info-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Taux de recouvrement</strong>
                            <span class="text-muted">{{ $pct }} %</span>
                        </div>
                        <div class="progress progress-thin">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                {{-- Informations contribuable --}}
                <div class="col-lg-4">
                    <div class="card mb-4 info-card">
                        <div class="card-header">Informations</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="info-label">Téléphone</div>
                                <div class="info-value">{{ $contribuable->telephone ?? '—' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Type client DGI</div>
                                <div class="info-value"><span class="badge bg-primary">{{ $contribuable->type_client ?? 'CC' }}</span></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $contribuable->email ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Adresse</div>
                                <div class="info-value">{{ $contribuable->adresse ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Division fiscale</div>
                                <div class="info-value">{{ $contribuable->division_fiscal ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Régime fiscal</div>
                                <div class="info-value">{{ $contribuable->regime_fiscal ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Adresse cadastrale</div>
                                <div class="info-value">{{ $contribuable->adresse_cadas ?? '—' }}</div>
                            </div>
                            <hr>
                            <div class="small text-muted">
                                Enregistré le
                                {{ $contribuable->created_at ? $contribuable->created_at->format('d/m/Y H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tableau des factures --}}
                <div class="col-lg-8">
                    <div class="card mb-4 info-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Historique des factures ({{ $stats['nb_factures'] }})</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($factures->isEmpty())
                                <div class="text-center text-muted py-4">
                                    Aucune facture enregistrée pour ce client.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Date</th>
                                                <th>Service</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Payé</th>
                                                <th class="text-end">Reste</th>
                                                <th class="text-center">Statut</th>
                                                <th class="text-center no-print">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($factures as $f)
                                                @php
                                                    $statut = $f->statut_paiement;
                                                    $badgeClass =
                                                        $statut === 'soldée'
                                                            ? 'badge-soldee'
                                                            : ($statut === 'partielle'
                                                                ? 'badge-partielle'
                                                                : 'badge-impayee');
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $f->code }}</strong></td>
                                                    <td>{{ $f->date ? \Carbon\Carbon::parse($f->date)->format('d/m/Y') : '—' }}
                                                    </td>
                                                    <td>{{ optional($f->Service)->intitule ?? '—' }}</td>
                                                    <td class="text-end">
                                                        {{ number_format($f->montant_total, 0, ',', ' ') }}</td>
                                                    <td class="text-end text-success">
                                                        {{ number_format($f->montant_paye, 0, ',', ' ') }}</td>
                                                    <td
                                                        class="text-end {{ $f->reste_a_payer > 0.009 ? 'text-danger' : 'text-muted' }}">
                                                        {{ number_format($f->reste_a_payer, 0, ',', ' ') }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span
                                                            class="badge-statut {{ $badgeClass }}">{{ ucfirst($statut) }}</span>
                                                    </td>
                                                    <td class="text-center no-print">
                                                        <a href="{{ route('module_ordre_facture.show', $f->id) }}"
                                                            class="btn btn-sm btn-info" title="Voir">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3" class="text-end">Totaux</th>
                                                <th class="text-end">
                                                    {{ number_format($stats['montant_total'], 0, ',', ' ') }}</th>
                                                <th class="text-end text-success">
                                                    {{ number_format($stats['montant_paye'], 0, ',', ' ') }}</th>
                                                <th class="text-end text-danger">
                                                    {{ number_format($stats['reste_a_payer'], 0, ',', ' ') }}</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Historique des règlements --}}
                    <div class="card mb-4 info-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fa fa-money-bill-wave me-1"></i>
                                Historique des règlements ({{ $reglements->count() }})
                            </span>
                            @if ($reglements->count() > 0)
                                <span class="text-success fw-semibold">
                                    Total encaissé : {{ number_format($reglements->sum('montant'), 0, ',', ' ') }} FCFA
                                </span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if ($reglements->isEmpty())
                                <div class="text-center text-muted py-4">
                                    Aucun règlement enregistré pour ce client.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Date</th>
                                                <th>Facture</th>
                                                <th>Mode</th>
                                                <th>Référence</th>
                                                <th class="text-end">Montant (FCFA)</th>
                                                <th>Enregistré par</th>
                                                <th>Observation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reglements as $r)
                                                <tr>
                                                    <td><strong>{{ $r->code ?? '—' }}</strong></td>
                                                    <td>{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('d/m/Y') : '—' }}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border">
                                                            {{ optional($r->Facture)->code ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $modeIcons = [
                                                                'Espèces' => 'fa-money-bill',
                                                                'Chèque' => 'fa-money-check',
                                                                'Virement' => 'fa-exchange-alt',
                                                                'Mobile Money' => 'fa-mobile-alt',
                                                            ];
                                                            $icon = $modeIcons[$r->mode] ?? 'fa-credit-card';
                                                        @endphp
                                                        <i class="fa {{ $icon }} text-secondary me-1"></i>
                                                        {{ $r->mode }}
                                                    </td>
                                                    <td class="text-muted">{{ $r->reference_paiement ?? '—' }}</td>
                                                    <td class="text-end text-success fw-semibold">
                                                        {{ number_format($r->montant, 0, ',', ' ') }}
                                                    </td>
                                                    <td class="text-muted small">{{ optional($r->User)->name ?? '—' }}
                                                    </td>
                                                    <td class="text-muted small">{{ $r->observation ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="5" class="text-end">Total encaissé</th>
                                                <th class="text-end text-success">
                                                    {{ number_format($reglements->sum('montant'), 0, ',', ' ') }}</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @include('partials.script')
@endsection
