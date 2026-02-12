<?php

namespace Modules\Annuaire\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formation extends Model
{
    use HasFactory;
    protected $fillable = ['title','start_date','end_date','place','users_id','document_link'];
    protected $connection = 'tenant';
}
