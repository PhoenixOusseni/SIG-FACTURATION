# Rapport d’intégration SFE Bridge

## Synthèse et point d’insertion

L’application est un monolithe Laravel 8 / PHP 7.3+ avec contrôleurs CRUD, modèles Eloquent et vues Blade. La facture client entre par `FactureController::store`, est immédiatement enregistrée avec le statut `Brouillon`, ses lignes sont enregistrées dans `element_factures`, puis le stock est décrémenté dans la même transaction. Elle reste modifiable et supprimable (sauf si elle possède un règlement), peut être imprimée sans validation fiscale, puis reçoit des règlements séparés.

Le dépôt contient un simulateur `VirtualMcfService` qui fabrique localement SIG, NIM, compteurs et QR. Cette voie ne doit plus être utilisée en production : le seul point d’intégration autorisé est le bridge HTTP local. Le point correct est une action explicite de finalisation entre brouillon et impression. Cette action valide les données, appelle `InvoiceSigningService`, persiste la réponse fiscale de manière atomique, passe la facture à `Fiscalisée`, la verrouille, puis autorise impression et paiement. Un échec conserve au plus le brouillon ; il ne crée jamais de facture finale.

Cycle constaté : création et stockage du brouillon → édition libre → certification MCF simulée optionnelle → impression libre → règlements (`impayée`, `partielle`, `soldée`). Les statuts métier observés sont `Brouillon` et les valeurs libres envoyées par les formulaires ; le paiement est calculé, pas stocké comme statut. Les avoirs ont `en attente` par défaut.

Tables principales : `factures`, `element_factures`, `produits`, `tvas`, `bics`, `typefactures`, `modepaies`, `contribuables`, `reglements`, `facture_avoirs`, `element_facture_avoirs`, `entetes`, `mcf_certifications`, `mcf_machine_states`, `stocks` et `mouvements_stock`. Les informations société sont dans le singleton `entetes` (`denomination`, adresse `postale`, téléphone, email, IFU, RCCM, régime/division fiscale, identifiant SFE). Les taxes viennent de `produits.tva_id → tvas.taux`, de `produits.montant_ts`, et de `factures.bic_id`.

La génération PDF n’est pas réellement serveur : `print_facture.blade.php` est une vue d’impression navigateur. Elle contient déjà un bandeau SECeF et génère le QR côté navigateur depuis la valeur fiscale. Les reçus de règlement ont leur propre vue d’impression.

Les permissions sont des tests de chaînes de rôle (`administrateur`, `superviseur`, `operateur`) et ne couvrent pas systématiquement les factures. Il faut réserver configuration, rapports Z et supervision aux administrateurs/superviseurs, tout en autorisant la finalisation aux rôles de facturation. Le gestionnaire global d’exceptions Laravel est standard ; les contrôleurs utilisent surtout `smilify` et exposent parfois directement le message d’exception.

## Contrat OpenAPI 1.0.0 (lu le 16 juillet 2026)

Le bridge ne déclare aucun schéma d’authentification OpenAPI. Il ne propose pas d’endpoint distinct de validation, contribuable, QR, synchronisation, annulation ou diagnostic. Les fonctions absentes ne doivent pas être inventées.

| Classe | Méthode et URL | Objet | Requête | Réponse 200 | Erreurs déclarées |
|---|---|---|---|---|---|
| Invoice signing / validation / QR | `POST /api/v1/certifier` | Envoie une facture au MCF et retourne la signature DGI | `BridgeFactureRequest` | `BridgeFactureResponse` | `422 HTTPValidationError`; erreurs métier dans `succes=false, erreur` |
| Health | `GET /api/v1/status` | État du processus bridge | aucune | objet non typé | aucune documentée |
| Diagnostics / device discovery | `GET /api/v1/ports` | Ports COM disponibles | aucune | tableau `{device, description}` | aucune documentée |
| Configuration | `GET /api/v1/config` | Configuration locale | aucune | `BridgeConfig` | aucune documentée |
| Configuration | `POST /api/v1/config` | Met à jour la configuration locale | `BridgeConfig` | `BridgeGenericResponse` | `422 HTTPValidationError` |
| Device status / taxpayer | `GET /api/v1/mcf/etat` | Commande C1h ; état, NIM, IFU et compteurs | aucune | `BridgeMCFEteat` | erreur métier dans `succes/erreur` |
| Synchronization | `GET /api/v1/mcf/connexion` | Commande C2h ; connexion DGI et transactions | aucune | `BridgeMCFConnexion` | erreur métier dans `succes/erreur` |
| Reports / other | `POST /api/v1/mcf/rapport` | Rapport X (lecture) ou Z (clôture) | `{type_rapport: X|Z}` | `BridgeGenericResponse` | `422 HTTPValidationError`; erreur métier |

`BridgeFactureRequest` exige `numero_interne`, `client`, `lignes`, `paiements`. Il accepte `type_facture` (`FV`, `FA`, `EV`, `EA`), `mode_prix` (TTC recommandé), `categorie_psvb`, `nature_avoir`, `reference_originale`. Le client contient IFU, nom, téléphone, adresse, email, RCCM et type (`CC`, `PM`, `PP`, `PC`, etc.). Chaque ligne exige désignation, quantité, prix unitaire, taux TVA et groupe A–F ; elle accepte type article DGI (`LOCBIE`, `LOCSER`, `IMPBIE`, etc.) et TS. Chaque paiement exige mode et montant.

La réponse de certification contient `succes`, `erreur`, `signature`, `nim`, `qrcode_data`, `date_certification`, `psvb`, `compteur_total`, `compteur_type`. Elle ne contient aucun champ nommé numéro de vérification : l’UI doit afficher NIM et compteurs comme identifiants de vérification, sans fabriquer une valeur.

