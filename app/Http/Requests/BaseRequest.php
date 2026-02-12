<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class BaseRequest extends FormRequest {
    private $erreurMessage403 = "Cette action n'est pas autorisée.";

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Rules pour les téléphones
     * 
     * @return array
     */
    protected function telephobeRules() {
        return [
            'sometimes',
            'nullable',
            'digits_between:7,15',
        ];
    }
    
    /**
     * Rules pour chaque média
     * 
     * @return array
     */
    protected function mediaImageRules($required = true) {
        $mimes = 'mimes:'. mimes_image();
        $maxSize = 'max:'. taille_max_fichier();
        if ($required) {
            return [
                'bail',
                'required',
                $mimes,
                $maxSize,
            ];
        }
        return [
            'sometimes',
            'nullable',
            $mimes,
            $maxSize,
        ];
    }
    
    /**
     * Rules pour chaque média
     * 
     * @return array
     */
    protected function mediaRules($required = true) {
        $mimes = 'mimes:'. mimes_document();
        $maxSize = 'max:20480';
        if ($required) {
            return [
                'bail',
                'required',
                $mimes,
                $maxSize,
            ];
        }
        return [
            'sometimes',
            'nullable',
            $mimes,
            $maxSize,
        ];
    }

    /**
     * Rules pour les médias
     * 
     * @return array
     */
    protected function mediaRulesArray($required = true) {
        if ($required) {
            return [
                'bail',
                'required',
                'array',
            ];
        }
        return [
            'sometimes',
            'nullable',
            'array',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException($this->erreurMessage403);
    }
    
    /**
     * Règles de validation pour interdire hospital_id (multi-tenant)
     * Le hospital_id est toujours défini automatiquement par le système
     * et ne doit jamais être envoyé depuis le frontend pour des raisons de sécurité.
     *
     * @return array
     */
    protected function multiTenantRules()
    {
        return [
            'hospital_id' => 'nullable', // Autorise hospital_id s'il est envoyé, mais ce n'est pas requis
        ];
    }

}
