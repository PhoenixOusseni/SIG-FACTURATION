# Documentation Technique & Architecture Système — SIG-FACTURATION (App FEC)
**Système Intégré de Facturation Normalisée & Certification Fiscale SECeF / DGI Bénin & Burkina Faso**
*Version : 3.0 — Novembre 2026*

---

## 1. Vue d'Ensemble de l'Architecture

### 1.1 Contexte & Objectif
Le système **SIG-FACTURATION** est une solution complète de gestion commerciale et de facturation normalisée conforme aux spécifications de la **Direction Générale des Impôts (DGI)** et du protocole **SECeF ST 1.0**. 

Il assure l'émission, la signature cryptographique, l'impression normalisée et l'archivage infalsifiable des documents de vente (Factures de Vente `FV` et Factures d'Avoir `FA`) en liaison avec un boîtier physique de certification fiscale (**Module de Contrôle Fiscal - MCF Eltrade CC300** ou son émulateur fiscal logiciel).

### 1.2 Schéma d'Architecture Globale

```
+---------------------------------------------------------------------------------+
|                                NAVIGATEUR CLIENT                                |
|  - Hub Facturation Unifié (Ventes FV, Avoirs FA, Devis/Proformas, Bordereaux)   |
|  - Moteur de saisie dynamique (Stock concaténé, Select2 AJAX, Lignes live)      |
|  - Moteur d'impression & Prévisualisation (A4, Ticket thermique, Duplicatas)    |
+----------------------------------------+----------------------------------------+
                                         | HTTP / HTTPS (JSON & Forms)
                                         v
+---------------------------------------------------------------------------------+
|                         BACKEND LARAVEL 10 / PHP 8.2                            |
|                                                                                 |
|  [ ROUTEURS & MIDDLEWARE ]                                                      |
|   - routes/web.php (Hub Facturation, Avoirs, MCF Console, Rapports X/Z, Logs)   |
|                                                                                 |
|  [ CONTRÔLEURS MÉTIER ]                                                         |
|   - FactureController      : Hub unifié, validation formulaires, résilience MCF |
|   - FactureAvoirController : Avoirs financiers RRR, retours marchandise RAM/RAN |
|   - RapportController      : Rapports statistiques X & Z (Standard DGI 4.3)     |
|   - McfController          : Diagnostics, horloge RTC, liaison DGI, port série  |
|                                                                                 |
|  [ COUCHE SERVICES (DOMAINE FISCAL) ]                                           |
|   - InvoiceSigningService  : Mathématiques fiscales, proratisation, payload SECeF|
|   - StatusSnapshotService  : Algorithme de connectivité & monitoring DGI 7J     |
|   - McfSerialDriver        : Driver protocole Eltrade CC300 (C0h, 31h, 35h, 38h)|
|                                                                                 |
|  [ MODÈLES ELOQUENT & ATTRIBUTS CALCULÉS ]                                      |
|   - Facture, ElementFacture, FactureAvoir, ElementFactureAvoir, Produit...      |
+----------------------------------------+----------------------------------------+
                     |                                         |
                     | SQLite (database.sqlite)                | Port Série USB (COMxx)
                     v                                         v
+-----------------------------+        +------------------------------------------+
|      BASE DE DONNÉES        |        |          MODULE FISCAL MCF ELTRADE       |
|  - Données commerciales     |        |  - Boîtier physique Eltrade CC300        |
|  - Pistes d'audit (Logs)    |        |  - Puce cryptographique & horloge RTC    |
|  - Signatures & QR Codes    |        |  - Compteurs inviolables (FV / FA)       |
|  - 8 Lignes de commentaires |        |  - Télétransmission automatique DGI      |
+-----------------------------+        +------------------------------------------+
```

---

## 2. Conception de la Base de Données (Modèle Relationnel)

La base de données repose sur un schéma relationnel strict garantissant la traçabilité financière et fiscale.

### 2.1 Schéma Entités / Relations (ERD)

