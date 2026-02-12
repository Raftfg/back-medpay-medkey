<?php

/**
 * Script pour corriger les erreurs de syntaxe où $connection a été mal inséré
 */

require __DIR__ . '/../vendor/autoload.php';

$modulesPath = __DIR__ . '/../Modules';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($modulesPath),
    RecursiveIteratorIterator::SELF_FIRST
);

$fixedFiles = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        
        // Chercher le pattern: protected $fillable = [\n    protected $connection = 'tenant';
        if (preg_match('/protected\s+\$fillable\s*=\s*\[\s*\n\s*protected\s+\$connection\s*=\s*[\'"]tenant[\'"];/', $content)) {
            $relativePath = str_replace(__DIR__ . '/../', '', $filePath);
            echo "🔧 Correction: {$relativePath}\n";
            
            // Corriger: déplacer $connection avant $fillable
            $newContent = preg_replace(
                '/(protected\s+\$fillable\s*=\s*\[\s*\n)\s*(protected\s+\$connection\s*=\s*[\'"]tenant[\'"];\s*\n)/',
                '$2$1',
                $content
            );
            
            // Si ça n'a pas marché, essayer une autre approche
            if ($newContent === $content) {
                $newContent = preg_replace(
                    '/protected\s+\$fillable\s*=\s*\[\s*\n\s*protected\s+\$connection\s*=\s*[\'"]tenant[\'"];\s*\n/',
                    "protected \$connection = 'tenant';\n\n    protected \$fillable = [\n",
                    $content
                );
            }
            
            if ($newContent !== $content) {
                file_put_contents($filePath, $newContent);
                $fixedFiles[] = $relativePath;
                echo "   ✅ Corrigé\n";
            }
        }
    }
}

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                        RÉSUMÉ                                ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "✅ Fichiers corrigés: " . count($fixedFiles) . "\n";

if (!empty($fixedFiles)) {
    echo "\nFichiers corrigés:\n";
    foreach ($fixedFiles as $file) {
        echo "  - {$file}\n";
    }
}

echo "\n";
