<?php

namespace Modules\Annuaire\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Annuaire\Entities\Formation;
use Modules\Annuaire\Entities\Employer;
use Modules\Annuaire\Entities\Contrat;
use Modules\Annuaire\Entities\Competence;
use Modules\Annuaire\Entities\Certification;
use Modules\Annuaire\Entities\Experience_pro;
use Modules\Annuaire\Observers\FormationObserver;
use Modules\Annuaire\Observers\CompetenceObserver;
use Modules\Annuaire\Observers\CertificationObserver;
use Modules\Annuaire\Observers\Experience_proObserver;
use Modules\Annuaire\Observers\EmployerObserver;
use Modules\Annuaire\Observers\ContratObserver;

class AnnuaireServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Annuaire';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'annuaire';

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

        Certification::observe(CertificationObserver::class);
        Competence::observe(CompetenceObserver::class);
        Experience_pro::observe(Experience_proObserver::class);
        Formation::observe(FormationObserver::class);
        Employer::observe(EmployerObserver::class);
        Contrat::observe(ContratObserver::class);
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
