<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terminal extends Model
{
    use HasFactory;
    // protected $table = 'type_centre_sanitaire';
    protected $fillable = [
        'is_synced',
        'nom',
        'Description',
        'idcentre',
        'deleted_at',
    ];

    public static function boot()
    {
        parent::boot();

        // Lorsqu'un terminal est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($terminal) {
            $nextId = Terminal::max('id') + 1;
            $terminal->id = max(5000001, min($nextId, 10000000));
        });
    }
}
