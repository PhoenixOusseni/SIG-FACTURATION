@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Détails Fournisseur</title>
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

        .bg-gradient-slate {
            background: linear-gradient(135deg, #475569 0%, #1e293b 100%);
        }

        .bg-gradient-teal {
            background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);
        }

        .bg-gradient-red {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
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

        .avatar-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #10b981, #047857);
            color: #fff;
            font-weight: 700;
            font-size: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <main>
        <x-page-header icon="truck" title="Fiche fournisseur"></x-page-header>

        <div class="container-xl px-4 mt-n10">

            {{-- Bandeau identité --}}
            <div class="card mb-4 info-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="avatar-circle">
                            {{ strtoupper(substr($fournisseur->assujeti ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{ $fournisseur->assujeti ?? '—' }}</h4>
                            <div class="text-muted">
                                <i class="fa fa-tag"></i>
                                {{ optional($fournisseur->Categorie)->libelle ?? 'Sans catégorie' }}
                                @if ($fournisseur->ifu)
                                    &nbsp;·&nbsp; <strong>IFU :</strong> {{ $fournisseur->ifu }}
                                @endif
                                @if ($fournisseur->rccm)
                                    &nbsp;·&nbsp; <strong>RCCM :</strong> {{ $fournisseur->rccm }}
                                @endif
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                data-bs-target="#editFournisseurModal" title="Modifier les informations du fournisseur">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="{{ route('print_fiche_fournisseur', $fournisseur->id) }}" target="_blank"
                                class="btn btn-sm btn-success" title="Imprimer la fiche fournisseur">
                                <i class="fa fa-print"></i>
                            </a>
                            <form action="{{ route('module_fournisseur.destroy', $fournisseur->id) }}" method="POST"
                                class="d-inline-block"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ? Cette action est irréversible.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer le fournisseur">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            <a href="{{ route('module_fournisseur.index') }}" class="btn btn-sm btn-secondary" title="Retour à la liste des fournisseurs">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistiques --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-slate p-3">
                        <div class="label">Factures fournisseurs</div>
                        <div class="value">{{ $stats['nb_factures'] }}</div>
                        <div class="sub">facture(s) reçue(s)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-blue p-3">
                        <div class="label">Montant total facturé</div>
                        <div class="value" style="font-size:1.2rem">
                            {{ number_format($stats['montant_total'], 0, ',', ' ') }}</div>
                        <div class="sub">FCFA (TTC)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-amber p-3">
                        <div class="label">Total dépenses</div>
                        <div class="value" style="font-size:1.2rem">
                            {{ number_format($stats['total_depenses'], 0, ',', ' ') }}</div>
                        <div class="sub">{{ $stats['nb_depenses'] }} opération(s)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-green p-3">
                        <div class="label">Engagement total</div>
                        <div class="value" style="font-size:1.2rem">
                            {{ number_format($stats['montant_total'] + $stats['total_depenses'], 0, ',', ' ') }}</div>
                        <div class="sub">FCFA (factures + dépenses)</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-teal p-3">
                        <div class="label">Total soldé</div>
                        <div class="value" style="font-size:1.2rem">
                            {{ number_format($stats['total_solde'], 0, ',', ' ') }}</div>
                        <div class="sub">FCFA réglés</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-red p-3">
                        <div class="label">Reste à solder</div>
                        <div class="value" style="font-size:1.2rem">
                            {{ number_format(max(0, $stats['reste_a_solder']), 0, ',', ' ') }}</div>
                        <div class="sub">FCFA restants</div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Informations fournisseur --}}
                <div class="col-lg-4">
                    <div class="card mb-4 info-card">
                        <div class="card-header">Informations</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="info-label">Téléphone</div>
                                <div class="info-value">{{ $fournisseur->telephone ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Adresse postale</div>
                                <div class="info-value">{{ $fournisseur->adresse ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Localisation</div>
                                <div class="info-value">{{ $fournisseur->adresse_localisation ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Division fiscale</div>
                                <div class="info-value">{{ $fournisseur->division_fiscal ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Régime fiscal</div>
                                <div class="info-value">{{ $fournisseur->regime_fiscal ?? '—' }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="info-label">Adresse cadastrale</div>
                                <div class="info-value">{{ $fournisseur->adresse_cadas ?? '—' }}</div>
                            </div>
                            <hr>
                            <div class="small text-muted">
                                Enregistré le
                                {{ $fournisseur->created_at ? $fournisseur->created_at->format('d/m/Y H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tableau des factures + dépenses + règlements --}}
                <div class="col-lg-8">

                    {{-- Factures fournisseurs --}}
                    <div class="card mb-4 info-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Historique des factures fournisseurs ({{ $stats['nb_factures'] }})</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($factures->isEmpty())
                                <div class="text-center text-muted py-4">Aucune facture enregistrée pour ce fournisseur.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Date</th>
                                                <th>Dépôt</th>
                                                <th class="text-end">Total TTC</th>
                                                <th class="text-end">Réglé</th>
                                                <th class="text-end">Reste</th>
                                                <th>Statut</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($factures as $f)
                                                @php
                                                    $sp = $f->statut_paiement;
                                                    $badgeClass = match ($sp) {
                                                        'soldée' => 'bg-success',
                                                        'partielle' => 'bg-warning text-dark',
                                                        default => 'bg-danger',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $f->code }}</strong></td>
                                                    <td>{{ $f->date ? \Carbon\Carbon::parse($f->date)->format('d/m/Y') : '—' }}
                                                    </td>
                                                    <td>{{ optional($f->Service)->libelle ?? '—' }}</td>
                                                    <td class="text-end">
                                                        {{ number_format($f->montant_total, 0, ',', ' ') }}</td>
                                                    <td class="text-end text-success fw-semibold">
                                                        {{ number_format($f->montant_paye, 0, ',', ' ') }}</td>
                                                    <td class="text-end text-danger fw-semibold">
                                                        {{ number_format($f->reste_a_payer, 0, ',', ' ') }}</td>
                                                    <td><span
                                                            class="badge {{ $badgeClass }}">{{ ucfirst($sp) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('module_facture_fournisseur.show', $f->id) }}"
                                                            class="btn btn-sm btn-info" title="Voir">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <th colspan="3" class="text-end">Total</th>
                                                <th class="text-end">
                                                    {{ number_format($stats['montant_total'], 0, ',', ' ') }}</th>
                                                <th class="text-end text-success">
                                                    {{ number_format($stats['total_regle_factures'], 0, ',', ' ') }}</th>
                                                <th class="text-end text-danger">
                                                    {{ number_format(max(0, $stats['montant_total'] - $stats['total_regle_factures']), 0, ',', ' ') }}
                                                </th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Règlements de factures fournisseurs --}}
                    <div class="card mb-4 info-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Historique des règlements de factures fournisseurs
                                ({{ $reglementsFournisseur->count() }})</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($reglementsFournisseur->isEmpty())
                                <div class="text-center text-muted py-4">Aucun règlement de facture enregistré.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Date</th>
                                                <th>Facture</th>
                                                <th>Mode</th>
                                                <th>Référence</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reglementsFournisseur as $r)
                                                <tr>
                                                    <td><strong>{{ $r->code }}</strong></td>
                                                    <td>{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('d/m/Y') : '—' }}
                                                    </td>
                                                    <td>{{ optional($r->FactureFournisseur)->code ?? '—' }}</td>
                                                    <td>{{ $r->mode }}</td>
                                                    <td>{{ $r->reference_paiement ?? '—' }}</td>
                                                    <td class="text-end text-success fw-semibold">
                                                        {{ number_format($r->montant, 0, ',', ' ') }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('module_reglement_fournisseur.show', $r->id) }}"
                                                            class="btn btn-sm btn-info" title="Voir">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <th colspan="5" class="text-end">Total réglé (factures)</th>
                                                <th class="text-end text-success">
                                                    {{ number_format($stats['total_regle_factures'], 0, ',', ' ') }}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Dépenses --}}
                    <div class="card mb-4 info-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Historique des dépenses ({{ $stats['nb_depenses'] }})</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($depenses->isEmpty())
                                <div class="text-center text-muted py-4">Aucune dépense enregistrée pour ce fournisseur.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Motif</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-end">Réglé</th>
                                                <th class="text-end">Reste</th>
                                                <th>Statut</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($depenses as $d)
                                                @php
                                                    $sp = $d->statut_paiement;
                                                    $badgeClass = match ($sp) {
                                                        'soldée' => 'bg-success',
                                                        'partielle' => 'bg-warning text-dark',
                                                        default => 'bg-danger',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td>{{ $d->date ? $d->date->format('d/m/Y') : '—' }}</td>
                                                    <td>{{ $d->motif }}</td>
                                                    <td class="text-end">{{ number_format($d->montant, 0, ',', ' ') }}
                                                    </td>
                                                    <td class="text-end text-success fw-semibold">
                                                        {{ number_format($d->montant_paye, 0, ',', ' ') }}</td>
                                                    <td class="text-end text-danger fw-semibold">
                                                        {{ number_format($d->reste_a_payer, 0, ',', ' ') }}</td>
                                                    <td><span
                                                            class="badge {{ $badgeClass }}">{{ ucfirst($sp) }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('module_depense.show', $d->id) }}"
                                                            class="btn btn-sm btn-info" title="Voir">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <th colspan="2" class="text-end">Total</th>
                                                <th class="text-end">
                                                    {{ number_format($stats['total_depenses'], 0, ',', ' ') }}</th>
                                                <th class="text-end text-success">
                                                    {{ number_format($stats['total_regle_depenses'], 0, ',', ' ') }}</th>
                                                <th class="text-end text-danger">
                                                    {{ number_format(max(0, $stats['total_depenses'] - $stats['total_regle_depenses']), 0, ',', ' ') }}
                                                </th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Règlements de dépenses --}}
                    <div class="card mb-4 info-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Historique des règlements de dépenses ({{ $reglementsDepenses->count() }})</span>
                        </div>
                        <div class="card-body p-0">
                            @if ($reglementsDepenses->isEmpty())
                                <div class="text-center text-muted py-4">Aucun règlement de dépense enregistré.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Dépense (motif)</th>
                                                <th>Mode</th>
                                                <th>Référence</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($reglementsDepenses as $rd)
                                                <tr>
                                                    <td>{{ $rd->date ? $rd->date->format('d/m/Y') : '—' }}</td>
                                                    <td>{{ optional($rd->depense)->motif ?? '—' }}</td>
                                                    <td>{{ $rd->mode ?? '—' }}</td>
                                                    <td>{{ $rd->reference_paiement ?? '—' }}</td>
                                                    <td class="text-end text-success fw-semibold">
                                                        {{ number_format($rd->montant, 0, ',', ' ') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <th colspan="4" class="text-end">Total réglé (dépenses)</th>
                                                <th class="text-end text-success">
                                                    {{ number_format($stats['total_regle_depenses'], 0, ',', ' ') }}</th>
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

        {{-- Modal modification --}}
        <div class="modal fade" id="editFournisseurModal" tabindex="-1" aria-labelledby="editFournisseurModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFournisseurModalLabel">Modifier le fournisseur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">X</button>
                    </div>
                    <form action="{{ route('module_fournisseur.update', $fournisseur->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Désignation <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="assujeti"
                                        value="{{ $fournisseur->assujeti }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                    <select name="categories_id" class="form-select" required>
                                        @php
                                            $categories = \App\Models\Categorie::all();
                                        @endphp
                                        @foreach ($categories as $categorie)
                                            <option value="{{ $categorie->id }}"
                                                {{ $fournisseur->categories_id == $categorie->id ? 'selected' : '' }}>
                                                {{ $categorie->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Adresse postale</label>
                                    <input type="text" class="form-control" name="adresse"
                                        value="{{ $fournisseur->adresse }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adresse (localisation)</label>
                                    <input type="text" class="form-control" name="adresse_localisation"
                                        value="{{ $fournisseur->adresse_localisation }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Téléphone</label>
                                    <input type="text" class="form-control" name="telephone"
                                        value="{{ $fournisseur->telephone }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">IFU</label>
                                    <input type="text" class="form-control" name="ifu"
                                        value="{{ $fournisseur->ifu }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RCCM</label>
                                    <input type="text" class="form-control" name="rccm"
                                        value="{{ $fournisseur->rccm }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Division fiscale</label>
                                    <input type="text" class="form-control" name="division_fiscal"
                                        value="{{ $fournisseur->division_fiscal }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Régime fiscal</label>
                                    <input type="text" class="form-control" name="regime_fiscal"
                                        value="{{ $fournisseur->regime_fiscal }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Adresse cadastrale</label>
                                    <input type="text" class="form-control" name="adresse_cadas"
                                        value="{{ $fournisseur->adresse_cadas }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fa fa-times"></i>&nbsp; Annuler
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i>&nbsp; Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
@endsection