```
       +--------------------+               +---------------------+
       |    CONTRIBUABLE    | 1           * |       FACTURE       |
       |--------------------|<--------------|---------------------|
       | id                 |               | id                  |
       | nom / raison_social|               | code (FACT-00001/26)|
       | ifu (13 chiffres)  |               | date, statut        |
       | assujeti (Oui/Non) |               | remise_1, remise_2  |
       | telephone, adresse |               | print_count         |
       +--------------------+               | first_printed_at    |
                                            | dgi_signature       |
                                            | dgi_nim_machine     |
                                            | dgi_compteur        |
                                            | dgi_qrcode          |
                                            | notes (JSON)        |
                                            +----------+----------+
                                                       |
                        +------------------------------+-----------------------------+
                        | 1                                                          | 1
                        | *                                                          | *
             +----------v----------+                                      +----------v----------+
             |   ELEMENT_FACTURE   |                                      |      REGLEMENT      |
             |---------------------|                                      |---------------------|
             | id                  |                                      | id                  |
             | facture_id          |                                      | facture_id          |
             | produit_id          |                                      | code (RGL-00001/26) |
             | quantite            |                                      | mode (ESP, VIR, CHQ)|
             | prix_unitaire       |                                      | montant             |
             +----------+----------+                                      | reference_paiement  |
                        | *                                               +---------------------+
                        | 1
             +----------v----------+
             |       PRODUIT       |
             |---------------------|
             | id                  |
             | code, designation   |
             | prix_unitaire       |
             | qte (Stock physique)|
             | famille_produit     | (LOCBIE, IMPBIE, LOCSER, IMPSER)
             | tva_id (Groupe A/B) |
             +---------------------+
```

### 2.2 Tables Clés & Rôle des Colonnes Fiscales

#### A. Table `factures`
- **`code`** : Numérotation séquentielle interne (ex: `FACT-00001/2026`).
- **`statut`** : Cycle de vie :
  - `BROUILLON` : Facture préparatoire, non certifiée, sans impact physique sur les stocks.
  - `Certifiée` : Facture signée fiscalement par le boîtier MCF. Irréversible et non modifiable.
  - `Erreur MCF` : Tentative de certification rejetée par le matériel.
  - `Annulée par Avoir` : Statut apposé lorsqu'un avoir total a régularisé la facture.
- **`dgi_signature`** : Empreinte cryptographique générée par la puce fiscale du boîtier Eltrade.
- **`dgi_nim_machine`** : Numéro d'Identification de la Machine (NIM) attribué par la DGI (ex: `DT02200520-3`).
- **`dgi_compteur`** : Compteur inviolable DGI (ex: `125/2500 FV`).
- **`dgi_qrcode`** : Contenu officiel DGI encodé dans le QR code.
- **`print_count` & `first_printed_at`** : Suivi des réimpressions pour déclenchement automatique du **DUPLICATA légal**.
- **`notes`** : Champ texte/JSON stockant les métadonnées étendues : modes de paiements convenus, lignes de commentaires A à H, erreurs de certification.

#### B. Table `element_factures`
- Lignes d'articles, quantités, prix unitaires, remises et rattachement au groupe fiscal de TVA (Groupe A Exonéré, Groupe B 18%, etc.).

#### C. Table `facture_avoirs` & `element_facture_avoirs`
- Enregistre les avoirs fiscaux légaux (`FA` / `EA`).
- **`facture_id`** : Clé étrangère liant l'avoir à sa facture d'origine (obligatoire pour avoir sur facture, optionnelle pour RRR).
- **`nature`** : `RAM`/`RAN` (Retour de marchandise avec réintégration de stock) ou `RRR` (Avoir financier sans mouvement de stock).
- **`print_count` & `first_printed_at`** : Gestion du duplicata sur facture d'avoir.
- **`notes`** : JSON contenant le sous-type RRR (`rrr_sous_type`), la désignation personnalisée (`rrr_designation`) et les factures concernées.

#### D. Table `mcf_rapports`
- Enregistrement historique des clôtures fiscales Z et lectures X.
- Stocke les ventilations brutes (`details_bruts`), par type de facture (`par_type_facture`), par matrice de taxe (`par_type_et_groupe_taxe`), par mode de règlement (`details_reglements`) ainsi que les indicateurs obligatoires k, l, m.

---

## 3. Architecture Métier & Fonctionnalités Clés

### 3.1 Hub de Facturation Unifié (`FactureController.php`)
- **Tri Chronologique Unifié** : Le contrôleur fusionne les factures de vente (`FV`), factures d'avoir (`FA`), factures proformas et bordereaux de livraison en une collection unique ordonnée par timestamp décroissant. Cela élimine l'effet de réorganisation au rafraîchissement.
- **Optimisation des Modals** : Les modals d'avoir global (RRR / Annulation) et d'émission intègrent des listes déroulantes avec recherche Select2 asynchrone pour la sélection des clients et des factures certifiées éligibles.
- **Résilience du Bouton de Certification** : Le bouton `Enregistrer & Fiscaliser (MCF)` n'est plus bloqué par un niveau de statut `warning` (ex: avertissement 7 jours sans synchro DGI). Il est désactivé uniquement si les champs obligatoires sont incomplets (client, type de facture, mode de règlement, ligne d'article valide). Dès que le formulaire est complet, le bouton s'active immédiatement en vert.

