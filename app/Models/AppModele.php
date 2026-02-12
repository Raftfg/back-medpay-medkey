<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppModele extends Model
{
    
    /**
     * Attempt to find the user id of the currently logged in user
     * Supports Cartalyst Sentry/Sentinel based authentication, as well as stock Auth
     **/
    public function getSystemUserId()
    {
        try {
            if (class_exists($class = '\SleepingOwl\AdminAuth\Facades\AdminAuth')
                || class_exists($class = '\Cartalyst\Sentry\Facades\Laravel\Sentry')
                || class_exists($class = '\Cartalyst\Sentinel\Laravel\Facades\Sentinel')
            ) {
                return ($class::check()) ? $class::getUser()->id : null;
            } elseif (\Auth::check()) {
                return \Auth::user()->getAuthIdentifier();
            } elseif (\Auth::check()) {
                //Seulement si l'utilisateur est connecté. Il faut noter que la confirmation de compte peut se faire sans être conencté. Donc, ça crée un bug quand l'utilisateur n,est pas connecté
                $user = user_web();
                return $user ? $user->id : null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Obtenir l'objet grâce à son uuid
     *
     * @param       $value
     *
     * @return mixed
     */
    public static function findByUuid($value) {
        return self::where('uuid', $value)->first();
    }

}
