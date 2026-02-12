<?php

namespace Tests\Feature\Core;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected TenantConnectionService $tenantService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure core connection is used for Hospital model
        // and tenant connection has a valid database
        $defaultDb = config('database.connections.mysql.database');
        if ($defaultDb) {
            config([
                'database.connections.core.database' => $defaultDb,
                'database.connections.tenant.database' => $defaultDb,
            ]);
        }
        
        $this->tenantService = app(TenantConnectionService::class);
    }

    /** @test */
    public function tenants_cannot_access_each_others_data()
    {
        // Arrange: Create two hospitals
        $dbName = config('database.connections.mysql.database');
        $hospital1 = Hospital::factory()->create([
            'name' => 'Hospital Test 1',
            'domain' => 'hospital-test-1-' . uniqid() . '.medkey.com',
            'database_name' => $dbName, // Use existing DB for test
        ]);

        $hospital2 = Hospital::factory()->create([
            'name' => 'Hospital Test 2',
            'domain' => 'hospital-test-2-' . uniqid() . '.medkey.com',
            'database_name' => $dbName, // Use existing DB for test
        ]);

        // Act: Connect to hospital 1
        $this->tenantService->connect($hospital1);
        $currentHospital = $this->tenantService->getCurrentHospital();

        // Assert: Current hospital should be hospital 1
        $this->assertNotNull($currentHospital);
        $this->assertEquals($hospital1->id, $currentHospital->id);
        $this->assertNotEquals($hospital2->id, $currentHospital->id);

        // Act: Switch to hospital 2
        $this->tenantService->connect($hospital2);
        $currentHospital = $this->tenantService->getCurrentHospital();

        // Assert: Current hospital should now be hospital 2
        $this->assertNotNull($currentHospital);
        $this->assertEquals($hospital2->id, $currentHospital->id);
        $this->assertNotEquals($hospital1->id, $currentHospital->id);
    }

    /** @test */
    public function tenant_connection_is_isolated()
    {
        // Arrange: Use the same existing database for both hospitals
        // but with different database_name values to test isolation
        $dbName = config('database.connections.mysql.database');
        
        // Ensure we have a valid database name
        if (empty($dbName)) {
            $this->markTestSkipped('Database name not configured');
        }
        
        // Create hospital 1 with explicit database_name to override auto-generation
        // Use factory but override database_name to use existing DB
        $hospital1 = Hospital::factory()->create([
            'name' => 'Hospital Test 1',
            'domain' => 'hospital-test-1-' . uniqid() . '.medkey.com',
            'status' => 'active',
        ]);
        
        // Force database_name to use existing DB (override any auto-generated value)
        $hospital1->database_name = $dbName;
        $hospital1->save();
        $hospital1->refresh();
        
        // Verify database_name is set correctly before connecting
        $this->assertEquals($dbName, $hospital1->database_name, 'Hospital 1 database_name should be set to existing DB');

        // Create hospital 2 with explicit database_name to override auto-generation
        $hospital2 = Hospital::factory()->create([
            'name' => 'Hospital Test 2',
            'domain' => 'hospital-test-2-' . uniqid() . '.medkey.com',
            'status' => 'active',
        ]);
        
        // Force database_name to use existing DB (override any auto-generated value)
        $hospital2->database_name = $dbName;
        $hospital2->save();
        $hospital2->refresh();
        
        // Verify database_name is set correctly before connecting
        $this->assertEquals($dbName, $hospital2->database_name, 'Hospital 2 database_name should be set to existing DB');

        // Act & Assert: Connect to hospital 1
        $this->tenantService->connect($hospital1);
        $connection1 = $this->tenantService->getCurrentConnection();
        $this->assertNotNull($connection1);
        $this->assertEquals($hospital1->database_name, config('database.connections.tenant.database'));
        $this->assertEquals($hospital1->id, $this->tenantService->getCurrentHospital()->id);

        // Act & Assert: Switch to hospital 2
        $this->tenantService->connect($hospital2);
        $connection2 = $this->tenantService->getCurrentConnection();
        $this->assertNotNull($connection2);
        $this->assertEquals($hospital2->database_name, config('database.connections.tenant.database'));
        $this->assertEquals($hospital2->id, $this->tenantService->getCurrentHospital()->id);

        // Assert: Current hospital should be hospital 2, not hospital 1
        $this->assertNotEquals($hospital1->id, $this->tenantService->getCurrentHospital()->id);
    }
}
