<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\EnteteController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ContribuableController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FactureProformaController;
use App\Http\Controllers\FactureAvoirController;
use App\Http\Controllers\BordereauLivraisonController;
use App\Http\Controllers\ReglementController;
use App\Http\Controllers\ReglementFournisseurController;
use App\Http\Controllers\FactureFournisseurController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\TvaController;
use App\Http\Controllers\BicController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\ReglementDepenseController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransfertStockController;
use App\Http\Controllers\McfController;
use App\Http\Controllers\TypefactureController;
use App\Http\Controllers\ModePaieController;
use App\Http\Controllers\SfeController;
use App\Http\Controllers\Comptoir\CaisseController;
use App\Http\Controllers\Comptoir\SessionCaisseController;
use App\Http\Controllers\Comptoir\TicketComptoirController;
use App\Http\Controllers\Comptoir\ComptoirMetaController;
use App\Http\Controllers\Comptoir\ConversionTicketController;
use App\Http\Controllers\Comptoir\DashboardComptoirController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// For storage link
Route::get("/link", function () {
    $targetFolder = storage_path("app/public");
    $linkFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage';
    symlink($targetFolder, $linkFolder);
});

Route::get('/', [PageController::class, 'auth'])->name('login');
Route::post('authentification', [AuthController::class, 'login'])->name('auth');
Route::post('deconnexion', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [PageController::class, 'dashboard'])->name('dashboard');

    Route::resource('module_categorie', CategorieController::class);

    Route::resource('module_typefacture', TypefactureController::class);

    Route::resource('module_modepaie', ModePaieController::class);

    Route::resource('module_entete', EnteteController::class);

    Route::resource('module_contribuable', ContribuableController::class);
    Route::get('print_fiche_contribuable/{id}', [ContribuableController::class, 'print'])->name('print_fiche_contribuable');

    Route::resource('module_fournisseur', FournisseurController::class)->except(['create', 'edit']);
    Route::get('print_fiche_fournisseur/{id}', [FournisseurController::class, 'print'])->name('print_fiche_fournisseur');

    Route::resource('module_produit', ProduitController::class);

    Route::resource('module_tva', TvaController::class);

    Route::resource('module_bic', BicController::class);

    Route::resource('module_depense', DepenseController::class)->except(['create', 'edit']);
    Route::get('reglement_depense', [ReglementDepenseController::class, 'index'])->name('reglement_depense.index');
    Route::post('reglement_depense/{depenseId}', [ReglementDepenseController::class, 'store'])->name('reglement_depense.store');
    Route::get('supp_reglement_depense/{id}', [ReglementDepenseController::class, 'destroy'])->name('supp_reglement_depense');
    Route::post('solder_depense/{depenseId}', [ReglementDepenseController::class, 'solder'])->name('solder_depense');
    Route::get('print_reglement_depense/{id}', [ReglementDepenseController::class, 'printReglement'])->name('print_reglement_depense');
    Route::get('print_depense/{id}', [ReglementDepenseController::class, 'printDepense'])->name('print_depense');

    // ===== Facture Fournisseur =====
    Route::resource('module_facture_fournisseur', FactureFournisseurController::class)->except(['create']);
    Route::get('supp_facture_fournisseur/{id}', [FactureFournisseurController::class, 'destroy']);
    Route::get('print_facture_fournisseur/{id}', [FactureFournisseurController::class, 'print'])->name('print_facture_fournisseur');

    // ===== Règlements Fournisseur =====
    Route::resource('module_reglement_fournisseur', ReglementFournisseurController::class)->except(['create', 'edit']);
    Route::get('supp_reglement_fournisseur/{id}', [ReglementFournisseurController::class, 'destroy'])->name('supp_reglement_fournisseur');
    Route::get('print_reglement_fournisseur/{id}', [ReglementFournisseurController::class, 'print'])->name('print_reglement_fournisseur');
    Route::post('solder_facture_fournisseur/{factureId}', [ReglementFournisseurController::class, 'solder'])->name('solder_facture_fournisseur');

    // ===== Facture =====
    Route::resource('module_ordre_facture', FactureController::class)->except(['create']);
    Route::get('tous_odre_facture', [FactureController::class, 'all_facture'])->name('all_facture');
    Route::get('supp_ordre_facture/{id}', [FactureController::class, 'destroy']);
    Route::get('print_ordre_facture/{id}', [FactureController::class, 'print'])->name('print_ordre_facture');
    Route::get('print_bon_livraison/{id}', [FactureController::class, 'bon_livraison'])->name('print_bon_livraison');

    // ===== Facture Proforma =====
    Route::resource('module_facture_proforma', FactureProformaController::class)->except(['create']);
    Route::get('tous_facture_proforma', [FactureProformaController::class, 'all_facture'])->name('all_facture_proforma');
    Route::get('supp_facture_proforma/{id}', [FactureProformaController::class, 'destroy']);
    Route::get('print_facture_proforma/{id}', [FactureProformaController::class, 'print'])->name('print_facture_proforma');

    // ===== Facture d'Avoir =====
    Route::resource('module_facture_avoir', FactureAvoirController::class)->except(['create']);
    Route::get('tous_facture_avoir', [FactureAvoirController::class, 'all_facture'])->name('all_facture_avoir');
    Route::get('supp_facture_avoir/{id}', [FactureAvoirController::class, 'destroy']);
    Route::get('print_facture_avoir/{id}', [FactureAvoirController::class, 'print'])->name('print_facture_avoir');
    Route::post('module_facture_avoir/{id}/certifier', [FactureAvoirController::class, 'certifier'])->name('module_facture_avoir.certifier');

    // ===== Bordereau de Livraison =====
    Route::resource('module_bordereau_livraison', BordereauLivraisonController::class)->except(['create', 'edit']);
    Route::get('tous_bordereau_livraison', [BordereauLivraisonController::class, 'all_bordereau'])->name('all_bordereau_livraison');
    Route::get('bordereau_from_proforma/{id}', [BordereauLivraisonController::class, 'createFromProforma'])->name('bordereau_from_proforma');
    Route::get('facture_from_bordereau/{id}', [BordereauLivraisonController::class, 'transformToFacture'])->name('facture_from_bordereau');
    Route::get('bordereau_livre/{id}', [BordereauLivraisonController::class, 'markAsDelivered'])->name('bordereau_livre');
    Route::get('print_bordereau_livraison/{id}', [BordereauLivraisonController::class, 'print'])->name('print_bordereau_livraison');

    // ===== Règlements =====
    Route::resource('module_reglement', ReglementController::class)->except(['create', 'edit']);
    Route::get('supp_reglement/{id}', [ReglementController::class, 'destroy'])->name('supp_reglement');
    Route::get('print_reglement/{id}', [ReglementController::class, 'print'])->name('print_reglement');
    Route::post('solder_facture/{factureId}', [ReglementController::class, 'solder'])->name('solder_facture');

    Route::resource('module_utilisateur', UserController::class);
    Route::put('users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::post('change_password/{id}', [UserController::class, 'change_password']);
    Route::get('supp_user/{id}', [UserController::class, 'destroy']);

    //------------------ Gestion du personnel et des services ------------------//
    Route::resource('gestion_service', ServiceController::class);

    Route::resource('gestion_entete', EnteteController::class);

    //------------------ Etats et rapports ------------------//
    Route::get('solde_client', [PageController::class, 'solde_client'])->name('solde_client');
    Route::get('print_solde_client', [PageController::class, 'print_solde_client'])->name('print_solde_client');
    Route::get('etat_facture_metier', [PageController::class, 'etat_facture_metier'])->name('etat_facture_metier');
    Route::get('etat_facture_service', [PageController::class, 'etat_facture_service'])->name('etat_facture_service');
    Route::get('etat_taxe', [PageController::class, 'etat_taxe'])->name('etat_taxe');
    Route::get('print_etat_taxe', [PageController::class, 'print_etat_taxe'])->name('print_etat_taxe');

    Route::get('aide-support', [PageController::class, 'aide_support'])->name('aide_support');

    // ===== Gestion du Stock =====
    Route::get('gestion_stock', [StockController::class, 'index'])->name('gestion_stock.index');
    Route::post('gestion_stock/entree', [StockController::class, 'entree'])->name('gestion_stock.entree');
    Route::post('gestion_stock/sortie', [StockController::class, 'sortie'])->name('gestion_stock.sortie');
    Route::post('gestion_stock/ajustement', [StockController::class, 'ajustement'])->name('gestion_stock.ajustement');
    Route::post('gestion_stock/ajustement-groupe', [StockController::class, 'ajustementGroupe'])->name('gestion_stock.ajustement_groupe');

    // ===== Transferts de Stock =====
    Route::get('gestion_transfert', [TransfertStockController::class, 'index'])->name('gestion_transfert.index');
    Route::post('gestion_transfert', [TransfertStockController::class, 'store'])->name('gestion_transfert.store');
    Route::get('gestion_transfert/{id}', [TransfertStockController::class, 'show'])->name('gestion_transfert.show');
    Route::post('gestion_transfert/{id}/valider', [TransfertStockController::class, 'valider'])->name('gestion_transfert.valider');
    Route::post('gestion_transfert/{id}/annuler', [TransfertStockController::class, 'annuler'])->name('gestion_transfert.annuler');

    // ===== Console SFE / boîtier MCF physique =====
    Route::get('mcf', [McfController::class, 'index'])->name('mcf.index');
    Route::post('mcf/configurer', [McfController::class, 'configurer'])->name('mcf.configurer');
    Route::post('mcf/tester', [McfController::class, 'tester'])->name('mcf.tester');
    Route::post('mcf/rapport', [McfController::class, 'rapport'])->name('mcf.rapport');
    Route::get('sfe/status', [SfeController::class, 'status'])->name('sfe.status');
    Route::post('mcf/certifier/{facture}', [McfController::class, 'certifier'])->name('mcf.certifier');
    Route::post('mcf/annuler/{certification}', [McfController::class, 'annuler'])->name('mcf.annuler');

    // ===== Facturation Comptoir (POS non-MCF) =====
    Route::middleware(['comptoir.enabled'])->group(function () {
        Route::resource('module_caisse', CaisseController::class)->except(['create', 'edit']);

        Route::get('module_comptoir', [TicketComptoirController::class, 'pos'])->name('module_comptoir.pos');
        Route::get('module_comptoir/dashboard', [DashboardComptoirController::class, 'index'])->name('module_comptoir.dashboard');
        Route::get('module_comptoir/ouvrir', [SessionCaisseController::class, 'showOuvrir'])->name('module_comptoir.ouvrir.form');

        Route::post('module_comptoir/session/ouvrir', [SessionCaisseController::class, 'ouvrir'])->name('module_comptoir.ouvrir');
        Route::post('module_comptoir/session/arret', [SessionCaisseController::class, 'arret'])->name('module_comptoir.arret');
        Route::post('module_comptoir/session/reprise', [SessionCaisseController::class, 'reprise'])->name('module_comptoir.reprise');
        Route::post('module_comptoir/session/fermeture', [SessionCaisseController::class, 'fermeture'])->name('module_comptoir.fermeture');
        Route::get('module_comptoir/session/courante', [SessionCaisseController::class, 'couranteJson'])->name('module_comptoir.session.courante');

        Route::get('module_comptoir/meta', [ComptoirMetaController::class, 'meta'])->name('module_comptoir.meta');
        Route::get('module_comptoir/produits/search', [ComptoirMetaController::class, 'searchProduits'])->name('module_comptoir.produits.search');
        Route::get('module_comptoir/contribuables/search', [ComptoirMetaController::class, 'searchContribuables'])->name('module_comptoir.contribuables.search');

        Route::get('module_comptoir/tickets/en-attente', [TicketComptoirController::class, 'enAttente'])->name('module_comptoir.tickets.en_attente');
        Route::post('module_comptoir/tickets', [TicketComptoirController::class, 'store'])->name('module_comptoir.tickets.store');
        Route::put('module_comptoir/tickets/{id}', [TicketComptoirController::class, 'update'])->name('module_comptoir.tickets.update');
        Route::post('module_comptoir/tickets/{id}/attente', [TicketComptoirController::class, 'mettreEnAttente'])->name('module_comptoir.tickets.attente');
        Route::post('module_comptoir/tickets/{id}/reprendre', [TicketComptoirController::class, 'reprendre'])->name('module_comptoir.tickets.reprendre');
        Route::post('module_comptoir/tickets/{id}/valider-stock', [TicketComptoirController::class, 'validerStock'])->name('module_comptoir.tickets.valider_stock');
        Route::post('module_comptoir/tickets/{id}/payer', [TicketComptoirController::class, 'payer'])->name('module_comptoir.tickets.payer');
        Route::post('module_comptoir/tickets/{id}/annuler', [TicketComptoirController::class, 'annuler'])->name('module_comptoir.tickets.annuler');
        Route::delete('module_comptoir/tickets/{id}', [TicketComptoirController::class, 'destroy'])->name('module_comptoir.tickets.destroy');
        Route::get('module_comptoir/tickets', [TicketComptoirController::class, 'index'])->name('module_comptoir.tickets.index');
        Route::get('module_comptoir/tickets/{id}', [TicketComptoirController::class, 'show'])->name('module_comptoir.tickets.show');
        Route::get('print_ticket_comptoir/{id}', [TicketComptoirController::class, 'print'])->name('print_ticket_comptoir');

        Route::get('print_arret_caisse/{id}', [SessionCaisseController::class, 'printArret'])->name('print_arret_caisse');
        Route::get('print_fermeture_caisse/{id}', [SessionCaisseController::class, 'printFermeture'])->name('print_fermeture_caisse');

        Route::get('module_comptoir/conversion', [ConversionTicketController::class, 'index'])
            ->name('module_comptoir.conversion.index');
        Route::post('module_comptoir/conversion', [ConversionTicketController::class, 'store'])
            ->name('module_comptoir.conversion.store');
    });

    // ===== Paramètres — Import / Export =====
    Route::get('parametres',                        [ImportExportController::class, 'index'])->name('parametres.index');
    Route::get('parametres/export/{type}',          [ImportExportController::class, 'export'])->name('parametres.export');
    Route::get('parametres/template/{type}',        [ImportExportController::class, 'template'])->name('parametres.template');
    Route::post('parametres/import/{type}',         [ImportExportController::class, 'import'])->name('parametres.import');
    Route::get('parametres/backup',                 [ImportExportController::class, 'backupAll'])->name('parametres.backup');
    Route::get('parametres/backup-sql',             [ImportExportController::class, 'backupSql'])->name('parametres.backup-sql');
    Route::delete('parametres/purge/{type}',        [ImportExportController::class, 'purge'])->name('parametres.purge');
});
