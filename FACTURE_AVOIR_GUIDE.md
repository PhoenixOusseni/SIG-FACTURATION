# 📋 Module Facture d'Avoir - Guide d'Installation et d'Utilisation

## 🎯 Qu'est-ce qu'une Facture d'Avoir ?

Une facture d'avoir est un document comptable qui permet d'**annuler totalement ou partiellement une facture déjà établie**. Elle est utilisée pour :

- **Retour de marchandises** : le client renvoie tout ou partie des produits achetés
- **Erreur sur la facture initiale** : correction d'erreur de quantité, prix, TVA ou référence
- **Remise ou rabais accordé** après la facturation
- **Annulation d'une vente** ou d'une prestation de services
- **Produit défectueux** ou non conforme donnant lieu à un remboursement

---

## 🚀 Installation du Module

### Étape 1 : Exécuter les Migrations

Les migrations créent les tables nécessaires dans la base de données :

```bash
php artisan migrate
```

Cela crée deux tables :
- **`facture_avoirs`** : Enregistre les avoirs
- **`element_facture_avoirs`** : Enregistre les produits de chaque avoir

### Étape 2 : Vérification

Une fois les migrations exécutées, le module sera automatiquement disponible :
- ✅ Menu latéral : "Factures clients" → "Factures d'avoir"
- ✅ Routes : `http://votre-site/module_facture_avoir`

---

## 📁 Fichiers Créés

### **Modèles** (`app/Models/`)
- ✅ `FactureAvoir.php` - Modèle principal avec relations
- ✅ `ElementFactureAvoir.php` - Modèle pour les éléments
- ✅ `Facture.php` - Mise à jour avec relation aux avoirs

### **Contrôleur** (`app/Http/Controllers/`)
- ✅ `FactureAvoirController.php` - Gestion complète du cycle de vie

### **Routes** (`routes/web.php`)
- ✅ Ajout import et ressource `module_facture_avoir`
- ✅ Ajout routes spéciales : affichage liste, impression, suppression

### **Vues** (`resources/views/pages/facture_avoir/`)
- ✅ `index.blade.php` - Formulaire création + liste des avoirs
- ✅ `show.blade.php` - Affichage détails d'un avoir
- ✅ `edit.blade.php` - Modification d'un avoir
- ✅ `liste.blade.php` - Liste complète de tous les avoirs
- ✅ `print.blade.php` - Impression professionnelle

### **Navigation** (`resources/views/partials/sideNav.blade.php`)
- ✅ Ajout lien dans menu "Factures clients"

### **Migrations** (`database/migrations/`)
- ✅ `2026_07_03_000001_create_facture_avoirs_table.php`
- ✅ `2026_07_03_000002_create_element_facture_avoirs_table.php`

---

## 🎮 Utilisation du Module

### **Créer une Facture d'Avoir**

1. Allez dans **Factures clients** → **Factures d'avoir**
2. Remplissez le formulaire :
   - **Facture d'origine** : sélectionnez la facture à corriger
   - **Client** : le client sera pré-sélectionné selon la facture
   - **Motif** : choisissez parmi les 5 motifs disponibles
   - **Éléments** : ajoutez les produits retournés/à corriger
3. Cliquez **"Enregistrer la facture d'avoir"**

### **Consulter un Avoir**

- Cliquez sur l'icône 👁️ dans la liste
- Consultez les détails compllets
- Options : Modifier, Imprimer, Supprimer, Retour

### **Imprimer un Avoir**

- Depuis la page d'un avoir : **Imprimer** 
- Format : Document professionnel avec :
  - En-tête "FACTURE D'AVOIR"
  - Tous les détails du document
  - Tableau des produits
  - Montant total à avoir

### **Modifier un Avoir**

- Cliquez **Modifier** depuis la page d'un avoir
- Changez les informations nécessaires
- Cliquez **Enregistrer les modifications**

### **Supprimer un Avoir**

- Depuis la page d'un avoir : **Supprimer**
- Une confirmation vous sera demandée

