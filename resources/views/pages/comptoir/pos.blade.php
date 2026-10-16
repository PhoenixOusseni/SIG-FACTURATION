@extends('layouts.master')

@section('title')
    <title>SIG - CFE | Point de vente (Comptoir)</title>
@endsection

@section('style')
    @include('partials.style')
    <link rel="stylesheet" href="{{ asset('css/comptoir-pos.css') }}">
@endsection

@section('content')
    @php
        $categories = $categories ?? collect();
        $tvas = $tvas ?? collect();
        $caisses = $caisses ?? collect();
    @endphp
    <main class="pos-main">
        <div class="pos-page">
            {{-- Slim session bar --}}
            <div class="pos-bar">
                <span id="posSessBadge" class="pos-pill none">…</span>
                <span class="pos-meta">Caisse <strong id="posCaisseLabel">—</strong></span>
                <span class="pos-meta">Date <strong id="posDateMetier">—</strong></span>
                <span class="pos-meta">Fond <strong id="posFond">—</strong></span>
                <span class="pos-meta">Caissier <strong id="posCaissier">—</strong></span>
                <span class="pos-meta">N° <strong id="posNextNum">—</strong></span>
                <div class="pos-bar-actions">
                    <button type="button" class="btn btn-light btn-sm" id="btnOpenSession">Ouverture</button>
                    <button type="button" class="btn btn-warning btn-sm" id="btnArret" style="display:none">Arrêt</button>
                    <button type="button" class="btn btn-info btn-sm text-white" id="btnReprise"
                        style="display:none">Reprise</button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnFermeture"
                        style="display:none">Fermeture</button>
                </div>
            </div>

            <div class="pos-shell pos-grid">
                <div class="pos-blocker" id="posBlocker">
                    <div class="pos-blocker-inner">
                        <h3>Caisse non ouverte</h3>
                        <p class="text-muted small mb-2">Ouvrez une session pour vendre (sans quitter cet écran).</p>
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenSession2">Ouvrir la caisse</button>
                    </div>
                </div>

                {{-- Products --}}
                <div class="pos-col">
                    <div class="pos-col-hd">
                        <span>Produits</span>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-light btn-sm" id="btnQuickProduct"
                                title="Nouveau produit">+ Produit</button>
                        </div>
                    </div>
                    <div class="pos-toolbar">
                        <input type="search" id="posProductSearch" class="form-control form-control-sm flex-grow-1"
                            placeholder="Code / désignation · Entrée" autocomplete="off">
                    </div>
                    <div class="pos-hint">Scanner + Entrée · F2 payer · F3 attente · +/- qty</div>
                    <div class="pos-col-bd p-0">
                        <table class="pos-prod-list">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Désignation</th>
                                    <th class="text-end">HT</th>
                                    <th class="text-end">Stk</th>
                                </tr>
                            </thead>
                            <tbody id="posProductBody">
                                <tr>
                                    <td colspan="4" class="pos-empty">Chargement…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Cart --}}
                <div class="pos-col">
                    <div class="pos-col-hd">
                        <span id="ticketCodeLabel">Nouveau ticket</span>
                    </div>
                    <div class="pos-col-bd p-0">
                        <table class="pos-cart-table">
                            <thead>
                                <tr>
                                    <th>Article</th>
                                    <th class="text-center">Qté</th>
                                    <th class="text-end">TTC</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="posCartBody">
                                <tr>
                                    <td colspan="4" class="pos-empty">Panier vide</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="pos-totals">
                        <div class="tr"><span>HT</span><span id="totHt">0</span></div>
                        <div class="tr"><span>TVA</span><span id="totTva">0</span></div>
                        <div class="tr"><span>Remise</span><span id="totRemise">0</span></div>
                        <div class="tr ttc"><span>TTC</span><span id="totTtc">0</span></div>
                    </div>
                </div>

                {{-- Meta + actions --}}
                <div class="pos-col pos-col-meta">
                    <div class="pos-col-hd">Ticket</div>
                    <div class="pos-fields">
                        <div class="pos-field">
                            <label>Client</label>
                            <div class="pos-field-row pos-client-wrap">
                                <input type="text" id="inputClientSearch" placeholder="Rechercher…" autocomplete="off">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnQuickClient"
                                    title="Nouveau client">+</button>
                                <div class="pos-client-dd" id="clientDropdown"></div>
                            </div>
                        </div>
                        <div class="pos-field">
                            <label>Client passage</label>
                            <input type="text" id="inputClientLibre" placeholder="Nom libre">
                        </div>
                        <div class="pos-field">
                            <label>Vendeur</label>
                            <select id="selectSeller"></select>
                        </div>
                        <div class="pos-field">
                            <label>Remise (FCFA)</label>
                            <input type="number" id="inputRemise" min="0" step="1" placeholder="0">
                        </div>
                        <div class="pos-field">
                            <label>Date métier</label>
                            <input type="text" id="inputDateDisplay" readonly value="—"
                                style="background:#f1f5f9">
                        </div>
                    </div>
                    <div class="pos-held-hd">En attente</div>
                    <div class="pos-held" id="posHeldList">
                        <div class="pos-empty py-2">Aucun</div>
                    </div>
                    <div class="pos-actions">
                        <button type="button" class="btn btn-pay" id="btnPay">Encaisser (F2)</button>
                        <button type="button" class="btn btn-warning btn-sm" id="btnHold">Mise en attente
                            (F3)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnNewTicket">Nouveau
                            ticket</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnCancelTicket">Annuler
                            ticket</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Pay modal ═══ --}}
        <div class="modal fade pos-modal" id="posPayModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-white">Règlement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="pos-pay-sum mb-2">
                            <span>TTC à encaisser</span>
                            <span class="ttc-big" id="payTtcLabel">0</span>
                        </div>
                        <div class="mb-1">
                            <label class="form-label mb-0">Mode de règlement</label>
                            <div class="form-control-plaintext py-0 fw-bold">Espèces</div>
                        </div>
                        <div id="payCashFields" class="mt-2">
                            <label class="form-label">Montant reçu (espèces)</label>
                            <input type="number" class="form-control" id="payRecu" min="0" step="1">
                            <div class="pos-change">
                                <span>Rendu monnaie</span>
                                <span id="payRendu">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Retour</button>
                        <button type="button" class="btn btn-success btn-sm" id="btnPayConfirm">Confirmer</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Ouverture ═══ --}}
        <div class="modal fade pos-modal" id="posOpenModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-white">Ouverture de caisse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Caisse</label>
                            <select class="form-select" id="openCaisseId">
                                @foreach ($caisses as $c)
                                    <option value="{{ $c->id }}">{{ $c->libelle }} ({{ $c->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Montant initial (comptage espèces)</label>
                            <div class="table-responsive" style="max-height: 280px;">
                                <table class="table table-sm table-bordered mb-1" id="openFondTable">
                                    <thead>
                                        <tr class="table-dark">
                                            <th>Intitulé</th>
                                            <th class="text-end">Quantité</th>
                                            <th class="text-end">Valeur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ([10000, 5000, 2000, 1000, 500, 250, 200, 100, 50, 25, 10, 5] as $d)
                                            <tr data-denom="{{ $d }}">
                                                <td>{{ number_format($d, 0, ',', ' ') }} F</td>
                                                <td>
                                                    <input type="number"
                                                        class="form-control form-control-sm text-end open-fond-qte"
                                                        min="0" step="1" value="0">
                                                </td>
                                                <td class="text-end open-fond-valeur">0</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-end">Total</th>
                                            <th class="text-end" id="openFondTotal">0</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <input type="hidden" id="openFond" value="0">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Date métier</label>
                            <input type="date" class="form-control" id="openDate" value="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <input type="text" class="form-control" id="openNotes" placeholder="Optionnel">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary btn-sm" id="btnOpenConfirm">Ouvrir</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Arrêt ═══ --}}
        <div class="modal fade pos-modal" id="posArretModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-white">Arrêt de caisse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">Clôture de l’équipe (la séquence du jour continue).</p>
                        <div class="mb-2">
                            <label class="form-label">Fond déclaré (optionnel)</label>
                            <input type="number" class="form-control" id="arretFond" min="0" step="1">
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <input type="text" class="form-control" id="arretNotes">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-warning btn-sm" id="btnArretConfirm">Confirmer
                            l’arrêt</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Fermeture ═══ --}}
        <div class="modal fade pos-modal" id="posFermetureModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-white">Fermeture de caisse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal">X</button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-2">Clôture journalière — snapshot immuable.</p>
                        <div class="mb-2">
                            <label class="form-label">Montant déclaré</label>
                            <input type="number" class="form-control" id="fermMontant" min="0" step="1">
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <input type="text" class="form-control" id="fermNotes">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger btn-sm" id="btnFermetureConfirm">Fermer la
                            caisse</button>
                    </div>
                </div>
            </div>
        </div>

        @include('partials.quick_create_modals', [
            'categories' => $categories,
            'tvas' => $tvas,
            'bics' => collect(),
        ])

        <div class="pos-toast" id="posToast"></div>
    </main>
@endsection

@section('script')
    @include('partials.script')
    <script src="{{ asset('js/quick-create.js') }}"></script>
    <script>
        window.COMPTOIR_POS = @json($posConfig);
        window.COMPTOIR_POS.autoOpen = @json(request()->boolean('ouvrir') === '1');
    </script>
    <script src="{{ asset('js/comptoir-pos.js') }}"></script>
@endsection
