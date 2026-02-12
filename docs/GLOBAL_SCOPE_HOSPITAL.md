# Global Scope HospitalScope - Documentation

## Vue d'ensemble

Le `HospitalScope` est un Global Scope Laravel qui filtre automatiquement toutes les requêtes Eloquent par `hospital_id`. Il assure l'isolation complète des données entre les différents hôpitaux (tenants) sans avoir à ajouter manuellement `where('hospital_id', ...)` à chaque requête.

## Fonctionnement

### Application automatique

Le scope est appliqué automatiquement via le trait `BelongsToHospital` :

```php
use App\Traits\BelongsToHospital;

class Patient extends Model
{
    use BelongsToHospital;
}
```

### Filtrage automatique

Toutes les requêtes sont automatiquement filtrées :

```php
// Cette requête retourne uniquement les patients de l'hôpital courant
$patients = Patiente::all();

// Équivalent à :
// Patiente::where('hospital_id', currentHospitalId())->get();
```

### Assignation automatique

Lors de la création d'un modèle, `hospital_id` est automatiquement assigné :

```php
// hospital_id est automatiquement défini
$patient = Patiente::create([
    'firstname' => 'John',
    'lastname' => 'Doe',
    // hospital_id est ajouté automatiquement
]);
```

## Utilisation normale

### Requêtes standard

```php
// Tous les patients de l'hôpital courant
$patients = Patiente::all();

// Recherche dans l'hôpital courant uniquement
$patient = Patiente::where('firstname', 'John')->first();

// Comptage dans l'hôpital courant uniquement
$count = Patiente::count();

// Relations (automatiquement filtrées)
$user = User::with('patients')->first();
// Les patients retournés appartiennent à l'hôpital courant
```

### Création de modèles

```php
// hospital_id est automatiquement défini
$patient = Patiente::create([
    'firstname' => 'John',
    'lastname' => 'Doe',
]);

// Vous pouvez aussi le définir manuellement si nécessaire
$patient = Patiente::create([
    'firstname' => 'John',
    'lastname' => 'Doe',
    'hospital_id' => 2, // Surcharge l'assignation automatique
]);
```

## Désactivation ponctuelle

### Pour les administrateurs globaux

Si vous avez besoin de voir toutes les données (par exemple pour un administrateur global) :

```php
// Méthode 1 : Via le scope
$allPatients = Patiente::withoutHospital()->get();

// Méthode 2 : Via withoutGlobalScope
use App\Scopes\HospitalScope;
$allPatients = Patiente::withoutGlobalScope(HospitalScope::class)->get();

// Méthode 3 : Via allHospitals
$allPatients = Patiente::allHospitals()->get();
```

### Pour un hôpital spécifique

```php
// Récupérer les patients d'un hôpital spécifique
$patients = Patiente::forHospital(2)->get();

// Équivalent à :
// Patiente::withoutGlobalScope(HospitalScope::class)
//     ->where('hospital_id', 2)
//     ->get();
```

### Dans les relations

```php
// Désactiver le scope pour une relation spécifique
$user = User::with(['patients' => function ($query) {
    $query->withoutHospital();
}])->first();
```

## Exemples d'utilisation

### Contrôleur standard (isolation automatique)

```php
class PatientController extends Controller
{
    public function index()
    {
        // Retourne uniquement les patients de l'hôpital courant
        $patients = Patiente::all();
        
        return response()->json($patients);
    }

    public function store(Request $request)
    {
        // hospital_id est automatiquement défini
        $patient = Patiente::create($request->validated());
        
        return response()->json($patient, 201);
    }
}
```

### Contrôleur administrateur global

```php
class AdminController extends Controller
{
    public function allPatients()
    {
        // Voir tous les patients de tous les hôpitaux
        $patients = Patiente::withoutHospital()->get();
        
        return response()->json($patients);
    }

    public function patientsByHospital($hospitalId)
    {
        // Voir les patients d'un hôpital spécifique
        $patients = Patiente::forHospital($hospitalId)->get();
        
        return response()->json($patients);
    }

    public function statistics()
    {
        // Statistiques globales
        $totalPatients = Patiente::withoutHospital()->count();
        $patientsByHospital = Patiente::withoutHospital()
            ->selectRaw('hospital_id, count(*) as total')
            ->groupBy('hospital_id')
            ->get();
        
        return response()->json([
            'total' => $totalPatients,
            'by_hospital' => $patientsByHospital,
        ]);
    }
}
```

### Requêtes complexes

