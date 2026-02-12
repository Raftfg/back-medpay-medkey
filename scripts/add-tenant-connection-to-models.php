<?php

/**
 * Script pour ajouter la connexion 'tenant' aux modèles tenant
 * 
 * Usage: php scripts/add-tenant-connection-to-models.php [--dry-run]
 * 
 * Ce script :
 * 1. Trouve tous les fichiers de modèles dans Modules
 * 2. Ajoute `protected $connection = 'tenant';` si elle n'existe pas déjà
 * 3. Place la propriété après les autres propriétés protégées
 */

require __DIR__ . '/../vendor/autoload.php';

$dryRun = in_array('--dry-run', $argv);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Ajout de la connexion 'tenant' aux modèles                  ║\n";
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
$skippedFiles = [];
$totalFiles = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        
        // Vérifier si c'est un modèle Eloquent (extends Model)
        if (strpos($content, 'extends Model') !== false || strpos($content, 'extends Authenticatable') !== false) {
            $totalFiles++;
            $relativePath = str_replace(__DIR__ . '/../', '', $filePath);
            
            // Vérifier si la connexion existe déjà
            if (preg_match('/protected\s+\$connection\s*=/', $content)) {
                $skippedFiles[] = $relativePath;
                continue;
            }
            
            echo "📄 {$relativePath}\n";
            
            if (!$dryRun) {
                $lines = explode("\n", $content);
                $inserted = false;
                $insertAfterLine = -1;
                
                // Chercher où insérer la propriété $connection
                for ($i = 0; $i < count($lines); $i++) {
                    $line = $lines[$i];
                    
                    // Chercher après protected $table
                    if (preg_match('/^\s*protected\s+\$table\s*=/', $line)) {
                        $insertAfterLine = $i;
                        break;
                    }
                    
                    // Chercher après protected $guarded
                    if (preg_match('/^\s*protected\s+\$guarded\s*=/', $line)) {
                        $insertAfterLine = $i;
                        break;
                    }
                    
                    // Chercher après protected $fillable
                    if (preg_match('/^\s*protected\s+\$fillable\s*=/', $line)) {
                        $insertAfterLine = $i;
                        break;
                    }
                    
                    // Chercher après protected $connection (ne devrait pas arriver, mais au cas où)
                    if (preg_match('/^\s*protected\s+\$connection\s*=/', $line)) {
                        $inserted = true; // Déjà présent
                        break;
                    }
                }
                
                // Si on n'a pas trouvé de propriété protégée, chercher après l'ouverture de la classe
                if ($insertAfterLine === -1 && !$inserted) {
                    for ($i = 0; $i < count($lines); $i++) {
                        $line = $lines[$i];
                        // Chercher l'ouverture de la classe
                        if (preg_match('/^\s*class\s+\w+\s+extends\s+\w+\s*\{/', $line)) {
                            // Chercher après le dernier use statement dans la classe
                            $j = $i + 1;
                            while ($j < count($lines) && preg_match('/^\s*use\s+[^;]+;/', $lines[$j])) {
                                $j++;
                            }
                            // Passer les lignes vides
                            while ($j < count($lines) && trim($lines[$j]) === '') {
                                $j++;
                            }
                            $insertAfterLine = $j - 1; // Insérer avant la première propriété/méthode
                            break;
                        }
                    }
                }
                
                if ($insertAfterLine >= 0 && !$inserted) {
                    // Insérer la propriété après la ligne trouvée
                    array_splice($lines, $insertAfterLine + 1, 0, "    protected \$connection = 'tenant';");
                    $newContent = implode("\n", $lines);
                } else {
                    $newContent = $content;
                }
                
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
echo "📄 Modèles trouvés: {$totalFiles}\n";
echo "⏭️  Fichiers ignorés (connexion déjà définie): " . count($skippedFiles) . "\n";
if (!$dryRun) {
    echo "✅ Fichiers modifiés: " . count($modifiedFiles) . "\n";
    if (!empty($modifiedFiles)) {
        echo "\nFichiers modifiés:\n";
        foreach ($modifiedFiles as $file) {
            echo "  - {$file}\n";
        }
    }
} else {
    echo "[DRY-RUN] " . ($totalFiles - count($skippedFiles)) . " fichier(s) seraient modifié(s)\n";
}

echo "\n";
