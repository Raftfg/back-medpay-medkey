<?php

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Response;

/**
 * Logged user
 *
 * @return mixed
 */
if (!function_exists('user_api')) {

    function user_api() {
        return auth()->guard('api')->user();    //API Guard, s'il y a lieu
    }
}

/**
 * Réponse au format JSOn de Laravel. L'objectif est de le factoriser pour que, 
 * lorsque qu'on va opérer un changement sur toutes les réponses (payload) personnalisées,
 * on puisse les faire à un seul endroit
 *
 * @return Response
 */
if (!function_exists('reponse_json')) {

    function reponse_json($donnees, $statut = 200) {
        return response()->json($donnees, $statut);
    }

}

/**
 * Permet de garder le même format de réponse personnalisée
 *
 * @return Response
 */
if (!function_exists('reponse_json_transform')) {

    function reponse_json_transform($donnees, $statut = 200) {
        $data = [
            "data" => $donnees
        ];
        return reponse_json($data, $statut);
    }

}

/**
 * Formate la réponse d'échec de validation d'un formulaire
 * 
 * @param array $data Description
 * 
 * @return Response
 */
if (!function_exists('failed_validation_throw_exception')) {

    function failed_validation_throw_exception($data) {
        throw new HttpResponseException(
                reponse_json_transform([
                    'request_validation' => [
                        'success' => false,
                        'message' => __("Erreur de validation"),
                        'data' => $data
                    ]
                ], 422)
        );
    }

}

/**
 * Fournir la clé des entêtes du tableau à affiche
 * 
 * @return Response
 */
if (!function_exists('colonnes_table_affichable')) {

    function colonnes_table_affichable() {
        return "colonnesTableAffichable";
    }

}

/**
 * Transformer string en json objet
 * 
 * @return Array
 * */
if (!function_exists('caster_champ_json')) {

    function caster_champ_json($champ) {
        if($champ){
            $tableau = decode_json_array($champ);
            if(json_last_error() === JSON_ERROR_NONE){
                return $tableau;
            }else{
                \Log::info("Erreur de casting = ".$champ);
            }
        }
        return [];
    }
}
