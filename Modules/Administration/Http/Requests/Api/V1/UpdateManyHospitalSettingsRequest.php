<?php

namespace Modules\Administration\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest pour la mise à jour de plusieurs paramètres d'hôpital en une seule fois
 * 
 * @package Modules\Administration\Http\Requests\Api\V1
 */
class UpdateManyHospitalSettingsRequest extends FormRequest
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
            'settings' => 'required|array|min:1',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'required',
            'settings.*.type' => 'sometimes|string|in:string,integer,boolean,json,array',
            'settings.*.group' => 'sometimes|string|max:255',
            'settings.*.description' => 'sometimes|string|nullable|max:1000',
            'settings.*.is_public' => 'sometimes|boolean',
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
            'settings.required' => 'Les paramètres sont obligatoires.',
            'settings.array' => 'Les paramètres doivent être un tableau.',
            'settings.min' => 'Au moins un paramètre doit être fourni.',
            'settings.*.key.required' => 'La clé du paramètre est obligatoire.',
            'settings.*.key.max' => 'La clé du paramètre ne peut pas dépasser 255 caractères.',
            'settings.*.value.required' => 'La valeur du paramètre est obligatoire.',
            'settings.*.type.in' => 'Le type doit être l\'un des suivants : string, integer, boolean, json, array.',
            'settings.*.group.max' => 'Le groupe ne peut pas dépasser 255 caractères.',
            'settings.*.description.max' => 'La description ne peut pas dépasser 1000 caractères.',
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
