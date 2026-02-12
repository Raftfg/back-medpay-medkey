<?php

namespace Modules\Annuaire\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Annuaire\Entities\Employer;
use App\Core\Models\Hospital;
use Modules\Administration\Entities\Service;
use Modules\Administration\Entities\Department;
use Illuminate\Support\Str;

class EmployerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer l'hôpital courant pour générer des emails uniques
        $tenantService = app(\App\Core\Services\TenantConnectionService::class);
        $hospital = $tenantService->getCurrentHospital();
        
        // Récupérer un service et département (sans hospital_id car on est déjà dans la base tenant)
        $department = Department::first();
        $service = Service::first();
        
        if (!$department || !$service) {
            $this->command->warn("⚠️  Service ou département manquant. Veuillez d'abord exécuter DepartmentTableSeeder et ServiceTableSeeder.");
            return;
        }

        $employersData = [
            [
                'first_name' => 'Dr. Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'phone_number' => '+22961234567',
                'function' => 'Médecin Chef',
            ],
            [
                'first_name' => 'Dr. Marie',
                'last_name' => 'Martin',
                'email' => 'marie.martin@example.com',
                'phone_number' => '+22962345678',
                'function' => 'Chirurgien',
            ],
            [
                'first_name' => 'Inf. Sophie',
                'last_name' => 'Bernard',
                'email' => 'sophie.bernard@example.com',
                'phone_number' => '+22963456789',
                'function' => 'Infirmière',
            ],
            [
                'first_name' => 'Pharm. Luc',
                'last_name' => 'Dubois',
                'email' => 'luc.dubois@example.com',
                'phone_number' => '+22964567890',
                'function' => 'Pharmacien',
            ],
        ];

        foreach ($employersData as $index => $employerData) {
            $email = $hospital ? str_replace('@example.com', '@' . $hospital->domain, $employerData['email']) : $employerData['email'];
            
            Employer::updateOrCreate(
                [
                    'email' => $email,
                ],
                array_merge($employerData, [
                    'email' => $email,
                    'departments_id' => $department->id,
                    'services_id' => $service->id,
                    'uuid' => Str::uuid(),
                ])
            );
        }

        $this->command->info('✅ Employés créés.');
    }
}
