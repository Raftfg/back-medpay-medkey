<?php

namespace Modules\Annuaire\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $fillable=["title","description","users_id"];
    protected $connection = 'tenant';
    use HasFactory;
}
