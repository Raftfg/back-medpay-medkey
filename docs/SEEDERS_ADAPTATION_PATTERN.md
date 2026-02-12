# Pattern d'Adaptation des Seeders pour Database-Per-Tenant

## Principe

Dans l'architecture **database-per-tenant**, chaque seeder est exécuté **après** la connexion à la base tenant. Donc :
- ❌ Ne PAS itérer sur tous les hôpitaux
- ❌ Ne PAS utiliser `hospital_id` dans les requêtes
- ❌ Ne PAS inclure `hospital_id` lors de la création de données
- ✅ Utiliser l'hôpital courant via `TenantConnectionService` si nécessaire

## Pattern de Correction

### Avant (Multi-tenant avec hospital_id)
```php
public function run()
{
    $hospitals = Hospital::where('status', 'active')->get();
    
    foreach ($hospitals as $hospital) {
        $patients = Patiente::where('hospital_id', $hospital->id)->get(); // ❌
        Category::create(['hospital_id' => $hospital->id, ...]); // ❌
    }
}
```

### Après (Database-per-tenant)
```php
public function run()
{
    // On est déjà connecté à la base tenant
    // Récupérer l'hôpital courant si nécessaire
    $tenantService = app(\App\Core\Services\TenantConnectionService::class);
    $hospital = $tenantService->getCurrentHospital();
    
    // Les requêtes ne filtrent plus par hospital_id
    $patients = Patiente::all(); // ✅ Toutes les données sont déjà isolées
    
    // Création sans hospital_id
    Category::create([...]); // ✅ Pas de hospital_id
}
```

## Seeders à Corriger

- ✅ `CategoryTableSeeder` - Corrigé
- ✅ `UserTableSeeder` - Corrigé
- ⏳ `ProductTableSeeder` - À corriger
- ⏳ `ServiceTableSeeder` - À corriger
- ⏳ `MedicalActTableSeeder` - À corriger
- ⏳ `PackTableSeeder` - À corriger
- ⏳ `TypeVacationSeeder` - À corriger
- ⏳ `AbsentTableSeeder` - À corriger
- ⏳ `MovmentTableSeeder` - À corriger
- ⏳ `ConsultationRecordTableSeeder` - À corriger
- ⏳ Et tous les autres...

## Note Importante

Le script `seed-all-hospitals.php` se connecte déjà à chaque base tenant avant d'exécuter les seeders, donc les seeders n'ont pas besoin de gérer la connexion eux-mêmes.
