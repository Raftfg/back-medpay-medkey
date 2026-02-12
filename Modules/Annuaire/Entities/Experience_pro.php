<?php

namespace Modules\Annuaire\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience_pro extends Model
{
    use HasFactory;
    protected $fillable = ['start_date','end_date','place','job','users_id','missions','document_link'];
    protected $connection = 'tenant';
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