`BridgeConfig` contient `port_com`, `baudrate` (9600), `sfeid`, `ifu_vendeur`, `license_key`. Les réglages applicatifs (URL, timeout, contrôles/signature automatiques et identité société) restent dans Laravel ; les réglages matériels restent dans le bridge.

## Architecture cible et changements

Tous les appels passent par `app/Services/SFE`: `BridgeClient` centralise URL, timeout, JSON, journalisation et erreurs ; `HealthCheckService` agrège bridge, boîtier et configuration ; `DeviceStatusService` couvre état, connexion, ports et rapports ; `ConfigurationService` couvre la configuration du bridge ; `InvoiceSigningService` construit et certifie le payload. Aucun contrôleur ne doit utiliser `Http` directement.

La migration ajoute à `entetes` l’URL, le timeout, deux options automatiques et l’adresse société explicite ; elle ajoute `factures.sfe_locked_at`. Les résultats restent dans les colonnes DGI existantes et `mcf_certifications`, évitant une duplication destructive. À terme, renommer `mcf_certifications` en `sfe_certifications` serait plus clair mais nécessite une migration coordonnée.

L’écran société doit ajouter les cinq réglages SFE et réutiliser IFU, dénomination, adresse, téléphone et email. La barre supérieure doit interroger `sfe/status` au chargement si le contrôle automatique est actif et afficher vert « Connecté », orange « Avertissement » ou rouge « Déconnecté », avec le message actionnable. Une tâche `sfe:monitor`, toutes les cinq minutes, met le statut en cache et journalise les anomalies. Le planificateur Laravel doit être exécuté par le système (`php artisan schedule:run` chaque minute).

La finalisation doit être idempotente, interdite si déjà signée, et enregistrer signature, NIM, QR, horodatage et compteurs. Après succès, les routes update/delete et toute modification de lignes doivent retourner 423 ou un message d’avoir obligatoire. L’impression finale doit être interdite avant fiscalisation lorsque la signature automatique est active. Le QR doit provenir exclusivement de `qrcode_data`.

Les corrections légales passent par `POST /api/v1/certifier` avec `type_facture=FA` (ou `EA`), `nature_avoir` et `reference_originale` pointant sur la référence fiscale originale. Le bridge ne fournit aucun endpoint d’annulation : l’UI oriente vers la certification de `facture_avoirs` (`InvoiceSigningService::signAvoir`).

### Couverture types (FEC 05 §1.7) — mise à jour 2026-07-19
- **FV / EV** : certifiés via `signFacture` ; type bridge persisté (plus de hardcode FV) ; lock sur FV+EV.
- **FT / ET** (acomptes) : mappés bridge en FV/EV ; mentions print D'ACOMPTE / EXPORTATION.
- **FA / EA** : certifiés via `signAvoir` (natures COR/RAN/RAM/RRR, référence originale) ; impression SECeF propre à l’avoir.
- Détails : `docs/SFE_INVOICE_TYPES_COMPLIANCE.md`.

## Gestion des erreurs

Les connexions refusées et timeouts donnent « Bridge SFE hors ligne… » ; HTTP 422 détaille chaque chemin de champ ; `succes=false` affiche le texte du boîtier (IFU, taux, configuration, busy, signature). Toutes les erreurs sont journalisées avec endpoint et réponse, sans secret de licence dans le contexte. Les erreurs de configuration et de boîtier sont visibles dans le statut global. Ne jamais réessayer automatiquement une certification après un timeout : la réponse peut avoir été perdue après signature et un second envoi pourrait fiscaliser deux fois. Une procédure de rapprochement par numéro interne est nécessaire, mais le bridge 1.0 ne fournit pas d’endpoint de consultation.

## Plan de tests et validation finale

- Bridge indisponible : connexion refusée, message actionnable, log, aucun verrou/signature.
- Timeout ambigu : aucun retry automatique, brouillon conservé, alerte de rapprochement.
- Boîtier indisponible/busy/config invalide : `succes=false` propagé, aucune finalisation.
- Payload invalide, IFU/taux/groupe invalides : détails 422 visibles et journalisés.
- Succès FV : une seule certification, tous les champs persistés, facture verrouillée, stock cohérent.
- Double clic/rejeu : deuxième finalisation refusée.
- QR : contenu strictement identique à `qrcode_data`, rendu imprimable hors CDN en production.
- Impression : signature, NIM, compteurs, horodatage, contribuable et QR visibles ; brouillon clairement marqué/non imprimable selon politique.
- Avoir FA/EA : référence originale obligatoire, avoir signé, original inchangé et verrouillé.
- Paiement après signature : autorisé sans mutation des données fiscales.
- Ports/config/état/connexion/rapports X et Z : droits, schémas, succès et erreurs couverts.
- Monitoring : transitions connecté/avertissement/déconnecté, cache, log et notification.

Checklist de livraison : migration exécutée ; URL/timeout configurés ; IFU Laravel identique à `mcf/etat.ifu` ; licence/port/SFEID configurés dans le bridge ; scheduler actif ; simulateur inaccessible en production ; routes de mutation verrouillées ; avoir fiscal opérationnel ; aucune clé de licence loguée ; tests de panne et impression signés par métier.

## Écart restant du contrat bridge

Pour satisfaire totalement la mission, le bridge devrait ajouter une consultation idempotente par `numero_interne` (récupération après timeout), préciser le schéma de `/status`, déclarer les codes HTTP/erreurs métier, et documenter officiellement les valeurs de groupes TVA, types clients/articles, modes de paiement et natures d’avoir. Aucun endpoint actuel ne permet une authentification applicative explicite ni une vérification séparée de « bridge authenticated ».
