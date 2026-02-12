<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indigence extends Model
{
    use HasFactory;
    protected $table = 'indigence';
    protected $fillable = [
        'agemin', 'agemax', 'pourcentage', 'observation',
    ];
}
