<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Movment\Entities\Antecedent;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;

class AntecedentController extends Controller
{
    protected $patienteRepository;

    public function __construct(PatienteRepositoryEloquent $patienteRepository)
    {
        $this->patienteRepository = $patienteRepository;
    }

    /**
     * Liste des antécédents d'un patient
     */
    public function index(Request $request)
    {
        try {
            $patientId = $request->input('patients_id');
            
            if (!$patientId) {
                return reponse_json_transform([
                    'message' => 'L\'identifiant du patient est requis'
                ], 400);
            }

            $antecedents = Antecedent::where('patients_id', $patientId)
                ->orderBy('created_at', 'desc')
                ->get();

            return reponse_json_transform([
                'data' => $antecedents,
                'message' => 'Liste des antécédents récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des antécédents: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération des antécédents'
            ], 500);
        }
    }

    /**
     * Créer un nouvel antécédent (F2.2)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patients_id' => 'required|numeric|exists:patients,id',
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:médical,chirurgical,familial',
                'cim10_code' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_cured' => 'nullable|boolean',
                'movments_id' => 'nullable|numeric|exists:movments,id',
            ]);

            // Vérifier que le patient existe
            $patient = $this->patienteRepository->find($validated['patients_id']);
            if (!$patient) {
                return reponse_json_transform([
                    'message' => 'Patient non trouvé'
                ], 404);
            }

            // Utiliser la connexion du modèle pour la transaction
            DB::connection('tenant')->beginTransaction();

            $antecedent = Antecedent::create([
                'uuid' => Str::uuid(),
                'patients_id' => $validated['patients_id'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'cim10_code' => $validated['cim10_code'] ?? null,
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'is_cured' => $validated['is_cured'] ?? false,
                'movments_id' => $validated['movments_id'] ?? null,
            ]);

            DB::connection('tenant')->commit();

            return reponse_json_transform([
                'data' => $antecedent,
                'message' => 'Antécédent enregistré avec succès'
            ], 201);
        } catch (ValidationException $e) {
            DB::connection('tenant')->rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Erreur lors de la création de l\'antécédent: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de l\'enregistrement de l\'antécédent: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un antécédent
     */
    public function update(Request $request, $id)
    {
        try {
            $antecedent = Antecedent::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|in:médical,chirurgical,familial',
                'cim10_code' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_cured' => 'nullable|boolean',
            ]);

            DB::connection('tenant')->beginTransaction();

            $antecedent->update($validated);

            DB::connection('tenant')->commit();

            return reponse_json_transform([
                'data' => $antecedent,
                'message' => 'Antécédent mis à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            DB::connection('tenant')->rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Erreur lors de la mise à jour de l\'antécédent: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la mise à jour de l\'antécédent: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Supprimer un antécédent
     */
    public function destroy($id)
    {
        try {
            $antecedent = Antecedent::findOrFail($id);
            
            DB::connection('tenant')->beginTransaction();
            $antecedent->delete();
            DB::connection('tenant')->commit();

            return reponse_json_transform([
                'message' => 'Antécédent supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Erreur lors de la suppression de l\'antécédent: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la suppression de l\'antécédent: ' . $e->getMessage()
            ], 500);
        }
    }
}
