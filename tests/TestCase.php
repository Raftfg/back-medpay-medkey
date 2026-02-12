<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Configure connections for tests
        $defaultDb = config('database.connections.mysql.database');
        $coreDb = config('database.connections.core.database', 'medkey_core');
        
        if ($defaultDb) {
            // Set tenant connection to use default database for tests
            config([
                'database.connections.tenant.database' => $defaultDb,
            ]);
            
            // Set core connection to use medkey_core (or default if core doesn't exist)
            // In tests, we'll use the same database but ensure core connection points to medkey_core
            if ($coreDb !== $defaultDb) {
                // Try to use medkey_core, but fallback to default if it doesn't exist
                try {
                    $testCoreDb = $coreDb;
                    // Test if database exists
                    $testConfig = config('database.connections.core');
                    $testConfig['database'] = $testCoreDb;
                    $tempConnection = 'test_core_check';
                    config(["database.connections.{$tempConnection}" => $testConfig]);
                    DB::connection($tempConnection)->getPdo();
                    config(["database.connections.core.database" => $testCoreDb]);
                    DB::purge($tempConnection);
                } catch (\Exception $e) {
                    // If medkey_core doesn't exist, use default database
                    config([
                        'database.connections.core.database' => $defaultDb,
                    ]);
                }
            } else {
                config([
                    'database.connections.core.database' => $defaultDb,
                ]);
            }
            
            // Ensure the connections are properly initialized
            try {
                DB::connection('core')->getPdo();
                DB::connection('tenant')->getPdo();
            } catch (\Exception $e) {
                // If connection fails, that's okay for tests - we'll handle it
            }
        }
    }
}
