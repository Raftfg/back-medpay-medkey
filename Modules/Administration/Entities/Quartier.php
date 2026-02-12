<?php

namespace Modules\Administration\Entities;

use App\Models\Arrondissement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class quartier extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'nom',
    //     'arrondissements_id',
    // ];
    protected $guarded = [];
    protected $connection = 'tenant';
    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissements_id');
    }

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\QuartierFactory::new();
    }
}
