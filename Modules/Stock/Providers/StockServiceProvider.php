<?php

namespace Modules\Stock\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Stock\Entities\ConditioningUnit;
use Modules\Stock\Entities\SaleUnit;
use Modules\Stock\Entities\AdministrationRoute;
use Modules\Stock\Entities\Category;
use Modules\Stock\Entities\Product;
use Modules\Stock\Entities\Sale;
use Modules\Stock\Entities\SaleProduct;
use Modules\Stock\Entities\Stock;
use Modules\Stock\Entities\StockProduct;
use Modules\Stock\Entities\StockTransfer;
use Modules\Stock\Entities\StockTransferProduct;
use Modules\Stock\Entities\Store;
use Modules\Stock\Entities\Supplier;
use Modules\Stock\Entities\Supply;
use Modules\Stock\Entities\SupplyProduct;
use Modules\Stock\Entities\TypeProduct;
use Modules\Stock\Observers\ConditioningUnitObserver;
use Modules\Stock\Observers\SaleUnitObserver;
use Modules\Stock\Observers\AdministrationRouteObserver;
use Modules\Stock\Observers\CategoryObserver;
use Modules\Stock\Observers\ProductObserver;
use Modules\Stock\Observers\SaleObserver;
use Modules\Stock\Observers\SaleProductObserver;
use Modules\Stock\Observers\StockObserver;
use Modules\Stock\Observers\StockProductObserver;
use Modules\Stock\Observers\StockTransferObserver;
use Modules\Stock\Observers\StockTransferProductObserver;
use Modules\Stock\Observers\StoreObserver;
use Modules\Stock\Observers\SupplierObserver;
use Modules\Stock\Observers\SupplyObserver;
use Modules\Stock\Observers\SupplyProductObserver;
use Modules\Stock\Observers\TypeProductObserver;

use Illuminate\Database\Eloquent\Factory;

class StockServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Stock';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'stock';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        //bind the observers to each entities
        ConditioningUnit::observe(ConditioningUnitObserver::class);
        SaleUnit::observe(SaleUnitObserver::class);
        AdministrationRoute::observe(AdministrationRouteObserver::class);
        Category::observe(CategoryObserver::class);
        Product::observe(ProductObserver::class);
        Product::observe(ProductObserver::class);
        Sale::observe(SaleObserver::class);
        SaleProduct::observe(SaleProductObserver::class);
        Stock::observe(StockObserver::class);
        StockProduct::observe(StockProductObserver::class);
        StockTransfer::observe(StockTransferObserver::class);
        StockTransferProduct::observe(StockTransferProductObserver::class);
        Store::observe(StoreObserver::class);
        Supplier::observe(SupplierObserver::class);
        Supply::observe(SupplyObserver::class);
        SupplyProduct::observe(SupplyProductObserver::class);
        TypeProduct::observe(TypeProductObserver::class);
     }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
