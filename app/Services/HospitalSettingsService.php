<?php

namespace App\Services;

use Modules\Administration\Entities\Hospital;
use Modules\Administration\Entities\HospitalSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * Service HospitalSettingsService
 * 
 * Gère les paramètres de configuration par hôpital avec mise en cache.
 * 
 * @package App\Services
 */
class HospitalSettingsService
{
    /**
     * Durée de mise en cache des paramètres (en minutes)
     *
     * @var int
     */
    protected $cacheDuration = 60;

    /**
     * Récupère tous les paramètres de l'hôpital courant
     *
     * @param  int|null  $hospitalId
     * @return Collection
     */
    public function all(?int $hospitalId = null): Collection
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            return collect([]);
        }

        return Cache::remember(
            "hospital_settings_{$hospitalId}",
            now()->addMinutes($this->cacheDuration),
            function () use ($hospitalId) {
                return HospitalSetting::where('hospital_id', $hospitalId)
                    ->get()
                    ->keyBy('key');
            }
        );
    }

    /**
     * Récupère un paramètre spécifique
     *
     * @param  string  $key
     * @param  mixed  $default
     * @param  int|null  $hospitalId
     * @return mixed
     */
    public function get(string $key, $default = null, ?int $hospitalId = null)
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            return $default;
        }

        $settings = $this->all($hospitalId);
        $setting = $settings->get($key);

        return $setting ? $setting->value : $default;
    }

    /**
     * Définit un paramètre
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $type
     * @param  string  $group
     * @param  string|null  $description
     * @param  bool  $isPublic
     * @param  int|null  $hospitalId
     * @return HospitalSetting
     */
    public function set(
        string $key,
        $value,
        string $type = 'string',
        string $group = 'general',
        ?string $description = null,
        bool $isPublic = false,
        ?int $hospitalId = null
    ): HospitalSetting {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            throw new \RuntimeException('Aucun hôpital défini pour définir un paramètre.');
        }

        // Déterminer automatiquement le type si c'est un tableau ou un objet
        if (is_array($value) || is_object($value)) {
            $type = 'json';
        } elseif (is_bool($value)) {
            $type = 'boolean';
        } elseif (is_int($value)) {
            $type = 'integer';
        }

        $setting = HospitalSetting::updateOrCreate(
            [
                'hospital_id' => $hospitalId,
                'key' => $key,
            ],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        // Invalider le cache
        $this->clearCache($hospitalId);

        return $setting;
    }

    /**
     * Supprime un paramètre
     *
     * @param  string  $key
     * @param  int|null  $hospitalId
     * @return bool
     */
    public function delete(string $key, ?int $hospitalId = null): bool
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            return false;
        }

        $deleted = HospitalSetting::where('hospital_id', $hospitalId)
            ->where('key', $key)
            ->delete();

        if ($deleted) {
            $this->clearCache($hospitalId);
        }

        return $deleted > 0;
    }

    /**
     * Récupère tous les paramètres d'un groupe spécifique
     *
     * @param  string  $group
     * @param  int|null  $hospitalId
     * @return Collection
     */
    public function getGroup(string $group, ?int $hospitalId = null): Collection
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            return collect([]);
        }

        return $this->all($hospitalId)
            ->filter(function ($setting) use ($group) {
                return $setting->group === $group;
            })
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });
    }

    /**
     * Récupère les paramètres publics (accessibles sans authentification)
     *
     * @param  int|null  $hospitalId
     * @return Collection
     */
    public function getPublic(?int $hospitalId = null): Collection
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            return collect([]);
        }

        return HospitalSetting::where('hospital_id', $hospitalId)
            ->where('is_public', true)
            ->get()
            ->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });
    }

    /**
     * Invalide le cache des paramètres
     *
     * @param  int|null  $hospitalId
     * @return void
     */
    public function clearCache(?int $hospitalId = null): void
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId !== null) {
            Cache::forget("hospital_settings_{$hospitalId}");
        }
    }

    /**
     * Récupère plusieurs paramètres en une seule fois
     *
     * @param  array  $keys
     * @param  int|null  $hospitalId
     * @return array
     */
    public function getMany(array $keys, ?int $hospitalId = null): array
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            return [];
        }

        $settings = $this->all($hospitalId);
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $settings->get($key)?->value ?? null;
        }

        return $result;
    }

    /**
     * Définit plusieurs paramètres en une seule fois
     *
     * @param  array  $settings
     * @param  int|null  $hospitalId
     * @return void
     */
    public function setMany(array $settings, ?int $hospitalId = null): void
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            throw new \RuntimeException('Aucun hôpital défini pour définir des paramètres.');
        }

        foreach ($settings as $key => $value) {
            if (is_array($value)) {
                $this->set(
                    $key,
                    $value['value'] ?? $value,
                    $value['type'] ?? 'string',
                    $value['group'] ?? 'general',
                    $value['description'] ?? null,
                    $value['is_public'] ?? false,
                    $hospitalId
                );
            } else {
                $this->set($key, $value, 'string', 'general', null, false, $hospitalId);
            }
        }
    }

    /**
     * Vérifie si un paramètre existe
     *
     * @param  string  $key
     * @param  int|null  $hospitalId
     * @return bool
     */
    public function has(string $key, ?int $hospitalId = null): bool
    {
        $hospitalId = $hospitalId ?? currentHospitalId();

        if ($hospitalId === null) {
            return false;
        }

        return $this->all($hospitalId)->has($key);
    }
}
