<?php

namespace Modules\Hospitalization\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Hospitalization\Entities\Bed;
use Modules\Hospitalization\Entities\BedPatient;
use Modules\Hospitalization\Entities\Room;
use Modules\Hospitalization\Observers\BedObserver;
use Modules\Hospitalization\Observers\BedPatientObserver;
use Modules\Hospitalization\Observers\RoomObserver;

class HospitalizationServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected string $moduleName = 'Hospitalization';

    /**
     * @var string $moduleNameLower
     */
    protected string $moduleNameLower = 'hospitalization';

    /**
     * Boot the application events.
     * @return void
     */
    public function boot()
    {
        // $this->registerCommands();
        // $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        //bind the observers to each entities
        Room::observe(RoomObserver::class);
        Bed::observe(BedObserver::class);
        BedPatient::observe(BedPatientObserver::class);
    }

    // /**
    //  * Register commands in the format of Command::class
    //  */
    // protected function registerCommands(): void
    // {
    //     // $this->commands([]);
    // }

    // /**
    //  * Register command Schedules.
    //  */
    // protected function registerCommandSchedules(): void
    // {
    //     // $this->app->booted(function () {
    //     //     $schedule = $this->app->make(Schedule::class);
    //     //     $schedule->command('inspire')->hourly();
    //     // });
    // }

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
