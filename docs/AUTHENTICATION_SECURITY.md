# Authentification & Sécurité Multi-Tenant

## Vue d'ensemble

Ce document décrit l'implémentation de l'authentification et de la sécurité pour la plateforme multi-tenant. Le système garantit que :

1. **Isolation des utilisateurs** : Chaque utilisateur appartient à un seul hôpital
2. **Isolation des données** : Les utilisateurs ne peuvent accéder qu'aux données de leur hôpital
3. **Sécurité renforcée** : Vérifications à plusieurs niveaux (middleware, policies, scopes)

---

## 1. Middleware d'authentification

### EnsureUserBelongsToHospital

Ce middleware vérifie que l'utilisateur authentifié appartient à l'hôpital (tenant) courant.

**Localisation** : `app/Http/Middleware/EnsureUserBelongsToHospital.php`

**Fonctionnalités** :
- Vérifie que l'utilisateur est authentifié
- Vérifie que l'hôpital courant est défini
- Vérifie que l'utilisateur appartient à l'hôpital courant
- Bloque l'accès avec une erreur 403 si les conditions ne sont pas remplies
- Log les tentatives d'accès non autorisées

**Enregistrement** :
- Automatiquement appliqué aux routes `api` dans `app/Http/Kernel.php`
- Peut être utilisé individuellement avec `'ensure.user.hospital'`

**Exemple d'utilisation** :
```php
// Dans routes/api.php
Route::middleware(['auth:api', 'ensure.user.hospital'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
});
```

---

## 2. Authentification adaptée

### Login avec vérification d'hôpital

Le contrôleur `AuthController` a été adapté pour :

1. **Vérifier l'hôpital courant** : Avant de chercher l'utilisateur, on vérifie que l'hôpital est défini
2. **Filtrer par hôpital** : La recherche d'utilisateur se fait uniquement dans l'hôpital courant
3. **Double vérification** : Après authentification, on vérifie que l'utilisateur appartient bien à l'hôpital courant
4. **Retour des informations** : La réponse inclut les informations de l'hôpital

**Code modifié** : `Modules/Acl/Http/Controllers/Api/V1/AuthController.php`

**Exemple de réponse** :
```json
{
    "access_token": "...",
    "user": {
        "id": 1,
        "email": "user@example.com",
        "hospital_id": 1
    },
    "role": {...},
    "permissions": [...],
    "hospital": {
        "id": 1,
        "name": "Hôpital Central",
        "domain": "hopital1.ma-plateforme.com"
    }
}
```

---

## 3. Policies Multi-Tenant

### BaseTenantPolicy

Classe de base pour toutes les policies multi-tenant.

**Localisation** : `app/Policies/BaseTenantPolicy.php`

**Méthodes utilitaires** :
- `belongsToSameHospital(User $user, Model $model)` : Vérifie que l'utilisateur et le modèle appartiennent au même hôpital
- `belongsToCurrentHospital(User $user)` : Vérifie que l'utilisateur appartient à l'hôpital courant
- `isGlobalAdmin(User $user)` : Vérifie si l'utilisateur est super-admin (accès à tous les hôpitaux)

### PatientPolicy

Exemple de policy pour les patients.

**Localisation** : `app/Policies/PatientPolicy.php`

**Méthodes** :
- `viewAny()` : Voir la liste des patients
- `view()` : Voir un patient spécifique
- `create()` : Créer un patient
- `update()` : Modifier un patient
- `delete()` : Supprimer un patient
- `restore()` : Restaurer un patient supprimé
- `forceDelete()` : Supprimer définitivement un patient

**Exemple d'utilisation** :
```php
// Dans un contrôleur
public function show(Patiente $patient)
{
    $this->authorize('view', $patient);
    return new PatientResource($patient);
}
```

### Autres Policies

- `CashRegisterPolicy` : Pour les caisses
- `FacturePolicy` : Pour les factures
- Créez d'autres policies en étendant `BaseTenantPolicy`

**Enregistrement** : Dans `app/Providers/AuthServiceProvider.php`

```php
protected $policies = [
    \Modules\Patient\Entities\Patiente::class => \App\Policies\PatientPolicy::class,
    \Modules\Cash\Entities\CashRegister::class => \App\Policies\CashRegisterPolicy::class,
    \Modules\Payment\Entities\Facture::class => \App\Policies\FacturePolicy::class,
];
```

---

## 4. Rôles et Permissions par Hôpital

### Architecture

Le système utilise **Spatie Laravel Permission** avec isolation par hôpital :

1. **Rôles** : Peuvent être définis globalement ou par hôpital
2. **Permissions** : Peuvent être définies globalement ou par hôpital
3. **Assignation** : Les rôles et permissions sont assignés aux utilisateurs dans le contexte de leur hôpital

### Bonnes pratiques

1. **Préfixer les permissions** : Utilisez des préfixes pour identifier les permissions par module
   - `create patients`
   - `update patients`
   - `delete patients`
   - `view patients`

2. **Vérifier dans les policies** : Utilisez `$user->can('permission')` dans les policies

