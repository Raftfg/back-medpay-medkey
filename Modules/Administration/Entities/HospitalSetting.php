<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Modèle HospitalSetting
 * 
 * Représente un paramètre de configuration pour un hôpital.
 * Permet de stocker des paramètres personnalisés par hôpital.
 * 
 * @package Modules\Administration\Entities
 */
class HospitalSetting extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array<int, string>
     */
    protected $connection = 'tenant';
    
    protected $fillable = [
        'hospital_id',
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'value' => 'array', // Cast automatique pour les types JSON/array
    ];


    /**
     * Accesseur pour obtenir la valeur castée selon le type
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        // Si la valeur est déjà un tableau (cast automatique), on la retourne
        if (is_array($value)) {
            return $value;
        }

        // Sinon, on cast selon le type
        switch ($this->type) {
            case 'integer':
                return (int) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
            case 'array':
                return json_decode($value, true) ?? [];
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Mutateur pour stocker la valeur selon le type
     *
     * @param  mixed  $value
     * @return void
     */
    public function setValueAttribute($value)
    {
        // Si c'est un tableau ou un objet, on le convertit en JSON
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
            $this->attributes['type'] = 'json';
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Scope pour filtrer par groupe
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $group
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope pour filtrer les paramètres publics
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Factory pour les tests
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\HospitalSettingFactory::new();
    }
}
