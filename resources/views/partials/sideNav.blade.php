<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sidenav shadow-right sidenav-light">
            <div class="sidenav-menu">
                <div class="nav accordion" id="accordionSidenav">

                    <a title="Tableau de bord" class="nav-link collapsed mt-3" href="{{ route('dashboard') }}">
                        <div class="nav-link-icon"><i data-feather="grid"></i></div>
                        Tableau de bord
                    </a>

                    <div class="sidenav-menu-heading text-center text-danger">Pages</div>

                    <a title="Administration" class="nav-link collapsed" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapsePagesAdmin" aria-expanded="false"
                        aria-controls="collapsePagesAD">
                        <div class="nav-link-icon"><i data-feather="lock"></i></div>
                        Administration
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePagesAdmin" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            @if (in_array(Auth::user()->role, ['administrateur', 'superviseur']))
                                <a class="nav-link" href="{{ route('module_utilisateur.index') }}">
                                    <div class="nav-link-icon"><i data-feather="users"></i></div>
                                    Utilisateurs
                                </a>
                            @endif
                            <a class="nav-link" href="{{ route('module_entete.index') }}">
                                <div class="nav-link-icon"><i data-feather="layout"></i></div>
                                Entête
                            </a>
                        </nav>
                    </div>

                    <a title="Structure" class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse"
                        data-bs-target="#collapsePagesPara" aria-expanded="false" aria-controls="collapsePagesPA">
                        <div class="nav-link-icon"><i data-feather="layers"></i></div>
                        Structure
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePagesPara" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="{{ route('module_produit.index') }}">
                                <div class="nav-link-icon"><i data-feather="package"></i></div>
                                Produits
                            </a>
                            <a class="nav-link" href="{{ route('module_categorie.index') }}">
                                <div class="nav-link-icon"><i data-feather="tag"></i></div>
                                Type de Client
                            </a>
                            <a class="nav-link" href="{{ route('module_contribuable.index') }}">
                                <div class="nav-link-icon"><i data-feather="briefcase"></i></div>
                                Clients
                            </a>
                            <a class="nav-link" href="{{ route('module_fournisseur.index') }}">
                                <div class="nav-link-icon"><i data-feather="shopping-bag"></i></div>
                                Fournisseurs
                            </a>
                            <a class="nav-link" href="{{ route('module_tva.index') }}">
                                <div class="nav-link-icon"><i data-feather="percent"></i></div>
                                Groupe de TVA
                            </a>
                            <a class="nav-link" href="{{ route('module_bic.index') }}">
                                <div class="nav-link-icon"><i data-feather="hash"></i></div>
                                PSVB
                            </a>
                            <a class="nav-link" href="{{ route('gestion_service.index') }}">
                                <div class="nav-link-icon"><i data-feather="archive"></i></div>
                                Dépôts
                            </a>
                            <a class="nav-link" href="{{ route('module_typefacture.index') }}">
                                <div class="nav-link-icon"><i data-feather="file"></i></div>
                                Type de facture
                            </a>
                            <a class="nav-link" href="{{ route('module_modepaie.index') }}">
                                <div class="nav-link-icon"><i data-feather="dollar-sign"></i></div>
                                Mode paie
                            </a>
                        </nav>
                    </div>

                    <div class="sidenav-menu-heading text-center text-danger">FACTURATION</div>

                    <a title="Factures clients" class="nav-link collapsed" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapsePages" aria-expanded="false"
                        aria-controls="collapsePagesST">
                        <div class="nav-link-icon"><i data-feather="file-plus"></i></div>
                        Factures clients
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePages" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="{{ route('module_facture_proforma.index') }}">
                                <div class="nav-link-icon"><i data-feather="file-text"></i></div>
                                Facture proforma
                            </a>
                            <a class="nav-link" href="{{ route('module_ordre_facture.index') }}">
                                <div class="nav-link-icon"><i data-feather="check-square"></i></div>
                                Facture de vente
                            </a>
                            <a class="nav-link" href="{{ route('module_facture_avoir.index') }}">
                                <div class="nav-link-icon"><i data-feather="rotate-ccw"></i></div>
                                Factures d'avoir
                            </a>
                            <a class="nav-link" href="{{ route('module_bordereau_livraison.index') }}">
                                <div class="nav-link-icon"><i data-feather="truck"></i></div>
                                Bordereau de livraison
                            </a>
                            <a class="nav-link" href="{{ route('module_reglement.index') }}">
                                <div class="nav-link-icon"><i data-feather="check-circle"></i></div>
                                Règlements factures
                            </a>
                        </nav>
                    </div>

                    <a title="Factures fournisseurs" class="nav-link collapsed" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapsePagesFournisseurs" aria-expanded="false"
                        aria-controls="collapsePagesFournisseurs">
                        <div class="nav-link-icon"><i data-feather="file-minus"></i></div>
                        Factures fournisseurs
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePagesFournisseurs" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="{{ route('module_facture_fournisseur.index') }}">
                                <div class="nav-link-icon"><i data-feather="edit-3"></i></div>
                                Saisie Facture
                            </a>
                            <a class="nav-link" href="{{ route('module_reglement_fournisseur.index') }}">
                                <div class="nav-link-icon"><i data-feather="check-circle"></i></div>
                                Règlement facture
                            </a>
                        </nav>
                    </div>

                    <a title="Dépenses" class="nav-link collapsed" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#collapsePagesDP" aria-expanded="false"
                        aria-controls="collapsePagesDP">
                        <div class="nav-link-icon"><i data-feather="credit-card"></i></div>
                        Dépenses
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePagesDP" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="{{ route('module_depense.index') }}">
                                <div class="nav-link-icon"><i data-feather="minus-circle"></i></div>
                                Saisi de dépense
                            </a>
                            <a class="nav-link" href="{{ route('reglement_depense.index') }}">
                                <div class="nav-link-icon"><i data-feather="check-circle"></i></div>
                                Règlement de dépense
                            </a>
                        </nav>
                    </div>

                    <a title="Etats facturation" class="nav-link collapsed" href="javascript:void(0);"
                        data-bs-toggle="collapse" data-bs-target="#pagesCollapseErrorEBS" aria-expanded="false"
                        aria-controls="pagesCollapseError">
                        <div class="nav-link-icon"><i data-feather="pie-chart"></i></div>
                        Etats facturation
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="pagesCollapseErrorEBS" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav">
                            <a class="nav-link" href="{{ route('solde_client') }}">
                                <div class="nav-link-icon"><i data-feather="user"></i></div>
                                Solde client
                            </a>
                            <a class="nav-link" href="{{ route('etat_taxe') }}">
                                <div class="nav-link-icon"><i data-feather="percent"></i></div>
                                Etat des taxes
                            </a>
                            <a class="nav-link" href="{{ route('etat_facture_metier') }}">
                                <div class="nav-link-icon"><i data-feather="bar-chart-2"></i></div>
                                Etat facture par Categorie
                            </a>
                            <a class="nav-link" href="{{ route('etat_facture_service') }}">
                                <div class="nav-link-icon"><i data-feather="archive"></i></div>
                                Etat facture par depot
                            </a>
                        </nav>
                    </div>

                    {{--
                        Facturation Comptoir (POS) — always listed in the side panel.
                        Routes stay behind EnsureComptoirEnabled; nav uses named routes when registered.
                    --}}
                    <div class="sidenav-menu-heading text-center text-danger">FACTURE COMPTOIR</div>
                    @if (\Illuminate\Support\Facades\Route::has('module_comptoir.pos'))
                        @if (\Illuminate\Support\Facades\Route::has('module_comptoir.dashboard') && Auth::user()->isSuperviseurComptoir())
                            <a title="Tableau de bord" class="nav-link"
                                href="{{ route('module_comptoir.dashboard') }}">
                                <div class="nav-link-icon"><i data-feather="trending-up"></i></div>
                                Tableau de bord
                            </a>
                        @endif
                        <a title="Point de vente (POS)" class="nav-link" href="{{ route('module_comptoir.pos') }}">
                            <div class="nav-link-icon"><i data-feather="shopping-cart"></i></div>
                            Point de vente
                        </a>
                        <a title="Ouverture de caisse" class="nav-link"
                            href="{{ route('module_comptoir.ouvrir.form') }}">
                            <div class="nav-link-icon"><i data-feather="unlock"></i></div>
                            Ouverture de caisse
                        </a>
                        <a title="État des ventes" class="nav-link"
                            href="{{ route('module_comptoir.tickets.index') }}">
                            <div class="nav-link-icon"><i data-feather="list"></i></div>
                            État des ventes
                        </a>
                        
                        <a title="Caisses" class="nav-link" href="{{ route('module_caisse.index') }}">
                            <div class="nav-link-icon"><i data-feather="box"></i></div>
                            Caisses
                        </a>
                    @else
                        <div class="nav-link text-muted small px-3">Module comptoir non chargé (routes absentes)</div>
                    @endif

                    <div class="sidenav-menu-heading text-center text-danger">GESTION DE STOCK</div>

                    <a title="Entrée en stock" class="nav-link"
                        href="{{ route('gestion_stock.index', ['open' => 'entree']) }}">
                        <div class="nav-link-icon"><i data-feather="arrow-up"></i></div>
                        Entrée en stock
                    </a>

                    <a title="Sortie de stock" class="nav-link"
                        href="{{ route('gestion_stock.index', ['open' => 'sortie']) }}">
                        <div class="nav-link-icon"><i data-feather="arrow-down"></i></div>
                        Sortie de stock
                    </a>

                    <a title="Inventaire" class="nav-link"
                        href="{{ route('gestion_stock.index', ['open' => 'inventaire']) }}">
                        <div class="nav-link-icon"><i data-feather="clipboard"></i></div>
                        Inventaire
                    </a>

                    <a title="Transferts inter-dépôts" class="nav-link"
                        href="{{ route('gestion_transfert.index') }}">
                        <div class="nav-link-icon"><i data-feather="swap-horizontal"></i></div>
                        Transferts inter-dépôts
                    </a>

                    <div class="sidenav-menu-heading text-center text-danger">Outils</div>

                    <a title="Boîtier fiscal SFE" class="nav-link collapsed" href="{{ route('mcf.index') }}">
                        <div class="nav-link-icon"><i data-feather="cpu"></i></div>
                        Boîtier fiscal SFE
                    </a>
                    <a title="Paramètres" class="nav-link collapsed" href="{{ route('parametres.index') }}">
                        <div class="nav-link-icon"><i data-feather="settings"></i></div>
                        Paramètres
                    </a>

                    <a title="Aide &amp; Support" class="nav-link collapsed" href="{{ route('aide_support') }}">
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
