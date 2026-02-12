<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */

    protected $commands = [

        \App\Console\Commands\SynchroniserEncaissements::class,
        \App\Console\Commands\SynchroniserDecaissements::class,
        \App\Console\Commands\SynchroniserPatients::class,
        \App\Console\Commands\SynchroniserPayements::class,
        \App\Console\Commands\SynchroniserFactures::class,
        \App\Console\Commands\SynchroniserEspeces::class,
        \App\Console\Commands\synchroniserPatients::class,
        
        \App\Console\Commands\synchroniserTerminals::class,
        \App\Console\Commands\SynchroniserIndigencePatients::class,
        \App\Console\Commands\SynchroniserPriseEnChargePatients::class,
        \App\Console\Commands\SynchroniserAffectTerminals::class,
        \App\Console\Commands\SynchroniserUsers::class,
        \App\Console\Commands\ResetCashRegisterBalance::class,
        
        // Commandes CORE (Multi-tenant)
        \App\Core\Console\Commands\CreateCoreDatabaseCommand::class,
        \App\Core\Console\Commands\CreateHospitalCommand::class,
        \App\Core\Console\Commands\TenantCreateCommand::class, // PHASE 5: Création avec provisioning automatique
        \App\Core\Console\Commands\TenantProvisionCommand::class, // PHASE 5: Provisioning d'un tenant existant
        \App\Core\Console\Commands\TenantStatusCommand::class, // PHASE 5: Statut de provisioning
        \App\Core\Console\Commands\TenantModuleEnableCommand::class, // PHASE 6: Activation de modules
        \App\Core\Console\Commands\TenantModuleDisableCommand::class, // PHASE 6: Désactivation de modules
        \App\Core\Console\Commands\TenantModuleListCommand::class, // PHASE 6: Liste des modules
        \App\Core\Console\Commands\TenantMigrateCommand::class,
        \App\Core\Console\Commands\TenantMigrateAllCommand::class, // Exécute les migrations sur TOUS les tenants
        \App\Core\Console\Commands\TenantMigrateMissingTableCommand::class, // Exécute une migration uniquement pour les tenants qui n'ont pas la table
        \App\Console\Commands\AssignServicesToRoomsCommand::class, // Assigne les services aux chambres
        \App\Core\Console\Commands\TenantSchemaSyncCommand::class, // Synchronise les schémas de tous les tenants (intelligent)
        \App\Core\Console\Commands\TenantSchemaValidateCommand::class, // Valide l'intégrité des schémas DME
        \App\Core\Console\Commands\TenantSeedCommand::class,
        \App\Core\Console\Commands\TenantSeedAllCommand::class, // Exécute les seeders sur TOUS les tenants
        \App\Core\Console\Commands\TenantListCommand::class,
        \App\Core\Console\Commands\FindDuplicatesCommand::class, // Détecte et nettoie les doublons dans les tables
        \App\Core\Console\Commands\FindDuplicatesAdvancedCommand::class, // Détection avancée avec critères personnalisés
        \App\Core\Console\Commands\CheckDmeDocumentsCommand::class, // Vérification de l'intégrité des documents DME
        \App\Core\Console\Commands\MigrateExistingDataCommand::class, // PHASE 3: Migration des données existantes
        \App\Core\Console\Commands\RemoveHospitalIdCommand::class, // PHASE 4: Suppression des colonnes hospital_id
        \App\Console\Commands\CreateDoctorCommand::class, // Création d'un docteur
    ];


    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // $schedule->command('encaissements:synchroniser')->everyFiveMinutes();
        // $schedule->command('encaissements:synchroniser')->everyMinute()->appendOutputTo(storage_path('logs/scheduler_output.log'));
        $schedule->command('encaissements:synchroniser')->everyMinute();
        $schedule->command('decaissements:synchroniser')->everyMinute();
        $schedule->command('patients:synchroniser')->everyMinute();
        $schedule->command('payements:synchroniser')->everyMinute();
        $schedule->command('factures:synchroniser')->everyMinute();
        $schedule->command('especes:synchroniser')->everyMinute();
        $schedule->command('terminals:synchroniser')->everyMinute();
        $schedule->command('indigencepatients:synchroniser')->everyMinute();
        $schedule->command('priseenchargepatients:synchroniser')->everyMinute();
        $schedule->command('affectterminals:synchroniser')->everyMinute();
        $schedule->command('users:synchroniser')->everyMinute();
        $schedule->command('cashregister:resetbalance');

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
