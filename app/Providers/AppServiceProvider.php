<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Enregistrement du service de connexion tenant en tant que singleton
        // Cela garantit que l'état de la connexion est partagé entre les middlewares
        $this->app->singleton(\App\Core\Services\TenantConnectionService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(125);

        // Configuration de Passport pour la durée de vie des tokens
        // IMPORTANT: Les tokens expirent trop rapidement par défaut, causant des 401 immédiatement après login
        if (class_exists(\Laravel\Passport\Passport::class)) {
            // Tokens d'accès valides pour 365 jours (personnalisable via config)
            \Laravel\Passport\Passport::tokensExpireIn(
                now()->addDays(config('passport.token_expiration', 365))
            );
            
            // Tokens de rafraîchissement valides pour 365 jours
            \Laravel\Passport\Passport::refreshTokensExpireIn(
                now()->addDays(config('passport.refresh_token_expiration', 365))
            );
            
            // Tokens d'accès personnel valides pour 365 jours
            \Laravel\Passport\Passport::personalAccessTokensExpireIn(
                now()->addDays(config('passport.personal_access_token_expiration', 365))
            );

            // CRITIQUE: Forcer Passport à utiliser la connexion 'core' pour les tokens OAuth
            // Dans une architecture multi-tenant, les tokens doivent être dans la base centrale
            // car le middleware Tenant ne peut pas établir la connexion avant l'authentification
            \Laravel\Passport\Passport::useClientModel(\App\Models\Passport\Client::class);
            \Laravel\Passport\Passport::useTokenModel(\App\Models\Passport\Token::class);
            \Laravel\Passport\Passport::useRefreshTokenModel(\App\Models\Passport\RefreshToken::class);
            \Laravel\Passport\Passport::usePersonalAccessClientModel(\App\Models\Passport\PersonalAccessClient::class);
            \Laravel\Passport\Passport::useAuthCodeModel(\App\Models\Passport\AuthCode::class);
        }

    }
}
