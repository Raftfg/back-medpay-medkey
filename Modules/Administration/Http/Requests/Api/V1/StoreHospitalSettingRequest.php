<?php

namespace Modules\Administration\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest pour la création/mise à jour d'un paramètre d'hôpital
 * 
 * @package Modules\Administration\Http\Requests\Api\V1
 */
class StoreHospitalSettingRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // L'autorisation sera gérée par la Policy
        return true;
    }

    /**
     * Règles de validation
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string|max:255',
            'value' => 'required',
            'type' => 'sometimes|string|in:string,integer,boolean,json,array',
            'group' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable|max:1000',
            'is_public' => 'sometimes|boolean',
        ];
    }

    /**
     * Messages de validation personnalisés
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.required' => 'La clé du paramètre est obligatoire.',
            'key.max' => 'La clé du paramètre ne peut pas dépasser 255 caractères.',
            'value.required' => 'La valeur du paramètre est obligatoire.',
            'type.in' => 'Le type doit être l\'un des suivants : string, integer, boolean, json, array.',
            'group.max' => 'Le groupe ne peut pas dépasser 255 caractères.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
        ];
    }

    /**
     * Préparer les données pour la validation
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // S'assurer que hospital_id n'est jamais fourni par le frontend
        $this->merge([
            'hospital_id' => null, // Sera injecté automatiquement par le service
        ]);
    }
}
