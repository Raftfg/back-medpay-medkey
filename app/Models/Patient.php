<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;
    
    protected $fillable = [
 
        'is_synced',
        'user_id',
        'nom',
        'prenom',
        'age',
        'adresse',
        'telephone',
        'email',
        'whatsapp',
        'profession',
        'sexe',
        'urgencecontact',
        'situationmatrimoniale',
        'pays_id',
        'departement_id',
        'commune_id',
        'arrondissement_id',
        'quartier_id',
        'centresanitaires_id',
        'autre',
        'deleted_at',
    ];

    public function pays()
    {
        return $this->belongsTo(Pays::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }


    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class);
    }

    public function quartier()
    {
        return $this->belongsTo(Quartier::class);
    }
    public function Centresanitaire()
    {
        return $this->belongsTo(CentreSanitaire::class, 'centresanitaire_id');
    }

    // public function Centresanitaire()
    // {
    //     return $this->belongsTo(CentreSanitaire::class, 'centresanitaire_id');
    // }

    // public function situationMatrimoniale()
    // {
    //     return $this->belongsTo(SituationMatrimoniale::class);
    // }



    
    // La methode boot est propre à laravel est appelé ou intilaiser lors de la creation et de la suppression d'un enregistrement 
    public static function boot()
    {
        parent::boot();

        // Lorsqu'un patient est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($patient) {
            $nextId = Patient::max('id') + 1;
            $patient->id = max(5000001, min($nextId, 10000000));
        });

        // Lorsqu'un patient est supprimé, renumérotez les IDs pour combler les trous
        // static::deleted(function ($patient) {
        //     $idToDecrement = $patient->id;
        //     Patient::where('id', '>', $idToDecrement)->decrement('id');
        // });


    }







}
