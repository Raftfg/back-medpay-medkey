<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAct extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'user_id',
        'name',
        'description',
        'tarif',
        // 'centresanitaires_id',

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
