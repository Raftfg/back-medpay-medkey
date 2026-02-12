<?php

namespace Tests\Feature\Core;

use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;
use App\Core\Services\ModuleService;
use App\Core\Services\TenantConnectionService;
use App\Http\Middleware\EnsureModuleEnabled;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ModuleMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    protected ModuleService $moduleService;
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
        
        $this->moduleService = app(ModuleService::class);
        $this->tenantService = app(TenantConnectionService::class);
    }

    /** @test */
    public function middleware_allows_access_when_module_is_enabled()
    {
        // Arrange
        $defaultDb = config('database.connections.mysql.database');
        $hospital = Hospital::factory()->create([
            'database_name' => $defaultDb, // Use existing DB for test
        ]);
        $this->moduleService->enableModule($hospital, 'Patient');
        $this->tenantService->connect($hospital);

        $middleware = new EnsureModuleEnabled($this->moduleService, $this->tenantService);
        $request = Request::create('/api/patient/test', 'GET');
        $request->headers->set('Host', $hospital->domain);

        $next = function ($req) {
            return response()->json(['success' => true]);
        };

        // Act
        $response = $middleware->handle($request, $next, 'Patient');

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getContent())->success);
    }

    /** @test */
    public function middleware_blocks_access_when_module_is_disabled()
    {
        // Arrange
        $defaultDb = config('database.connections.mysql.database');
        $hospital = Hospital::factory()->create([
            'database_name' => $defaultDb, // Use existing DB for test
        ]);
        // Module not enabled
        $this->tenantService->connect($hospital);

        $middleware = new EnsureModuleEnabled($this->moduleService, $this->tenantService);
        $request = Request::create('/api/stock/test', 'GET');
        $request->headers->set('Host', $hospital->domain);

        $next = function ($req) {
            return response()->json(['success' => true]);
        };

        // Act
        $response = $middleware->handle($request, $next, 'Stock');

        // Assert
        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('MODULE_NOT_ENABLED', $data['error']);
    }

    /** @test */
    public function middleware_returns_403_when_tenant_not_found()
    {
        // Arrange
        $middleware = new EnsureModuleEnabled($this->moduleService, $this->tenantService);
        $request = Request::create('/api/patient/test', 'GET');

        $next = function ($req) {
            return response()->json(['success' => true]);
        };

        // Act
        $response = $middleware->handle($request, $next, 'Patient');

        // Assert
        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('TENANT_NOT_FOUND', $data['error']);
    }

    /** @test */
    public function middleware_can_detect_module_from_route()
    {
        // Arrange
        $defaultDb = config('database.connections.mysql.database');
        $hospital = Hospital::factory()->create([
            'database_name' => $defaultDb, // Use existing DB for test
        ]);
        $this->moduleService->enableModule($hospital, 'Patient');
        $this->tenantService->connect($hospital);

        $middleware = new EnsureModuleEnabled($this->moduleService, $this->tenantService);
        $request = Request::create('/api/patient/test', 'GET');
        $request->headers->set('Host', $hospital->domain);

        $next = function ($req) {
            return response()->json(['success' => true]);
        };

        // Act - No module name passed, should detect from route
        $response = $middleware->handle($request, $next);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
    }
}
