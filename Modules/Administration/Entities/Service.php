<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Service extends Model
{
    use HasFactory;

    protected $table = 'services'; // Nom de la table
    protected $connection = 'tenant';

    protected $fillable = ['hospital_id', 'name', 'description', 'departments_id', 'code'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'departments_id');
    }

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\ServiceFactory::new();
    }
}
