<?php

use App\Models\Ecole;
use Modules\Acl\Entities\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Guard par défaut du web
 *
 * @return string
 */
if (!function_exists('guard_web')) {

    function guard_web() {
        return config('auth.defaults.guard');
    }

}

if (!function_exists('formatDate')){
    
    function formatDate($date) {    
        
        return date('d/m/Y à H\h i', strtotime($date));
    }
}

if (!function_exists('format_letter')){
    
    function format_letter($price) {     
        $fmt = numfmt_create('fr', NumberFormatter::SPELLOUT);
        return $fmt->format($price); 
    } 
}

if (!function_exists('format_number')){
    
    function format_number($nombre) {     
        
        $nombreFormate = number_format($nombre, 0, ',', ' ');
       
        return $nombreFormate;
    }
}

if (!function_exists('format_number_decimal')) {
    function format_number_decimal($nombre) {
        // Utilisation de number_format pour formater le nombre avec deux chiffres après la virgule
        $nombreFormate = number_format($nombre, 2, ',', ' ');

        // Remplacer la virgule par un point
        $nombreFormate = str_replace(',', '.', $nombreFormate);

        return $nombreFormate;
    }
}

/**
 * Random password temp for SMS
 * 
 * @param int $long
 * 
 * @return string $string
 */
if (!function_exists('rand_password_temp')) {

    function rand_password_temp($long) {
        return rand_majuscule_nombre($long);
    }

}

/**
 * Random password temp for SMS
 * 
 * @param int $long
 * 
 * @return string $string
 */
if (!function_exists('rand_majuscule_nombre')) {

    function rand_majuscule_nombre($long) {
        $characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
        $string = '';
        for ($i = 0; $i < $long; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

}

/**
 * Random minuscule nombre
 * 
 * @param int $long
 * 
 * @return string $string
 */
if (!function_exists('rand_minuscule_nombre')) {

    function rand_minuscule_nombre($long) {
        $characters = 'abcdefghijklmnpqrstuvwxyz123456789';
        $string = '';
        for ($i = 0; $i < $long; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

}

/**
 * Décode json string sous forme de array
 * 
 * @param string $valeur
 * 
 * @return Ecole
 */
if (!function_exists('decode_json_array')) {

    function decode_json_array($valeur) {
        return json_decode($valeur, true);
    }

}


/**
 * Donne la langue courante
 * 
 * @return string
 */
if (!function_exists('langue')) {

    function langue() {
        return app()->getLocale();
    }

}

/**
 * Donne la langue courante
 * 
 * @return array
 */
if (!function_exists('langues_disponibles')) {

    function langues_disponibles() {
        return [
            'fr',
            'en',
        ];
    }

}

/**
 * Jsute pour factoriser le Mail queue et tracker l'erreur pouvant subvenir
 * @param Class $classEnvoi Description
 * @return string
 * */
if (!function_exists('mail_queue')) {

    function mail_queue($classEnvoi) {
        try{
            if (app()->environment() == 'local') {
                \Mail::send($classEnvoi);
            }elseif (app()->environment() == 'production') {
                \Mail::queue($classEnvoi);
            }else{
                \Mail::queue($classEnvoi);
            }
        } catch (Exception $ex) {
            \Log::info($ex->getMessage());
        }
    }

}

/**
 * Préfixe des tables du projet (Application)
 * @return string
 * */
if (!function_exists('prefixe_table')) {

    function prefixe_table() {
        return env('PREFIXE_TABLE', 'dr_');
    }

}

/**
 * Contient
 * 
 * @return boolean
 */
if (!function_exists('contient')) {

    function contient($texte, $rechreche) {
        if (strpos($texte, $rechreche) !== false) {
            return true;
        }
        return false;
    }

}

/**
 * Obtenir les colonnes d'une table donnée de la BD
 * 
 * @param string $nomTable Nom de la table
 * 
 * @return string
 * */
if (!function_exists('colonnes_table_bd')) {

    function colonnes_table_bd($nomTable, $exclureChamps = ['id', 'uuid', 'email_verified_at', 'password', 'remember_token', 'deleted_at']) {
        $colonnes =  \DB::getSchemaBuilder()->getColumnListing($nomTable);
        foreach($exclureChamps as $exclureChamp){
            if (($key = array_search($exclureChamp, $colonnes)) !== false) {
                unset($colonnes[$key]);
            }
        }
        return $colonnes;
    }

}

/**
 * Cette fonction permet de filtrer sur l'objet passé en paramètre puis retourner le builder
 * 
 * @param String $recherche Terme recherché
 * @param Model $objet Objet modèle de transaction en cours
 * @param String $nomRelation Nom de la relation dans le modèe Transaction
 * 
 * @return Builder
 * */
if (!function_exists('filtre_recherche_builder')) {

    function filtre_recherche_builder($recherche, $objet, $itemsBuilder) {
        $nomTableObjet = (new $objet)->getTable();
        $colonnes = colonnes_table_bd($nomTableObjet);
        $colonnes = array_values($colonnes);
        $itemsBuilder->where(function ($q) use ($nomTableObjet, $colonnes, $recherche) {
            $q->when($recherche, function ($query) use ($nomTableObjet, $colonnes, $recherche) {
                foreach ($colonnes as $colonne) {
                    $query->orWhere("$nomTableObjet.$colonne", 'like', '%' . $recherche . '%');
                }
            });
        });

        //\Log::info($itemsBuilder->toSql());
        return $itemsBuilder;
    }

}

/**
 * Taille max fichier en Ko
 * 
 * @return string
 * */
if (!function_exists('taille_max_fichier')) {

    function taille_max_fichier() {
        return 20480;        
    }

}

/**
 * Liste des mimes
 * 
 * @return string
 * */
if (!function_exists('mimes_document')) {

    function mimes_document() {
        return "jpg,jpeg,png,bmp,csv,txt,xlsx,xls,pdf,doc,docx";        
    }

}

/**
 * Liste des mimes image
 * 
 * @return string
 * */
if (!function_exists('mimes_image')) {

    function mimes_image() {
        return "jpg,jpeg,png,bmp,gif";        
    }

}

/**
 * Middleware par défaut pour l'espace admin système
 * 
 * @return Array
 * */
if (!function_exists('middleware_systeme_defaut')) {

    function middleware_systeme_defaut() {
        return ['auth:api', 'verified'];
    }
}

/**
 * Liste des type de notifications envoyées
 * 
 * @return array
 */
if (!function_exists('objet_notification_service')) {

    function objet_notification_service() {
        return [
            'COURRIEL' => __("COURRIEL"),
            'SMS' => __("SMS"),
            'WHATSAPP' => __("WHATSAPP"),
        ];
    }

}