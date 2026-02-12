<?php

namespace Modules\Tracking\Entities;

use Spatie\Activitylog\Models\Activity;
use Modules\Administration\Entities\Hospital;
class ActivityLog extends Activity
{
    

    protected $fillable = [
        'hospital_id',
    ];

    /*
      |--------------------------------------------------------------------------
      | FUNCTIONS
      |--------------------------------------------------------------------------
     */

    /*
      |--------------------------------------------------------------------------
      | RELATIONS
      |--------------------------------------------------------------------------
     */


    /*
      |--------------------------------------------------------------------------
      | SCOPES
      |--------------------------------------------------------------------------
     */
    /**
     * Scope a query to only .
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeChaineValeurEtapeDetailComplete($query) {
        return $query->where('log_name', 'ChaineValeurEtapeDetailComplete');
    }
    
    /**
     * Scope a query to only .
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvecChaineValeur($query) {
        $pt = config('filiere.prefixe_table');
        return $query->join($pt.'chaine_valeur_etapes', $pt.'chaine_valeur_etapes.id', '=', 'activity_log.subject_id')
                ->join($pt.'chaine_valeurs', $pt.'chaine_valeurs.id', '=', $pt.'chaine_valeur_etapes.chaine_valeur_id');
    }

    /*
      |--------------------------------------------------------------------------
      | ACCESORS
      |--------------------------------------------------------------------------
     */

    /*
      |--------------------------------------------------------------------------
      | MUTATORS
      |--------------------------------------------------------------------------
     */
}
