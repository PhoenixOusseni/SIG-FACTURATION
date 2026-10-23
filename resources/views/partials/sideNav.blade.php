<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sidenav shadow-right sidenav-light">
            <div class="sidenav-menu">
                <div class="nav accordion" id="accordionSidenav">

                    {{-- ── 1. TABLEAU DE BORD ── --}}
                    <div class="sidenav-menu-heading text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.25rem;">Général</div>
                    <a title="Tableau de bord" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <div class="nav-link-icon"><i data-feather="grid"></i></div>
                        Tableau de bord
                    </a>

                    {{-- ── 2. FACTURATION & VENTES ── --}}
                    {{-- ── 2. FACTURATION & VENTES ── --}}
                    <div class="sidenav-menu-heading text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.25rem;">Ventes & Facturation</div>

                    {{-- Hub Facturation Unifié (Entrée Principale Unique) --}}
                    @php
                        $isHubActive = request()->routeIs(['module_ordre_facture.*', 'module_facture_proforma.*', 'module_facture_avoir.*']);
                    @endphp
                    <a title="Hub de Facturation Unifié (Ventes, Proformas, Avoirs)" class="nav-link {{ $isHubActive ? 'active' : '' }}" href="{{ route('module_ordre_facture.index') }}">
                        <div class="nav-link-icon"><i data-feather="file-text"></i></div>
                        Hub Facturation
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-auto" style="font-size: 0.65rem;">Unifié</span>
                    </a>

                    {{-- Règlements & Encaissements Clients --}}
                    <a title="Règlements & Encaissements clients" class="nav-link {{ request()->routeIs('module_reglement.*') ? 'active' : '' }}" href="{{ route('module_reglement.index') }}">
                        <div class="nav-link-icon"><i data-feather="check-circle"></i></div>
                        Règlements clients
                    </a>

                    {{-- Bordereaux de livraison --}}
                    <a title="Bordereaux livraison" class="nav-link {{ request()->routeIs('module_bordereau_livraison.*') ? 'active' : '' }}" href="{{ route('module_bordereau_livraison.index') }}">
                        <div class="nav-link-icon"><i data-feather="truck"></i></div>
                        Bordereaux livraison
                    </a>

                    {{-- Facturation Comptoir (POS) --}}
                    @php
                        $isPosActive = request()->routeIs(['module_comptoir.*', 'module_caisse.*']);
                    @endphp
                    <a title="Point de Vente (POS)" class="nav-link {{ $isPosActive ? '' : 'collapsed' }}" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapsePos" aria-expanded="{{ $isPosActive ? 'true' : 'false' }}">
                        <div class="nav-link-icon"><i data-feather="shopping-cart"></i></div>
                        Vente Comptoir (POS)
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse {{ $isPosActive ? 'show' : '' }}" id="collapsePos" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            @if (\Illuminate\Support\Facades\Route::has('module_comptoir.pos'))
                                @if (\Illuminate\Support\Facades\Route::has('module_comptoir.dashboard') && Auth::user()->isSuperviseurComptoir())
                                    <a class="nav-link {{ request()->routeIs('module_comptoir.dashboard') ? 'active' : '' }}" href="{{ route('module_comptoir.dashboard') }}">
                                        <div class="nav-link-icon"><i data-feather="trending-up"></i></div>
                                        Tableau de bord POS
                                    </a>
                                @endif
                                <a class="nav-link {{ request()->routeIs('module_comptoir.pos') ? 'active' : '' }}" href="{{ route('module_comptoir.pos') }}">
                                    <div class="nav-link-icon"><i data-feather="shopping-bag"></i></div>
                                    Caisse / Point de vente
                                </a>
                                <a class="nav-link {{ request()->routeIs('module_comptoir.ouvrir.form') ? 'active' : '' }}" href="{{ route('module_comptoir.ouvrir.form') }}">
                                    <div class="nav-link-icon"><i data-feather="unlock"></i></div>
                                    Ouverture caisse
                                </a>
                                <a class="nav-link {{ request()->routeIs('module_comptoir.tickets.*') ? 'active' : '' }}" href="{{ route('module_comptoir.tickets.index') }}">
                                    <div class="nav-link-icon"><i data-feather="list"></i></div>
                                    Tickets & Ventes
                                </a>
                                <a class="nav-link {{ request()->routeIs('module_caisse.*') ? 'active' : '' }}" href="{{ route('module_caisse.index') }}">
                                    <div class="nav-link-icon"><i data-feather="box"></i></div>
                                    Gestion des Caisses
                                </a>
                            @endif
                        </nav>
                    </div>

                    {{-- Rapports & États --}}
                    @php
                        $isEtatsActive = request()->routeIs(['rapports.*', 'solde_client', 'etat_taxe', 'etat_facture_metier', 'etat_facture_service']);
                    @endphp
                    <a title="États & Rapports" class="nav-link {{ $isEtatsActive ? '' : 'collapsed' }}" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#pagesCollapseEtats" aria-expanded="{{ $isEtatsActive ? 'true' : 'false' }}">
                        <div class="nav-link-icon"><i data-feather="pie-chart"></i></div>
                        États & Rapports
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse {{ $isEtatsActive ? 'show' : '' }}" id="pagesCollapseEtats" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link {{ request()->routeIs('rapports.*') ? 'active' : '' }}" href="{{ route('rapports.index') }}">
                                <div class="nav-link-icon"><i data-feather="file-text"></i></div>
                                Rapports Fiscaux & Z
                            </a>
                            <a class="nav-link {{ request()->routeIs('solde_client') ? 'active' : '' }}" href="{{ route('solde_client') }}">
                                <div class="nav-link-icon"><i data-feather="user"></i></div>
                                Solde des clients
                            </a>
                            <a class="nav-link {{ request()->routeIs('etat_taxe') ? 'active' : '' }}" href="{{ route('etat_taxe') }}">
                                <div class="nav-link-icon"><i data-feather="percent"></i></div>
                                État des taxes (TVA)
                            </a>
                            <a class="nav-link {{ request()->routeIs('etat_facture_metier') ? 'active' : '' }}" href="{{ route('etat_facture_metier') }}">
                                <div class="nav-link-icon"><i data-feather="bar-chart-2"></i></div>
                                Factures par Catégorie
                            </a>
                            <a class="nav-link {{ request()->routeIs('etat_facture_service') ? 'active' : '' }}" href="{{ route('etat_facture_service') }}">
                                <div class="nav-link-icon"><i data-feather="archive"></i></div>
                                Factures par Dépôt
                            </a>
                        </nav>
                    </div>

                    {{-- ── 3. ACHATS & FOURNISSEURS ── --}}
                    <div class="sidenav-menu-heading text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.25rem;">Achats & Dépenses</div>

                    @php
                        $isAchatsActive = request()->routeIs(['module_facture_fournisseur.*', 'module_reglement_fournisseur.*', 'module_depense.*', 'reglement_depense.*']);
                    @endphp
                    <a title="Achats & Fournisseurs" class="nav-link {{ $isAchatsActive ? '' : 'collapsed' }}" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapsePagesAchats" aria-expanded="{{ $isAchatsActive ? 'true' : 'false' }}">
                        <div class="nav-link-icon"><i data-feather="file-minus"></i></div>
                        Achats fournisseurs
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse {{ $isAchatsActive ? 'show' : '' }}" id="collapsePagesAchats" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link {{ request()->routeIs('module_facture_fournisseur.*') ? 'active' : '' }}" href="{{ route('module_facture_fournisseur.index') }}">
                                <div class="nav-link-icon"><i data-feather="edit-3"></i></div>
                                Saisie Facture Achat
                            </a>
                            <a class="nav-link {{ request()->routeIs('module_reglement_fournisseur.*') ? 'active' : '' }}" href="{{ route('module_reglement_fournisseur.index') }}">
                                <div class="nav-link-icon"><i data-feather="check-circle"></i></div>
                                Règlements Fournisseurs
                            </a>
                            <a class="nav-link {{ request()->routeIs('module_depense.*') ? 'active' : '' }}" href="{{ route('module_depense.index') }}">
                                <div class="nav-link-icon"><i data-feather="minus-circle"></i></div>
                                Saisie de Dépenses
                            </a>
                            <a class="nav-link {{ request()->routeIs('reglement_depense.*') ? 'active' : '' }}" href="{{ route('reglement_depense.index') }}">
                                <div class="nav-link-icon"><i data-feather="dollar-sign"></i></div>
                                Règlements Dépenses
                            </a>
                        </nav>
                    </div>

                    {{-- ── 4. TIERS (CLIENTS & FOURNISSEURS) ── --}}
                    <div class="sidenav-menu-heading text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.25rem;">Tiers & Partenaires</div>

                    @php
                        $isTiersActive = request()->routeIs(['module_contribuable.*', 'module_categorie.*', 'module_fournisseur.*']);
                    @endphp
                    <a title="Tiers & Partenaires" class="nav-link {{ $isTiersActive ? '' : 'collapsed' }}" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapseTiers" aria-expanded="{{ $isTiersActive ? 'true' : 'false' }}">
                        <div class="nav-link-icon"><i data-feather="users"></i></div>
                        Clients & Fournisseurs
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse {{ $isTiersActive ? 'show' : '' }}" id="collapseTiers" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link {{ request()->routeIs('module_contribuable.*') ? 'active' : '' }}" href="{{ route('module_contribuable.index') }}">
                                <div class="nav-link-icon"><i data-feather="briefcase"></i></div>
                                Clients (Contribuables)
                            </a>
                            <a class="nav-link {{ request()->routeIs('module_categorie.*') ? 'active' : '' }}" href="{{ route('module_categorie.index') }}">
                                <div class="nav-link-icon"><i data-feather="tag"></i></div>
                                Catégories de Client
                            </a>
                            <a class="nav-link {{ request()->routeIs('module_fournisseur.*') ? 'active' : '' }}" href="{{ route('module_fournisseur.index') }}">
                                <div class="nav-link-icon"><i data-feather="shopping-bag"></i></div>
                                Fournisseurs
                            </a>
                        </nav>
                    </div>

                    {{-- ── 5. ARTICLES & STOCKS ── --}}
                    <div class="sidenav-menu-heading text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.25rem;">Articles & Stocks</div>

                    @php
                        $isStockActive = request()->routeIs(['module_produit.*', 'gestion_service.*', 'gestion_stock.*', 'gestion_transfert.*']);
                    @endphp
                    <a title="Articles & Stocks" class="nav-link {{ $isStockActive ? '' : 'collapsed' }}" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapseStock" aria-expanded="{{ $isStockActive ? 'true' : 'false' }}">
                        <div class="nav-link-icon"><i data-feather="package"></i></div>
                        Produits & Stocks
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse {{ $isStockActive ? 'show' : '' }}" id="collapseStock" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link {{ request()->routeIs('module_produit.*') ? 'active' : '' }}" href="{{ route('module_produit.index') }}">
                                <div class="nav-link-icon"><i data-feather="box"></i></div>
                                Catalogue Produits
                            </a>
                            <a class="nav-link {{ request()->routeIs('gestion_service.*') ? 'active' : '' }}" href="{{ route('gestion_service.index') }}">
                                <div class="nav-link-icon"><i data-feather="archive"></i></div>
                                Dépôts & Magasins
                            </a>
                            <a class="nav-link" href="{{ route('gestion_stock.index', ['open' => 'entree']) }}">
                                <div class="nav-link-icon"><i data-feather="arrow-up"></i></div>
                                Entrée en stock
                            </a>
                            <a class="nav-link" href="{{ route('gestion_stock.index', ['open' => 'sortie']) }}">
                                <div class="nav-link-icon"><i data-feather="arrow-down"></i></div>
                                Sortie de stock
                            </a>
                            <a class="nav-link" href="{{ route('gestion_stock.index', ['open' => 'inventaire']) }}">
                                <div class="nav-link-icon"><i data-feather="clipboard"></i></div>
                                Inventaire
                            </a>
                            <a class="nav-link {{ request()->routeIs('gestion_transfert.*') ? 'active' : '' }}" href="{{ route('gestion_transfert.index') }}">
                                <div class="nav-link-icon"><i data-feather="refresh-cw"></i></div>
                                Transferts inter-dépôts
                            </a>
                        </nav>
                    </div>

                    {{-- ── 6. DISPOSITIF FISCAL & PARAMÈTRES ── --}}
                    <div class="sidenav-menu-heading text-muted text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px; padding: 0.75rem 1rem 0.25rem;">Configuration & FEC</div>

                    {{-- Boîtier fiscal SFE --}}
                    <a title="Console SFE & Boîtier MCF" class="nav-link {{ request()->routeIs('mcf.*') ? 'active' : '' }}" href="{{ route('mcf.index') }}">
                        <div class="nav-link-icon"><i data-feather="cpu"></i></div>
                        Console SFE & Boîtier MCF
                    </a>

                    {{-- Référentiels fiscaux DGI & Facturation --}}
                    @php
                        $isReferentielsActive = request()->routeIs(['module_tva.*', 'module_bic.*', 'module_typefacture.*', 'module_modepaie.*']);
                    @endphp
                    <a title="Référentiels fiscaux" class="nav-link {{ $isReferentielsActive ? '' : 'collapsed' }}" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapseReferentiels" aria-expanded="{{ $isReferentielsActive ? 'true' : 'false' }}">
                        <div class="nav-link-icon"><i data-feather="sliders"></i></div>
                        Référentiels Fiscaux
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse {{ $isReferentielsActive ? 'show' : '' }}" id="collapseReferentiels" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link {{ request()->routeIs('module_tva.*') ? 'active' : '' }}" href="{{ route('module_tva.index') }}">
                                <div class="nav-link-icon"><i data-feather="percent"></i></div>
                                Groupes de TVA (A-P)
                            </a>
                            <a class="nav-link {{ request()->routeIs('module_bic.*') ? 'active' : '' }}" href="{{ route('module_bic.index') }}">
                                <div class="nav-link-icon"><i data-feather="hash"></i></div>
                                Catégories PSVB (DGI)
                            </a>
                            <a class="nav-link {{ request()->routeIs('module_typefacture.*') ? 'active' : '' }}" href="{{ route('module_typefacture.index') }}">
                                <div class="nav-link-icon"><i data-feather="file"></i></div>
                                Types de facture
                            </a>
                            <a class="nav-link {{ request()->routeIs('module_modepaie.*') ? 'active' : '' }}" href="{{ route('module_modepaie.index') }}">
                                <div class="nav-link-icon"><i data-feather="dollar-sign"></i></div>
                                Modes de paiement
                            </a>
                        </nav>
                    </div>

                    {{-- Configuration Société & Système --}}
                    @php
                        $isConfigActive = request()->routeIs(['module_entete.*', 'module_utilisateur.*', 'parametres.*', 'audit.*']);
                    @endphp
                    <a title="Administration" class="nav-link {{ $isConfigActive ? '' : 'collapsed' }}" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapsePagesAdmin" aria-expanded="{{ $isConfigActive ? 'true' : 'false' }}">
                        <div class="nav-link-icon"><i data-feather="settings"></i></div>
                        Paramètres Société
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse {{ $isConfigActive ? 'show' : '' }}" id="collapsePagesAdmin" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link {{ request()->routeIs('module_entete.*') ? 'active' : '' }}" href="{{ route('module_entete.index') }}">
                                <div class="nav-link-icon"><i data-feather="layout"></i></div>
                                Paramètres Entreprise
                            </a>
                            <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                                <div class="nav-link-icon"><i data-feather="shield"></i></div>
                                Journal d'Audit (DGI)
                            </a>
                            @if (in_array(Auth::user()->role, ['administrateur', 'superviseur']))
                                <a class="nav-link {{ request()->routeIs('module_utilisateur.*') ? 'active' : '' }}" href="{{ route('module_utilisateur.index') }}">
                                    <div class="nav-link-icon"><i data-feather="users"></i></div>
                                    Utilisateurs & Accès
                                </a>
                            @endif
                            <a class="nav-link {{ request()->routeIs('parametres.*') ? 'active' : '' }}" href="{{ route('parametres.index') }}">
                                <div class="nav-link-icon"><i data-feather="database"></i></div>
                                Import / Export
                            </a>
                        </nav>
                    </div>

                    {{-- Aide & Support --}}
                    <a title="Aide &amp; Support" class="nav-link {{ request()->routeIs('aide_support') ? 'active' : '' }}" href="{{ route('aide_support') }}">
                        <div class="nav-link-icon"><i data-feather="life-buoy"></i></div>
                        Aide &amp; Support
                    </a>

                </div>
            </div>
            <div class="sidenav-footer" style="background: #133e9a">
                <div class="sidenav-footer-content text-center text-light">
                    <div class="sidenav-footer-subtitle text-light">Utilisateur connecté(e):</div>
                    <div class="sidenav-footer-title">{{ Auth::user()->login }}</div>
                </div>
            </div>
        </nav>
    </div>
    @include('partials.etat_modal')

