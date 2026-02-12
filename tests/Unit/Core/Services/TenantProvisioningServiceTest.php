<?php

namespace Tests\Unit\Core\Services;

use App\Core\Models\Hospital;
use App\Core\Services\ModuleService;
use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantProvisioningServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected TenantProvisioningService $service;
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
        
        $this->service = app(TenantProvisioningService::class);
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
        $status = $this->service->getProvisioningStatus($hospital);

        // Assert
        $this->assertIsArray($status);
        $this->assertArrayHasKey('database_exists', $status);
        $this->assertArrayHasKey('migrations_count', $status);
        $this->assertArrayHasKey('modules_count', $status);
        $this->assertArrayHasKey('is_provisioned', $status);
    }

    /** @test */
    public function it_can_activate_modules()
    {
        // Arrange
        $hospital = Hospital::factory()->create();

        // Act
        $this->service->activateModules($hospital, ['Patient', 'Stock']);

        // Assert
        $this->assertTrue($this->moduleService->isModuleEnabled($hospital, 'Patient'));
        $this->assertTrue($this->moduleService->isModuleEnabled($hospital, 'Stock'));
    }

    /** @test */
    public function it_can_deactivate_modules()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->activateModules($hospital, ['Patient', 'Stock']);

        // Act
        $this->service->deactivateModules($hospital, ['Patient']);

        // Assert
        $this->assertFalse($this->moduleService->isModuleEnabled($hospital, 'Patient'));
        $this->assertTrue($this->moduleService->isModuleEnabled($hospital, 'Stock'));
    }

    /** @test */
    public function it_can_get_module_config()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $config = ['setting1' => 'value1', 'setting2' => 'value2'];
        $this->moduleService->enableModules($hospital, ['Patient']);

        // Act
        $this->moduleService->updateModuleConfig($hospital, 'Patient', $config);
        $retrievedConfig = $this->moduleService->getModuleConfig($hospital, 'Patient');

        // Assert
        $this->assertEquals($config, $retrievedConfig);
    }
}