### 3.2 Gestion du Stock : Saisie vs Impression (`Produit.php`)
- **À la sélection / émission** : L'accesseur `label_selection` concatène l'état du stock :
  ```
  [ART01] Ciment CPJ 45 — [Groupe B (18%)] — [LOCBIE] · Stock: 15
  [SRV01] Audit réseau — [Groupe B (18%)] — [LOCSER] · Prestation
  ```
- **Sur la facture imprimée** : L'accesseur `label_fiscal` masque strictement le stock physique pour respecter les exigences de présentation commerciale :
  ```
  [ART01] Ciment CPJ 45 — [Groupe B (18%)] — [LOCBIE]
  ```
  L'en-tête de colonne affiche de manière épurée **« Article / Prestation »**.

### 3.3 Cartouche Fiscal de Sécurité SECeF (`print_facture.blade.php`, `facture_avoir/print.blade.php`)
- **NIM Normalisé** : L'ancienne mention `Machine :` est remplacée par **`NIM :`**.
- **QR Code Agrandie** : Dimensions calibrées à 62px (conteneur 66px) avec niveau de correction M pour une lisibilité instantanée par les applications de contrôle DGI.
- **Code SECeF Formaté** : Espacement par tirets tous les 4 caractères (`TEST-FACT-JXBH-02P7-BSZC-4M2T`).
- **Typographie** : Police monospace JetBrains Mono haute lisibilité avec conteneur élargi à 46% éliminant tout risque de débordement.

### 3.4 Lignes de Commentaires DGI Obligatoires (Codes A à H)
Conformément à l'exigence réglementaire SECeF stipulant que le SFE doit pouvoir inscrire au moins 8 lignes de commentaires codifiées :
- **Code A** : *Réf. exo.* — Référence du certificat d'exonération.
- **Code B** : *Base juridique* — Base juridique d'exonération fiscale.
- **Codes C à H** : Lignes réservées configurables avec étiquette et contenu personnalisables.
- Ces lignes sont persistées dans `factures.notes['lignes_commentaires']` et imprimées dans un bloc dédié au-dessus du total net à payer.

### 3.5 Gestion du DUPLICATA Légal pour Factures & Avoirs
- À la première impression physique ou écran, `first_printed_at` est horodaté et `print_count` est initialisé à 1 (document original).
- À toute impression ultérieure (`print_count > 1`) :
  - Un filigrane diagonal gris **DUPLICATA** est apposé en arrière-plan.
  - Un badge rouge officiel **DUPLICATA N° X** est inscrit dans l'en-tête du document avec la date et l'heure de la réimpression.
- L'incrémentation est sécurisée via les routes AJAX `module_ordre_facture.marquer_imprimee` et `module_facture_avoir.marquer_imprimee`.

