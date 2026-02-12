# Exemples d'utilisation du Global Scope HospitalScope

## Exemple 1 : Contrôleur standard (isolation automatique)

```php
<?php

namespace Modules\Patient\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Patient\Entities\Patiente;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Liste des patients de l'hôpital courant
     * Le scope filtre automatiquement par hospital_id
     */
    public function index()
    {
        // Retourne uniquement les patients de l'hôpital courant
        $patients = Patiente::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($patients);
    }

    /**
     * Créer un patient
     * hospital_id est automatiquement défini
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'phone' => 'nullable|string',
            // hospital_id est ajouté automatiquement par le trait
        ]);

        $patient = Patiente::create($validated);
        
        return response()->json($patient, 201);
    }

    /**
     * Afficher un patient
     * Vérifie automatiquement que le patient appartient à l'hôpital courant
     */
    public function show($id)
    {
        // Si le patient n'appartient pas à l'hôpital courant, retourne 404
        $patient = Patiente::with(['user', 'hospital'])
            ->findOrFail($id);
        
        return response()->json($patient);
    }
}
```

## Exemple 2 : Administrateur global (désactivation du scope)

```php
<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Hospital;
use Illuminate\Http\Request;

class GlobalAdminController extends Controller
{
    /**
     * Middleware pour vérifier que l'utilisateur est admin global
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('super-admin')) {
                abort(403, 'Accès réservé aux administrateurs globaux');
            }
            return $next($request);
        });
    }

    /**
     * Liste tous les patients de tous les hôpitaux
     */
    public function allPatients()
    {
        $patients = Patiente::withoutHospital()
            ->with(['hospital', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return response()->json($patients);
    }

    /**
     * Statistiques globales
     */
    public function statistics()
    {
        $stats = [
            'total_patients' => Patiente::withoutHospital()->count(),
            'patients_by_hospital' => Patiente::withoutHospital()
                ->selectRaw('hospital_id, COUNT(*) as total')
                ->groupBy('hospital_id')
                ->with('hospital:id,name')
                ->get(),
            'total_hospitals' => Hospital::active()->count(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Patients d'un hôpital spécifique
     */
    public function patientsByHospital($hospitalId)
    {
        $patients = Patiente::forHospital($hospitalId)
            ->with('user')
            ->paginate(20);
        
        return response()->json($patients);
    }
}
```

## Exemple 3 : Requêtes avec relations

```php
<?php

namespace Modules\Patient\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Patient\Entities\Patiente;
use Modules\Movment\Entities\Movment;

class PatientMovementController extends Controller
{
    /**
     * Mouvements d'un patient
     * Les mouvements sont automatiquement filtrés par hospital_id
     */
    public function patientMovements($patientId)
    {
        // Vérifie que le patient appartient à l'hôpital courant
        $patient = Patiente::findOrFail($patientId);
        
        // Les mouvements sont automatiquement filtrés par hospital_id
        $movements = Movment::where('patients_id', $patientId)
            ->with('patient')
            ->orderBy('arrivaldate', 'desc')
            ->get();
        
        return response()->json($movements);
    }

    /**
     * Tous les mouvements de l'hôpital courant
     */
    public function index()
    {
        // Automatiquement filtré par hospital_id
        $movements = Movment::with('patient')
            ->orderBy('arrivaldate', 'desc')
            ->paginate(20);
        
        return response()->json($movements);
    }
}
```

## Exemple 4 : Recherche avec isolation

```php
<?php

namespace Modules\Patient\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Patient\Entities\Patiente;
use Illuminate\Http\Request;

class PatientSearchController extends Controller
{
    /**
     * Recherche de patients
     * Les résultats sont automatiquement filtrés par hospital_id
     */
    public function search(Request $request)
    {
        $query = Patiente::query();
        
        // Recherche par nom
        if ($request->has('name')) {
            $query->where(function ($q) use ($request) {
                $q->where('firstname', 'like', "%{$request->name}%")
                  ->orWhere('lastname', 'like', "%{$request->name}%");
            });
        }
        
        // Recherche par téléphone
        if ($request->has('phone')) {
            $query->where('phone', 'like', "%{$request->phone}%");
        }
        
        // Tous les résultats sont automatiquement filtrés par hospital_id
        $patients = $query->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($patients);
    }
}
```

