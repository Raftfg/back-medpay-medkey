<?php

namespace Modules\Administration\Entities;

use App\Models\Commune;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class arrondissement extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'nom',
    //     'communes_id',
    // ];
    protected $guarded = [];
    protected $connection = 'tenant';

    public function commune()
    {
        return $this->belongsTo(Commune::class, 'communes_id');
    }
    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\ArrondissementFactory::new();
    }
}
