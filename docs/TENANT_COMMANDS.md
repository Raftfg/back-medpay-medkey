# Commandes Tenant - Documentation

## 📋 Commandes Disponibles

### 1. `tenant:migrate` - Exécuter les migrations d'un tenant

Exécute les migrations pour un hôpital (tenant) spécifique.

**Syntaxe :**
```bash
php artisan tenant:migrate {hospital_id} [options]
```

**Exemples :**
```bash
# Migration normale
php artisan tenant:migrate 1

# Migration avec suppression des tables existantes (fresh)
php artisan tenant:migrate 1 --fresh

# Migration + seeders
php artisan tenant:migrate 1 --seed

# Migration avec chemin personnalisé
php artisan tenant:migrate 1 --path=database/tenant/migrations
```

**Options :**
- `--fresh` : Supprime toutes les tables avant de migrer
- `--seed` : Exécute les seeders après la migration
- `--path=` : Chemin vers les migrations à exécuter

---

### 2. `tenant:seed` - Exécuter les seeders d'un tenant

Exécute les seeders pour un hôpital (tenant) spécifique.

**Syntaxe :**
```bash
php artisan tenant:seed {hospital_id} [options]
```

**Exemples :**
```bash
# Tous les seeders
php artisan tenant:seed 1

# Seeder spécifique
php artisan tenant:seed 1 --class=DatabaseSeeder
```

**Options :**
- `--class=` : Classe de seeder spécifique à exécuter

---

### 3. `tenant:list` - Lister tous les tenants

Affiche la liste de tous les hôpitaux (tenants) avec leurs informations.

**Syntaxe :**
```bash
php artisan tenant:list [options]
```

**Exemples :**
```bash
# Tous les hôpitaux
php artisan tenant:list

# Filtrer par statut
php artisan tenant:list --status=active
php artisan tenant:list --status=provisioning
```

**Options :**
- `--status=` : Filtrer par statut (active, inactive, suspended, provisioning)

**Sortie :**
```
📋 Liste des hôpitaux (tenants) :

+----+------------------+------------------------------+----------------------+-----------+---------------------+
| ID | Nom              | Domaine                      | Base de données      | Statut    | Créé le             |
+----+------------------+------------------------------+----------------------+-----------+---------------------+
| 1  | Hôpital Central  | hopital-central.medkey.com   | medkey_hospital_1    | ● active  | 2025-01-20 10:00:00 |
+----+------------------+------------------------------+----------------------+-----------+---------------------+

Total : 1 hôpital(s)
```

---

## 🚀 Workflow Complet

### 1. Créer un nouvel hôpital

```bash
php artisan hospital:create "Hôpital Central" \
    --domain="hopital-central.medkey.com" \
    --database="medkey_hospital_1"
```

### 2. Créer la base de données tenant

```sql
CREATE DATABASE `medkey_hospital_1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ou via phpMyAdmin / MySQL Workbench.

### 3. Exécuter les migrations

```bash
php artisan tenant:migrate 1
```

### 4. Exécuter les seeders (optionnel)

```bash
php artisan tenant:seed 1
```

### 5. Vérifier la liste des tenants

```bash
php artisan tenant:list
```

---

## ⚠️ Notes Importantes

### Migrations Tenant

Les migrations tenant doivent être placées dans `database/tenant/migrations/`.

**Important :** Les migrations tenant ne doivent **PAS** contenir de colonne `hospital_id` car chaque tenant a sa propre base de données isolée.

### Connexion Automatique

Les commandes `tenant:migrate` et `tenant:seed` :
- ✅ Se connectent automatiquement à la base de données du tenant
- ✅ Exécutent les migrations/seeders sur cette base
- ✅ Se déconnectent automatiquement après exécution

### Statut de l'Hôpital

Lors de la première migration réussie :
- Si l'hôpital est en statut `provisioning`, il passe automatiquement à `active`
- La date `provisioned_at` est mise à jour

---

## 🔧 Dépannage

### Erreur : "La base de données n'existe pas"

**Solution :** Créez d'abord la base de données :
```sql
CREATE DATABASE `medkey_hospital_1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Erreur : "Erreur de connexion"

**Vérifiez :**
1. Les identifiants MySQL dans le `.env`
2. Que la base de données existe
3. Que l'utilisateur MySQL a les droits d'accès

### Erreur : "No migrations found"

**Solution :** Vérifiez que les migrations existent dans `database/tenant/migrations/`

---

## 📝 Exemples Complets

### Exemple 1 : Provisioning complet d'un nouvel hôpital

```bash
# 1. Créer l'hôpital
php artisan hospital:create "Hôpital Saint-Antoine" \
    --domain="saint-antoine.medkey.com" \
    --database="medkey_saint_antoine"

# 2. Créer la base de données (via SQL)
# CREATE DATABASE `medkey_saint_antoine` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. Migrer
php artisan tenant:migrate 2 --seed

# 4. Vérifier
php artisan tenant:list
```

### Exemple 2 : Réinitialiser un tenant

```bash
# Supprimer toutes les tables et re-migrer
php artisan tenant:migrate 1 --fresh --seed
```

---

**Date de création** : 2025-01-20
