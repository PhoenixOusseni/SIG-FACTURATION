@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Ticket {{ $ticket->code }}</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
@php
    $ticket->loadMissing(['Elements', 'Reglements.ModePaie', 'Contribuable', 'User', 'Caisse']);
@endphp
<main>
    <header class="page-header page-header-dark pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="file-text"></i></div>
                            Ticket {{ $ticket->code }}
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mt-4 d-flex gap-2">
                        <a href="{{ route('module_comptoir.tickets.index') }}" class="btn btn-light btn-sm">Retour</a>
                        <a href="{{ route('print_ticket_comptoir', $ticket->id) }}" target="_blank" class="btn btn-light btn-sm">Imprimer</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10">
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><span class="text-muted small d-block">Statut</span><strong>{{ $ticket->statut }}</strong></div>
                    <div class="col-md-3"><span class="text-muted small d-block">Date métier</span><strong>{{ optional($ticket->date_metier)->format('d/m/Y') }}</strong></div>
                    <div class="col-md-3"><span class="text-muted small d-block">Client</span><strong>{{ optional($ticket->Contribuable)->assujeti ?: ($ticket->client_libre ?: '—') }}</strong></div>
                    <div class="col-md-3"><span class="text-muted small d-block">TTC</span><strong>{{ number_format((float)$ticket->montant_ttc, 0, ',', ' ') }} FCFA</strong></div>
                </div>
            </div>
        </div>
        <div class="card mb-3 shadow-sm">
            <div class="card-header">Lignes</div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th class="text-end">Qté</th>
                            <th class="text-end">PU HT</th>
                            <th class="text-end">TVA %</th>
                            <th class="text-end">TTC ligne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ticket->Elements as $el)
                            <tr>
                                <td>{{ $el->designation }}</td>
                                <td class="text-end">{{ $el->quantite }}</td>
                                <td class="text-end">{{ number_format((float)$el->prix_unitaire, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ $el->tva_taux }}</td>
                                <td class="text-end">{{ number_format((float)$el->montant_ttc_ligne, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-3 text-end border-top">
                    Remise : {{ number_format((float)$ticket->remise, 0, ',', ' ') }} ·
                    HT : {{ number_format((float)$ticket->montant_ht, 0, ',', ' ') }} ·
                    <strong>TTC : {{ number_format((float)$ticket->montant_ttc, 0, ',', ' ') }}</strong>
                </div>
            </div>
        </div>
        @if ($ticket->Reglements->count())
            <div class="card shadow-sm">
                <div class="card-header">Paiements</div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Mode</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Reçu</th>
                                <th class="text-end">Rendu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ticket->Reglements as $r)
                                <tr>
                                    <td>{{ $r->mode }}</td>
                                    <td class="text-end">{{ number_format((float)$r->montant, 0, ',', ' ') }}</td>
                                    <td class="text-end">{{ $r->montant_recu !== null ? number_format((float)$r->montant_recu, 0, ',', ' ') : '—' }}</td>
                                    <td class="text-end">{{ $r->rendu !== null ? number_format((float)$r->rendu, 0, ',', ' ') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        <p class="text-muted small mt-3">Document non fiscal — hors certification MCF/SFE.</p>
    </div>
</main>
@endsection

@section('script')
    @include('partials.script')
@endsection
