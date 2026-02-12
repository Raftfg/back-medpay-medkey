<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Movment\Entities\DmeDocument;

/**
 * Commande pour vérifier l'intégrité des documents DME
 */
class CheckDmeDocumentsCommand extends Command
{
    protected $signature = 'dme:check-documents 
                            {--hospital-id= : ID de l\'hôpital spécifique (optionnel)}
                            {--fix : Corriger les chemins incorrects}
                            {--delete-missing : Supprimer les enregistrements sans fichier}
                            {--force : Supprimer automatiquement sans confirmation}';

    protected $description = 'Vérifie l\'intégrité des documents DME (fichiers manquants, chemins incorrects)';

    public function handle()
    {
        $hospitalId = $this->option('hospital-id');
        $fix = $this->option('fix');
        $deleteMissing = $this->option('delete-missing');
        $force = $this->option('force');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Vérification de l\'intégrité des documents DME            ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Récupérer les hôpitaux à traiter
        if ($hospitalId) {
            $hospital = Hospital::find($hospitalId);
            if (!$hospital) {
                $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
                return Command::FAILURE;
            }
            $hospitals = collect([$hospital]);
        } else {
            $hospitals = Hospital::active()->get();
        }

        if ($hospitals->isEmpty()) {
            $this->warn('⚠️  Aucun tenant actif trouvé.');
            return Command::SUCCESS;
        }

        $connectionService = app(TenantConnectionService::class);
        $totalChecked = 0;
        $totalMissing = 0;
        $totalFixed = 0;
        $totalDeleted = 0;

        foreach ($hospitals as $hospital) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");
            $this->newLine();

            try {
                $connectionService->connect($hospital);

                if (!DB::getSchemaBuilder()->hasTable('dme_documents')) {
                    $this->warn("   ⚠️  La table 'dme_documents' n'existe pas dans cette base.");
                    continue;
                }

                $documents = DmeDocument::all();
                $this->info("   📋 {$documents->count()} document(s) trouvé(s)");
                $this->newLine();

                foreach ($documents as $document) {
                    $totalChecked++;
                    $filePath = $document->file_path;
                    
                    if (empty($filePath)) {
                        $this->warn("   ⚠️  Document #{$document->id} : Chemin vide");
                        if ($deleteMissing) {
                            $document->delete();
                            $totalDeleted++;
                            $this->line("      ✅ Supprimé");
                        }
                        continue;
                    }

                    // Nettoyer le chemin (enlever les préfixes incorrects)
                    $cleanedPath = $filePath;
                    $cleanedPath = ltrim($cleanedPath, '/');
                    $cleanedPath = preg_replace('#^storage/#', '', $cleanedPath);
                    $cleanedPath = preg_replace('#^/storage/#', '', $cleanedPath);
                    
                    // Vérifier si le fichier existe avec le chemin nettoyé
                    $exists = Storage::disk('public')->exists($cleanedPath);
                    
                    if (!$exists) {
                        // Essayer d'autres chemins possibles
                        $possiblePaths = [
                            $cleanedPath,
                            ltrim($filePath, '/'),
                            str_replace('storage/', '', $filePath),
                            str_replace('/storage/', '', $filePath),
                            preg_replace('#^storage/#', '', $filePath),
                            preg_replace('#^/storage/#', '', $filePath),
                        ];
                        
                        // Enlever les doublons
                        $possiblePaths = array_unique($possiblePaths);
                        
                        $found = false;
                        $foundPath = null;
                        foreach ($possiblePaths as $path) {
                            if (Storage::disk('public')->exists($path)) {
                                $foundPath = $path;
                                $found = true;
                                break;
                            }
                        }
                        
                        if ($found && $foundPath) {
                            if ($fix) {
                                $document->file_path = $foundPath;
                                $document->save();
                                $totalFixed++;
                                $this->info("   ✅ Document #{$document->id} : Chemin corrigé");
                                $this->line("      Ancien : {$filePath}");
                                $this->line("      Nouveau : {$foundPath}");
                            } else {
                                $this->warn("   ⚠️  Document #{$document->id} : Fichier trouvé avec un autre chemin");
                                $this->line("      Enregistré : {$filePath}");
                                $this->line("      Trouvé à : {$foundPath}");
                                $this->line("      Utilisez --fix pour corriger automatiquement");
                            }
                        } else {
                            $totalMissing++;
                            $this->error("   ❌ Document #{$document->id} : Fichier manquant");
                            $this->line("      Titre : {$document->title}");
                            $this->line("      Chemin : {$filePath}");
                            $this->line("      Chemin nettoyé : {$cleanedPath}");
                            $this->line("      Chemin complet testé : " . Storage::disk('public')->path($cleanedPath));
                            
                            if ($deleteMissing) {
                                if ($force || $this->confirm("      Supprimer cet enregistrement ?", false)) {
                                    $document->delete();
                                    $totalDeleted++;
                                    $this->line("      ✅ Supprimé");
                                }
                            }
                        }
                    } else {
                        // Le fichier existe avec le chemin nettoyé, corriger si nécessaire
                        if ($cleanedPath !== $filePath && $fix) {
                            $document->file_path = $cleanedPath;
                            $document->save();
                            $totalFixed++;
                            $this->info("   ✅ Document #{$document->id} : Chemin nettoyé");
                            $this->line("      Ancien : {$filePath}");
                            $this->line("      Nouveau : {$cleanedPath}");
                        } else {
                            $this->line("   ✅ Document #{$document->id} : OK");
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : {$e->getMessage()}");
            } finally {
                try {
                    $connectionService->disconnect();
                } catch (\Exception $e) {
                    // Ignorer
                }
            }
        }

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    RÉSUMÉ                                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info("Total documents vérifiés : {$totalChecked}");
        $this->warn("Total fichiers manquants : {$totalMissing}");
        if ($fix) {
            $this->info("Total chemins corrigés : {$totalFixed}");
        }
        if ($deleteMissing) {
            $this->info("Total enregistrements supprimés : {$totalDeleted}");
        }

        return Command::SUCCESS;
    }
}
