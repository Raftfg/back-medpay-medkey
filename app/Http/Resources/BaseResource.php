<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource {
    //Les relations qui seront incluses par défaut dans la ressource envoyé à la vue
    protected $defautInclusRelations = [
    ];
    
    protected $valeurDefaut = null;
    
    /*protected function displayAcl($entite) {
        $current_user = user_api();
        return [
            'Voir_module_configuration_et_parametrage' => $current_user ? $current_user->isPermission("Voir_module_configuration_et_parametrage $entite") : false,
            'read' =>   $current_user ? $current_user->isPermission("read $entite") : false,
            'update' => $current_user ? $current_user->isPermission("update $entite") : false,
            'delete' => $current_user ? $current_user->isPermission("delete $entite") : false,
        ];
    }*/

      protected function displayAcl($entite) {
        $current_user = user_api();
        return [
            'read' =>  true,
            'update' => true,
            'delete' => true,
        ];
    }

    protected function displayAclCentrale() {
        return [
            'create' => false,
            'read' =>   true,
            'update' => false,
            'delete' => false,
        ];
    }


    /**
     * Traite les relations si la relation a été demandée dans la requête
     * 
     * @param string $relation
     * 
     * @return boolean
     */
    protected function isIncluRelation(string $relation) {
        if(request()->inclus_relations){
            $inclus_relations = explode(',', request()->inclus_relations);
            foreach($inclus_relations as $inclu_relation){
                if(contient($inclu_relation, $relation) || contient($inclu_relation, ".$relation"))
                        return true;
            }
            return false;
        }
        return false;
    }
    
    /**
     * Si l'inclusion est par défaut
     * 
     * @param string $relation
     * 
     * @return boolean
     */
    protected function isIncluRelationDefaut(string $relation) {
        if(in_array($relation, $this->defautInclusRelations)){
            return true;
        }
        return false;
    }

    /**
     * Si l'inclusion est par défaut ou si la relation a été demandée dans la requête
     * 
     * @param string $relation
     * 
     * @return boolean
     */
    protected function checkIncluRelation(string $relation) {
        return $this->isIncluRelation($relation) || $this->isIncluRelationDefaut($relation);
    }
}