---

## 🏷️ Motifs d'Avoir Disponibles

| Motif | Cas d'utilisation |
|-------|------------------|
| **Retour** | Client retourne les marchandises |
| **Erreur** | Correction d'une erreur sur la facture |
| **Annulation** | Annulation totale de la vente |
| **Remise** | Remise ou rabais accordé après facturation |
| **Défectueux** | Produit défectueux ou non conforme |

---

## 📊 Code de l'Avoir

Format automatique : **`AVR-XXXXX/YYYY`**

Exemple : `AVR-00001/2026`

- **AVR** : Préfixe (Avoir)
- **XXXXX** : Numéro séquentiel (réinitialisé chaque année)
- **YYYY** : Année en cours

---

## 💾 Structure de la Base de Données

### Table `facture_avoirs`
```
id                  INTEGER PRIMARY KEY
code               VARCHAR (ex: AVR-00001/2026)
reference          VARCHAR (optionnel)
date               DATE
statut             VARCHAR (par défaut: "en attente")
objet              VARCHAR (optionnel)
motif              VARCHAR (retour|erreur|annulation|remise|produit_defectueux)
remise_1           DECIMAL (optionnel)
remise_2           DECIMAL (optionnel)
facture_id         FOREIGN KEY → factures
users_id           FOREIGN KEY → users
contribuables_id   FOREIGN KEY → contribuables
service_id         FOREIGN KEY → services
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

### Table `element_facture_avoirs`
```
id                  INTEGER PRIMARY KEY
facture_avoir_id    FOREIGN KEY → facture_avoirs
produits_id         FOREIGN KEY → produits
quantite            INTEGER
prix_unitaire       DECIMAL
montant_total       DECIMAL (calculé)
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

---

## 🔍 Vérification de l'Installation

Après migration, vérifiez :

```bash
# Lister les migrations
php artisan migrate:status

# Vérifier les tables (MySQL)
SHOW TABLES LIKE 'facture_avoir%';
```

Vous devriez voir :
- ✅ `facture_avoirs` - migrated
- ✅ `element_facture_avoirs` - migrated

---

## ⚙️ Configuration Supplémentaire

Le module est **prêt à l'emploi**. Aucune configuration supplémentaire requise.

---

## 📝 Notes Importantes

1. **Traçabilité** : Chaque avoir peut être tracé vers sa facture d'origine
2. **Stock** : L'avoir n'impacte pas directement le stock (comme la proforma)
3. **Comptabilité** : L'avoir crée un crédit pour le client qui peut être :
   - Remboursé
   - Déduit d'une prochaine facture
4. **Légal** : Tous les avoirs ont un code unique et une trace d'audit

---

## 🆘 Troubleshooting

### Le module n'apparaît pas dans le menu
- Vérifiez que les migrations ont été exécutées : `php artisan migrate`
- Actualisez la page

### Erreur lors de la création d'un avoir
- Vérifiez que la facture d'origine existe
- Vérifiez que le client est valide

### Problème d'impression
- Assurez-vous que JavaScript est activé
- Essayez un autre navigateur

---

## 🎓 Exemple d'Utilisation Complète

1. **Situation** : Un client a reçu 2 ordinateurs portables à 400 000 FCFA l'unité (facture FAC-00001)
2. **Problème** : Un ordinateur est défectueux
3. **Solution** :
   - Créer une facture d'avoir avec :
     - Facture d'origine : FAC-00001
     - Motif : Produit défectueux
     - Produit : 1 × Ordinateur portable à 400 000 FCFA
     - Montant : 400 000 FCFA
4. **Résultat** :
   - Code automatique généré : AVR-00001/2026
   - Client a un crédit de 400 000 FCFA
   - Document imprimable pour justification comptable

---

## 📞 Support

Pour toute question ou problème :
- Consultez la section "Aide & Support" du menu
- Vérifiez les logs Laravel : `storage/logs/`

---

**✅ Module Facture d'Avoir - Installation Complète !**
