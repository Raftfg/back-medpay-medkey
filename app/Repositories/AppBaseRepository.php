<?php

namespace App\Repositories;
use Prettus\Repository\Eloquent\BaseRepository;
/**
 * Cette classe a été créée pour regorger les fonctions utilisables par nos propres repositoires
 */
abstract class AppBaseRepository extends BaseRepository
{

    /**
     * Vérifie si l'objet existe par son email
     *
     * @param       $value
     * @param array $columns
     *
     * @return boolean
     */
    public function existEmail($value, $columns = ['*']): bool {
        return $this->findByField('email', $value, $columns)->count() >= 1;
    }

    /**
     * Obtenir l'objet grâce à son uuid
     *
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByUuid($value, $columns = ['*']) {
        return $this->findByField('uuid', $value, $columns);
    }

    /**
     * Obtenir l'objet grâce à son uuid ou 404
     *
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByUuidOrFail($value, $columns = ['*']) {
        $item = $this->findByUuid($value, $columns);
        if(!count($item)){
            abort(404);
        }
        return $item;
    }
    
}
