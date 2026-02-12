<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Policies multi-tenant
        \Modules\Patient\Entities\Patiente::class => \App\Policies\PatientPolicy::class,
        // Ajoutez d'autres mappings ici au fur et à mesure
        // \Modules\Cash\Entities\CashRegister::class => \App\Policies\CashRegisterPolicy::class,
        // \Modules\Payment\Entities\Facture::class => \App\Policies\FacturePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Enregistrer le TenantUserProvider pour gérer l'authentification multi-tenant
        \Illuminate\Support\Facades\Auth::provider('tenant', function ($app, array $config) {
            return new \App\Auth\TenantUserProvider($app['hash'], $config['model']);
        });
    }
}
