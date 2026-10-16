@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Conversion tickets → facture</title>
@endsection

@section('style')
    @include('partials.style')
@endsection

@section('content')
<main>
    <header class="page-header page-header-dark pb-10">
        <div class="container-xl px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="repeat"></i></div>
                            Conversion tickets → facture
                        </h1>
                    </div>
                    <div class="col-12 col-xl-auto mt-4 d-flex gap-2">
                        <a href="{{ route('module_comptoir.tickets.index') }}" class="btn btn-light btn-sm">État des ventes</a>
                        <a href="{{ route('module_comptoir.pos') }}" class="btn btn-light btn-sm">POS</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl px-4 mt-n10">
        <x-form-errors class="mb-3" />

        <div class="alert alert-warning shadow-sm">
            <strong>Non fiscal sur les tickets.</strong>
            La conversion crée une facture classique <em>sans</em> certification SFE/MCF automatique.
            Les stocks ne sont pas re-mouvementés (déjà sortis à l’encaissement).
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-header">Filtres</div>
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Date métier</label>
                        <input type="date" name="date_metier" class="form-control form-control-sm" value="{{ request('date_metier') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Caisse</label>
                        <select name="caisse_id" class="form-select form-select-sm">
                            <option value="">Toutes</option>
                            @foreach ($caisses as $c)
                                <option value="{{ $c->id }}" @if(request('caisse_id')==$c->id) selected @endif>{{ $c->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Client</label>
                        <select name="contribuable_id" class="form-select form-select-sm">
                            <option value="">Tous</option>
                            @foreach ($contribuables as $c)
                                <option value="{{ $c->id }}" @if(request('contribuable_id')==$c->id) selected @endif>
                                    {{ $c->assujeti }} @if($c->ifu) ({{ $c->ifu }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Recherche</label>
                        <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Code / client">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm" type="submit">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('module_comptoir.conversion.store') }}" id="conversionForm">
            @csrf
            <div class="card mb-3 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Tickets payés (non convertis)</span>
                    <span class="text-muted small">Sélection : <strong id="selCount">0</strong> · TTC : <strong id="selTtc">0</strong></span>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead>
                            <tr>
                                <th style="width:40px" class="text-center"><input type="checkbox" id="checkAll"></th>
                                <th>Code</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Caisse</th>
                                <th class="text-end">TTC</th>
                                <th>Paiements</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tickets as $t)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="ticket-check" name="ticket_ids[]"
                                               value="{{ $t->id }}" data-ttc="{{ (float) $t->montant_ttc }}">
                                    </td>
                                    <td><strong class="text-primary">{{ $t->code }}</strong></td>
                                    <td>{{ optional($t->date_metier)->format('d/m/Y') }}</td>
                                    <td>{{ optional($t->Contribuable)->assujeti ?: ($t->client_libre ?: '—') }}</td>
                                    <td>{{ optional($t->Caisse)->code }}</td>
                                    <td class="text-end">{{ number_format((float)$t->montant_ttc, 0, ',', ' ') }}</td>
                                    <td class="small text-muted">
                                        @foreach ($t->Reglements as $r)
                                            {{ $r->mode }}: {{ number_format((float)$r->montant, 0, ',', ' ') }}
                                            @if (!$loop->last) · @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Aucun ticket payé à convertir.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($tickets->hasPages())
                    <div class="card-footer">{{ $tickets->links() }}</div>
                @endif
            </div>

            <div class="card shadow-sm">
                <div class="card-header">Facture cible</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client (contribuable) *</label>
                            <select name="contribuable_id" class="form-select" required>
                                <option value="">— Choisir —</option>
                                @foreach ($contribuables as $c)
                                    <option value="{{ $c->id }}" @if(old('contribuable_id')==$c->id) selected @endif>
                                        {{ $c->assujeti }} @if($c->ifu) ({{ $c->ifu }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date facture</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type facture</label>
                            <select name="typefacture_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($typefactures as $tf)
                                    <option value="{{ $tf->id }}" @if(old('typefacture_id')==$tf->id) selected @endif>
                                        {{ $tf->code }} — {{ $tf->libelle ?? $tf->designation ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mode paie (entête, optionnel)</label>
                            <select name="modepaie_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($modepaies as $m)
                                    <option value="{{ $m->id }}">{{ $m->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnConvert" disabled>
                            Convertir en facture soldée
                        </button>
                        <a href="{{ route('module_comptoir.pos') }}" class="btn btn-outline-secondary">POS</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
@endsection

@section('script')
    @include('partials.script')
    <script>
    (function ($) {
        function refresh() {
            var n = 0, ttc = 0;
            $('.ticket-check:checked').each(function () {
                n++;
                ttc += parseFloat($(this).data('ttc')) || 0;
            });
            $('#selCount').text(n);
            $('#selTtc').text(ttc.toLocaleString('fr-FR', { maximumFractionDigits: 0 }));
            $('#btnConvert').prop('disabled', n < 1);
        }
        $('#checkAll').on('change', function () {
            $('.ticket-check').prop('checked', this.checked);
            refresh();
        });
        $(document).on('change', '.ticket-check', refresh);
        $('#conversionForm').on('submit', function () {
            if ($('.ticket-check:checked').length < 1) {
                alert('Sélectionnez au moins un ticket.');
                return false;
            }
            return confirm('Créer une facture soldée à partir des tickets sélectionnés ?');
        });
        refresh();
    })(jQuery);
    </script>
@endsection
