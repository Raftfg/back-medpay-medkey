<?php

namespace Modules\Notifier\Entities;

use App\Models\AppModele;
use Venturecraft\Revisionable\RevisionableTrait;
use Modules\Administration\Entities\Hospital;
class NotifierTracking extends AppModele
{
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = [
        'hospital_id',
        'uuid',
        'sujet',
        'message',
        'destinataires',
        'objet',
        'nombre_fois',
    ];
    // protected $hidden = [];
    // protected $dates = [];
        
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
   

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
