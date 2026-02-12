<?php

/**
 * Script pour retirer automatiquement le trait BelongsToHospital de tous les modèles
 * 
 * Usage: php scripts/remove-belongs-to-hospital-trait.php [--dry-run]
 * 
 * Ce script :
 * 1. Trouve tous les fichiers de modèles qui utilisent BelongsToHospital
 * 2. Retire le trait et son import
 * 3. Retire les méthodes associées (scopeWithoutHospital, scopeForHospital, etc.)
 * 4. Retire les relations hospital() si elles existent
 */

require __DIR__ . '/../vendor/autoload.php';

$dryRun = in_array('--dry-run', $argv);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Suppression du trait BelongsToHospital des modèles          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

if ($dryRun) {
    echo "⚠️  MODE SIMULATION - Aucune modification ne sera effectuée\n\n";
}

// Trouver tous les fichiers PHP dans Modules
$modulesPath = __DIR__ . '/../Modules';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulesPath),
    RecursiveIteratorIterator::SELF_FIRST
);

$modifiedFiles = [];
$totalFiles = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        
        // Vérifier si le fichier utilise BelongsToHospital
        if (strpos($content, 'BelongsToHospital') !== false) {
            $totalFiles++;
            $relativePath = str_replace(__DIR__ . '/../', '', $filePath);
            
            echo "📄 {$relativePath}\n";
            
            if (!$dryRun) {
                $modified = false;
                $newContent = $content;
                
                // 1. Retirer l'import (toutes les variantes)
                $newContent = preg_replace(
                    '/use\s+App\\\Traits\\\BelongsToHospital;\s*\r?\n/',
                    '',
                    $newContent
                );
                
                // 2. Retirer le trait de la déclaration de classe (toutes les variantes possibles)
                // Cas: use HasFactory, BelongsToHospital
                $newContent = preg_replace(
                    '/use\s+HasFactory\s*,\s*BelongsToHospital\s*;/',
                    'use HasFactory;',
                    $newContent
                );
                // Cas: use BelongsToHospital, HasFactory
                $newContent = preg_replace(
                    '/use\s+BelongsToHospital\s*,\s*HasFactory\s*;/',
                    'use HasFactory;',
                    $newContent
                );
                // Cas: use SoftDeletes, HasFactory, BelongsToHospital
                $newContent = preg_replace(
                    '/use\s+SoftDeletes\s*,\s*HasFactory\s*,\s*BelongsToHospital\s*;/',
                    'use SoftDeletes, HasFactory;',
                    $newContent
                );
                // Cas: use HasFactory, SoftDeletes, BelongsToHospital
                $newContent = preg_replace(
                    '/use\s+HasFactory\s*,\s*SoftDeletes\s*,\s*BelongsToHospital\s*;/',
                    'use HasFactory, SoftDeletes;',
                    $newContent
                );
                // Cas: use BelongsToHospital, SoftDeletes, HasFactory
                $newContent = preg_replace(
                    '/use\s+BelongsToHospital\s*,\s*SoftDeletes\s*,\s*HasFactory\s*;/',
                    'use SoftDeletes, HasFactory;',
                    $newContent
                );
                // Cas: use RevisionableTrait, BelongsToHospital
                $newContent = preg_replace(
                    '/use\s+RevisionableTrait\s*,\s*BelongsToHospital\s*;/',
                    'use RevisionableTrait;',
                    $newContent
                );
                // Cas: use BelongsToHospital, RevisionableTrait
                $newContent = preg_replace(
                    '/use\s+BelongsToHospital\s*,\s*RevisionableTrait\s*;/',
                    'use RevisionableTrait;',
                    $newContent
                );
                // Cas: use BelongsToHospital seul
                $newContent = preg_replace(
                    '/use\s+BelongsToHospital\s*;/',
                    '',
                    $newContent
                );
                // Cas: BelongsToHospital au milieu d'une liste
                $newContent = preg_replace(
                    '/,\s*BelongsToHospital\s*,/',
                    ',',
                    $newContent
                );
                $newContent = preg_replace(
                    '/,\s*BelongsToHospital\s*;/',
                    ';',
                    $newContent
                );
                $newContent = preg_replace(
                    '/BelongsToHospital\s*,\s*/',
                    '',
                    $newContent
                );
                
                // 3. Retirer les méthodes du trait (scopeWithoutHospital, scopeForHospital, etc.)
                // Ces méthodes sont généralement définies dans le trait, donc pas besoin de les retirer
                // sauf si elles sont redéfinies dans le modèle
                
                // 4. Retirer les relations hospital() si elles existent
                $newContent = preg_replace(
                    '/\s*\/\*\*.*?\*\/\s*public\s+function\s+hospital\(\)\s*\{[^}]*\}/s',
                    '',
                    $newContent
                );
                
                if ($newContent !== $content) {
                    file_put_contents($filePath, $newContent);
                    $modifiedFiles[] = $relativePath;
                    echo "   ✅ Modifié\n";
                } else {
                    echo "   ⚠️  Aucune modification nécessaire\n";
                }
            } else {
                echo "   [DRY-RUN] Serait modifié\n";
            }
        }
    }
}

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ                                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "📄 Fichiers trouvés: {$totalFiles}\n";
if (!$dryRun) {
    echo "✅ Fichiers modifiés: " . count($modifiedFiles) . "\n";
    if (!empty($modifiedFiles)) {
        echo "\nFichiers modifiés:\n";
        foreach ($modifiedFiles as $file) {
            echo "  - {$file}\n";
        }
    }
} else {
    echo "[DRY-RUN] {$totalFiles} fichier(s) seraient modifié(s)\n";
}

echo "\n";
