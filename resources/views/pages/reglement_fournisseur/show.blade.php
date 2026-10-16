@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Règlement {{ $reglement->code }}</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        :root {
            --primary-color: #315192;
            --text-light: #6c757d;
        }

        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 1.05rem;
            color: #212529;
            margin-bottom: 18px;
        }

        .section-title {
            border-bottom: 2px solid #315192;
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .amount-card {
            background: linear-gradient(135deg, #315192 0%, #1a2942 100%);
            color: #fff;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(49, 81, 146, .25);
        }

        .amount-card .label {
            font-size: .85rem;
            text-transform: uppercase;
            opacity: .85;
            margin-bottom: 8px;
        }

        .amount-card .value {
            font-size: 1.9rem;
            font-weight: 700;
        }

        .badge-mode {
            font-size: .9rem;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 8px;
            background: #e0e7ff;
            color: #3730a3;
        }

        .badge-statut {
            font-size: .85rem;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .badge-soldée    { background:#d1fae5; color:#065f46; }
        .badge-partielle { background:#fef3c7; color:#92400e; }
        .badge-impayée   { background:#fee2e2; color:#991b1b; }

        @media print {
            .no-print { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
@endsection

@section('content')
    <main>
        <header class="page-header page-header-dark header-gradient pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="dollar-sign"></i></div>
                                Règlement fournisseur {{ $reglement->code }}
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10" style="margin-bottom: 6rem;">

            {{-- Action bar --}}
            <div class="card mb-4 no-print">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('module_reglement_fournisseur.index') }}" class="btn btn-light btn-sm">
                        <i data-feather="arrow-left"></i> Retour
                    </a>
                    <div>
                        <a href="{{ route('module_facture_fournisseur.show', $reglement->facture_fournisseur_id) }}" class="btn btn-info btn-sm">
                            <i data-feather="file-text"></i>&nbsp; Voir la facture
                        </a>
                        <a href="{{ route('print_reglement_fournisseur', $reglement->id) }}" target="_blank" class="btn btn-success btn-sm">
                            <i data-feather="printer"></i>&nbsp; Imprimer le reçu
                        </a>
                        <a href="{{ route('supp_reglement_fournisseur', $reglement->id) }}" class="btn btn-danger btn-sm"
                            onclick="return confirm('Supprimer ce règlement ?')">
                            <i data-feather="trash-2"></i>&nbsp; Supprimer
                        </a>
                    </div>
                </div>
            </div>

            {{-- Montant --}}
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="amount-card">
                        <div class="label">Montant réglé</div>
                        <div class="value">{{ number_format($reglement->montant, 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <div class="info-label">Mode de paiement</div>
                                    <div><span class="badge-mode">{{ $reglement->mode }}</span></div>
                                </div>
                                <div class="col-6">
                                    <div class="info-label">Date du règlement</div>
                                    <div class="info-value mb-0">
                                        <strong>{{ \Carbon\Carbon::parse($reglement->date)->format('d/m/Y') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informations du règlement --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">Informations du règlement</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-label">Code règlement</div>
                            <div class="info-value"><strong style="color:var(--primary-color);">{{ $reglement->code }}</strong></div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Référence paiement</div>
                            <div class="info-value">{{ $reglement->reference_paiement ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-label">Enregistré par</div>
                            <div class="info-value">{{ optional($reglement->User)->login ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="info-label">Observation</div>
                            <div class="info-value">
                                @if ($reglement->observation)
                                    {{ $reglement->observation }}
                                @else
                                    <span class="text-muted">Aucune observation</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Facture fournisseur associée --}}
            @php
                $facture = $reglement->FactureFournisseur;
            @endphp
            @if ($facture)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="section-title mb-0">Facture fournisseur associée</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-label">N° facture</div>
                                <div class="info-value">
                                    <a href="{{ route('module_facture_fournisseur.show', $facture->id) }}">
                                        <strong>{{ $facture->code }}</strong>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Date facture</div>
                                <div class="info-value">{{ \Carbon\Carbon::parse($facture->date)->format('d/m/Y') }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Référence</div>
                                <div class="info-value">{{ $facture->reference ?? '—' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Fournisseur</div>
                                <div class="info-value">
                                    <strong>{{ optional($facture->Fournisseur)->assujeti ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>

                        <hr>

                        @php
                            $total = $facture->montant_total;
                            $paye = $facture->montant_paye;
                            $reste = $facture->reste_a_payer;
                            $statutP = $facture->statut_paiement;
                        @endphp
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="info-label">Total facture</div>
                                <div class="info-value"><strong>{{ number_format($total, 0, ',', ' ') }} FCFA</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Total payé</div>
                                <div class="info-value text-success"><strong>{{ number_format($paye, 0, ',', ' ') }} FCFA</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Reste à payer</div>
                                <div class="info-value text-danger"><strong>{{ number_format($reste, 0, ',', ' ') }} FCFA</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-label">Statut</div>
                                <div class="info-value">
                                    @if ($statutP === 'soldée')
                                        <span class="badge-statut badge-soldée">Soldée</span>
                                    @elseif ($statutP === 'partielle')
                                        <span class="badge-statut badge-partielle">Partielle</span>
                                    @else
                                        <span class="badge-statut badge-impayée">Impayée</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Historique des paiements de la facture --}}
            @if ($facture)
                @php
                    $historique = $facture->ReglementFournisseurs()->orderByDesc('date')->orderByDesc('id')->get();
                @endphp
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="section-title mb-0">
                            Historique des paiements ({{ $historique->count() }})
                        </h5>
                        @if ($reste > 0.009)
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#solderFactureModal">
                                <i class="fa fa-check-double"></i>&nbsp; Solder la facture
                            </button>
                        @else
                            <span class="badge-statut badge-soldée">Facture soldée</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th class="text-end">Montant</th>
                                        <th>Référence</th>
                                        <th>Enregistré par</th>
                                        <th style="width:140px; text-align:center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historique as $h)
                                        <tr @if ($h->id == $reglement->id) style="background:#eef2ff;" @endif>
                                            <td>
                                                <strong>{{ $h->code }}</strong>
                                                @if ($h->id == $reglement->id)
                                                    <span class="badge bg-primary ms-1">en cours</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($h->date)->format('d/m/Y') }}</td>
                                            <td><span class="badge-mode">{{ $h->mode }}</span></td>
                                            <td class="text-end"><strong>{{ number_format($h->montant, 0, ',', ' ') }} FCFA</strong></td>
                                            <td>{{ $h->reference_paiement ?? '—' }}</td>
                                            <td>{{ optional($h->User)->login ?? '—' }}</td>
                                            <td class="text-center">
                                                @if ($h->id != $reglement->id)
                                                    <a href="{{ route('module_reglement_fournisseur.show', $h->id) }}"
                                                        class="btn btn-sm btn-info" title="Détails"
                                                        style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('print_reglement_fournisseur', $h->id) }}" target="_blank"
                                                    class="btn btn-sm btn-success" title="Imprimer le reçu"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a href="{{ route('supp_reglement_fournisseur', $h->id) }}"
                                                    class="btn btn-sm btn-danger" title="Supprimer"
                                                    onclick="return confirm('Supprimer ce règlement ?')"
                                                    style="border-radius:50%;width:32px;height:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total réglé :</strong></td>
                                        <td class="text-end"><strong class="text-success">{{ number_format($paye, 0, ',', ' ') }} FCFA</strong></td>
                                        <td colspan="3"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Reste à payer :</strong></td>
                                        <td class="text-end"><strong class="text-danger">{{ number_format($reste, 0, ',', ' ') }} FCFA</strong></td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Modal SOLDER --}}
                @if ($reste > 0.009)
                    <div class="modal fade" id="solderFactureModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('solder_facture_fournisseur', $facture->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Solder la facture {{ $facture->code }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-success">
                                            <i class="fa fa-info-circle"></i>
                                            Reste à payer :
                                            <strong>{{ number_format($reste, 2, ',', ' ') }} FCFA</strong>.
                                            Saisissez le montant à régler (≤ reste).
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="small">Date <span class="text-danger">*</span></label>
                                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small">Mode <span class="text-danger">*</span></label>
                                                <select name="mode" class="form-select" required>
                                                    @foreach (['Espèces','Chèque','Virement','Mobile Money','Carte bancaire'] as $m)
                                                        <option value="{{ $m }}">{{ $m }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small">Montant à régler (FCFA) <span class="text-danger">*</span></label>
                                                <input type="number" name="montant" class="form-control" step="0.01" min="0.01" max="{{ $reste }}" value="{{ $reste }}" required>
                                                <small class="text-muted">Max : {{ number_format($reste, 2, ',', ' ') }}</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small">Référence paiement</label>
                                                <input type="text" name="reference_paiement" class="form-control" placeholder="N° chèque / transaction...">
                                            </div>
                                            <div class="col-12">
                                                <label class="small">Observation</label>
                                                <textarea name="observation" class="form-control" rows="2">Solde de la facture {{ $facture->code }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-check-double"></i>&nbsp; Solder
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="section-title mb-0">Informations système</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Date d'enregistrement</div>
                            <div class="info-value">{{ $reglement->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Dernière modification</div>
                            <div class="info-value">{{ $reglement->updated_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
