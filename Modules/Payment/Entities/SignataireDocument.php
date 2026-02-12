<?php

namespace Modules\Payment\Entities;

use Illuminate\Support\Str;
use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Payment\Database\factories\SignataireFactory;

class SignataireDocument extends Model
{
    use HasFactory;
    protected $table = 'signataires_document';
    protected $connection = 'tenant';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'reference',
        'signataires_id',
        'uuid',
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
