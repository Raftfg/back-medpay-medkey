<?php

namespace Modules\Patient\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Patient\Entities\Patiente;
use App\Core\Models\Hospital;
use Illuminate\Support\Str;

class PatientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer l'hôpital courant pour générer des IPP uniques
        $tenantService = app(\App\Core\Services\TenantConnectionService::class);
        $hospital = $tenantService->getCurrentHospital();

        $patientsData = [
            [
                'ipp' => 'IPP001',
                'firstname' => 'Jean',
                'lastname' => 'Dupont',
                'gender' => 'M',
                'date_birth' => '1980-01-15',
                'phone' => '+22961234567',
                'email' => 'jean.dupont@example.com',
            ],
            [
                'ipp' => 'IPP002',
                'firstname' => 'Marie',
                'lastname' => 'Martin',
                'gender' => 'F',
                'date_birth' => '1985-03-20',
                'phone' => '+22962345678',
                'email' => 'marie.martin@example.com',
            ],
            [
                'ipp' => 'IPP003',
                'firstname' => 'Pierre',
                'lastname' => 'Bernard',
                'gender' => 'M',
                'date_birth' => '1990-07-10',
                'phone' => '+22963456789',
                'email' => 'pierre.bernard@example.com',
            ],
            [
                'ipp' => 'IPP004',
                'firstname' => 'Sophie',
                'lastname' => 'Dubois',
                'gender' => 'F',
                'date_birth' => '1992-11-25',
                'phone' => '+22964567890',
                'email' => 'sophie.dubois@example.com',
            ],
            [
                'ipp' => 'IPP005',
                'firstname' => 'Luc',
                'lastname' => 'Moreau',
                'gender' => 'M',
                'date_birth' => '1988-05-08',
                'phone' => '+22965678901',
                'email' => 'luc.moreau@example.com',
            ],
        ];

        foreach ($patientsData as $index => $patientData) {
            // Générer un IPP unique
            $hospitalId = $hospital ? $hospital->id : 1;
            $ipp = 'IPP' . str_pad($hospitalId * 100 + ($index + 1), 3, '0', STR_PAD_LEFT);
            
            Patiente::updateOrCreate(
                [
                    'ipp' => $ipp,
                ],
                array_merge($patientData, [
                    'ipp' => $ipp,
                    'uuid' => Str::uuid(),
                    'is_synced' => false,
                ])
            );
        }

        $this->command->info('✅ Patients créés.');
    }
}
