<?php

use Modules\Acl\Entities\User;
use App\Core\Models\Hospital;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Core\Services\TenantConnectionService;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- DEBUG USER START ---\n";

// 1. Force connection to the correct tenant
$hospital = Hospital::where('domain', 'hopital-centralma-plateforme.com')->first();
if (!$hospital) {
    die("Hospital 'Hopital CentralMA' not found in core DB.\n");
}
echo "Hospital found: {$hospital->name} (ID: {$hospital->id})\n";
echo "Target Database: {$hospital->database_name}\n";

try {
    $service = app(TenantConnectionService::class);
    $service->connect($hospital);
    echo "Connected to tenant database.\n";
} catch (\Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// 1.5. CHECK SCHEMA - Add hospital_id if missing
$hasColumn = Illuminate\Support\Facades\Schema::connection('tenant')->hasColumn('users', 'hospital_id');
if (!$hasColumn) {
    echo "  [WARN] 'hospital_id' column MISSING in 'users' table. Adding it now...\n";
    Illuminate\Support\Facades\Schema::connection('tenant')->table('users', function ($table) {
        $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
    });
    echo "  [OK] 'hospital_id' column added.\n";
} else {
    echo "  [OK] 'hospital_id' column exists.\n";
}

// 2. Find user
$email = 'admin@hopital-centralma-plateforme.com';
// Use DB query directly to see raw data first
$rawUser = DB::connection('tenant')->table('users')->where('email', $email)->first();

if (!$rawUser) {
    die("User NOT FOUND in tenant database '{$hospital->database_name}' via raw SQL.\n");
}

echo "User found (Raw SQL):\n";
echo "  ID: {$rawUser->id}\n";
echo "  Hospital ID: " . ($rawUser->hospital_id ?? 'NULL') . "\n";
echo "  Password Hash: " . substr($rawUser->password, 0, 20) . "...\n";
echo "  Deleted At: " . ($rawUser->deleted_at ?? 'NULL') . "\n";

// 3. Test Hash
echo "Testing password 'password'...\n";
if (Hash::check('password', $rawUser->password)) {
    echo "  [OK] Hash matches.\n";
} else {
    echo "  [FAIL] Hash DOES NOT match.\n";
    
    // Resetting password
    echo "  Resetting password to 'password'...\n";
    $newHash = Hash::make('password');
    DB::connection('tenant')->table('users')->where('id', $rawUser->id)->update(['password' => $newHash]);
    echo "  Password reset complete.\n";
}

// 4. Verify Hospital ID Match
if ($rawUser->hospital_id != $hospital->id) {
    echo "  [FAIL] Hospital ID mismatch! User has " . ($rawUser->hospital_id ?? 'NULL') . ", Hospital has {$hospital->id}.\n";
    echo "  Fixing Hospital ID...\n";
    DB::connection('tenant')->table('users')->where('id', $rawUser->id)->update(['hospital_id' => $hospital->id]);
    echo "  Hospital ID fixed.\n";
} else {
    echo "  [OK] Hospital ID matches.\n";
}

echo "--- DEBUG USER END ---\n";
