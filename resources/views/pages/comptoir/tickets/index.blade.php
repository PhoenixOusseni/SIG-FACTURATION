@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Point de vente (Comptoir)</title>
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
                                <div class="page-header-icon"><i data-feather="list"></i></div>
                                État des ventes — Comptoir
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mt-4 d-flex flex-wrap gap-2">
                            @if (Auth::user()->isSuperviseurComptoir())
                                <a href="{{ route('module_comptoir.conversion.index') }}"
                                    class="btn btn-light btn-sm">Conversion → facture</a>
                            @endif
                            <a href="{{ route('module_comptoir.pos') }}" class="btn btn-light btn-sm">Point de vente</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <x-form-errors class="mb-3" />

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            @if (session('form_error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('form_error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif

            <div class="card mb-3 shadow-sm">
                <div class="card-header">Filtres</div>
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Date métier</label>
                            <input type="date" name="date_metier" class="form-control form-control-sm"
                                value="{{ request('date_metier') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Statut du ticket</label>
                            <select name="statut" class="form-select form-select-sm">
                                <option value="">Tous</option>
                                @foreach (['brouillon', 'en_attente', 'paye', 'annule', 'converti'] as $st)
                                    <option value="{{ $st }}" @if (request('statut') === $st) selected @endif>
                                        {{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Caisse</label>
                            <select name="caisse_id" class="form-select form-select-sm">
                                <option value="">Toutes</option>
                                @foreach ($caisses as $c)
                                    <option value="{{ $c->id }}" @if (request('caisse_id') == $c->id) selected @endif>
                                        {{ $c->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-sm" type="submit">Filtrer</button>
                            <a href="{{ route('module_comptoir.tickets.index') }}"
                                class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">{{ $sessions->total() }} ouverture(s) de caisse</span>
            </div>

            @forelse ($sessions as $session)
                @php
                    $statutBadges = [
                        'ouverte' => 'bg-success',
                        'en_arret' => 'bg-warning text-dark',
                        'fermee' => 'bg-secondary',
                    ];
                    $collapseId = 'session-' . $session->id;
                @endphp
                <div class="card shadow-sm mb-3">
                    <div class="card-header">
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <a class="fw-bold text-decoration-none" data-bs-toggle="collapse" href="#{{ $collapseId }}"
                                role="button" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                <i data-feather="chevron-down"></i>
                                {{ optional($session->Caisse)->libelle ?? '—' }}
                            </a>
                            <span class="badge {{ $statutBadges[$session->statut] ?? 'bg-secondary' }}">
                                {{ $session->statut }}
                            </span>
                            <span class="text-muted small">Date métier
                                {{ optional($session->date_metier)->format('d/m/Y') }}</span>
                            <span class="text-muted small">
                                Ouverte par
                                {{ trim((optional($session->OpenedBy)->prenom ?? '') . ' ' . (optional($session->OpenedBy)->nom ?? '')) ?: '—' }}
                            </span>
                            <span class="text-muted small">Fond initial
                                {{ number_format((float) $session->fond_initial, 0, ',', ' ') }}</span>
                            <span class="text-muted small">{{ $session->Tickets->count() }} ticket(s)</span>
                            <span class="ms-auto fw-bold">
                                Total ventes {{ number_format((float) $session->montant_total, 0, ',', ' ') }}
                            </span>
                        </div>
                    </div>
                    <div class="collapse" id="{{ $collapseId }}">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Vendeur</th>
                                        <th>Statut</th>
                                        <th class="text-end">Montant TTC</th>
                                        <th style="width:220px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($session->Tickets as $t)
                                        <tr>
                                            <td><strong class="text-primary">{{ $t->code }}</strong></td>
                                            <td>{{ optional($t->date_metier)->format('d/m/Y') }}</td>
                                            <td>{{ optional($t->Contribuable)->assujeti ?: ($t->client_libre ?: '—') }}
                                            </td>
                                            <td>{{ optional($t->User)->prenom }} {{ optional($t->User)->nom }}</td>
                                            <td><span class="text-danger">{{ $t->statut }}</span></td>
                                            <td class="text-end">{{ number_format((float) $t->montant_ttc, 0, ',', ' ') }}
                                            </td>
                                            <td class="text-end text-nowrap">
                                                <a href="{{ route('module_comptoir.tickets.show', $t->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (in_array($t->statut, ['paye', 'converti'], true))
                                                    <a href="{{ route('print_ticket_comptoir', $t->id) }}" target="_blank"
                                                        class="btn btn-sm btn-success">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                @endif
                                                @if (in_array($t->statut, ['brouillon', 'en_attente'], true))
                                                    @if ($session->isOuverte())
                                                        <button type="button"
                                                            class="btn btn-sm btn-success py-0 px-2 btn-encaisser"
                                                            data-url="{{ route('module_comptoir.tickets.payer', $t->id) }}"
                                                            data-code="{{ $t->code }}"
                                                            data-ttc="{{ number_format((float) $t->montant_ttc, 2, '.', '') }}">
                                                            Encaisser
                                                        </button>
                                                    @endif
                                                    <form action="{{ route('module_comptoir.tickets.destroy', $t->id) }}"
                                                        method="POST" class="d-inline-block"
                                                        onsubmit="return confirm('Supprimer le ticket {{ $t->code }} ? Cette action est irréversible.')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-danger py-0 px-2">Suppr.</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-3">Aucun ticket.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card shadow-sm">
                    <div class="card-body text-center text-muted py-4">Aucune ouverture de caisse.</div>
                </div>
            @endforelse

            @if ($sessions->hasPages())
                <div class="mt-2">{{ $sessions->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- ═══ Encaisser modal ═══ --}}
        <div class="modal fade" id="encaisserModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <form id="encaisserForm" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Encaisser <span id="encaisserCode"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Fermer">X</button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <label class="form-label">TTC à encaisser</label>
                                <input type="text" class="form-control" id="encaisserTtcDisplay" readonly>
                            </div>
                            <input type="hidden" name="paiements[0][modepaie_id]" value="{{ $espModeId }}">
                            <input type="hidden" name="paiements[0][montant]" id="encaisserMontant">
                            <input type="hidden" name="expected_ttc" id="encaisserExpected">
                            <div class="mb-2">
                                <label class="form-label">Montant reçu (espèces)</label>
                                <input type="number" class="form-control" name="paiements[0][montant_recu]"
                                    id="encaisserRecu" min="0" step="1">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 p-3 border-top">
                            <button type="submit" class="btn btn-success btn-sm">Confirmer l'encaissement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('script')
    @include('partials.script')
    <script>
        document.querySelectorAll('.btn-encaisser').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var ttc = btn.dataset.ttc;
                document.getElementById('encaisserForm').action = btn.dataset.url;
                document.getElementById('encaisserCode').textContent = btn.dataset.code;
                document.getElementById('encaisserTtcDisplay').value = ttc;
                document.getElementById('encaisserMontant').value = ttc;
                document.getElementById('encaisserExpected').value = ttc;
                document.getElementById('encaisserRecu').value = ttc;
                var modalEl = document.getElementById('encaisserModal');
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });
        });
    </script>
@endsection
