@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Bordereau {{ $bordereau->code }}</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .info-label { font-size: 0.78rem; color: #6c757d; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 0.25rem;}
        .info-value { font-size: 1rem; font-weight: 500; color: #2c3e50;}
        .workflow-step { padding: 0.75rem 1rem; border-radius: 0.5rem; flex: 1; text-align: center; font-weight: 600; font-size: 0.9rem;}
        .workflow-step.done { background: #d1e7dd; color: #0f5132; }
        .workflow-step.current { background: #fff3cd; color: #664d03; border: 2px dashed #ffc107; }
        .workflow-step.pending { background: #e9ecef; color: #6c757d; }
        .workflow-arrow { display: flex; align-items: center; justify-content: center; color: #6c757d; }
    </style>
@endsection

@section('content')
    <main>
        <header class="page-header pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="truck"></i></div>
                                Bordereau de livraison {{ $bordereau->code }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom: 6rem;">

            {{-- Barre d'actions --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            @if ($bordereau->statut === 'facturé')
                                <span class="badge bg-success p-2">
                                    <i data-feather="check-circle" style="width:14px;height:14px;"></i> Facturé
                                </span>
                            @elseif ($bordereau->statut === 'livré')
                                <span class="badge bg-primary p-2">
                                    <i data-feather="check" style="width:14px;height:14px;"></i> Livré
                                </span>
                            @elseif ($bordereau->statut === 'annulé')
                                <span class="badge bg-danger p-2">Annulé</span>
                            @else
                                <span class="badge bg-warning text-dark p-2">En attente</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('module_bordereau_livraison.index') }}" class="btn btn-light btn-sm">
                                <i data-feather="arrow-left"></i>
                            </a>

                            @if ($bordereau->statut !== 'facturé' && $bordereau->statut !== 'annulé')
                                <a href="{{ route('bordereau_livre', $bordereau->id) }}" class="btn btn-primary btn-sm"
                                    onclick="return confirm('Marquer ce bordereau comme livré ?')">
                                    <i data-feather="check"></i>&nbsp; Marquer livré
                                </a>
                                <a href="{{ route('facture_from_bordereau', $bordereau->id) }}" class="btn btn-success btn-sm"
                                    onclick="return confirm('Transformer ce bordereau en facture définitive ?')">
                                    <i data-feather="file-text"></i>&nbsp; Transformer en facture
                                </a>
                            @endif

                            <a href="{{ route('print_bordereau_livraison', $bordereau->id) }}"
                                target="_blank" class="btn btn-warning btn-sm">
                                <i data-feather="printer"></i>&nbsp; Imprimer
                            </a>

                            @if ($bordereau->statut !== 'facturé')
                                <a href="#" class="btn btn-danger btn-sm"
                                    onclick="if(confirm('Supprimer ce bordereau ?')) { document.getElementById('delForm').submit(); } return false;">
                                    <i data-feather="trash-2"></i>&nbsp; Supprimer
                                </a>
                                <form id="delForm" action="{{ route('module_bordereau_livraison.destroy', $bordereau->id) }}"
                                    method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Workflow visuel --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-stretch gap-2">
                        <div class="workflow-step {{ $bordereau->FactureProforma ? 'done' : 'pending' }}">
                            1. Proforma
                            @if ($bordereau->FactureProforma)
                                <div class="small mt-1">
                                    <a href="{{ route('module_facture_proforma.show', $bordereau->FactureProforma->id) }}" class="text-decoration-none">
                                        {{ $bordereau->FactureProforma->code }}
                                    </a>
                                </div>
                            @else
                                <div class="small mt-1 text-muted">— direct —</div>
                            @endif
                        </div>
                        <div class="workflow-arrow"><i data-feather="arrow-right"></i></div>
                        <div class="workflow-step {{ $bordereau->Facture ? 'done' : 'current' }}">
                            2. Bordereau de livraison
                            <div class="small mt-1">{{ $bordereau->code }}</div>
                        </div>
                        <div class="workflow-arrow"><i data-feather="arrow-right"></i></div>
                        <div class="workflow-step {{ $bordereau->Facture ? 'done' : 'pending' }}">
                            3. Facture définitive
                            @if ($bordereau->Facture)
                                <div class="small mt-1">
                                    <a href="{{ route('module_ordre_facture.show', $bordereau->Facture->id) }}" class="text-decoration-none">
                                        {{ $bordereau->Facture->code }}
                                    </a>
                                </div>
                            @else
                                <div class="small mt-1 text-muted">— en attente —</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informations générales --}}
            <div class="card mb-4">
                <div class="card-header">Informations générales</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Code</div>
                            <div class="info-value">{{ $bordereau->code }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Référence</div>
                            <div class="info-value">{{ $bordereau->reference ?? '—' }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Date</div>
                            <div class="info-value">{{ $bordereau->date }}</div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="info-label">Émis par</div>
                            <div class="info-value">{{ optional($bordereau->User)->login ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-label">Client</div>
                            <div class="info-value">{{ optional($bordereau->Contribuable)->assujeti ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-label">Ligne métier</div>
                            <div class="info-value">{{ optional($bordereau->Categorie)->libelle ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-label">Ligne de service</div>
                            <div class="info-value">{{ optional($bordereau->Service)->libelle ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-label">Transporteur</div>
                            <div class="info-value">{{ $bordereau->transporteur ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-label">Livreur</div>
                            <div class="info-value">{{ $bordereau->livreur ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-label">Statut</div>
                            <div class="info-value">{{ $bordereau->statut }}</div>
                        </div>
                        @if ($bordereau->observation)
                            <div class="col-md-12 mb-3">
                                <div class="info-label">Observation</div>
                                <div class="info-value">{{ $bordereau->observation }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Éléments --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <span>Éléments livrés</span>
                    <span class="badge bg-primary">{{ $elements->count() }} ligne(s)</span>
                </div>
                <div class="card-body">
                    @if ($elements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Désignation</th>
                                        <th class="text-end">Quantité</th>
                                        <th class="text-end">Prix unitaire</th>
                                        <th class="text-end">Remise (%)</th>
                                        <th class="text-end">Montant total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($elements as $el)
                                        <tr>
                                            <td><strong>{{ optional($el->Produit)->code }}</strong></td>
                                            <td>{{ optional($el->Produit)->designation }}</td>
                                            <td class="text-end">{{ number_format($el->quantite, 2, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($el->prix_unitaire, 2, ',', ' ') }}</td>
                                            <td class="text-end">{{ number_format($el->remise ?? 0, 2, ',', ' ') }}</td>
                                            <td class="text-end"><strong>{{ number_format($el->montant_total, 2, ',', ' ') }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Total HT estimé</th>
                                        <th class="text-end">{{ number_format($montant_total, 2, ',', ' ') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted p-4">Aucun élément</div>
                    @endif
                </div>
            </div>

            {{-- Récapitulatif financier avec remise --}}
            @php
                $totalTtcBic = (float) $bordereau->montant_total;
                $remise_1 = (float) ($bordereau->remise_1 ?? 0);
                $remise_2 = (float) ($bordereau->remise_2 ?? 0);
                $remise = $remise_1 + $remise_2;
                $netAPayer = max(0, round($totalTtcBic - $remise, 2));
            @endphp
            <div class="card mb-4">
                <div class="card-header">
                    <span>Récapitulatif financier</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-label">Total estimé (TTC + PSVBB)</div>
                            <div class="info-value"><strong>{{ number_format($totalTtcBic, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Remise 1</div>
                            <div class="info-value text-warning"><strong>− {{ number_format($remise_1, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Remise 2</div>
                            <div class="info-value text-warning"><strong>− {{ number_format($remise_2, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-label">Net à payer estimé</div>
                            <div class="info-value"><strong>{{ number_format($netAPayer, 0, ',', ' ') }} FCFA</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
