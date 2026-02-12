<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Movment\Entities\DmeDocument;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;

class DmeDocumentController extends Controller
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

            $documents = DmeDocument::with(['uploadedBy', 'clinicalObservation'])
                ->where('patients_id', $patientId)
                ->orderBy('document_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return reponse_json_transform([
                'data' => $documents,
                'message' => 'Liste des documents récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des documents: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération des documents'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patients_id' => 'required|numeric|exists:patients,id',
                'title' => 'required|string|max:255',
                'type' => 'required|in:certificat_medical,ordonnance,resultat_examen,compte_rendu,imagerie,laboratoire,autre',
                'file' => 'required|file|max:10240', // 10MB max
                'description' => 'nullable|string',
                'document_date' => 'nullable|date',
                'movments_id' => 'nullable|numeric|exists:movments,id',
                'clinical_observation_id' => 'nullable|numeric|exists:clinical_observations,id',
            ]);

            $patient = $this->patienteRepository->find($validated['patients_id']);
            if (!$patient) {
                return reponse_json_transform([
                    'message' => 'Patient non trouvé'
                ], 404);
            }

            DB::beginTransaction();

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('dme/documents/' . $patient->id, $fileName, 'public');

            $document = DmeDocument::create([
                'patients_id' => $validated['patients_id'],
                'title' => $validated['title'],
                'type' => $validated['type'],
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'description' => $validated['description'] ?? null,
                'document_date' => $validated['document_date'] ?? now(),
                'movments_id' => $validated['movments_id'] ?? null,
                'clinical_observation_id' => $validated['clinical_observation_id'] ?? null,
                'uploaded_by' => auth()->id(),
            ]);

            DB::commit();

            return reponse_json_transform([
                'data' => $document->load(['uploadedBy']),
                'message' => 'Document enregistré avec succès'
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du document: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de l\'enregistrement du document'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $document = DmeDocument::with(['uploadedBy', 'patient', 'clinicalObservation'])
                ->findOrFail($id);

            return reponse_json_transform([
                'data' => $document,
                'message' => 'Document récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            return reponse_json_transform([
                'message' => 'Document non trouvé'
            ], 404);
        }
    }

    public function download($id)
    {
        try {
            Log::info('Tentative de téléchargement', ['document_id' => $id]);
            
            $document = DmeDocument::find($id);
            
            if (!$document) {
                Log::warning('Document non trouvé pour téléchargement', ['document_id' => $id]);
                return reponse_json_transform([
                    'message' => 'Document non trouvé'
                ], 404);
            }
            
            if (empty($document->file_path)) {
                Log::warning('Chemin de fichier vide pour le document', [
                    'document_id' => $id,
                    'document' => $document->toArray()
                ]);
                return reponse_json_transform([
                    'message' => 'Chemin de fichier non défini'
                ], 404);
            }
            
            // Nettoyer le chemin du fichier (enlever les préfixes incorrects)
            $filePath = $document->file_path;
            $filePath = ltrim($filePath, '/');
            $filePath = preg_replace('#^storage/#', '', $filePath);
            $filePath = preg_replace('#^/storage/#', '', $filePath);
            
            $foundPath = null;
            
            // Liste des chemins possibles à vérifier
            $possiblePaths = [
                $filePath, // Chemin nettoyé
                $document->file_path, // Chemin original
                ltrim($document->file_path, '/'), // Sans le slash initial
                str_replace('storage/', '', $document->file_path), // Sans le préfixe storage/
                str_replace('/storage/', '', $document->file_path), // Sans le préfixe /storage/
            ];
            
            // Enlever les doublons
            $possiblePaths = array_unique($possiblePaths);
            
            // Essayer chaque chemin
            foreach ($possiblePaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    $foundPath = $path;
                    break;
                }
            }
            
            // Si aucun chemin ne fonctionne, vérifier aussi avec file_exists directement
            if (!$foundPath) {
                $basePath = storage_path('app/public');
                foreach ($possiblePaths as $path) {
                    $fullPath = $basePath . '/' . ltrim($path, '/');
                    if (file_exists($fullPath)) {
                        $foundPath = $path;
                        break;
                    }
                }
            }
            
            if (!$foundPath) {
                // Vérifier si c'est un document de test/seeder (fichier jamais uploadé)
                $isTestData = (
                    strpos($document->file_path, '/storage/documents/') !== false ||
                    strpos($document->file_path, 'storage/documents/') !== false ||
                    strpos($document->file_path, 'documents/') === 0
                ) && !Storage::disk('public')->exists($filePath);
                
                Log::warning('Fichier physique non trouvé', [
                    'document_id' => $id,
                    'file_path' => $document->file_path,
                    'cleaned_path' => $filePath,
                    'possible_paths_tested' => $possiblePaths,
                    'storage_base_path' => Storage::disk('public')->path(''),
                    'is_test_data' => $isTestData,
                    'storage_exists_check' => array_map(function($p) {
                        return ['path' => $p, 'exists' => Storage::disk('public')->exists($p)];
                    }, $possiblePaths)
                ]);
                
                $message = 'Fichier non trouvé sur le serveur';
                if ($isTestData) {
                    $message .= ' (ce document semble être une donnée de test - le fichier n\'a jamais été uploadé)';
                }
                
                return reponse_json_transform([
                    'message' => $message,
                    'file_path' => $document->file_path,
                    'title' => $document->title
                ], 404);
            }
            
            // Utiliser le chemin trouvé
            $filePath = $foundPath;

            // Télécharger le fichier
            $fileName = $document->file_name ?: basename($filePath);
            Log::info('Téléchargement réussi', [
                'document_id' => $id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'original_file_path' => $document->file_path
            ]);
            
            return Storage::disk('public')->download($filePath, $fileName);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Document non trouvé (ModelNotFoundException): ' . $e->getMessage(), ['document_id' => $id]);
            return reponse_json_transform([
                'message' => 'Document non trouvé'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du document: ' . $e->getMessage(), [
                'document_id' => $id,
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return reponse_json_transform([
                'message' => 'Erreur lors du téléchargement du document: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $document = DmeDocument::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'type' => 'sometimes|required|in:certificat_medical,ordonnance,resultat_examen,compte_rendu,imagerie,laboratoire,autre',
                'description' => 'nullable|string',
                'document_date' => 'nullable|date',
            ]);

            DB::beginTransaction();
            $document->update($validated);
            DB::commit();

            return reponse_json_transform([
                'data' => $document->load(['uploadedBy']),
                'message' => 'Document mis à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du document: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la mise à jour du document'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $document = DmeDocument::findOrFail($id);
            
            DB::beginTransaction();
            
            // Supprimer le fichier
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            $document->delete();
            DB::commit();

            return reponse_json_transform([
                'message' => 'Document supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du document: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la suppression du document'
            ], 500);
        }
    }
}
