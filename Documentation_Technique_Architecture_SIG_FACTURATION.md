# Documentation Technique & Architecture Système — SIG-FACTURATION (App FEC)
**Système Intégré de Facturation Normalisée & Certification Fiscale SECeF / DGI Bénin**
*Version : 2.5 — Octobre 2026*

---

## 1. Vue d'Ensemble de l'Architecture

### 1.1 Contexte & Objectif
Le système **SIG-FACTURATION** est une solution complète de gestion commerciale et de facturation normalisée conforme aux spécifications de la **Direction Générale des Impôts (DGI) du Bénin** et du protocole **SECeF ST 1.0**. 

Il assure l'émission, la signature cryptographique, l'impression normalisée et l'archivage infalsifiable des documents de vente (Factures de Vente `FV` et Factures d'Avoir `FA`) en liaison avec un boîtier physique de certification fiscale (**Module de Contrôle Fiscal - MCF Eltrade CC300** ou son émulateur SFE Bridge).

### 1.2 Schéma d'Architecture Globale

```
+---------------------------------------------------------------------------------+
|                                NAVIGATEUR CLIENT                                |
|  - Interfaces d'administration & Caisses (Blade, HTML5, Vanilla CSS / Bootstrap)|
|  - Composants dynamiques (Select2 AJAX, Modals, Impression thermique & A4)      |
+----------------------------------------+----------------------------------------+
                                         | HTTP / HTTPS (JSON & Forms)
                                         v
+---------------------------------------------------------------------------------+
|                         BACKEND LARAVEL 10 / PHP 8.2                            |
|                                                                                 |
|  [ ROUTEURS & MIDDLEWARE ]                                                      |
|   - routes/web.php (Hub Facturation, Avoirs, MCF, Clients, Produits, Rapports)  |
|                                                                                 |
|  [ CONTRÔLEURS MÉTIER ]                                                         |
|   - FactureController      : Cycle de vie FV, remises, règlements, résilience   |
|   - FactureAvoirController : Avoirs financiers RRR, retours de stock RAN        |
|   - McfController          : Diagnostics, horloge RTC, liaison DGI, rapports Z  |
|                                                                                 |
|  [ COUCHE SERVICES (DOMAINE FISCAL) ]                                          |
|   - InvoiceSigningService  : Validation fiscale, calculs TTC, construction payload|
|   - StatusSnapshotService  : Algorithme statut 7J, monitoring liaison DGI      |
|   - McfSerialDriver        : Driver protocole Eltrade CC300 (31h, 35h, 38h...) |
|                                                                                 |
|  [ MODÈLES ELOQUENT & MATHÉMATIQUES FISCALES ]                                  |
|   - Facture, ElementFacture, FactureAvoir, Reglement, Produit, Contribuable...  |
+----------------------------------------+----------------------------------------+
                     |                                         |
                     | SQLite (database.sqlite)                | HTTP REST / Série
                     v                                         v
+-----------------------------+        +------------------------------------------+
|      BASE DE DONNÉES        |        |          BRIDGE SFE / MCF ELTRADE        |
|  - Données commerciales     |        |  - Serveur local (FastAPI / COM / USB)   |
|  - Pistes d'audit (Logs)    |        |  - Boîtier Eltrade CC300 (Puce RTC, DGI) |
|  - Signatures & QR Codes    |        |  - Liaison télétransmission DGI Bénin    |
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
       | telephone, adresse |               | dgi_signature       |
       +--------------------+               | dgi_nim_machine     |
                                            | dgi_compteur        |
                                            | dgi_qrcode          |
                                            | sfe_locked_at       |
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
             | type_produit        | (Bien ou Service)
             | tva_id (Groupe A/B) |
             +---------------------+
```

### 2.2 Tables Clés & Rôle des Colonnes Fiscales

#### A. Table `factures`
- **`code`** : Numérotation séquentielle interne (ex: `FACT-00001/2026`).
- **`statut`** : Cycle de vie :
  - `Brouillon` : Facture préparatoire, non certifiée, sans impact physique sur les stocks ni sur la trésorerie officielle.
  - `Certifiée` : Facture signée fiscalement par le boîtier MCF. Irréversible et non modifiable.
  - `Erreur SFE` / `Erreur MCF` : Tentative de certification rejetée par le matériel.
