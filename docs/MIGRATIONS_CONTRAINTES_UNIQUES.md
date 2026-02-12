# 🔧 MIGRATIONS - CORRECTION DES CONTRAINTES UNIQUES MULTI-TENANT

**Date**: 2025-01-15  
**Version**: 1.0  
**Status**: ✅ **MIGRATIONS CRÉÉES**

---

## 📊 PROBLÈME IDENTIFIÉ

Plusieurs tables ont des contraintes uniques sur des colonnes (`name`, `code`) qui empêchent le même nom/code dans différents hôpitaux. Pour le multi-tenant, ces contraintes doivent être composites : `(hospital_id, name)` ou `(hospital_id, code)`.

---

## ✅ MIGRATIONS CRÉÉES

| Migration | Table | Contrainte Corrigée | Status |
|-----------|-------|---------------------|--------|
| `2025_01_15_100042_fix_type_products_unique_constraint.php` | `type_products` | `name` → `(hospital_id, name)` | ✅ |
| `2025_01_15_100043_fix_categories_unique_constraint.php` | `categories` | `name` → `(hospital_id, name)` | ✅ |
| `2025_01_15_100044_fix_sale_units_unique_constraint.php` | `sale_units` | `name` → `(hospital_id, name)` | ✅ |
| `2025_01_15_100045_fix_conditioning_units_unique_constraint.php` | `conditioning_units` | `name` → `(hospital_id, name)` | ✅ |
| `2025_01_15_100046_fix_administration_routes_unique_constraint.php` | `administration_routes` | `name` → `(hospital_id, name)` | ✅ |
| `2025_01_15_100047_fix_stores_unique_constraint.php` | `stores` | `code` + `name` → `(hospital_id, code)` + `(hospital_id, name)` | ✅ |
| `2025_01_15_100048_fix_products_unique_constraint.php` | `products` | `code` → `(hospital_id, code)` | ✅ |
| `2025_01_15_100049_fix_stocks_unique_constraint.php` | `stocks` | `name` → `(hospital_id, name)` | ✅ |
| `2025_01_15_100050_fix_suppliers_unique_constraint.php` | `suppliers` | `email` + `phone_number` → `(hospital_id, email)` + `(hospital_id, phone_number)` | ✅ |
| `2025_01_15_100051_fix_rooms_unique_constraint.php` | `rooms` | `code` → `(hospital_id, code)` | ✅ |
| `2025_01_15_100052_fix_beds_unique_constraint.php` | `beds` | `code` + `name` → `(hospital_id, code)` + `(hospital_id, name)` | ✅ |
| `2025_01_15_100054_fix_services_unique_constraint.php` | `services` | `code` → `(hospital_id, code)` | ✅ |
| `2025_01_15_100056_fix_medical_acts_unique_constraint.php` | `medical_acts` | `code` → `(hospital_id, code)` | ✅ |

**Total**: 13 migrations créées

---

## 🔄 ORDRE D'EXÉCUTION

Ces migrations doivent être exécutées **APRÈS** les migrations qui ajoutent `hospital_id` aux tables :

1. ✅ Migrations `add_hospital_id_to_*_table.php` (déjà créées)
2. ✅ Migrations `fix_*_unique_constraint.php` (nouvelles migrations)

### Commande d'exécution

```bash
# Exécuter toutes les migrations du module Stock
php artisan module:migrate Stock

# Ou exécuter toutes les migrations de tous les modules
php artisan module:migrate
```

---

## 📋 DÉTAILS DES CORRECTIONS

### 1. type_products
- **Avant**: `name` unique (global)
- **Après**: `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "Drugs" dans plusieurs hôpitaux

### 2. categories
- **Avant**: `name` unique (global)
- **Après**: `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "COMPRIME" dans plusieurs hôpitaux

### 3. sale_units
- **Avant**: `name` unique (global)
- **Après**: `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "U", "MG", etc. dans plusieurs hôpitaux

### 4. conditioning_units
- **Avant**: `name` unique (global)
- **Après**: `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "Ampoule", "Plaquette", etc. dans plusieurs hôpitaux

### 5. administration_routes
- **Avant**: `name` unique (global)
- **Après**: `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "Orale", "Rectum", etc. dans plusieurs hôpitaux

### 6. stores
- **Avant**: `code` unique + `name` unique (global)
- **Après**: `(hospital_id, code)` unique + `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "MAG-67205" dans plusieurs hôpitaux

### 7. products
- **Avant**: `code` unique (global)
- **Après**: `(hospital_id, code)` unique (par hôpital)
- **Impact**: Permet "DRU-COM-15896" dans plusieurs hôpitaux

### 8. stocks
- **Avant**: `name` unique (global)
- **Après**: `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "Stock Gros" dans plusieurs hôpitaux

### 9. suppliers
- **Avant**: `email` unique + `phone_number` unique (global)
- **Après**: `(hospital_id, email)` unique + `(hospital_id, phone_number)` unique (par hôpital)
- **Impact**: Permet "sobap@gmail.com" et "61524876" dans plusieurs hôpitaux
- **Note**: Pour `email` nullable, MySQL permet plusieurs NULL dans une contrainte unique composite

### 10. rooms
- **Avant**: `code` unique (global)
- **Après**: `(hospital_id, code)` unique (par hôpital)
- **Impact**: Permet "CH-001" dans plusieurs hôpitaux

### 11. beds
- **Avant**: `code` unique + `name` unique (global)
- **Après**: `(hospital_id, code)` unique + `(hospital_id, name)` unique (par hôpital)
- **Impact**: Permet "LIT-001" et "Lit 1" dans plusieurs hôpitaux

### 12. services
- **Avant**: `code` unique (global)
- **Après**: `(hospital_id, code)` unique (par hôpital)
- **Impact**: Permet le même code de service dans plusieurs hôpitaux
- **Note**: Pour `code` nullable, MySQL permet plusieurs NULL dans une contrainte unique composite

### 13. medical_acts
- **Avant**: `code` unique (global)
- **Après**: `(hospital_id, code)` unique (par hôpital)
- **Impact**: Permet le même code d'acte médical dans plusieurs hôpitaux

---

## ⚠️ IMPORTANT

Ces migrations doivent être exécutées **AVANT** d'exécuter les seeders, sinon vous obtiendrez des erreurs de contrainte unique.

### Ordre complet recommandé :

```bash
# 1. Migrations qui ajoutent hospital_id
php artisan module:migrate

# 2. Migrations qui corrigent les contraintes uniques (automatique avec module:migrate)
# Les nouvelles migrations seront détectées et exécutées

# 3. Seeders
php artisan module:seed Administration
php artisan module:seed Stock
# etc.
```

---

## 🔍 VÉRIFICATION

Après exécution des migrations, vous pouvez vérifier les contraintes :

```sql
-- Vérifier les index uniques sur type_products
SHOW INDEX FROM type_products WHERE Non_unique = 0;

-- Devrait montrer : type_products_hospital_name_unique sur (hospital_id, name)
```

---

**Document généré le**: 2025-01-15  
**Version**: 1.0  
**Status**: ✅ **MIGRATIONS CRÉÉES**
