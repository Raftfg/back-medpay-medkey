<?php

namespace Modules\Acl\Entities;

// use Modules\Acl\Entities\Role;

class Role extends \Spatie\Permission\Models\Role {

    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Spatie\Translatable\HasTranslations;

    protected $translatable = ['display_name'];
    protected $fillable = ['name'];
    protected $connection = 'tenant';


    /*
      |--------------------------------------------------------------------------
      | FUNCTIONS
      |--------------------------------------------------------------------------
     */

    /**
     * Vérifie si on peut supprimer le rôle
     * Certains rôles ne seront pas supprimés dans la plateforme
     * 
     * @return bool
     */
    public function isDeleted() {
        return !($this->name == "Super" || $this->name == "Admin");
    }

    /*
      |--------------------------------------------------------------------------
      | RELATIONS
      |--------------------------------------------------------------------------
     */


    /*
      |--------------------------------------------------------------------------
      | SCOPES
      |--------------------------------------------------------------------------
     */

    /**
     * Scope a query to only select Admin.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuper($query) {
        return $query->where('name', 'Super')
                        ->where('guard_name', guard_web())
                        ->firstOrFail();
    }

    /**
     * Scope a query to only select Admin.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAdmin($query) {
        return $query->where('name', 'Admin')
                        ->where('guard_name', guard_web())
                        ->firstOrFail();
    }

    /**
     * Scope a query to only select Intervenant.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAgent($query) {
        return $query->where('name', 'Agent')
                        ->where('guard_name', guard_web())
                        ->firstOrFail();
    }

    /**
     * Scope a query to only select Technicien.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClient($query) {
        return $query->where('name', 'Client')
                        ->where('guard_name', guard_web())
                        ->firstOrFail();
    }

    /**
     * Scope a query to only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereNonSuperAdmin($query) {
        return $query->where('name', '!=', 'Super')
                        ->where('guard_name', guard_web());
    }

    /*
      |--------------------------------------------------------------------------
      | MUTATORS
      |--------------------------------------------------------------------------
     */

     // User.php

public function roles()
{
    return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
}

}
