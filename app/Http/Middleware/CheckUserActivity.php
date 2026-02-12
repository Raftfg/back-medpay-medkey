<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
// use Modules\Payment\Entities\Facture;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Vérifiez si l'utilisateur est connecté
        if (Auth::check()) {
            // Vérifiez la dernière activité
            $lastActivity = Auth::user()->tel_mobile_verified_at;

            // Définissez la limite d'inactivité (en minutes)
            $inactiveLimit = 15;

            // Vérifiez si l'utilisateur est inactif depuis plus longtemps que la limite
            if (Carbon::now()->diffInMinutes($lastActivity) > $inactiveLimit) {
                Auth::logout(); // Déconnexion de l'utilisateur
            }

            // Mettez à jour le temps de la dernière activité à maintenant
            Auth::user()->update(['tel_mobile_verified_at' => now()]);
        }
        return $next($request);
    }
}
