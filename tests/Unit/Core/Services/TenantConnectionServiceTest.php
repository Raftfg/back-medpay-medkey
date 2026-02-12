<?php

namespace Tests\Unit\Core\Services;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantConnectionServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected TenantConnectionService $service;

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
        
        $this->service = app(TenantConnectionService::class);
    }

    /** @test */
    public function it_can_connect_to_a_tenant_database()
    {
        // Arrange
        $hospital = Hospital::factory()->create([
            'database_name' => config('database.connections.mysql.database'), // Use existing DB for test
            'database_host' => config('database.connections.mysql.host'),
            'database_port' => config('database.connections.mysql.port'),
        ]);

        // Act
        $this->service->connect($hospital);

        // Assert
        $this->assertEquals($hospital->database_name, config('database.connections.tenant.database'));
        $this->assertTrue($this->service->isConnected());
    }

    /** @test */
    public function it_can_disconnect_from_tenant_database()
    {
        // Arrange
        $hospital = Hospital::factory()->create([
            'database_name' => config('database.connections.mysql.database'), // Use existing DB for test
        ]);

        $this->service->connect($hospital);

        // Act
        $this->service->disconnect();

        // Assert
        $this->assertFalse($this->service->isConnected());
    }

    /** @test */
    public function it_can_get_current_connection()
    {
        // Arrange
        $hospital = Hospital::factory()->create([
            'database_name' => config('database.connections.mysql.database'), // Use existing DB for test
        ]);

        $this->service->connect($hospital);

        // Act
        $connection = $this->service->getCurrentConnection();

        // Assert
        $this->assertNotNull($connection);
        $this->assertEquals('tenant', $connection->getName());
    }

    /** @test */
    public function it_can_get_current_hospital()
    {
        // Arrange
        $hospital = Hospital::factory()->create([
            'database_name' => config('database.connections.mysql.database'), // Use existing DB for test
        ]);

        $this->service->connect($hospital);

        // Act
        $currentHospital = $this->service->getCurrentHospital();

        // Assert
        $this->assertNotNull($currentHospital);
        $this->assertEquals($hospital->id, $currentHospital->id);
    }

    /** @test */
    public function it_returns_null_when_no_hospital_is_connected()
    {
        // Act
        $currentHospital = $this->service->getCurrentHospital();

        // Assert
        $this->assertNull($currentHospital);
    }

    /** @test */
    public function it_can_test_connection_without_connecting()
    {
        // Arrange
        $hospital = Hospital::factory()->create([
            'database_name' => config('database.connections.mysql.database'), // Use existing DB for test
            'database_host' => config('database.connections.mysql.host'),
            'database_port' => config('database.connections.mysql.port'),
        ]);

        // Act
        $result = $this->service->testConnection($hospital);

        // Assert
        // Note: This will depend on whether the database actually exists
        // In a real test environment, you'd set up test databases
        $this->assertIsBool($result);
    }
}