3. **Middleware de permissions** : Utilisez le middleware Spatie pour protéger les routes
   ```php
   Route::middleware(['auth:api', 'permission:create patients'])->group(function () {
       Route::post('/patients', [PatientController::class, 'store']);
   });
   ```

### Exemple de création de rôles et permissions

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Créer des permissions
Permission::create(['name' => 'create patients']);
Permission::create(['name' => 'update patients']);
Permission::create(['name' => 'delete patients']);
Permission::create(['name' => 'view patients']);

// Créer un rôle
$role = Role::create(['name' => 'medecin']);

// Assigner des permissions au rôle
$role->givePermissionTo(['view patients', 'create patients', 'update patients']);

// Assigner le rôle à un utilisateur
$user->assignRole('medecin');
```

---

## 5. Sécurité renforcée

### Couches de sécurité

1. **Middleware TenantMiddleware** : Définit l'hôpital courant
2. **Middleware EnsureUserBelongsToHospital** : Vérifie l'appartenance de l'utilisateur
3. **Global Scope HospitalScope** : Filtre automatiquement les requêtes
4. **Policies** : Vérifient les autorisations au niveau des actions
5. **Validation dans les contrôleurs** : Vérifications supplémentaires si nécessaire

### Logs de sécurité

Les tentatives d'accès non autorisées sont loggées avec :
- ID de l'utilisateur
- ID de l'hôpital de l'utilisateur
- ID de l'hôpital courant
- Adresse IP
- URL de la requête

**Exemple de log** :
```
[2025-01-15 10:30:00] local.WARNING: Tentative d'accès non autorisé {
    "user_id": 5,
    "user_hospital_id": 2,
    "current_hospital_id": 1,
    "ip": "192.168.1.100",
    "url": "https://hopital1.ma-plateforme.com/api/patients/123"
}
```

---

## 6. Exemples d'utilisation

### Dans un contrôleur

```php
use Illuminate\Http\Request;
use Modules\Patient\Entities\Patiente;

class PatientController extends Controller
{
    public function index()
    {
        // Le Global Scope filtre automatiquement par hospital_id
        $patients = Patiente::all();
        return PatientResource::collection($patients);
    }

    public function show(Patiente $patient)
    {
        // La Policy vérifie l'appartenance à l'hôpital
        $this->authorize('view', $patient);
        return new PatientResource($patient);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patiente::class);
        
        $data = $request->validated();
        // hospital_id sera automatiquement ajouté par le Global Scope
        $patient = Patiente::create($data);
        
        return new PatientResource($patient);
    }
}
```

### Dans une route

```php
// routes/api.php
Route::middleware(['auth:api', 'ensure.user.hospital'])->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store'])
        ->middleware('permission:create patients');
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
    Route::put('/patients/{patient}', [PatientController::class, 'update'])
        ->middleware('permission:update patients');
});
```

### Désactiver temporairement le scope (admin global)

```php
// Pour un super-admin qui doit voir tous les hôpitaux
use App\Scopes\HospitalScope;

// Désactiver le scope
Patiente::withoutGlobalScope(HospitalScope::class)->get();

// Ou pour une requête spécifique
Patiente::withoutGlobalScopes()->get();
```

---

## 7. Checklist de sécurité

Avant la mise en production, vérifiez :

- [ ] Tous les modèles critiques ont `hospital_id`
- [ ] Tous les modèles critiques utilisent `HospitalScope`
- [ ] Toutes les routes API sont protégées par `ensure.user.hospital`
- [ ] Toutes les actions critiques ont des policies
- [ ] Les permissions sont correctement définies
- [ ] Les logs de sécurité sont activés
- [ ] Les tests d'intégration vérifient l'isolation
- [ ] La documentation est à jour

---

## 8. Troubleshooting

### Problème : "Aucun hôpital défini pour cette requête"

**Cause** : Le middleware `TenantMiddleware` n'a pas pu identifier l'hôpital.

**Solutions** :
1. Vérifiez que le domaine est correctement configuré
2. Vérifiez que l'hôpital existe dans la base de données
3. Vérifiez les headers HTTP si vous utilisez `X-Tenant-Domain`

### Problème : "Vous n'avez pas accès aux données de cet hôpital"

**Cause** : L'utilisateur tente d'accéder aux données d'un autre hôpital.

**Solutions** :
1. Vérifiez que l'utilisateur a le bon `hospital_id`
2. Vérifiez que le domaine correspond à l'hôpital de l'utilisateur
3. Vérifiez les logs pour plus de détails

### Problème : Les données ne sont pas filtrées

**Cause** : Le `HospitalScope` n'est pas appliqué au modèle.

**Solutions** :
1. Vérifiez que le modèle utilise le trait `BelongsToHospital`
2. Vérifiez que le scope est bien enregistré dans le `boot()` du modèle
3. Vérifiez que `currentHospitalId()` retourne une valeur

---

## 9. Évolutions futures

- [ ] Support des utilisateurs multi-hôpitaux (avec table pivot)
- [ ] Audit trail complet des accès
- [ ] Notifications en cas de tentatives d'accès non autorisées
- [ ] Dashboard de sécurité pour les administrateurs
- [ ] Intégration avec des systèmes de détection d'intrusion
