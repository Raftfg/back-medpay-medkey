<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Modules\Acl\Entities\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CreateDoctorCommand extends Command
{
    protected $signature = 'doctor:create 
                            {--hospital-id= : ID de l\'hôpital spécifique}
                            {--name= : Nom du docteur}
                            {--prenom= : Prénom du docteur}
                            {--email= : Email du docteur}
                            {--password= : Mot de passe (par défaut: MotDePasse)}
                            {--force : Exécuter sans confirmation}';
    
    protected $description = 'Créer un docteur dans la base de données pour un ou tous les tenants';

    protected TenantConnectionService $tenantConnectionService;

    public function __construct(TenantConnectionService $tenantConnectionService)
    {
        parent::__construct();
        $this->tenantConnectionService = $tenantConnectionService;
    }

    public function handle()
    {
        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║  Création d'un docteur                                      ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝");

        $hospitalId = $this->option('hospital-id');
        $force = $this->option('force');

        $hospitals = Hospital::query();
        if ($hospitalId) {
            $hospitals->where('id', $hospitalId);
        }
        $hospitals = $hospitals->get();

        if ($hospitals->isEmpty()) {
            $this->error("Aucun hôpital trouvé.");
            return Command::FAILURE;
        }

        // Demander les informations si non fournies
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('Nom du docteur', 'Docteur');
        }

        $prenom = $this->option('prenom');
        if (!$prenom) {
            $prenom = $this->ask('Prénom du docteur', 'Test');
        }

        $email = $this->option('email');
        if (!$email) {
            $email = $this->ask('Email du docteur');
        }

        $password = $this->option('password') ?: 'MotDePasse';

        $results = [];
        $totalCreated = 0;

        foreach ($hospitals as $hospital) {
            $this->line("\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");

            try {
                // Connecter au tenant
                $this->tenantConnectionService->connect($hospital);

                // Générer un email unique pour cet hôpital si non fourni
                $doctorEmail = $email;
                if (!$this->option('email')) {
                    $doctorEmail = strtolower(str_replace(' ', '', $name)) . '.' . strtolower(str_replace(' ', '', $prenom)) . '@' . str_replace(['.', ' '], ['', ''], strtolower($hospital->domain));
                }

                // Vérifier si l'utilisateur existe déjà
                $existingUser = User::where('email', $doctorEmail)->first();
                if ($existingUser) {
                    $this->warn("   ⚠️  Un utilisateur avec l'email '{$doctorEmail}' existe déjà.");
                    if (!$force && !$this->confirm("Voulez-vous le mettre à jour ?", false)) {
                        $results[] = ['Hôpital' => $hospital->name, 'Statut' => '⏩ Ignoré', 'Email' => $doctorEmail];
                        continue;
                    }
                }

                if (!$force && !$this->confirm("Créer/mettre à jour le docteur '{$name} {$prenom}' ({$doctorEmail}) pour l'hôpital '{$hospital->name}' ?", true)) {
                    $this->info("   ⏩ Création annulée pour cet hôpital.");
                    $results[] = ['Hôpital' => $hospital->name, 'Statut' => '⏩ Annulé', 'Email' => $doctorEmail];
                    continue;
                }

                // Créer ou mettre à jour l'utilisateur
                $user = User::updateOrCreate(
                    ['email' => $doctorEmail],
                    [
                        'name' => $name,
                        'prenom' => $prenom,
                        'password' => Hash::make($password),
                        'email_verified_at' => now()->toDateTimeString(),
                        'uuid' => (string) Str::uuid(),
                    ]
                );

                // Assigner le rôle "Doctor" ou "medecin"
                $doctorRole = Role::where(function($q) {
                    $q->where('name', 'Doctor')
                      ->orWhere('name', 'medecin')
                      ->orWhere('name', 'Médecin')
                      ->orWhere('name', 'Docteur')
                      ->orWhere('name', 'like', '%Médecin%')
                      ->orWhere('name', 'like', '%Docteur%')
                      ->orWhere('name', 'like', '%Doctor%')
                      ->orWhere('name', 'like', '%medecin%');
                })->where('guard_name', 'api')->first();

                if ($doctorRole) {
                    if (!$user->hasRole($doctorRole->name, 'api')) {
                        $user->assignRole($doctorRole);
                        $this->info("   ✅ Rôle '{$doctorRole->name}' assigné");
                    }
                } else {
                    // Créer le rôle "Doctor" s'il n'existe pas
                    $doctorRole = Role::create([
                        'name' => 'Doctor',
                        'guard_name' => 'api',
                        'uuid' => (string) Str::uuid(),
                    ]);
                    $user->assignRole($doctorRole);
                    $this->info("   ✅ Rôle 'Doctor' créé et assigné");
                }

                $this->info("   ✅ Docteur créé/mis à jour : {$name} {$prenom} ({$doctorEmail})");
                $totalCreated++;
                $results[] = ['Hôpital' => $hospital->name, 'Statut' => '✅ Succès', 'Email' => $doctorEmail, 'Nom' => "{$name} {$prenom}"];

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur lors du traitement de l'hôpital {$hospital->name}: " . $e->getMessage());
                Log::error("CreateDoctorCommand - Erreur pour l'hôpital {$hospital->name}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                $results[] = ['Hôpital' => $hospital->name, 'Statut' => '❌ Erreur', 'Email' => 'N/A', 'Nom' => 'N/A'];
            }
        }

        $this->line("\n╔══════════════════════════════════════════════════════════════╗");
        $this->info("║                    RÉSULTATS                              ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝");
        $this->table(
            ['Hôpital', 'Statut', 'Email', 'Nom'],
            $results
        );
        $this->info("\nTotal docteurs créés/mis à jour : {$totalCreated} sur " . count($results));

        return Command::SUCCESS;
    }
}
