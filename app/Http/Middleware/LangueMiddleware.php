<?php

namespace App\Http\Middleware;
use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;
use Closure;

class LangueMiddleware extends Middleware {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $localization = $request->header('Accept-Language');
        $localization = in_array($localization, langues_disponibles(), true) ? $localization : 'fr';
        app()->setLocale($localization);

        return $next($request);
    }

}
