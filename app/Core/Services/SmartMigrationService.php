<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;

/**
 * Service de migration intelligente pour architecture multi-tenant
 * 
 * Applique uniquement les changements nécessaires en préservant les données existantes
 */
class SmartMigrationService
{
    protected SchemaValidationService $schemaValidator;

    public function __construct(SchemaValidationService $schemaValidator)
    {
        $this->schemaValidator = $schemaValidator;
    }

    /**
     * Applique une migration de manière intelligente (idempotente)
     * 
     * @param string $tableName Nom de la table
     * @param array $expectedSchema Schéma attendu
     * @param callable $migrationCallback Callback pour créer la table si elle n'existe pas
     * @return array ['created' => bool, 'modified' => bool, 'added_columns' => [], 'errors' => []]
     */
    public function applySmartMigration(
        string $tableName, 
        array $expectedSchema, 
        callable $migrationCallback
    ): array {
        $result = [
            'created' => false,
            'modified' => false,
            'added_columns' => [],
            'errors' => [],
        ];

        try {
            // Vérifier l'état actuel
            $validation = $this->schemaValidator->validateTableSchema($tableName, $expectedSchema);

            // Si la table n'existe pas, créer complètement
            if (!$validation['exists']) {
                DB::beginTransaction();
                try {
                    $migrationCallback();
                    $result['created'] = true;
                    DB::commit();
                    Log::info("Table {$tableName} créée avec succès");
                } catch (\Exception $e) {
                    DB::rollBack();
                    $result['errors'][] = "Erreur lors de la création de la table: " . $e->getMessage();
                    Log::error("Erreur lors de la création de la table {$tableName}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return $result;
                }
            } else {
                // La table existe, ajouter uniquement les colonnes manquantes
                if (!empty($validation['missing_columns'])) {
                    $hasData = $validation['has_data'];
                    
                    DB::beginTransaction();
                    try {
                        foreach ($validation['missing_columns'] as $columnName) {
                            if (!isset($expectedSchema['columns'][$columnName])) {
                                continue;
                            }

                            $columnDef = $expectedSchema['columns'][$columnName];
                            
                            // Si la table contient des données, être prudent
                            if ($hasData) {
                                // Vérifier si la colonne peut être ajoutée sans valeur par défaut
                                $nullable = $columnDef['nullable'] ?? true;
                                $default = $columnDef['default'] ?? null;
                                
                                if (!$nullable && $default === null) {
                                    $result['errors'][] = "Impossible d'ajouter la colonne {$columnName}: NOT NULL sans valeur par défaut sur une table avec données";
                                    continue;
                                }
                            }

                            Schema::table($tableName, function (Blueprint $table) use ($columnName, $columnDef) {
                                $this->addColumnSafely($table, $columnName, $columnDef);
                            });

                            $result['added_columns'][] = $columnName;
                            $result['modified'] = true;
                            
                            Log::info("Colonne {$columnName} ajoutée à la table {$tableName}");
                        }

                        // Ajouter les contraintes FK manquantes
                        if (isset($validation['missing_foreign_keys']) && !empty($validation['missing_foreign_keys'])) {
                            foreach ($validation['missing_foreign_keys'] as $constraintName) {
                                if (!isset($expectedSchema['foreign_keys'][$constraintName])) {
                                    continue;
                                }

                                $fkDef = $expectedSchema['foreign_keys'][$constraintName];
                                
                                Schema::table($tableName, function (Blueprint $table) use ($fkDef) {
                                    $table->foreign($fkDef['column'])
                                        ->references($fkDef['references'])
                                        ->on($fkDef['on'])
                                        ->onUpdate($fkDef['onUpdate'] ?? 'cascade')
                                        ->onDelete($fkDef['onDelete'] ?? 'restrict');
                                });

                                Log::info("Contrainte FK {$constraintName} ajoutée à la table {$tableName}");
                            }
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $result['errors'][] = "Erreur lors de l'ajout des colonnes: " . $e->getMessage();
                        Log::error("Erreur lors de la modification de la table {$tableName}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        return $result;
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            $result['errors'][] = "Erreur générale: " . $e->getMessage();
            Log::error("Erreur dans applySmartMigration pour {$tableName}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $result;
        }
    }

    /**
     * Ajoute une colonne de manière sécurisée
     */
    private function addColumnSafely(Blueprint $table, string $columnName, array $columnDef): void
    {
        $type = $columnDef['type'] ?? 'string';
        $length = $columnDef['length'] ?? null;
        $nullable = $columnDef['nullable'] ?? true;
        $default = $columnDef['default'] ?? null;
        $unsigned = $columnDef['unsigned'] ?? false;
        $comment = $columnDef['comment'] ?? null;

        $column = null;

        switch ($type) {
            case 'bigint':
            case 'integer':
            case 'int':
                $column = $table->{$type}($columnName, $length, $unsigned);
                break;
            case 'string':
            case 'varchar':
                $column = $table->string($columnName, $length ?? 255);
                break;
            case 'text':
                $column = $table->text($columnName);
                break;
            case 'date':
                $column = $table->date($columnName);
                break;
            case 'datetime':
            case 'timestamp':
                $column = $table->{$type}($columnName);
                break;
            case 'boolean':
                $column = $table->boolean($columnName);
                break;
            case 'enum':
                $values = $columnDef['values'] ?? [];
                $column = $table->enum($columnName, $values);
                break;
            case 'uuid':
                $column = $table->uuid($columnName);
                break;
            default:
                $column = $table->string($columnName);
        }

        if ($column) {
            if (!$nullable) {
                $column->nullable(false);
            }
            
            if ($default !== null) {
                $column->default($default);
            }
            
            if ($comment) {
                $column->comment($comment);
            }
        }
    }

    /**
     * Vérifie si une migration a déjà été exécutée pour un tenant
     */
    public function migrationExecuted(string $migrationName): bool
    {
        try {
            if (!Schema::hasTable('migrations')) {
                return false;
            }

            return DB::table('migrations')
                ->where('migration', 'like', "%{$migrationName}%")
                ->exists();
        } catch (\Exception $e) {
            Log::error("Erreur lors de la vérification de la migration {$migrationName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
