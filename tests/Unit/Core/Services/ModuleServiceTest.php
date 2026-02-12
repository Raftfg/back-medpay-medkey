<?php

namespace Tests\Unit\Core\Services;

use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;
use App\Core\Services\ModuleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuleServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected ModuleService $service;

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
        
        $this->service = app(ModuleService::class);
        Cache::flush(); // Clear cache before each test
    }

    /** @test */
    public function it_can_enable_a_module()
    {
        // Arrange
        $hospital = Hospital::factory()->create();

        // Act
        $module = $this->service->enableModule($hospital, 'Patient');

        // Assert
        $this->assertInstanceOf(HospitalModule::class, $module);
        $this->assertTrue($module->is_enabled);
        $this->assertEquals('Patient', $module->module_name);
        $this->assertEquals($hospital->id, $module->hospital_id);
    }

    /** @test */
    public function it_can_disable_a_module()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->enableModule($hospital, 'Patient');

        // Act
        $result = $this->service->disableModule($hospital, 'Patient');

        // Assert
        $this->assertTrue($result);
        $module = HospitalModule::where('hospital_id', $hospital->id)
            ->where('module_name', 'Patient')
            ->first();
        $this->assertFalse($module->is_enabled);
    }

    /** @test */
    public function it_can_check_if_module_is_enabled()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->enableModule($hospital, 'Patient');

        // Act
        $isEnabled = $this->service->isModuleEnabled($hospital, 'Patient');

        // Assert
        $this->assertTrue($isEnabled);
    }

    /** @test */
    public function it_returns_false_when_module_is_not_enabled()
    {
        // Arrange
        $hospital = Hospital::factory()->create();

        // Act
        $isEnabled = $this->service->isModuleEnabled($hospital, 'Stock');

        // Assert
        $this->assertFalse($isEnabled);
    }

    /** @test */
    public function it_can_get_all_enabled_modules()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->enableModule($hospital, 'Patient');
        $this->service->enableModule($hospital, 'Stock');
        $this->service->enableModule($hospital, 'Cash');

        // Act
        $enabledModules = $this->service->getEnabledModules($hospital);

        // Assert
        $this->assertCount(3, $enabledModules);
        $this->assertContains('Patient', $enabledModules);
        $this->assertContains('Stock', $enabledModules);
        $this->assertContains('Cash', $enabledModules);
    }

    /** @test */
    public function it_can_get_available_modules()
    {
        // Act
        $availableModules = $this->service->getAvailableModules();

        // Assert
        $this->assertIsArray($availableModules);
        // Should contain at least some known modules
        $this->assertNotEmpty($availableModules);
    }

    /** @test */
    public function it_can_get_modules_status()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->enableModule($hospital, 'Patient');

        // Act
        $status = $this->service->getModulesStatus($hospital);

        // Assert
        $this->assertIsArray($status);
        $this->assertTrue($status['Patient'] ?? false);
    }

    /** @test */
    public function it_can_enable_multiple_modules()
    {
        // Arrange
        $hospital = Hospital::factory()->create();

        // Act
        $activated = $this->service->enableModules($hospital, ['Patient', 'Stock', 'Cash']);

        // Assert
        $this->assertCount(3, $activated);
        $this->assertContains('Patient', $activated);
        $this->assertContains('Stock', $activated);
        $this->assertContains('Cash', $activated);
    }

    /** @test */
    public function it_can_disable_multiple_modules()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->enableModules($hospital, ['Patient', 'Stock', 'Cash']);

        // Act
        $deactivated = $this->service->disableModules($hospital, ['Patient', 'Stock']);

        // Assert
        $this->assertCount(2, $deactivated);
        $this->assertFalse($this->service->isModuleEnabled($hospital, 'Patient'));
        $this->assertFalse($this->service->isModuleEnabled($hospital, 'Stock'));
        $this->assertTrue($this->service->isModuleEnabled($hospital, 'Cash'));
    }

    /** @test */
    public function it_caches_enabled_modules()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->enableModule($hospital, 'Patient');

        // Act - First call should hit database
        $enabled1 = $this->service->getEnabledModules($hospital);

        // Clear the database record (simulating cache)
        HospitalModule::where('hospital_id', $hospital->id)->delete();

        // Second call should use cache
        $enabled2 = $this->service->getEnabledModules($hospital);

        // Assert
        $this->assertEquals($enabled1, $enabled2);
    }

    /** @test */
    public function it_clears_cache_when_module_is_enabled()
    {
        // Arrange
        $hospital = Hospital::factory()->create();
        $this->service->enableModule($hospital, 'Patient');

        // Get modules (populates cache)
        $this->service->getEnabledModules($hospital);

        // Act - Enable another module (should clear cache)
        $this->service->enableModule($hospital, 'Stock');

        // Assert - Both should be enabled
        $enabled = $this->service->getEnabledModules($hospital);
        $this->assertContains('Patient', $enabled);
        $this->assertContains('Stock', $enabled);
    }
}
