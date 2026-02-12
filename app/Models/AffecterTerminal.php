<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffecterTerminal extends Model
{
    use HasFactory;

    protected $table = 'affecter_terminal';
    protected $fillable = [

        'is_synced',
        'user_id',
        'terminal_id',
        'idcentre',
        'deleted_at',

    ];




    public function getUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTerminal()
    {
        return $this->belongsTo(Terminal::class, 'terminal_id');
    }

    public static function boot()
    {
        parent::boot();

        // Lorsqu'un terminal est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($affectterminal) {
            $nextId = AffecterTerminal::max('id') + 1;
            $affectterminal->id = max(5000001, min($nextId, 10000000));
        });
    }


}