### 3.6 Avoirs Financiers RRR & Retours RAM
- **Avoirs RRR (Rabais, Remise, Ristourne)** :
  - Rattachés au code de prestation `LOCSER`.
  - N'entraînent aucune création erronée de produit en base de données `produits`.
  - Pas de mouvement de stock physique.
  - Titre normalisé et libellé de ligne personnalisable (ex: *Ristourne commerciale sur chiffre d'affaires 2026*).
- **Avoirs de Retour (RAM / RAN)** :
  - Contrôle strict du solde fiscal disponible sur la facture d'origine.
  - Réintégration automatique des articles physiques retournés dans le stock du magasin (`$produit->increment('qte', $qte)`).

---

## 4. Spécification des Rapports Statistiques DGI (Standard SECeF 4.3)

Les rapports X (lecture temporaire) et Z (clôture fiscale définitive) sont structurés selon les points obligatoires de la norme SECeF :

```
+---------------------------------------------------------------------------------+
|                       RAPPORT FISCAL X / Z (STANDARD DGI 4.3)                   |
+---------------------------------------------------------------------------------+
| EN-TÊTE : Nom commercial, IFU, ISF, NIM, Date/Heure, Type rapport, Période      |
|                                                                                 |
| 1. RÉCAPITULATIF PAR TYPE DE FACTURE (Points g, i)                              |
|    - Type (EA, EV, FA, FT, FV, TOTAL) | Nombre | HT | TVA | TTC                  |
|                                                                                 |
| 2. MONTANTS PAR GROUPE DE TAXATION ET TYPE DE FACTURE (Point h)                 |
|    - Matrice Type x Groupe (A 0%, B 18%, etc.) avec taux, HT, TVA et TTC        |
|                                                                                 |
| 3. MONTANTS PAR MODE DE PAIEMENT (Point j)                                      |
|    - Espèces, Virement, Chèque, Mobile Money, Carte, Crédit / À terme           |
|                                                                                 |
| 4. REMISES, RISTOURNES ET RABAIS (RÉDUCTIONS COMMERCIALES) (Point k)            |
|    - Table détaillée : N° facture RRR | Date | Montant (XOF)                    |
|    - Ligne TOTAL RRR                                                            |
|                                                                                 |
| 5. AUTRES ENREGISTREMENTS AYANT RÉDUIT LES VENTES (Point l)                     |
|    - Table détaillée : N° facture | Nature (COR/RAN/RAM) | Date | Montant (XOF) |
|    - Ligne TOTAL                                                                |
|                                                                                 |
| 6. VENTES INCOMPLÈTES (BROUILLONS) (Point m)                                    |
|    - Boîte normalisée : Nombre de ventes incomplètes : X                        |
|    - Table détaillée si X > 0 : N° Facture | Date | Client | Montant (XOF)      |
|                                                                                 |
| 7. SYNTHÈSE FINANCIÈRE GLOBALE & CONTRÔLES DGI                                  |
|    - Chiffre d'Affaires Brut HT, TVA collectée, TS, PSVB/BIC, Timbre, Net TTC  |
+---------------------------------------------------------------------------------+
```

- **Détail des Tables 4, 5 et 6 (Exigence DGI SECeF)** :
  - **Table 4 (Réductions RRR)** : Répertorie chaque avoir financier ou remise commerciale sur facture avec son numéro de document, sa date et son montant.
  - **Table 5 (Autres avoirs et régularisations)** : Répertorie tous les avoirs physiques de retour marchandise (`RAM`), d'annulation totale (`RAN`), de correction (`COR`) ou d'export (`EA`).
  - **Table 6 (Ventes incomplètes)** : Affiche le compteur officiel et, le cas échéant, la liste des documents préparatoires en brouillon ou en échec de signature afin de garantir l'absence de fraude ou de vente omise.
- **Suppression des En-têtes Étatiques Inappropriés** : Les mentions « Direction Générale des Impôts » en en-tête des rapports imprimés ont été supprimées : le rapport débute directement par l'identification de l'entreprise émettrice.
- **Charte Graphique Unifiée** : L'ensemble des 3 rapports (Rapport X/Z, Bilan Périodique A4, Rapport Articles A) adopte une charte visuelle moderne : en-têtes de tableaux bleu marine `#002b49`, barre verticale cyan `#0056b3` sur les titres de sections, lignes totales en surbrillance bleu pastel `#e8f0fe` et typographie JetBrains Mono pour les montants.

---

## 5. Guide de Maintenance & Dépannage

### 5.1 Commandes et Codes Protocolaires Eltrade CC300
- **`31h` (Enregistrement article)** : Soumet les désignations, quantités, prix et codes de taxes (`A`, `B`, etc.).
- **`35h` (Enregistrement règlement)** : Soumet les modes de règlement et montants.
- **`38h` (Finalisation DGI)** : Doit impérativement être soumise après la commande `35h` lorsque le solde du ticket est réglé.

### 5.2 Erreurs Fréquentes & Procédures de Résolution
1. **Erreur finalisation DGI (38h) : La commande 35 doit être soumise en premier** :
   - *Cause* : Tentative d'envoyer la commande de clôture `38h` sans avoir soumis au préalable la commande de paiement `35h`, ou tentative de réémission d'une facture sur un ticket fiscal resté ouvert dans le boîtier.
   - *Procédure* : Fermer le ticket en cours ou redémarrer le boîtier physique pour purger la mémoire tampon série.
2. **Dépassement du solde fiscal disponible sur Avoir** :
   - *Cause* : Le montant de l'avoir dépasse le solde TTC restant éligible sur la facture d'origine.
   - *Procédure* : Vérifier les avoirs déjà certifiés sur cette facture et ajuster le montant ou les quantités retournées.
3. **Bouton de certification grisé** :
   - *Cause* : Formulaire incomplet (client, type, mode de règlement ou ligne d'article non renseignés).
   - *Procédure* : Vérifier que chaque ligne comporte une quantité $> 0$ et un prix unitaire $> 0$.

### 5.3 Commandes Utiles de Maintenance
```bash
# Vider les caches compilés après modification des vues ou des routes
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Vérification syntaxique PHP
php -l app/Http/Controllers/FactureController.php
php -l app/Http/Controllers/RapportController.php
php -l app/Models/Produit.php
```
