@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Transfert {{ $transfert->code }}</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
<main>
    <header class="page-header page-header-dark header-gradient pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="shuffle"></i></div>
                            Transfert {{ $transfert->code }}
                        </h1>
                    </div>
                    <div class="col-auto mt-4">
                        <a href="{{ route('gestion_transfert.index') }}" class="btn btn-light me-2">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                        @if ($transfert->statut === 'en_attente')
                            <form action="{{ route('gestion_transfert.valider', $transfert->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success me-2"
                                    onclick="return confirm('Confirmer la validation ? Le stock sera mis à jour immédiatement.')">
                                    <i class="fas fa-check me-1"></i> Valider le transfert
                                </button>
                            </form>
                            <form action="{{ route('gestion_transfert.annuler', $transfert->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-secondary"
                                    onclick="return confirm('Annuler ce transfert ?')">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10" style="margin-bottom: 8rem;">

        {{-- En-tête transfert --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row gx-4">
                    <div class="col-md-3">
                        <div class="small text-muted mb-1">Code</div>
                        <div class="fw-bold" style="color:var(--primary-color);font-size:1.1rem;">
                            {{ $transfert->code }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted mb-1">Date</div>
                        <div>{{ $transfert->date ? \Carbon\Carbon::parse($transfert->date)->format('d/m/Y') : '—' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="small text-muted mb-1">Statut</div>
                        <div>
                            @switch($transfert->statut)
                                @case('en_attente')
                                    <span class="badge bg-warning text-dark fs-6">En attente</span>
                                    @break
                                @case('valide')
                                    <span class="badge bg-success fs-6">Validé</span>
                                    @break
                                @case('annule')
                                    <span class="badge bg-secondary fs-6">Annulé</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted mb-1">Opérateur</div>
                        <div>{{ optional($transfert->user)->nom }} {{ optional($transfert->user)->prenom }}</div>
                    </div>
                </div>

                <hr>

                <div class="row gx-4 align-items-center">
                    <div class="col-md-5 text-center">
                        <div class="small text-muted mb-1">Dépôt source</div>
                        <div class="fw-bold fs-5">{{ optional($transfert->serviceSource)->libelle }}</div>
                    </div>
                    <div class="col-md-2 text-center">
                        <i class="fas fa-arrow-right fa-2x" style="color:var(--primary-color)"></i>
                    </div>
                    <div class="col-md-5 text-center">
                        <div class="small text-muted mb-1">Dépôt destination</div>
                        <div class="fw-bold fs-5">{{ optional($transfert->serviceDestination)->libelle }}</div>
                    </div>
                </div>

                @if ($transfert->note)
                    <hr>
                    <div class="small text-muted mb-1">Note</div>
                    <div>{{ $transfert->note }}</div>
                @endif
            </div>
        </div>

        {{-- Lignes produits --}}
        <div class="card mb-4">
            <div class="card-header">Produits transférés</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Produit</th>
                                <th>Unité</th>
                                <th class="text-end">Quantité</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transfert->elements as $i => $el)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ optional($el->produit)->code }}</td>
                                    <td>{{ optional($el->produit)->designation }}</td>
                                    <td>{{ optional($el->produit)->unite }}</td>
                                    <td class="text-end fw-bold">{{ number_format($el->quantite, 2, ',', ' ') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Nombre de lignes</th>
                                <th class="text-end">{{ $transfert->elements->count() }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if ($transfert->statut === 'en_attente')
            <div class="alert alert-warning d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Ce transfert est <strong>en attente</strong>. Le stock ne sera impacté qu'après validation.</span>
            </div>
        @elseif ($transfert->statut === 'valide')
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>Transfert <strong>validé</strong>. Les mouvements de stock ont été appliqués.</span>
            </div>
        @elseif ($transfert->statut === 'annule')
            <div class="alert alert-secondary d-flex align-items-center gap-2">
                <i class="fas fa-times-circle"></i>
                <span>Ce transfert a été <strong>annulé</strong>. Aucun impact sur le stock.</span>
            </div>
        @endif

    </div>
</main>
@endsection
