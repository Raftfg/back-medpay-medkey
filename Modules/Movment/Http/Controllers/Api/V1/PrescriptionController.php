<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Movment\Entities\Prescription;
use Modules\Movment\Entities\PrescriptionItem;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;

class PrescriptionController extends Controller
{
    protected $patienteRepository;

    public function __construct(PatienteRepositoryEloquent $patienteRepository)
    {
        $this->patienteRepository = $patienteRepository;
    }

    public function index(Request $request)
    {
        try {
            $patientId = $request->input('patients_id');
            
            if (!$patientId) {
                return reponse_json_transform([
                    'message' => 'L\'identifiant du patient est requis'
                ], 400);
            }

            $prescriptions = Prescription::with(['doctor', 'items', 'clinicalObservation'])
                ->where('patients_id', $patientId)
                ->orderBy('prescription_date', 'desc')
                ->get();

            return reponse_json_transform([
                'data' => $prescriptions,
                'message' => 'Liste des prescriptions récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des prescriptions: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération des prescriptions'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patients_id' => 'required|numeric|exists:patients,id',
                'prescription_date' => 'required|date',
                'notes' => 'nullable|string',
                'status' => 'nullable|in:active,completed,cancelled',
                'valid_until' => 'nullable|date|after:prescription_date',
                'movments_id' => 'nullable|numeric|exists:movments,id',
                'clinical_observation_id' => 'nullable|numeric|exists:clinical_observations,id',
                'items' => 'required|array|min:1',
                'items.*.medication_name' => 'required|string|max:255',
                'items.*.dosage' => 'nullable|string|max:100',
                'items.*.form' => 'nullable|string|max:100',
                'items.*.administration_route' => 'nullable|string|max:100',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.frequency' => 'nullable|string|max:100',
                'items.*.instructions' => 'nullable|string',
                'items.*.duration_days' => 'nullable|integer|min:1',
            ]);

            $patient = $this->patienteRepository->find($validated['patients_id']);
            if (!$patient) {
                return reponse_json_transform([
                    'message' => 'Patient non trouvé'
                ], 404);
            }

            DB::beginTransaction();

            $prescription = Prescription::create([
                'patients_id' => $validated['patients_id'],
                'prescription_date' => $validated['prescription_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'valid_until' => $validated['valid_until'] ?? null,
                'movments_id' => $validated['movments_id'] ?? null,
                'clinical_observation_id' => $validated['clinical_observation_id'] ?? null,
                'doctor_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'product_id' => $item['product_id'] ?? null,
                    'medication_name' => $item['medication_name'],
                    'dosage' => $item['dosage'] ?? null,
                    'form' => $item['form'] ?? null,
                    'administration_route' => $item['administration_route'] ?? null,
                    'quantity' => $item['quantity'],
                    'frequency' => $item['frequency'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                    'duration_days' => $item['duration_days'] ?? null,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            return reponse_json_transform([
                'data' => $prescription->load(['items', 'doctor']),
                'message' => 'Prescription enregistrée avec succès'
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la prescription: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de l\'enregistrement de la prescription'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $prescription = Prescription::with(['items', 'doctor', 'patient', 'clinicalObservation'])
                ->findOrFail($id);

            return reponse_json_transform([
                'data' => $prescription,
                'message' => 'Prescription récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            return reponse_json_transform([
                'message' => 'Prescription non trouvée'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $prescription = Prescription::findOrFail($id);

            $validated = $request->validate([
                'prescription_date' => 'sometimes|required|date',
                'notes' => 'nullable|string',
                'status' => 'sometimes|required|in:active,completed,cancelled',
                'valid_until' => 'nullable|date|after:prescription_date',
            ]);

            DB::beginTransaction();
            $prescription->update($validated);
            DB::commit();

            return reponse_json_transform([
                'data' => $prescription->load(['items', 'doctor']),
                'message' => 'Prescription mise à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la prescription: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la mise à jour de la prescription'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $prescription = Prescription::findOrFail($id);
            
            DB::beginTransaction();
            $prescription->delete();
            DB::commit();

            return reponse_json_transform([
                'message' => 'Prescription supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la prescription: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la suppression de la prescription'
            ], 500);
        }
    }
}