```php
// Jointures avec isolation automatique
$patients = Patiente::join('users', 'patients.users_id', '=', 'users.id')
    ->where('users.email', 'like', '%@example.com')
    ->get();
// Tous les résultats sont filtrés par hospital_id

// Agrégations
$stats = Patiente::selectRaw('
        COUNT(*) as total,
        AVG(age) as avg_age,
        MIN(age) as min_age,
        MAX(age) as max_age
    ')
    ->first();
// Les statistiques sont calculées uniquement pour l'hôpital courant

// Relations avec scope
$user = User::with('patients')->first();
// Seuls les patients de l'hôpital courant sont chargés
```

## Modèles avec le trait BelongsToHospital

Les modèles suivants ont le trait `BelongsToHospital` appliqué :

- ✅ `User` (Modules\Acl\Entities\User)
- ✅ `Patiente` (Modules\Patient\Entities\Patiente)
- ✅ `CashRegister` (Modules\Cash\Entities\CashRegister)
- ✅ `Movment` (Modules\Movment\Entities\Movment)
- ✅ `Facture` (Modules\Payment\Entities\Facture)
- ✅ `Stock` (Modules\Stock\Entities\Stock)
- ✅ `Store` (Modules\Stock\Entities\Store)
- ✅ `Absent` (Modules\Absence\Entities\Absent)
- ✅ `Room` (Modules\Hospitalization\Entities\Room)
- ✅ `Bed` (Modules\Hospitalization\Entities\Bed)
- ✅ `BedPatient` (Modules\Hospitalization\Entities\BedPatient)

## Ajouter le trait à un nouveau modèle

```php
<?php

namespace Modules\YourModule\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToHospital;

class YourModel extends Model
{
    use BelongsToHospital;
    
    // ... reste du modèle
}
```

## Méthodes disponibles

### Scopes du trait

- `withoutHospital()` : Désactive le scope pour cette requête
- `forHospital($hospitalId)` : Filtre par un hôpital spécifique
- `allHospitals()` : Inclut tous les hôpitaux (alias de `withoutHospital()`)

### Exemples

```php
// Sans le scope
$all = Patiente::withoutHospital()->get();

// Pour un hôpital spécifique
$hospital2 = Patiente::forHospital(2)->get();

// Tous les hôpitaux
$all = Patiente::allHospitals()->get();
```

## Cas particuliers

### Requêtes brutes (DB facade)

Les requêtes brutes ne sont pas affectées par le scope :

```php
// Cette requête n'est PAS filtrée
$patients = DB::table('patients')->get();

// Pour filtrer manuellement :
$hospitalId = currentHospitalId();
$patients = DB::table('patients')
    ->where('hospital_id', $hospitalId)
    ->get();
```

### Commandes Artisan

Dans les commandes Artisan, le scope peut ne pas fonctionner si le tenant n'est pas défini :

```php
// Dans une commande Artisan
public function handle()
{
    // Définir le tenant si nécessaire
    if ($hospitalId = $this->option('hospital')) {
        setTenant($hospitalId);
    }
    
    // Maintenant les requêtes sont filtrées
    $patients = Patiente::all();
}
```

### Tests

Dans les tests, vous pouvez désactiver le scope ou définir un tenant :

```php
// Désactiver le scope pour les tests
$patients = Patiente::withoutHospital()->get();

// Ou définir un tenant de test
setTenant(1);
$patients = Patiente::all(); // Filtré par hospital_id = 1
```

## Performance

Le scope ajoute une condition `WHERE` à chaque requête. Cela peut avoir un impact sur les performances si :

- Vous avez beaucoup de requêtes
- Les index ne sont pas optimisés

**Recommandations** :
- Assurez-vous que `hospital_id` est indexé dans toutes les tables
- Utilisez `withoutHospital()` uniquement quand c'est vraiment nécessaire
- Surveillez les requêtes lentes avec Laravel Telescope ou Debugbar

## Sécurité

⚠️ **IMPORTANT** : Le scope est une couche de sécurité supplémentaire, mais ne remplace pas la validation côté application.

**Bonnes pratiques** :
1. Validez toujours que l'utilisateur a accès à l'hôpital
2. Utilisez des Policies Laravel pour l'autorisation
3. Ne désactivez le scope que pour les administrateurs globaux authentifiés
4. Loggez les accès aux données multi-tenant

## Dépannage

### Le scope ne fonctionne pas

1. Vérifiez que le trait `BelongsToHospital` est utilisé
2. Vérifiez que `hospital_id` existe dans la table
3. Vérifiez que le middleware `TenantMiddleware` est exécuté
4. Vérifiez que `currentHospitalId()` retourne une valeur

### Toutes les données sont retournées

1. Vérifiez que le scope est bien appliqué
2. Vérifiez que `hospital_id` n'est pas `null` dans la base de données
3. Vérifiez que le middleware a bien défini le tenant

### Erreurs de performance

1. Vérifiez les index sur `hospital_id`
2. Utilisez `EXPLAIN` pour analyser les requêtes
3. Considérez l'utilisation de cache pour les requêtes fréquentes
