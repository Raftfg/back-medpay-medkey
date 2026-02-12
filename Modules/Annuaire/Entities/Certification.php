<?php

namespace Modules\Annuaire\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certification extends Model
{
    use HasFactory;
    protected $fillable = ['date','garant','description','users_id','document_link'];
    protected $connection = 'tenant';
}
