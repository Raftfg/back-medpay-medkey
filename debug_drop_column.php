<?php
// debug_drop_column.php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// 1. Connect to Tenant
echo "--- DEBUG DROP COLUMN START ---\n";

$domain = 'hopital-centralma-plateforme.com'; 
$hospital = Hospital::where('domain', $domain)->first();

if (!$hospital) {
    die("Hospital not found for domain: $domain\n");
}

echo "Hospital found: {$hospital->name} (ID: {$hospital->id})\n";
echo "Target Database: {$hospital->database_name}\n";

try {
    app(TenantConnectionService::class)->connect($hospital);
    echo "Connected to tenant database.\n";
} catch (\Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// 2. Drop Column
if (Schema::connection('tenant')->hasColumn('users', 'hospital_id')) {
    echo "  [INFO] 'hospital_id' column found in 'users' table. Dropping it...\n";
    Schema::connection('tenant')->table('users', function ($table) {
        $table->dropColumn('hospital_id');
    });
    echo "  [OK] 'hospital_id' column dropped.\n";
} else {
    echo "  [OK] 'hospital_id' column does not exist.\n";
}

// 3. Verify
if (!Schema::connection('tenant')->hasColumn('users', 'hospital_id')) {
    echo "VERIFICATION: Column is gone.\n";
} else {
    echo "VERIFICATION FAILED: Column still exists.\n";
}

echo "--- DEBUG DROP COLUMN END ---\n";
