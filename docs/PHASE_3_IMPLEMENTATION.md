# Phase 3 : Migration des Données Existantes - Documentation

## ✅ État d'Avancement

**Phase 3 : EN COURS** 🚧

La commande de migration a été créée et est prête à être utilisée.

---

## 📁 Fichiers Créés

### Commande Artisan
- ✅ `app/Core/Console/Commands/MigrateExistingDataCommand.php`
- ✅ Enregistrée dans `app/Console/Kernel.php`

---

## 🎯 Objectif

Migrer les données existantes (avec `hospital_id`) vers l'architecture **database-per-tenant** où chaque hôpital a sa propre base de données MySQL.

---

## 🔄 Processus de Migration

### Pour chaque hôpital existant :

1. **Créer ou mettre à jour dans CORE**
   - Créer l'entrée dans `hospitals` (base CORE)
   - Générer le nom de la base de données
   - Configurer le domaine

2. **Créer la base de données tenant**
   - Créer `medkey_hospital_{id}` (ou nom personnalisé)
   - Charset: `utf8mb4`, Collation: `utf8mb4_unicode_ci`

3. **Créer la structure des tables**
   - Utilise `tenant:migrate` pour exécuter les migrations
   - Les migrations tenant ne doivent PAS contenir `hospital_id`

4. **Migrer les données**
   - Filtrer par `hospital_id`
   - Copier vers la base tenant (sans la colonne `hospital_id`)

5. **Mettre à jour CORE**
   - Marquer l'hôpital comme `active`
   - Enregistrer `provisioned_at`

---

## 🚀 Utilisation

### Migration de tous les hôpitaux

```bash
php artisan tenant:migrate-existing
```

### Migration d'un hôpital spécifique

```bash
php artisan tenant:migrate-existing --hospital-id=1
```

### Mode simulation (dry-run)

```bash
php artisan tenant:migrate-existing --dry-run
```

### Forcer la migration (même si la base existe)

```bash
php artisan tenant:migrate-existing --force
```

---

## 📋 Tables Migrées

Par défaut, les tables suivantes sont migrées :

- `users`
- `patients`
- `movments`
- `factures`
- `stocks`
- `stores`
- `absents`
- `cash_registers`
- `hospitalizations`
- `beds`
- `rooms`

**Tables exclues** (données partagées ou CORE) :
- `migrations`
- `hospitals` (CORE)
- `hospital_modules` (CORE)
- `system_admins` (CORE)
- `hospital_settings` (CORE)
- `pays`, `departements`, `communes`, etc. (données géographiques partagées)

---

## ⚠️ Précautions

### Avant la Migration

1. **Sauvegarde complète** de la base de données principale
2. **Vérifier** que tous les hôpitaux ont un `hospital_id` valide
3. **Tester** avec `--dry-run` d'abord
4. **Vérifier** l'espace disque disponible

### Pendant la Migration

- La migration peut prendre du temps selon le volume de données
- Ne pas interrompre le processus
- Surveiller les logs Laravel

### Après la Migration

1. **Vérifier** que les données sont correctement migrées
2. **Tester** l'accès à chaque hôpital
3. **Vérifier** que les relations sont intactes

---

## 🔍 Vérification Post-Migration

### Vérifier les données migrées

```php
php artisan tinker

// Connecter à un tenant
$hospital = \App\Core\Models\Hospital::find(1);
$service = app(\App\Core\Services\TenantConnectionService::class);
$service->connect($hospital);

// Vérifier les données
$userCount = \Modules\Acl\Entities\User::count();
$patientCount = \Modules\Patient\Entities\Patient::count();
echo "Users: $userCount, Patients: $patientCount\n";
```

### Comparer les comptes

```sql
-- Base principale
SELECT hospital_id, COUNT(*) FROM users GROUP BY hospital_id;

-- Base tenant (après connexion)
SELECT COUNT(*) FROM users;
```

---

## 🛠️ Dépannage

### Erreur : "La base de données existe déjà"

**Solution** : Utilisez `--force` pour forcer la migration

```bash
php artisan tenant:migrate-existing --force
```

### Erreur : "Table not found"

**Cause** : Les migrations tenant n'ont pas été exécutées

**Solution** :
```bash
php artisan tenant:migrate {hospital_id}
```

### Erreur : "Foreign key constraint fails"

**Cause** : Les données migrées ont des références vers des tables non migrées

**Solution** : Vérifiez l'ordre de migration des tables dans `$tablesToMigrate`

---

## 📝 Notes Importantes

1. **Les colonnes `hospital_id` sont supprimées** lors de la migration
2. **Les foreign keys vers `hospitals` sont supprimées** (plus nécessaire)
3. **Les données sont isolées** : chaque hôpital a ses propres données
4. **La base principale reste intacte** : la migration copie, ne déplace pas

---

## 🔄 Rollback

**Important** : Il n'y a pas de rollback automatique. Pour annuler :

1. Supprimer les bases tenant créées
2. Supprimer les entrées dans `hospitals` (CORE)
3. Restaurer la base principale depuis la sauvegarde

---

## 📊 Statistiques

La commande affiche :
- Nombre d'hôpitaux migrés
- Nombre d'enregistrements migrés par table
- Erreurs éventuelles
- Temps d'exécution

---

## 🚀 Prochaines Étapes

Après la migration réussie :

1. **Phase 4** : Adaptation des modèles
   - Supprimer `BelongsToHospital`
   - Supprimer `HospitalScope`
   - Supprimer les colonnes `hospital_id` des migrations

2. **Tests** : Vérifier que tout fonctionne avec la nouvelle architecture

---

**Date de création** : 2025-01-20  
**Version** : 1.0
