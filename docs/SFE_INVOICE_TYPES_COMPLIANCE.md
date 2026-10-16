# SFE — Couverture des types de factures (FEC 05 §1.7)

Référence : **FEC 05 — Tableau de conformité SFE v1.0**  
Date de mise à jour : 2026-07-19

## Types couverts

| Type FEC | Code app | Bridge | O/F | Statut intégration |
|---|---|---|---|---|
| Facture de vente | FV | FV | O | Certifiable, verrouillage, impression SECeF |
| Facture d'acompte | FT | FV (map) | F | Certifiable ; mention print « D'ACOMPTE » |
| Facture d'avoir | FA | FA | O | Certifiable via `FactureAvoir` + nature + ref. originale |
| Vente export | EV | EV | F | Certifiable ; mention « EXPORTATION » ; lock corrigé |
| Acompte export | ET | EV (map) | F | Certifiable ; mentions export + acompte |
| Avoir export | EA | EA | F | Certifiable si original EV/ET |

## Natures d'avoir (FEC 1.25)

| Code | Libellé | Motif UI par défaut |
|---|---|---|
| COR | Correction | erreur |
| RAN | Annulation | annulation |
| RAM | Reprise biens/services | retour, produit_defectueux |
| RRR | Remise / ristourne / rabais | remise |

RRR sans original : `reference_originale = "RRR"` (FEC 1.26).  
Autres natures : original **fiscalisé** obligatoire ; `reference_originale` = `NIM-compteur_type-compteur_total`.

## Bugs corrigés

1. Persistence certification n’est plus forcée à `FV`.
2. `certification_active` accepte `FV` et `EV` (ventes).
3. Avoir : certification propre FA/EA (plus seulement le SIG de l’original à l’impression).
4. Mode paiement défaut `ESP` (aligné seed / bridge).
5. Types FA/EA retirés du formulaire de vente (flux avoir dédié).

## Contrat Bridge REST (manuel déploiement)

`POST /api/v1/certifier` — clés toujours envoyées :

- Vente : `nature_avoir=""`, `reference_originale=""`
- Avoir : `nature_avoir` ∈ COR|RAN|RAM|RRR, `reference_originale` = `NIM-compteur_type-compteur_total`
- `client.type_client` ∈ CC|PM|PP|PC (champ `contribuables.type_client`)
- `paiements[]` : un ou plusieurs `{mode, montant}` ; modes `ESP`/`VIR`/`CHQ`
- Réponse : `signature`, `qrcode_data`, `nim`, `date_certification`, `compteur_*`, **`psvb`** persisté

Admin : `GET /status`, `/config`, `/mcf/etat`, `/mcf/connexion`, `POST /mcf/rapport` `{type_rapport:X|Z}`.

## Smoke test (sans certifier de vraie facture)

```bash
php artisan sfe:smoke
php artisan sfe:smoke --payload   # affiche la forme JSON du contrat
php artisan sfe:smoke --json
```

Couvre : `GET /status`, `/config`, `/mcf/etat`, `/mcf/connexion`.

## Checklist ops production

- [ ] Bridge SFE en ligne (`php artisan sfe:smoke`)
- [ ] `GET /api/v1/mcf/etat` : IFU = IFU société Laravel
- [ ] Licence / port COM / SFEID configurés dans le bridge
- [ ] Clients : type DGI renseigné (PM/CC/PP/PC)
- [ ] Signature auto activée seulement si le bridge est stable
- [ ] Tester FV (lignes mixtes TVA/exo/TS) puis FA (RAN) sur boîtier réel
- [ ] Vérifier `reference_originale` = NIM-compteurs réels
- [ ] Tester EV + EA si export utilisé
- [ ] Scheduler `sfe:monitor` actif
- [ ] QR print offline (`public/js/qrcode.min.js`, plus de CDN)
- [ ] `VirtualMcfService` non utilisé en production

## Écarts FEC restants (hors scope types)

Voir plan / backlog : duplicata, journal électronique, rapports A/X/Z complets, 14 groupes de taxe, multi-paiements, HT/TTC switch, timbre quittance, alertes MCF >7j, etc.
