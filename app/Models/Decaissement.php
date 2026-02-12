<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Decaissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_synced',
        'motif',
        'montant',
        'facture',
        'description',
        'decaissement_user_id',
        'decaissement_centre_id',
    ];

    public static function getMontantParMois()
    {

        $user = auth()->user();
        $roleId = $user->role_id;



        $montants = static::selectRaw('MONTH(created_at) as mois, SUM(montant) as montant_total')
            ->where('decaissement_centre_id', $user->idcentre)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->keyBy('mois');

        if ($roleId == 10 || $roleId == 1) {

            $montants = static::selectRaw('MONTH(created_at) as mois, SUM(montant) as montant_total')
                ->groupBy('mois')
                ->orderBy('mois')
                ->get()
                ->keyBy('mois');
        }





        $allMonths = range(1, 12); // Liste de tous les mois (de 1 à 12)

        $montantsParMois = [];

        foreach ($allMonths as $mois) {
            $montantTotal = $montants->has($mois) ? $montants[$mois]->montant_total : 0; // Diviser par 100000

            $montantsParMois[] = [
                // 'mois' => $mois,
                'data' => $montantTotal,
            ];
        }

        return $montantsParMois;
    }


    public static function boot()

    {
        parent::boot();

        static::creating(function ($decaissement) {

            $nextId = Decaissement::max('id') + 1;
            $decaissement->id = max(5000001, min($nextId, 10000000));
        });
    }
}
