<?php

/**
 * Script pour adapter les seeders pour utiliser le modèle CORE Hospital
 * au lieu du modèle tenant Hospital
 */

require __DIR__ . '/../vendor/autoload.php';

$dryRun = in_array('--dry-run', $argv);

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  Adaptation des seeders pour utiliser Hospital CORE         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

if ($dryRun) {
    echo "⚠️  MODE SIMULATION - Aucune modification ne sera effectuée\n\n";
}

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
        
        // Chercher uniquement dans les seeders
        if (strpos($filePath, 'Database/Seeders') === false || strpos($filePath, 'Seeder.php') === false) {
            continue;
        }
        
        $content = file_get_contents($filePath);
        
        // Vérifier si le fichier utilise le modèle Hospital
        $usesHospital = strpos($content, 'Modules\\Administration\\Entities\\Hospital') !== false ||
                        strpos($content, 'use Modules\\Administration\\Entities\\Hospital') !== false ||
                        preg_match('/Hospital::(where|find|first|all|create|update)/', $content);
        
        if (!$usesHospital) {
            continue;
        }
        
        $totalFiles++;
        $relativePath = str_replace(__DIR__ . '/../', '', $filePath);
        
        echo "📄 {$relativePath}\n";
        
        if (!$dryRun) {
            $newContent = $content;
            
            // Remplacer l'import
            $newContent = preg_replace(
                '/use\s+Modules\\\Administration\\\Entities\\\Hospital;/',
                'use App\Core\Models\Hospital;',
                $newContent
            );
            
            // Remplacer les références complètes dans le code
            $newContent = preg_replace(
                '/\\\Modules\\\Administration\\\Entities\\\Hospital/',
                'App\Core\Models\Hospital',
                $newContent
            );
            
            // Ajouter l'import si Hospital est utilisé mais pas importé
            if (preg_match('/Hospital::(where|find|first|all|create|update)/', $newContent) && 
                strpos($newContent, 'use App\Core\Models\Hospital') === false &&
                strpos($newContent, 'use Modules\Administration\Entities\Hospital') === false) {
                // Ajouter l'import après les autres use statements
                if (preg_match('/(namespace\s+[^;]+;[\s\n]+)(use\s+[^;]+;[\s\n]+)+/', $newContent, $matches)) {
                    $newContent = preg_replace(
                        '/(namespace\s+[^;]+;[\s\n]+)((?:use\s+[^;]+;[\s\n]+)+)/',
                        '$1$2use App\Core\Models\Hospital;' . "\n",
                        $newContent
                    );
                } else {
                    // Ajouter après le namespace
                    $newContent = preg_replace(
                        '/(namespace\s+[^;]+;)/',
                        '$1' . "\n\nuse App\Core\Models\Hospital;",
                        $newContent
                    );
                }
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
