<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Movment\Entities\Vaccination;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;

class VaccinationController extends Controller
{
    protected $patienteRepository;

    public function __construct(PatienteRepositoryEloquent $patienteRepository)
    {
        $this->patienteRepository = $patienteRepository;
    }

    /**
     * Liste des vaccinations d'un patient
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

            $vaccinations = Vaccination::with(['doctor', 'movment'])
                ->where('patients_id', $patientId)
                ->orderBy('vaccination_date', 'desc')
                ->get();

            return reponse_json_transform([
                'data' => $vaccinations,
                'message' => 'Liste des vaccinations récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des vaccinations: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération des vaccinations'
            ], 500);
        }
    }

    /**
     * Créer une nouvelle vaccination
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patients_id' => 'required|numeric|exists:patients,id',
                'vaccine_name' => 'required|string|max:255',
                'vaccine_code' => 'nullable|string|max:50',
                'vaccination_date' => 'required|date',
                'batch_number' => 'nullable|string|max:100',
                'administration_route' => 'nullable|string|max:100',
                'site' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'doctor_id' => 'nullable|numeric|exists:users,id',
                'next_dose_date' => 'nullable|date|after:vaccination_date',
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

            $vaccination = Vaccination::create([
                'patients_id' => $validated['patients_id'],
                'vaccine_name' => $validated['vaccine_name'],
                'vaccine_code' => $validated['vaccine_code'] ?? null,
                'vaccination_date' => $validated['vaccination_date'],
                'batch_number' => $validated['batch_number'] ?? null,
                'administration_route' => $validated['administration_route'] ?? null,
                'site' => $validated['site'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'doctor_id' => $validated['doctor_id'] ?? auth()->id(),
                'next_dose_date' => $validated['next_dose_date'] ?? null,
                'movments_id' => $validated['movments_id'] ?? null,
            ]);

            DB::commit();

            return reponse_json_transform([
                'data' => $vaccination->load(['doctor', 'movment']),
                'message' => 'Vaccination enregistrée avec succès'
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la vaccination: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de l\'enregistrement de la vaccination'
            ], 500);
        }
    }

    /**
     * Mettre à jour une vaccination
     */
    public function update(Request $request, $id)
    {
        try {
            $vaccination = Vaccination::findOrFail($id);

            $validated = $request->validate([
                'vaccine_name' => 'sometimes|required|string|max:255',
                'vaccine_code' => 'nullable|string|max:50',
                'vaccination_date' => 'sometimes|required|date',
                'batch_number' => 'nullable|string|max:100',
                'administration_route' => 'nullable|string|max:100',
                'site' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
                'next_dose_date' => 'nullable|date|after:vaccination_date',
            ]);

            DB::beginTransaction();

            $vaccination->update($validated);

            DB::commit();

            return reponse_json_transform([
                'data' => $vaccination->load(['doctor', 'movment']),
                'message' => 'Vaccination mise à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la vaccination: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la mise à jour de la vaccination'
            ], 500);
        }
    }

    /**
     * Supprimer une vaccination
     */
    public function destroy($id)
    {
        try {
            $vaccination = Vaccination::findOrFail($id);
            
            DB::beginTransaction();
            $vaccination->delete();
            DB::commit();

            return reponse_json_transform([
                'message' => 'Vaccination supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la vaccination: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la suppression de la vaccination'
            ], 500);
        }
    }
}