## Exemple 5 : Agrégations avec isolation

```php
<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Patient\Entities\Patiente;
use Modules\Movment\Entities\Movment;
use Modules\Payment\Entities\Facture;

class DashboardController extends Controller
{
    /**
     * Statistiques du dashboard
     * Toutes les statistiques sont calculées pour l'hôpital courant uniquement
     */
    public function statistics()
    {
        $stats = [
            'total_patients' => Patiente::count(),
            'total_movements' => Movment::count(),
            'total_factures' => Facture::count(),
            'total_revenue' => Facture::sum('amount'),
            'patients_by_month' => Patiente::selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as total
                ')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];
        
        return response()->json($stats);
    }
}
```

## Exemple 6 : Désactivation conditionnelle

```php
<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Patient\Entities\Patiente;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Générer un rapport
     * Peut inclure tous les hôpitaux si l'utilisateur est admin global
     */
    public function generate(Request $request)
    {
        $query = Patiente::query();
        
        // Si l'utilisateur est admin global et demande tous les hôpitaux
        if (auth()->user()->hasRole('super-admin') && $request->boolean('all_hospitals')) {
            $query->withoutHospital();
        }
        // Sinon, le scope filtre automatiquement par hospital_id
        
        $patients = $query->with('hospital')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($patients);
    }
}
```

## Exemple 7 : Tests unitaires

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Hospital;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que le scope filtre automatiquement
     */
    public function test_scope_filters_by_hospital()
    {
        // Créer deux hôpitaux
        $hospital1 = Hospital::factory()->create();
        $hospital2 = Hospital::factory()->create();
        
        // Définir le tenant
        setTenant($hospital1->id);
        
        // Créer des patients pour chaque hôpital
        $patient1 = Patiente::factory()->create(['hospital_id' => $hospital1->id]);
        $patient2 = Patiente::factory()->create(['hospital_id' => $hospital2->id]);
        
        // Seul le patient de l'hôpital 1 est retourné
        $patients = Patiente::all();
        
        $this->assertCount(1, $patients);
        $this->assertEquals($hospital1->id, $patients->first()->hospital_id);
    }

    /**
     * Test la désactivation du scope
     */
    public function test_can_disable_scope()
    {
        $hospital1 = Hospital::factory()->create();
        $hospital2 = Hospital::factory()->create();
        
        setTenant($hospital1->id);
        
        Patiente::factory()->create(['hospital_id' => $hospital1->id]);
        Patiente::factory()->create(['hospital_id' => $hospital2->id]);
        
        // Sans le scope, tous les patients sont retournés
        $allPatients = Patiente::withoutHospital()->get();
        
        $this->assertCount(2, $allPatients);
    }
}
```

## Exemple 8 : Commandes Artisan

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Hospital;

class SyncPatientsCommand extends Command
{
    protected $signature = 'patients:sync {--hospital= : ID de l\'hôpital}';
    protected $description = 'Synchroniser les patients';

    public function handle()
    {
        $hospitalId = $this->option('hospital');
        
        if ($hospitalId) {
            // Définir le tenant pour cette commande
            setTenant($hospitalId);
            
            // Maintenant les requêtes sont filtrées
            $patients = Patiente::all();
            $this->info("Synchronisation de {$patients->count()} patients pour l'hôpital {$hospitalId}");
        } else {
            // Synchroniser tous les hôpitaux
            $hospitals = Hospital::active()->get();
            
            foreach ($hospitals as $hospital) {
                setTenant($hospital->id);
                $patients = Patiente::all();
                $this->info("Hôpital {$hospital->name}: {$patients->count()} patients");
            }
        }
    }
}
```
