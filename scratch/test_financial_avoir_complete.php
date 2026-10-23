<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Facture;
use App\Models\FactureAvoir;
use App\Models\ElementFactureAvoir;
use App\Models\Produit;
use App\Models\Contribuable;
use App\Models\Tva;
use App\Http\Requests\StoreFactureAvoirRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

echo "=== TEST COMPLET DE LA LOGIQUE D'AVOIRS SUCCESSIFS ET DE TRÉSORERIE ===\n\n";

DB::beginTransaction();

try {
    // 1. Setup client & product
    $contribuable = Contribuable::first();
    $tvaExoneree = Tva::where('taux', 0)->first() ?: Tva::first();

    $produit = Produit::create([
        'code' => 'TEST_P_' . time(),
        'designation' => 'Produit Test Successif',
        'prix_unitaire' => 10000,
        'qte' => 100,
        'tva_id' => $tvaExoneree->id,
        'type_ts' => 'MONTANT',
        'montant_ts' => 1000,
        'famille_produit' => 'LOCBIE',
    ]);

    // 2. Facture initiale : 10 articles @ 10 000 + 1 000 TS = 11 000 TTC / unité => 110 000 FCFA
    $typefacture = \App\Models\Typefacture::first();

    $facture = Facture::create([
        'code' => 'FV-TEST-' . time(),
        'contribuable_id' => $contribuable->id,
        'typefacture_id' => $typefacture ? $typefacture->id : null,
        'date' => now(),
        'statut' => 'Certifiée',
    ]);

    $facture->ElementFactures()->create([
        'produits_id' => $produit->id,
        'quantite' => 10,
        'prix_unitaire' => 10000,
    ]);

    echo "1. Facture initiale créée : {$facture->code}\n";
    echo "   - Quantité initiale : 10\n";
    echo "   - Montant Total : {$facture->montant_total} FCFA\n";
    echo "   - Peut subir RAN ? " . ($facture->canRan() ? 'OUI' : 'NON') . "\n";
    echo "   - Solde disponible avoir : {$facture->soldeAvoirDisponible()} FCFA\n\n";

    // 3. Premier avoir partiel : Reprise de 5 articles (RAM)
    $avoir1 = FactureAvoir::create([
        'code' => 'FA-TEST-1-' . time(),
        'facture_id' => $facture->id,
        'contribuables_id' => $contribuable->id,
        'nature_avoir' => 'RAM',
        'motif' => 'retour',
        'date' => now(),
        'statut' => 'validée',
    ]);

    ElementFactureAvoir::create([
        'facture_avoir_id' => $avoir1->id,
        'produits_id' => $produit->id,
        'quantite' => 5,
        'prix_unitaire' => 10000,
    ]);

    $facture->refresh();
    echo "2. Premier avoir partiel enregistré : 5 articles retournés (55 000 FCFA)\n";
    echo "   - aSubiAvoir : " . ($facture->aSubiAvoir() ? 'TRUE' : 'FALSE') . "\n";
    echo "   - aDejaAvoirPartiel : " . ($facture->aDejaAvoirPartiel() ? 'TRUE' : 'FALSE') . "\n";
    echo "   - canRan : " . ($facture->canRan() ? 'TRUE' : 'FALSE') . " (Doit être FALSE car déjà partiellement remboursée)\n";
    echo "   - Quantité déjà avoir : " . $facture->quantiteAvoirProduit($produit->id) . "\n";
    echo "   - Quantité restante : " . $facture->quantiteRestanteProduit($produit->id, 10) . " (Attendu : 5)\n";
    echo "   - Solde restant disponible pour avoir : " . $facture->soldeAvoirDisponible() . " FCFA (Attendu : 55 000)\n\n";

    // 4. Test tentative de soumission RAN (doit échouer !)
    $requestRan = new StoreFactureAvoirRequest();
    $requestRan->merge([
        'facture_id' => $facture->id,
        'nature_avoir' => 'RAN',
        'motif' => 'annulation',
        'date' => date('Y-m-d'),
        'produits_id' => [$produit->id],
        'quantite' => [10],
        'prix_unitaire' => [10000],
    ]);

    $rules = $requestRan->rules();
    $validatorRan = Validator::make($requestRan->all(), $rules);
    $requestRan->withValidator($validatorRan);

    if ($validatorRan->fails()) {
        echo "3. TEST SUCCÈS : Tentative RAN bloquée correctement par la validation backend !\n";
        echo "   Message reçu : " . implode(', ', $validatorRan->errors()->all()) . "\n\n";
    } else {
        echo "3. ERREUR : La tentative RAN n'a PAS été bloquée !\n\n";
    }

    // 5. Test tentative de dépassement de quantité restante (demande 6 alors qu'il n'en reste que 5)
    $requestQteTropGrande = new StoreFactureAvoirRequest();
    $requestQteTropGrande->merge([
        'facture_id' => $facture->id,
        'nature_avoir' => 'RAM',
        'motif' => 'retour',
        'date' => date('Y-m-d'),
        'produits_id' => [$produit->id],
        'quantite' => [6], // 6 > 5 restantes
        'prix_unitaire' => [10000],
    ]);

    $validatorQte = Validator::make($requestQteTropGrande->all(), $requestQteTropGrande->rules());
    $requestQteTropGrande->withValidator($validatorQte);

    if ($validatorQte->fails()) {
        echo "4. TEST SUCCÈS : Dépassement de quantité restante (6 > 5) bloqué correctement !\n";
        echo "   Message reçu : " . implode(', ', $validatorQte->errors()->all()) . "\n\n";
    } else {
        echo "4. ERREUR : Le dépassement de quantité n'a PAS été bloqué !\n\n";
    }

    // 6. Test d'un deuxième avoir valide sur le reliquat (quantité = 5)
    $requestValide = new StoreFactureAvoirRequest();
    $requestValide->merge([
        'facture_id' => $facture->id,
        'contribuables_id' => $contribuable->id,
        'nature_avoir' => 'RAM',
        'motif' => 'retour',
        'date' => date('Y-m-d'),
        'produits_id' => [$produit->id],
        'quantite' => [5],
        'prix_unitaire' => [10000],
    ]);

    $validatorValide = Validator::make($requestValide->all(), $requestValide->rules());
    $requestValide->withValidator($validatorValide);

    if (!$validatorValide->fails()) {
        echo "5. TEST SUCCÈS : Avoir valide sur les 5 articles restants accepté avec succès !\n\n";
    } else {
        echo "5. ERREUR : Avoir valide rejeté : " . implode(', ', $validatorValide->errors()->all()) . "\n\n";
    }

} finally {
    DB::rollBack();
    echo "=== ROLLBACK EFFECTUÉ : Base de données propre et intacte ===\n";
}
