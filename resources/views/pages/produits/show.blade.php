@extends('layouts.master')

@section('title')
    <title>SIG - CFE | {{ $item->code }} — {{ $item->designation }}</title>
@endsection

@section('style')
    @include('partials.style')
    <style>
        .info-block { padding: 1rem 0; }
        .info-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #8492a6;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 1rem;
            color: #1a2942;
            font-weight: 500;
        }
        .info-value.large { font-size: 1.2rem; font-weight: 700; }
        .divider-v {
            border-left: 1px solid #e5e9ec;
        }
        .stat-card {
            border-radius: 12px;
            padding: 1.4rem 1.6rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            right: -20px; bottom: -20px;
            width: 100px; height: 100px;
            background: rgba(255,255,255,.1);
            border-radius: 50%;
        }
        .stat-card .sc-icon { font-size: 2rem; opacity: .8; margin-bottom: .5rem; }
        .stat-card .sc-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1.2px; opacity: .9; margin-bottom: .2rem; }
        .stat-card .sc-value { font-size: 1.5rem; font-weight: 800; line-height: 1.1; }
        .stat-card .sc-sub { font-size: 11px; opacity: .8; margin-top: .4rem; }
        .bg-ventes { background: linear-gradient(135deg, #10b981, #047857); }
        .bg-achats { background: linear-gradient(135deg, #3b82f6, #1e40af); }
        .bg-stock  { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
        .hero-band {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            border-radius: 14px 14px 0 0;
            padding: 1.8rem 2rem;
            color: #fff;
        }
        .hero-band .prod-code {
            font-size: 11px; text-transform: uppercase; letter-spacing: 2px;
            opacity: .8; margin-bottom: 4px;
        }
        .hero-band .prod-name { font-size: 1.6rem; font-weight: 800; }
        .hero-band .prod-sub  { font-size: .9rem; opacity: .85; margin-top: 4px; }
    </style>
@endsection

@section('content')
<main>
    <x-page-header icon="package" title="Fiche produit"></x-page-header>

    <div class="container-xl px-4 mt-n10" style="margin-bottom:8rem;">

        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- ─── CARTE PRINCIPALE ────────────────────────────────────── --}}
        <div class="card mb-4" style="border:none;box-shadow:0 4px 24px rgba(30,64,175,.1);overflow:hidden;">

            {{-- Hero --}}
            <div class="hero-band">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="prod-code">Référence produit</div>
                        <div class="prod-name">{{ $item->designation }}</div>
                        <div class="prod-sub">
                            Code : <strong>{{ $item->code }}</strong>
                            @if ($item->unite) &nbsp;·&nbsp; Unité : <strong>{{ $item->unite }}</strong> @endif
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-2">
                        {{-- Badge famille produit --}}
                        @if ($item->famille_produit)
                            @php $familles = ['LOCBIE'=>'Bien Local','IMPBIE'=>'Bien importé','LOCSER'=>'Service Local','IMPSER'=>'Service Import']; @endphp
                            <span class="badge" style="background:rgba(255,255,255,.15);font-size:.8rem;padding:4px 10px;">
                                {{ $item->famille_produit }} — {{ $familles[$item->famille_produit] ?? '' }}
                            </span>
                        @endif
                        {{-- Boutons actions --}}
                        <div class="d-flex gap-2 mt-1">
                            <button class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);"
                                data-bs-toggle="modal" data-bs-target="#editProduitModal{{ $item->id }}">
                                <i class="fa fa-edit me-1"></i> Modifier
                            </button>
                            <button class="btn btn-sm" style="background:rgba(239,68,68,.5);color:#fff;border:none;"
                                data-bs-toggle="modal" data-bs-target="#deleteProduitModal{{ $item->id }}">
                                <i class="fa fa-trash me-1"></i> Supprimer
                            </button>
                            <a href="{{ route('module_produit.index') }}" class="btn btn-sm"
                                style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);">
                                <i class="fa fa-arrow-left me-1"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Corps des infos --}}
            <div class="card-body px-0 pb-0">

                {{-- ── Ligne 1 : KPI stock + prix ─────────────────── --}}
                <div class="row gx-0 text-center border-bottom">
                    @php
                        $qte      = (float)($item->qte ?? 0);
                        $taux     = $item->tva ? (float)$item->tva->taux : 0;
                        $tauxBic  = $item->bic ? (float)$item->bic->taux : 0;
                        $ht       = (float)($item->prix_unitaire ?? 0);
                        $tvaAmt   = $ht * $taux / 100;
                        $ttc      = $ht + $tvaAmt;
                        $bicAmt   = $ttc * $tauxBic / 100;
                        $ttcBic   = $ttc + $bicAmt;
                    @endphp

                    <div class="col-md-3 py-3 px-4">
                        <div class="info-label"><i class="fas fa-boxes me-1"></i>Stock disponible</div>
                        @if ($qte <= 0)
                            <div class="info-value large text-danger">{{ number_format($qte, 2, ',', ' ') }}</div>
                            <small class="text-danger">Rupture de stock</small>
                        @elseif ($qte <= 5)
                            <div class="info-value large text-warning">{{ number_format($qte, 2, ',', ' ') }}</div>
                            <small class="text-warning">Stock faible</small>
                        @else
                            <div class="info-value large text-success">{{ number_format($qte, 2, ',', ' ') }}</div>
                            <small class="text-muted">{{ $item->unite }}</small>
                        @endif
                    </div>

                    <div class="col-md-3 py-3 px-4 divider-v">
                        <div class="info-label"><i class="fas fa-tag me-1"></i>Prix HT</div>
                        <div class="info-value large" style="color:var(--primary-color)">
                            {{ number_format($ht, 2, ',', ' ') }}
                        </div>
                        <small class="text-muted">FCFA</small>
                    </div>

                    <div class="col-md-3 py-3 px-4 divider-v">
                        <div class="info-label"><i class="fas fa-percent me-1"></i>TVA
                            @if ($item->tva) ({{ $taux }} %) @endif
                        </div>
                        <div class="info-value large text-info">{{ number_format($tvaAmt, 2, ',', ' ') }}</div>
                        <small class="text-muted">{{ $item->tva ? $item->tva->intitule : '—' }}</small>
                    </div>

                    <div class="col-md-3 py-3 px-4 divider-v">
                        <div class="info-label"><i class="fas fa-calculator me-1"></i>Prix TTC + PSVBB</div>
                        <div class="info-value large text-success">{{ number_format($ttcBic, 2, ',', ' ') }}</div>
                        <small class="text-muted">FCFA</small>
                    </div>
                </div>

                {{-- ── Ligne 2 : Infos complémentaires ────────────── --}}
                <div class="row gx-0 border-bottom px-3 py-3">

                    <div class="col-md-3 info-block px-3">
                        <div class="info-label">PSVBB</div>
                        <div class="info-value">
                            @if ($item->bic)
                                {{ $item->bic->intitule }} — {{ $tauxBic }} %
                                <small class="text-muted">({{ number_format($bicAmt, 2, ',', ' ') }} FCFA)</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3 info-block px-3 divider-v">
                        <div class="info-label">Taxe spécifique (TS)</div>
                        <div class="info-value">
                            @if ($item->montant_ts)
                                {{ number_format($item->montant_ts, 2, ',', ' ') }} FCFA
                                @if ($item->description_ts)
                                    <br><small class="text-muted">{{ $item->description_ts }}</small>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-2 info-block px-3 divider-v">
                        <div class="info-label">Type du produit</div>
                        <div class="info-value">
                            @if ($item->famille_produit)
                                @php $familles = ['LOCBIE'=>'Bien Local','IMPBIE'=>'Bien importé','LOCSER'=>'Service Local','IMPSER'=>'Service Import']; @endphp
                                {{ $item->famille_produit }}
                                <br><small class="text-muted">{{ $familles[$item->famille_produit] ?? '' }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 info-block px-3 divider-v">
                        <div class="info-label">Description</div>
                        <div class="info-value">{{ $item->description ?: '—' }}</div>
                    </div>
                </div>

                {{-- ── Pied : métadonnées ──────────────────────────── --}}
                <div class="px-4 py-2 d-flex gap-4 text-muted" style="font-size:.82rem;background:#f8f9fb;">
                    <span><i class="fas fa-calendar-plus me-1"></i>Créé le {{ $item->created_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    <span><i class="fas fa-calendar-check me-1"></i>Modifié le {{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- ─── SECTION STATISTIQUES VENTES & ACHATS ───────────────── --}}
        <div class="row gx-4 mb-4">

            {{-- Ventes --}}
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card h-100" style="border:none;box-shadow:0 4px 24px rgba(16,185,129,.12);overflow:hidden;">
                    <div class="card-header" style="background:linear-gradient(135deg,#10b981,#047857);color:#fff;border:none;padding:1rem 1.5rem;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-up-right-dots fa-lg"></i>
                            <span class="fw-bold fs-6">Statistiques Ventes (clients)</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="row gx-0 text-center">
                            <div class="col-4 py-4 px-2">
                                <div class="info-label">Factures</div>
                                <div style="font-size:1.8rem;font-weight:800;color:#047857;">{{ $statsVentes['nb_factures'] }}</div>
                                <small class="text-muted">concernées</small>
                            </div>
                            <div class="col-4 py-4 px-2 divider-v">
                                <div class="info-label">Qté vendue</div>
                                <div style="font-size:1.8rem;font-weight:800;color:#047857;">
                                    {{ number_format($statsVentes['qte_vendue'], 2, ',', ' ') }}
                                </div>
                                <small class="text-muted">{{ $item->unite }}</small>
                            </div>
                            <div class="col-4 py-4 px-2 divider-v">
                                <div class="info-label">Total HT</div>
                                <div style="font-size:1.3rem;font-weight:800;color:#047857;">
                                    {{ number_format($statsVentes['montant_ht'], 0, ',', ' ') }}
                                </div>
                                <small class="text-muted">FCFA</small>
                            </div>
                        </div>
                        @if ($statsVentes['nb_factures'] === 0)
                            <div class="text-center text-muted py-3 border-top" style="font-size:.85rem;">
                                <i class="fas fa-inbox me-1"></i> Aucune vente enregistrée pour ce produit
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Achats --}}
            <div class="col-md-6">
                <div class="card h-100" style="border:none;box-shadow:0 4px 24px rgba(59,130,246,.12);overflow:hidden;">
                    <div class="card-header" style="background:linear-gradient(135deg,#3b82f6,#1e40af);color:#fff;border:none;padding:1rem 1.5rem;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-down-to-line fa-lg"></i>
                            <span class="fw-bold fs-6">Statistiques Achats (fournisseurs)</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="row gx-0 text-center">
                            <div class="col-4 py-4 px-2">
                                <div class="info-label">Factures</div>
                                <div style="font-size:1.8rem;font-weight:800;color:#1e40af;">{{ $statsAchats['nb_factures'] }}</div>
                                <small class="text-muted">concernées</small>
                            </div>
                            <div class="col-4 py-4 px-2 divider-v">
                                <div class="info-label">Qté achetée</div>
                                <div style="font-size:1.8rem;font-weight:800;color:#1e40af;">
                                    {{ number_format($statsAchats['qte_achetee'], 2, ',', ' ') }}
                                </div>
                                <small class="text-muted">{{ $item->unite }}</small>
                            </div>
                            <div class="col-4 py-4 px-2 divider-v">
                                <div class="info-label">Total HT</div>
                                <div style="font-size:1.3rem;font-weight:800;color:#1e40af;">
                                    {{ number_format($statsAchats['montant_ht'], 0, ',', ' ') }}
                                </div>
                                <small class="text-muted">FCFA</small>
                            </div>
                        </div>
                        @if ($statsAchats['nb_factures'] === 0)
                            <div class="text-center text-muted py-3 border-top" style="font-size:.85rem;">
                                <i class="fas fa-inbox me-1"></i> Aucun achat enregistré pour ce produit
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ─── MODAL MODIFICATION ──────────────────────────────────────── --}}
    <div class="modal fade" id="editProduitModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier — {{ $item->designation }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('module_produit.update', [$item->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1">Code</label>
                                <input type="text" class="form-control" name="code" value="{{ old('code', $item->code) }}" required>
                            </div>
                            <div class="col-md-5">
                                <label class="small mb-1">Désignation</label>
                                <input type="text" class="form-control" name="designation" value="{{ old('designation', $item->designation) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1">Unité</label>
                                <input type="text" class="form-control" name="unite" value="{{ old('unite', $item->unite) }}">
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-3">
                                <label class="small mb-1">Prix unitaire</label>
                                <input type="number" min="0" step="0.01" class="form-control" name="prix_unitaire" value="{{ old('prix_unitaire', $item->prix_unitaire) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1">Stock</label>
                                <input type="number" min="0" step="0.01" class="form-control" name="qte" value="{{ old('qte', $item->qte) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1">TVA</label>
                                <select class="form-select" name="tva_id">
                                    <option value="">-- Aucune --</option>
                                    @foreach ($tvas as $tva)
                                        <option value="{{ $tva->id }}" {{ old('tva_id', $item->tva_id) == $tva->id ? 'selected' : '' }}>
                                            {{ $tva->intitule }} ({{ $tva->taux }} %)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small mb-1">PSVBB</label>
                                <select class="form-select" name="bic_id">
                                    <option value="">-- Aucun --</option>
                                    @foreach ($bics as $bic)
                                        <option value="{{ $bic->id }}" {{ old('bic_id', $item->bic_id) == $bic->id ? 'selected' : '' }}>
                                            {{ $bic->intitule ?? $bic->code }} ({{ $bic->taux }} %)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-4">
                                <label class="small mb-1">Montant TS</label>
                                <input type="number" min="0" step="0.01" class="form-control" name="montant_ts" value="{{ old('montant_ts', $item->montant_ts) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="small mb-1">Description TS</label>
                                <input type="text" class="form-control" name="description_ts" value="{{ old('description_ts', $item->description_ts) }}" placeholder="Description de la taxe spécifique">
                            </div>
                        </div>

                        <div class="row gx-3 mb-3">
                            <div class="col-md-12">
                                <label class="small mb-1">Type produit</label>
                                <select name="famille_produit" class="form-select">
                                    <option value="">-- Sélectionner --</option>
                                    <option value="LOCBIE" {{ old('famille_produit', $item->famille_produit) == 'LOCBIE' ? 'selected' : '' }}>LOCBIE — Bien Local</option>
                                    <option value="IMPBIE" {{ old('famille_produit', $item->famille_produit) == 'IMPBIE' ? 'selected' : '' }}>IMPBIE — Bien importé</option>
                                    <option value="LOCSER" {{ old('famille_produit', $item->famille_produit) == 'LOCSER' ? 'selected' : '' }}>LOCSER — Service Local</option>
                                    <option value="IMPSER" {{ old('famille_produit', $item->famille_produit) == 'IMPSER' ? 'selected' : '' }}>IMPSER — Service Import</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="small mb-1">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $item->description) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-1">
                            <i class="fas fa-save me-1"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── MODAL SUPPRESSION ───────────────────────────────────────── --}}
    <div class="modal fade" id="deleteProduitModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmation de suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <p>Supprimer le produit <strong>{{ $item->designation }}</strong> ?</p>
                    <p class="text-muted small">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('module_produit.destroy', [$item->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Supprimer
                        </button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
