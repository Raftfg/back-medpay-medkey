<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Movment\Entities\ClinicalObservation;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;

class ClinicalObservationController extends Controller
{
    protected $patienteRepository;

    public function __construct(PatienteRepositoryEloquent $patienteRepository)
    {
        $this->patienteRepository = $patienteRepository;
    }

    /**
     * Liste des observations cliniques d'un patient
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

            $observations = ClinicalObservation::with(['doctor', 'movment'])
                ->where('patients_id', $patientId)
                ->orderBy('observation_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return reponse_json_transform([
                'data' => $observations,
                'message' => 'Liste des observations cliniques récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des observations: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération des observations cliniques'
            ], 500);
        }
    }

    /**
     * Créer une nouvelle observation clinique SOAP (F2.4)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patients_id' => 'required|numeric|exists:patients,id',
                'subjective' => 'nullable|string',
                'objective' => 'nullable|string',
                'assessment' => 'nullable|string',
                'plan' => 'nullable|string',
                'blood_pressure' => 'nullable|string|max:20',
                'heart_rate' => 'nullable|integer|min:0|max:300',
                'temperature' => 'nullable|numeric|min:30|max:45',
                'respiratory_rate' => 'nullable|integer|min:0|max:100',
                'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
                'weight' => 'nullable|numeric|min:0|max:500',
                'height' => 'nullable|numeric|min:0|max:300',
                'observation_date' => 'nullable|date',
                'type' => 'nullable|string|max:50',
                'movments_id' => 'nullable|numeric|exists:movments,id',
                'doctor_id' => 'nullable|numeric|exists:users,id',
            ]);

            // Vérifier que le patient existe
            $patient = $this->patienteRepository->find($validated['patients_id']);
            if (!$patient) {
                return reponse_json_transform([
                    'message' => 'Patient non trouvé'
                ], 404);
            }

            DB::beginTransaction();

            $observation = ClinicalObservation::create([
                'patients_id' => $validated['patients_id'],
                'subjective' => $validated['subjective'] ?? null,
                'objective' => $validated['objective'] ?? null,
                'assessment' => $validated['assessment'] ?? null,
                'plan' => $validated['plan'] ?? null,
                'blood_pressure' => $validated['blood_pressure'] ?? null,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
                'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'height' => $validated['height'] ?? null,
                'observation_date' => $validated['observation_date'] ?? now(),
                'type' => $validated['type'] ?? 'consultation',
                'movments_id' => $validated['movments_id'] ?? null,
                'doctor_id' => $validated['doctor_id'] ?? auth()->id(),
            ]);

            DB::commit();

            return reponse_json_transform([
                'data' => $observation->load(['doctor', 'movment']),
                'message' => 'Observation clinique enregistrée avec succès'
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'observation: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de l\'enregistrement de l\'observation clinique'
            ], 500);
        }
    }

    /**
     * Mettre à jour une observation clinique
     */
    public function update(Request $request, $id)
    {
        try {
            $observation = ClinicalObservation::findOrFail($id);

            $validated = $request->validate([
                'subjective' => 'nullable|string',
                'objective' => 'nullable|string',
                'assessment' => 'nullable|string',
                'plan' => 'nullable|string',
                'blood_pressure' => 'nullable|string|max:20',
                'heart_rate' => 'nullable|integer|min:0|max:300',
                'temperature' => 'nullable|numeric|min:30|max:45',
                'respiratory_rate' => 'nullable|integer|min:0|max:100',
                'oxygen_saturation' => 'nullable|numeric|min:0|max:100',
                'weight' => 'nullable|numeric|min:0|max:500',
                'height' => 'nullable|numeric|min:0|max:300',
                'observation_date' => 'nullable|date',
                'type' => 'nullable|string|max:50',
            ]);

            DB::beginTransaction();

            $observation->update($validated);

            DB::commit();

            return reponse_json_transform([
                'data' => $observation->load(['doctor', 'movment']),
                'message' => 'Observation clinique mise à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'observation: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la mise à jour de l\'observation clinique'
            ], 500);
        }
    }

    /**
     * Supprimer une observation clinique
     */
    public function destroy($id)
    {
        try {
            $observation = ClinicalObservation::findOrFail($id);
            
            DB::beginTransaction();
            $observation->delete();
            DB::commit();

            return reponse_json_transform([
                'message' => 'Observation clinique supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'observation: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la suppression de l\'observation clinique'
            ], 500);
        }
    }
}
