<?php

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * Commande pour créer la base de données CORE
 * 
 * Cette commande crée la base de données medkey_core si elle n'existe pas.
 * 
 * @package App\Core\Console\Commands
 */
class CreateCoreDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'core:create-database 
                            {--database=medkey_core : Nom de la base de données CORE}
                            {--force : Forcer la création même si la base existe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée la base de données CORE pour le système multi-tenant';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $databaseName = $this->option('database');
        $force = $this->option('force');

        $this->info("Création de la base de données CORE : {$databaseName}");

        try {
            // Récupérer les informations de connexion MySQL (sans base de données)
            $host = config('database.connections.mysql.host', '127.0.0.1');
            $port = config('database.connections.mysql.port', '3306');
            $username = config('database.connections.mysql.username', 'root');
            $password = config('database.connections.mysql.password', '');

            // Créer une connexion temporaire sans base de données
            $tempConfig = [
                'driver' => 'mysql',
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ];

            Config::set('database.connections.temp_mysql', $tempConfig);

            // Vérifier si la base existe déjà
            $existingDatabases = DB::connection('temp_mysql')
                ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);

            if (!empty($existingDatabases) && !$force) {
                $this->warn("La base de données '{$databaseName}' existe déjà.");
                if (!$this->confirm('Voulez-vous continuer quand même ?', false)) {
                    $this->info('Opération annulée.');
                    return Command::SUCCESS;
                }
            }

            // Créer la base de données
            $charset = 'utf8mb4';
            $collation = 'utf8mb4_unicode_ci';

            DB::connection('temp_mysql')->statement(
                "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}"
            );

            $this->info("✅ Base de données '{$databaseName}' créée avec succès !");
            $this->line("   - Charset: {$charset}");
            $this->line("   - Collation: {$collation}");

            // Mettre à jour la configuration
            $this->info("\n📝 N'oubliez pas de mettre à jour votre fichier .env :");
            $this->line("   CORE_DB_DATABASE={$databaseName}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la création de la base de données :");
            $this->error($e->getMessage());
            
            $this->newLine();
            $this->warn("💡 Solution alternative :");
            $this->line("   1. Ouvrez votre client MySQL (phpMyAdmin, MySQL Workbench, etc.)");
            $this->line("   2. Exécutez cette commande SQL :");
            $this->line("      CREATE DATABASE {$databaseName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            
            return Command::FAILURE;
        }
    }
}
