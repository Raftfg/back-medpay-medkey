<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema as DoctrineSchema;
use Doctrine\DBAL\Schema\Table as DoctrineTable;

/**
 * Service de validation et synchronisation de schémas pour architecture multi-tenant
 * 
 * Ce service garantit l'intégrité des schémas de base de données
 * en vérifiant l'état actuel et en appliquant uniquement les changements nécessaires.
 */
class SchemaValidationService
{
    /**
     * Vérifie si une table existe dans la base de données actuelle
     */
    public function tableExists(string $tableName): bool
    {
        try {
            return Schema::hasTable($tableName);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la vérification de l'existence de la table {$tableName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Vérifie si une colonne existe dans une table
     */
    public function columnExists(string $tableName, string $columnName): bool
    {
        try {
            if (!$this->tableExists($tableName)) {
                return false;
            }
            return Schema::hasColumn($tableName, $columnName);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la vérification de l'existence de la colonne {$tableName}.{$columnName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Récupère la définition complète d'une colonne
     */
    public function getColumnDefinition(string $tableName, string $columnName): ?array
    {
        try {
            if (!$this->columnExists($tableName, $columnName)) {
                return null;
            }

            $connection = DB::connection();
            $doctrineConnection = $connection->getDoctrineConnection();
            $schemaManager = $doctrineConnection->createSchemaManager();
            
            $table = $schemaManager->introspectTable($tableName);
            $column = $table->getColumn($columnName);

            return [
                'name' => $column->getName(),
                'type' => $column->getType()->getName(),
                'length' => $column->getLength(),
                'notnull' => $column->getNotnull(),
                'default' => $column->getDefault(),
                'autoincrement' => $column->getAutoincrement(),
                'unsigned' => $column->getUnsigned(),
                'comment' => $column->getComment(),
            ];
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération de la définition de la colonne {$tableName}.{$columnName}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Vérifie si une table contient des données
     */
    public function tableHasData(string $tableName): bool
    {
        try {
            if (!$this->tableExists($tableName)) {
                return false;
            }
            return DB::table($tableName)->exists();
        } catch (\Exception $e) {
            Log::error("Erreur lors de la vérification des données de la table {$tableName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Compte le nombre d'enregistrements dans une table
     */
    public function getTableRecordCount(string $tableName): int
    {
        try {
            if (!$this->tableExists($tableName)) {
                return 0;
            }
            return DB::table($tableName)->count();
        } catch (\Exception $e) {
            Log::error("Erreur lors du comptage des enregistrements de la table {$tableName}", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Vérifie si une contrainte de clé étrangère existe
     */
    public function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        try {
            if (!$this->tableExists($tableName)) {
                return false;
            }

            $connection = DB::connection();
            $doctrineConnection = $connection->getDoctrineConnection();
            $schemaManager = $doctrineConnection->createSchemaManager();
            
            $foreignKeys = $schemaManager->listTableForeignKeys($tableName);
            
            foreach ($foreignKeys as $foreignKey) {
                if ($foreignKey->getName() === $constraintName) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la vérification de la contrainte FK {$tableName}.{$constraintName}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Compare deux définitions de colonnes pour détecter les différences
     */
    public function compareColumnDefinitions(array $expected, array $actual): array
    {
        $differences = [];

        // Vérifier le type
        if (isset($expected['type']) && isset($actual['type'])) {
            $expectedType = $this->normalizeType($expected['type']);
            $actualType = $this->normalizeType($actual['type']);
            
            if ($expectedType !== $actualType) {
                $differences['type'] = [
                    'expected' => $expectedType,
                    'actual' => $actualType
                ];
            }
        }

        // Vérifier la longueur
        if (isset($expected['length']) && isset($actual['length'])) {
            if ($expected['length'] !== $actual['length']) {
                $differences['length'] = [
                    'expected' => $expected['length'],
                    'actual' => $actual['length']
                ];
            }
        }

        // Vérifier nullable
        if (isset($expected['nullable']) && isset($actual['notnull'])) {
            $expectedNullable = $expected['nullable'] ?? true;
            $actualNullable = !$actual['notnull'];
            
            if ($expectedNullable !== $actualNullable) {
                $differences['nullable'] = [
                    'expected' => $expectedNullable,
                    'actual' => $actualNullable
                ];
            }
        }

        return $differences;
    }

    /**
     * Normalise le type de colonne pour la comparaison
     */
    private function normalizeType(string $type): string
    {
        $typeMap = [
            'bigint' => 'bigint',
            'integer' => 'integer',
            'int' => 'integer',
            'string' => 'string',
            'varchar' => 'string',
            'text' => 'text',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'datetime',
            'boolean' => 'boolean',
            'tinyint' => 'boolean',
            'enum' => 'enum',
            'uuid' => 'string',
        ];

        $type = strtolower($type);
        return $typeMap[$type] ?? $type;
    }

    /**
     * Vérifie l'intégrité d'une table selon un schéma attendu
     * 
     * @param string $tableName Nom de la table
     * @param array $expectedSchema Schéma attendu avec les colonnes et leurs définitions
     * @return array ['exists' => bool, 'missing_columns' => [], 'different_columns' => [], 'has_data' => bool]
     */
    public function validateTableSchema(string $tableName, array $expectedSchema): array
    {
        $result = [
            'exists' => $this->tableExists($tableName),
            'missing_columns' => [],
            'different_columns' => [],
            'has_data' => false,
            'record_count' => 0,
        ];

        if (!$result['exists']) {
            return $result;
        }

        $result['has_data'] = $this->tableHasData($tableName);
        $result['record_count'] = $this->getTableRecordCount($tableName);

        // Vérifier chaque colonne attendue
        foreach ($expectedSchema['columns'] ?? [] as $columnName => $columnDefinition) {
            if (!$this->columnExists($tableName, $columnName)) {
                $result['missing_columns'][] = $columnName;
            } else {
                // Vérifier si la définition correspond
                $actualDefinition = $this->getColumnDefinition($tableName, $columnName);
                if ($actualDefinition) {
                    $differences = $this->compareColumnDefinitions($columnDefinition, $actualDefinition);
                    if (!empty($differences)) {
                        $result['different_columns'][$columnName] = $differences;
                    }
                }
            }
        }

        // Vérifier les contraintes de clés étrangères
        if (isset($expectedSchema['foreign_keys'])) {
            foreach ($expectedSchema['foreign_keys'] as $constraintName => $constraintDefinition) {
                if (!$this->foreignKeyExists($tableName, $constraintName)) {
                    $result['missing_foreign_keys'][] = $constraintName;
                }
            }
        }

        return $result;
    }

    /**
     * Génère un rapport de validation pour une table
     */
    public function generateValidationReport(string $tableName, array $expectedSchema): string
    {
        $validation = $this->validateTableSchema($tableName, $expectedSchema);
        
        $report = "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $report .= "Table: {$tableName}\n";
        $report .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $report .= "Existe: " . ($validation['exists'] ? '✅ Oui' : '❌ Non') . "\n";
        
        if ($validation['exists']) {
            $report .= "Contient des données: " . ($validation['has_data'] ? '✅ Oui' : '❌ Non') . "\n";
            $report .= "Nombre d'enregistrements: {$validation['record_count']}\n";
            
            if (!empty($validation['missing_columns'])) {
                $report .= "\n⚠️  Colonnes manquantes:\n";
                foreach ($validation['missing_columns'] as $column) {
                    $report .= "  - {$column}\n";
                }
            }
            
            if (!empty($validation['different_columns'])) {
                $report .= "\n⚠️  Colonnes avec différences:\n";
                foreach ($validation['different_columns'] as $column => $differences) {
                    $report .= "  - {$column}:\n";
                    foreach ($differences as $field => $diff) {
                        $report .= "    • {$field}: attendu={$diff['expected']}, actuel={$diff['actual']}\n";
                    }
                }
            }
            
            if (isset($validation['missing_foreign_keys']) && !empty($validation['missing_foreign_keys'])) {
                $report .= "\n⚠️  Contraintes FK manquantes:\n";
                foreach ($validation['missing_foreign_keys'] as $constraint) {
                    $report .= "  - {$constraint}\n";
                }
            }
            
            if (empty($validation['missing_columns']) && 
                empty($validation['different_columns']) && 
                (empty($validation['missing_foreign_keys'] ?? []))) {
                $report .= "\n✅ Schéma conforme\n";
            }
        }
        
        return $report;
    }
}
