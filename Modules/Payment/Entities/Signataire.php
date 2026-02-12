<?php

namespace Modules\Payment\Entities;

use Illuminate\Support\Str;
use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Payment\Database\factories\SignataireFactory;

class Signataire extends Model
{
    use HasFactory,  SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $connection = 'tenant';
protected $fillable = [
        'user_id',
        'type_document',
        'titre',
        'statut',
        'signature',
        'uuid',
        'id'
    ];

    // protected $casts = [
    //     'signature' => 'binary',
    // ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($signataire) {
            $signataire->uuid = (string) Str::uuid();
            // $signataire->uuid = (string) Str::uuid();
            // $signataire->signature = file_get_contents($signataire->signature->path());
        });
    }

  
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id',);
    }
}
