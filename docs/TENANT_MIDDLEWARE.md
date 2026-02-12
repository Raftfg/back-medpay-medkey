# Middleware TenantMiddleware - Documentation

## Vue d'ensemble

Le middleware `TenantMiddleware` détecte automatiquement le tenant (hôpital) à partir du domaine de la requête et le stocke dans la requête, la session et l'application pour un accès facile dans toute l'application.

## Fonctionnement

### 1. Détection du domaine

Le middleware récupère le domaine de la requête de plusieurs façons :

- **Production** : Utilise le host de la requête (ex: `hopital1.ma-plateforme.com`)
- **Développement local** : 
  - Header personnalisé `X-Tenant-Domain`
  - Paramètre de requête `tenant_domain`
  - Sous-domaine local (ex: `hopital1.localhost`)

### 2. Identification de l'hôpital

Le middleware recherche l'hôpital correspondant dans la base de données :
- Par domaine exact (`domain`)
- Par slug (si le domaine commence par le slug)

### 3. Stockage du tenant

Une fois l'hôpital identifié, il est stocké dans :
- **Requête** : `$request->hospital_id` et `$request->attributes->get('hospital')`
- **Session** : `session('hospital_id')` et `session('hospital')`
- **Application** : `app('hospital')` et `app('hospital_id')`

## Utilisation

### Dans les contrôleurs

```php
use App\Services\TenantService;

class PatientController extends Controller
{
    public function index()
    {
        // Méthode 1 : Via le service
        $hospital = TenantService::current();
        $hospitalId = TenantService::currentId();

        // Méthode 2 : Via les helpers
        $hospital = currentHospital();
        $hospitalId = currentHospitalId();

        // Méthode 3 : Via la requête
        $hospitalId = request()->get('hospital_id');
        $hospital = request()->attributes->get('hospital');

        // Méthode 4 : Via l'application
        $hospital = app('hospital');
        $hospitalId = app('hospital_id');

        // Filtrer les patients par hôpital
        $patients = Patiente::where('hospital_id', $hospitalId)->get();
    }
}
```

### Dans les modèles Eloquent

Le middleware est automatiquement exécuté avant chaque requête, donc vous pouvez utiliser directement `currentHospitalId()` :

```php
class PatientController extends Controller
{
    public function store(Request $request)
    {
        $patient = Patiente::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'hospital_id' => currentHospitalId(), // Automatique
        ]);
    }
}
```

### Dans les routes

Le middleware est automatiquement appliqué à toutes les routes `web` et `api`. Vous pouvez aussi l'utiliser individuellement :

```php
Route::middleware('tenant')->group(function () {
    Route::get('/patients', [PatientController::class, 'index']);
});
```

## Helpers disponibles

### `currentHospital()`
Retourne l'hôpital courant ou `null`.

```php
$hospital = currentHospital();
if ($hospital) {
    echo $hospital->name;
}
```

### `currentHospitalId()`
Retourne l'ID de l'hôpital courant ou `null`.

```php
$hospitalId = currentHospitalId();
```

### `hasTenant()`
Vérifie si un tenant est défini.

```php
if (hasTenant()) {
    // Un tenant est défini
}
```

### `setTenant($hospital)`
Définit le tenant courant (utile pour les tests ou l'administration).

```php
setTenant(1); // Par ID
setTenant($hospital); // Par objet Hospital
```

### `resetTenant()`
Réinitialise le tenant (utile pour les tests).

```php
resetTenant();
```

## Gestion des erreurs

### Domaine inconnu

Si le domaine n'est pas reconnu :
- **Production** : Erreur 404
- **Développement** : Message d'avertissement dans les logs

### Hôpital inactif

Si l'hôpital est inactif ou suspendu :
- **Status `suspended`** : Erreur 403 avec message approprié
- **Status `inactive`** : Erreur 403 avec message approprié

## Routes exclues

Certaines routes sont exclues de la détection du tenant :
- `health`
- `api/health`
- `admin/*` (si nécessaire)

Vous pouvez modifier la propriété `$excludedRoutes` dans le middleware pour ajouter d'autres routes.

## Configuration en développement

Pour permettre l'accès avec des domaines inconnus en développement, ajoutez dans `.env` :

```env
ALLOW_UNKNOWN_TENANTS_IN_DEV=true
```

Puis dans `config/app.php` :

```php
'allow_unknown_tenants_in_dev' => env('ALLOW_UNKNOWN_TENANTS_IN_DEV', false),
```

## Tests

Pour tester avec différents tenants en développement :

### Option 1 : Header personnalisé

```bash
curl -H "X-Tenant-Domain: hopital-central.ma-plateforme.com" http://localhost:8000/api/patients
```

### Option 2 : Paramètre de requête

```bash
curl "http://localhost:8000/api/patients?tenant_domain=hopital-central.ma-plateforme.com"
```

### Option 3 : Sous-domaine local

Configurez votre `/etc/hosts` :
```
127.0.0.1 hopital-central.localhost
127.0.0.1 clinique-ibn-sina.localhost
```

Puis accédez à : `http://hopital-central.localhost:8000`

## Performance

Le middleware utilise le cache pour améliorer les performances :
- Les hôpitaux sont mis en cache pendant 1 heure
- Clé de cache : `hospital_by_domain_{domain}`

Pour vider le cache :

```bash
php artisan cache:clear
```

## Notes importantes

1. **Ordre d'exécution** : Le middleware doit être exécuté après `StartSession` pour pouvoir utiliser la session.

2. **Authentification** : Le middleware s'exécute avant l'authentification, donc vous pouvez utiliser le tenant même pour les utilisateurs non authentifiés.

3. **API** : Pour les API, le tenant peut être passé via header ou paramètre en développement.

4. **Sécurité** : En production, assurez-vous que tous les domaines sont correctement configurés dans la table `hospitals`.
