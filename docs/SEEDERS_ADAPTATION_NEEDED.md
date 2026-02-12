# Adaptation des Seeders pour Database-Per-Tenant

## Problème

Les seeders utilisent encore `hospital_id` dans les requêtes et lors de la création de données, mais cette colonne a été supprimée des tables tenant dans l'architecture database-per-tenant.

## Solution

Dans l'architecture database-per-tenant :
- Chaque base tenant ne contient QUE les données de son hôpital
- Pas besoin de filtrer par `hospital_id` dans les requêtes
- Pas besoin d'inclure `hospital_id` lors de la création de données

## Seeders à adapter

Les seeders doivent :
1. Utiliser `App\Core\Models\Hospital` pour récupérer la liste des hôpitaux (✅ déjà fait)
2. Pour chaque hôpital, se connecter à sa base tenant
3. Créer les données SANS `hospital_id` dans les requêtes et les insertions

## Exemple de correction

**Avant :**
```php
$hospitals = Hospital::where('status', 'active')->get();
foreach ($hospitals as $hospital) {
    $patients = Patiente::where('hospital_id', $hospital->id)->get(); // ❌ hospital_id n'existe plus
    Category::create(['hospital_id' => $hospital->id, ...]); // ❌ hospital_id n'existe plus
}
```

**Après :**
```php
$hospitals = \App\Core\Models\Hospital::where('status', 'active')->get();
foreach ($hospitals as $hospital) {
    // Se connecter à la base tenant
    $tenantService->connect($hospital);
    
    // Les requêtes ne filtrent plus par hospital_id
    $patients = Patiente::all(); // ✅ Toutes les données sont déjà isolées
    Category::create([...]); // ✅ Pas de hospital_id
}
```

## Note importante

Les seeders doivent être exécutés **après** la connexion à la base tenant, ce qui est déjà géré par le script `seed-all-hospitals.php`.
