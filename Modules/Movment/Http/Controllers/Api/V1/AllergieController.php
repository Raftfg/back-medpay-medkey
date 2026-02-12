<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Movment\Entities\Allergie;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;

class AllergieController extends Controller
{
    protected $patienteRepository;

    public function __construct(PatienteRepositoryEloquent $patienteRepository)
    {
        $this->patienteRepository = $patienteRepository;
    }

    /**
     * Liste des allergies d'un patient
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

            $allergies = Allergie::where('patients_id', $patientId)
                ->orderBy('created_at', 'desc')
                ->get();

            return reponse_json_transform([
                'data' => $allergies,
                'message' => 'Liste des allergies récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des allergies: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération des allergies'
            ], 500);
        }
    }

    /**
     * Créer une nouvelle allergie (F2.3)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patients_id' => 'required|numeric|exists:patients,id',
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:médicament,aliment,environnemental,autre',
                'severity' => 'required|string|in:léger,modéré,sévère,anaphylaxie',
                'discovery_date' => 'nullable|date',
                'reactions' => 'nullable|string',
                'description' => 'nullable|string',
                'movments_id' => 'nullable|numeric|exists:movments,id',
            ]);

            // Vérifier que le patient existe
            $patient = $this->patienteRepository->find($validated['patients_id']);
            if (!$patient) {
                return reponse_json_transform([
                    'message' => 'Patient non trouvé'
                ], 404);
            }

            DB::beginTransaction();

            $allergie = Allergie::create([
                'uuid' => Str::uuid(),
                'patients_id' => $validated['patients_id'],
                'name' => $validated['name'],
                'type' => $validated['type'],
                'severity' => $validated['severity'],
                'discovery_date' => $validated['discovery_date'] ?? now(),
                'reactions' => $validated['reactions'] ?? null,
                'description' => $validated['description'] ?? null,
                'movments_id' => $validated['movments_id'] ?? null,
            ]);

            DB::commit();

            return reponse_json_transform([
                'data' => $allergie,
                'message' => 'Allergie enregistrée avec succès'
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'allergie: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de l\'enregistrement de l\'allergie'
            ], 500);
        }
    }

    /**
     * Mettre à jour une allergie
     */
    public function update(Request $request, $id)
    {
        try {
            $allergie = Allergie::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|string|in:médicament,aliment,environnemental,autre',
                'severity' => 'sometimes|required|string|in:léger,modéré,sévère,anaphylaxie',
                'discovery_date' => 'nullable|date',
                'reactions' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $allergie->update($validated);

            DB::commit();

            return reponse_json_transform([
                'data' => $allergie,
                'message' => 'Allergie mise à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'allergie: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la mise à jour de l\'allergie'
            ], 500);
        }
    }

    /**
     * Supprimer une allergie
     */
    public function destroy($id)
    {
        try {
            $allergie = Allergie::findOrFail($id);
            
            DB::beginTransaction();
            $allergie->delete();
            DB::commit();

            return reponse_json_transform([
                'message' => 'Allergie supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'allergie: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la suppression de l\'allergie'
            ], 500);
        }
    }
}
