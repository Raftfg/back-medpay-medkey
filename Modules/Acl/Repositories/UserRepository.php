<?php

namespace Modules\Acl\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Acl\Entities\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class TenantRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class UserRepository extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return User::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * Créer le user à partir de array
     * @param array $donnees
     * @return User
     */
    public function creer($donnees) {
        $attributs = [
            'name' => $donnees['name'],
            'prenom' => $donnees['prenom'],
            'email' => $donnees['email'],
            'password' => Hash::make($donnees['password']),
//            'adresse_code_civic' => isset($donnees['adresse_code_civic']) ? $donnees['adresse_code_civic'] : null,
//            'adresse_rue' => isset($donnees['adresse_rue']) ? $donnees['adresse_rue'] : null,
//            'adresse_apt' => isset($donnees['adresse_apt']) ? $donnees['adresse_apt'] : null,
//            'adresse_code_postal' => isset($donnees['adresse_code_postal']) ? $donnees['adresse_code_postal'] : null,
//            'tel' => isset($donnees['tel']) ? $donnees['tel'] : null,
//            'tel_mobile' => isset($donnees['tel_mobile']) ? $donnees['tel_mobile'] : null,
//            'fax' => isset($donnees['fax']) ? $donnees['fax'] : null,
        ];
        return $this->create($attributs);
    }

    /**
     * Créer le user à partir de array
     * @param array $donnees
     * @return User
     */
    public function modifier($donnees, $user) {
        $attributs = [
            'name' => $donnees['name'],
            'prenom' => $donnees['prenom'],
            'email' => $donnees['email'],
            'telephone' => $donnees['telephone'],
            'sexe' => $donnees['sexe'],
            'adresse' => $donnees['adresse'],
//            'adresse_code_civic' => isset($donnees['adresse_code_civic']) ? $donnees['adresse_code_civic'] : $user->adresse_code_civic,
//            'adresse_rue' => isset($donnees['adresse_rue']) ? $donnees['adresse_rue'] : $user->adresse_rue,
//            'adresse_apt' => isset($donnees['adresse_apt']) ? $donnees['adresse_apt'] : $user->adresse_apt,
//            'adresse_code_postal' => isset($donnees['adresse_code_postal']) ? $donnees['adresse_code_postal'] : $user->adresse_code_postal,
//            'tel' => isset($donnees['tel']) ? $donnees['tel'] : $user->tel,
//            'tel_mobile' => isset($donnees['tel_mobile']) ? $donnees['tel_mobile'] : $user->tel_mobile,
//            'fax' => isset($donnees['fax']) ? $donnees['fax'] : $user->tel_mobile,
        ];
        if(isset($donnees['password']) && $donnees['password']){
            $attributs['password'] = Hash::make($donnees['password']);
        }
        return $this->update($attributs, $user->id);
    }

    /**
     * Envoyer le mot de passe temporaire
     * @param User $user
     * @return String
     */
    public function genererMotDePasseAleatoire($user) {
        $motDePasse = rand_password_temp(10);
        $user->password = Hash::make($motDePasse);
        $user->save();
        return $motDePasse;
    }

}
