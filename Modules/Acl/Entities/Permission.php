<?php
namespace Modules\Acl\Entities;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
//use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Permission extends \Spatie\Permission\Models\Permission
{
//    use CrudTrait;
    use \Spatie\Translatable\HasTranslations;

    // protected $fillable = ['uuid', 'name', 'display_name', 'guard_name', 'updated_at', 'created_at', 'groupe'];
    protected $fillable = ['uuid', 'name', 'display_name', 'guard_name', 'updated_at', 'created_at'];
    protected $translatable = ['display_name'];
    protected $connection = 'tenant';

    public static function boot()
    {
        parent::boot();
        static::creating(function ($donnees) {
            $donnees->uuid = (string) Str::uuid();
       
        });
    }

    // public static function getSubPermissionsByGroup($group)
    // {
    //     return self::where('groupe', $group)->get();
    // }

    // public static function getAllSubPermissions()
    // {
    //     return self::whereNotNull('groupe')->get();
    // }

    // public static function listSubPermissionsByGroup()
    // {
    //     $groupedPermissions = self::getAllSubPermissions()->groupBy('groupe');

    //     $result = new Collection();

    //     foreach ($groupedPermissions as $group => $permissions) {
    //         $result->put($group, $permissions->pluck('name'));
    //     }

    //     return $result;
    // }
    
}