- **`dgi_signature`** : Empreinte cryptographique SHA-256 générée par la puce du boîtier Eltrade.
- **`dgi_nim_machine`** : Numéro d'Identification de la Machine (NIM) attribué officiellement par la DGI.
- **`dgi_compteur`** : Compteur inviolable DGI (ex: `125/2500 FV` représentant le rang de facture de vente sur le total des opérations de la machine).
- **`dgi_qrcode`** : Contenu textuel officiel DGI encodé dans le QR code pour vérification publique par l'application mobile des impôts.
- **`sfe_locked_at`** : Timestamp de verrouillage fiscal.

#### B. Table `element_factures`
- Contient les lignes d'articles, quantités, prix unitaires, remises de ligne, et rattachement au groupe fiscal de TVA (Groupe A Exonéré, Groupe B 18%, etc.).

#### C. Table `facture_avoirs` & `element_facture_avoirs`
- Enregistre les avoirs fiscaux légaux (`FA`).
- **`facture_id`** : Clé étrangère liant l'avoir à sa facture d'origine obligatoire.
- **`nature`** : `RAN` (Annulation/Retour avec stock) ou `RRR` (Avoir financier sans mouvement de stock).
- **`notes`** : Stocke au format JSON la sous-nature RRR (`rrr_sous_type`: *Remise*, *Rabais* ou *Ristourne*).

#### D. Table `reglements`
- Enregistre les encaissements réels rattachés à chaque facture (`modepaie_id`, `montant`, `date`, `reference_paiement`).
- Permet la multi-modalité (paiements mixtes et échelonnés).

#### E. Table `audit_logs`
- Journal infalsifiable enregistrant toute action utilisateur (`CREATE`, `UPDATE`, `SIGN`, `CERTIFIER`, `ERROR`, `ANNULER_ERREUR`).

---

## 3. Architecture du Code & Fonctionnalité par Fonctionnalité

### 3.1 Facturation de Vente (`FactureController.php`)

#### A. Méthode `store(Request $request)`
1. **Validation & Contrôle du Stock Disponible** :
   - Vérifie la cohérence du client, des modes et des lignes.
   - Parcourt les biens physiques : si `quantite_demandee > stock_actuel`, la requête est immédiatement rejetée avec code 422 sans altérer la base de données.
2. **Génération Séquentielle Inviolable du Code** :
   - Utilise une regex `preg_match('/(?:FACT|FAC)-(\d+)/')` sur la dernière facture de l'année pour garantir l'incrémentation sans trou ni duplication.
3. **Création Non-Destructive des Éléments & Règlements** :
   - Enregistre la facture et ses éléments.
   - **Règle d'or architecturale** : **Le stock physique n'est PAS décrémenté à cette étape.** Si la facture reste en brouillon ou si le boîtier échoue, les stocks du magasin restent 100% intacts.
4. **Appel Fiscal & Décrémentation Conditionnelle** :
   - Si `action_type === 'certifier'` :
     - Appelle `$this->sfeSigner->sign($facture)`.
     - **Uniquement si la signature réussit** : la méthode `$this->decrementStockFacture($facture)` est déclenchée pour déduire le stock physique, et le statut passe à `Certifiée`.
     - **Si le matériel échoue** : capture l'exception, enregistre l'erreur dans `notes->sfe_error`, passe le statut à `Erreur SFE`, **sans décrémenter aucun stock**.

#### B. Méthode `recertifier($id)` & `recertifierTout()`
- Destinée aux factures en statut `Erreur MCF` ou `Brouillon`.
- Tente la communication avec le boîtier MCF.
- Dès que la signature DGI est apposée avec succès, décrémente les stocks physiques vendus et consigne l'opération dans `AuditLog`.

#### C. Méthode `annulerErreur($id)`
- Permet à l'opérateur de remettre une facture bloquée en statut `Brouillon`.
- Purge le message `sfe_error` et autorise la modification immédiate des lignes d'articles ou des prix.

---

### 3.2 Gestion des Avoirs Fiscaux (`FactureAvoirController.php`)

#### A. Avoir d'Annulation Totale (Retour de Marchandise - RAN)
- Lorsqu'un client retourne des articles ou qu'une facture certifiée doit être annulée :
  - L'avoir reprend les articles d'origine.
  - La méthode `saveElements()` détecte la nature retour de marchandise et **réintègre automatiquement les quantités dans le stock physique** (`$produit->increment('qte', $quantite)`).

#### B. Avoir Financier RRR (Rabais, Remise, Ristourne)
- Aucune marchandise physique n'est restituée.
- Le formulaire capture le champ `rrr_sous_type` (ex: `Ristourne`).
- La persistance s'effectue dans `notes` et le document imprimé affiche le titre normalisé : **`AVOIR FINANCIER — {RABAIS|REMISE|RISTOURNE}`**.

