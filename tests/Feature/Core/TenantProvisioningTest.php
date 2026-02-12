<?php

namespace Tests\Feature\Core;

use App\Core\Models\Hospital;
use App\Core\Services\ModuleService;
use App\Core\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantProvisioningTest extends TestCase
{
    use DatabaseTransactions;

    protected TenantProvisioningService $provisioningService;
    protected ModuleService $moduleService;

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
        
        $this->provisioningService = app(TenantProvisioningService::class);
        $this->moduleService = app(ModuleService::class);
    }

    /** @test */
    public function it_can_get_provisioning_status()
    {
        // Arrange
        $hospital = Hospital::factory()->create([
            'database_name' => config('database.connections.mysql.database'), // Use existing DB
        ]);

        // Act
        $status = $this->provisioningService->getProvisioningStatus($hospital);

        // Assert
        $this->assertIsArray($status);
        $this->assertArrayHasKey('database_exists', $status);
        $this->assertArrayHasKey('migrations_count', $status);
        $this->assertArrayHasKey('modules_count', $status);
        $this->assertArrayHasKey('is_provisioned', $status);
    }

    /** @test */
    public function it_can_check_if_hospital_is_provisioned()
    {
        // Arrange
        $hospital = Hospital::factory()->create([
            'database_name' => config('database.connections.mysql.database'), // Use existing DB
        ]);

        // Act
        $isProvisioned = $this->provisioningService->isProvisioned($hospital);

        // Assert
        // This will depend on whether the database has migrations
        $this->assertIsBool($isProvisioned);
    }

    /** @test */
    public function it_can_activate_modules_during_provisioning()
    {
        // Arrange
        $hospital = Hospital::factory()->create();

        // Act
        $this->moduleService->enableModules($hospital, ['Patient', 'Stock']);

        // Assert
        $this->assertTrue($this->moduleService->isModuleEnabled($hospital, 'Patient'));
        $this->assertTrue($this->moduleService->isModuleEnabled($hospital, 'Stock'));
    }
}