#### C. Redirection Centralisée vers le Hub
- Les anciennes méthodes `show()` et `certifier()` qui redirigeaient vers un écran de détails isolé ont été alignées : toute action redirige désormais directement vers le Hub principal `module_ordre_facture.index`.

---

### 3.3 Moteur Mathématique & Élimination des Écarts Fiscaux (`Facture.php`)

Dans les versions antérieures, l'application des remises globales créait un écart de centimes ou de francs (ex: 803 FCFA) entre le montant TTC de la liste et le Net à Payer du ticket.

#### La Solution Implémentée :
Dans `Facture.php`, l'accesseur `getMontantTotalAttribute()` applique désormais la méthode de **proratisation de la base imposable** conforme aux normes DGI :
1. Calcul du Total Brut HT = $\sum (\text{Quantité} \times \text{Prix Unitaire})$.
2. Calcul du Total Net HT = $\text{Total Brut HT} - \text{Remise Globale HT}$.
3. Calcul des taxes sur la **base nette remisée** :
   $$\text{TVA (Groupe B)} = \text{Total Net HT (Taxable)} \times 18\%$$
   $$\text{BIC / PSVB} = \text{Total Net HT} \times \text{Taux BIC}$$
4. $\text{Total TTC Net} = \text{Total Net HT} + \text{TVA} + \text{BIC} + \text{TS}$.
5. $\text{Reste à Payer} = \max(0, \text{Total TTC Net} - \text{Total Règlements})$.

Cette harmonisation assure une concordance au franc près entre la base de données, l'affichage écran et le ticket thermique émis par le boîtier.

---

### 3.4 Pilote Matériel MCF Eltrade CC300 (`McfSerialDriver.php`)

Le dialogue avec le boîtier fiscal s'effectue via liaison série / USB conforme au protocole Eltrade :
- **`C0h` (Open Fiscal Receipt)** : Ouverture du ticket fiscal avec transmission de l'opérateur et du type (`FV` ou `FA`).
- **`C3h` (Client Info)** : Envoi de l'IFU du client (13 chiffres) et de sa raison sociale.
- **`31h` (Register Sale Line)** : Enregistrement de chaque article (Désignation, Quantité, Prix, Code TVA `B` pour 18%, `A` pour 0%).
- **`33h` (Subtotal & Discount)** : Envoi du sous-total et application des remises globales.
- **`35h` (Payment Registration)** :
  - `E` : Espèces
  - `B` : Virement bancaire
  - `C` : Chèque
  - `D` : Carte bancaire
  - `M` : Mobile Money (MoMo / Flooz)
  - `T` : Crédit / À terme
- **`38h` (Close Receipt & Sign)** : Clôture du ticket, calcul du hash cryptographique par la puce fiscale, incrémentation des compteurs inviolables et impression du QR Code.

---

### 3.5 Surveillance & Télédiffusion DGI (`StatusSnapshotService.php`)

L'algorithme de surveillance du boîtier assure la conformité avec la **règle légale DGI des 7 jours hors-ligne** :
- Le boîtier physique est autorisé à fonctionner hors-ligne jusqu'à un maximum de 7 jours consécutifs.
- `StatusSnapshotService` interroge la date `derniere_connexion_dgi` rapportée par la puce.
- Si le boîtier s'est synchronisé avec les serveurs de la DGI aujourd'hui, le compteur hors-ligne est immédiatement réinitialisé à **0 jour**.
- L'alerte modale rouge ne se déclenche que si le délai légal dépasse strictement les 7 jours sans télétransmission.

---

## 4. Guide de Maintenance & Bonnes Pratiques

1. **Sauvegarde de la Base SQLite** :
   - Fichier principal : `database/database.sqlite`.
   - Avant toute mise à jour majeure, exécuter une copie à chaud :
     ```powershell
     copy database\database.sqlite database\backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sqlite
     ```
2. **Gestion de l'arrêt machine / Fermeture de l'IDE** :
   - Toujours fermer l'IDE ou l'application avant d'éteindre le PC pour permettre à SQLite d'exécuter son `wal_checkpoint` et d'indexer vos historiques.
3. **Impossibilité de suppression fiscale** :
   - Rappel légal : Aucune facture certifiée ne peut être supprimée de la base de données. En cas d'erreur de saisie sur une facture certifiée, la seule procédure légale admise par la DGI est l'émission d'un **Avoir fiscal d'annulation (RAN)**.
