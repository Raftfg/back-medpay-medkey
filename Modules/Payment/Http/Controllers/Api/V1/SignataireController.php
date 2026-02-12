<?php

namespace Modules\Payment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Modules\Acl\Entities\User;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Payment\Entities\Signataire;
use Illuminate\Support\Facades\Validator;
use Modules\Payment\Http\Controllers\PaymentController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SignataireController extends PaymentController
{



    public function index()
    {
        try {
            $signataires = DB::table('signataires')
                ->select('signataires.id', 'signataires.uuid', 'signataires.user_id', 'signataires.type_document', 'signataires.titre', 'signataires.statut', 'signataires.created_at', 'signataires.updated_at', 'users.name', 'users.prenom')
                ->join('users', 'signataires.user_id', '=', 'users.id')
                ->get();

            foreach ($signataires as $signataire) {
                // Convertir le BLOB en base64
                $signataire->signature = base64_encode(DB::table('signataires')->where('id', $signataire->id)->value('signature'));
            }

            return response()->json([
                'success' => true,
                'data' => $signataires,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des signataires.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string',
            'type_document' => 'required|string',
            'statut' => 'required|in:Actif,Inactif',
            'user_id' => 'required|exists:users,uuid',
            'signature' => 'nullable|file|mimes:jpeg,png,jpg,JPG,svg|max:4048',
        ]);

        $uuid = $request->input('user_id');
        $user = User::where('uuid', $uuid)->firstOrFail();

        // Vérifier s'il existe déjà un signataire avec les mêmes informations pour cet utilisateur
        // $existingSignataire = Signataire::where('user_id', $user->id)
        //     ->where('type_document', $request->input('type_document'))
        //     ->where('titre', $request->input('titre'))
        //     ->where('statut', $request->input('statut'))
        //     ->first();

        // if ($existingSignataire) {
        //     return response()->json(['error' => 'Un signataire avec les mêmes informations existe déjà.'], 400);
        // }

        $signataire = new Signataire();
        $signataire->user_id = $user->id;
        $signataire->titre = $request->input('titre');
        $signataire->type_document = $request->input('type_document');
        $signataire->statut = $request->input('statut');

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $content = base64_encode(file_get_contents($file->path()));
            $signataire->signature = $content;
        }

        $signataire->save();

        return response()->json([
            'success' => true,
            'data' => $signataire,
            'message' => 'Signataire ajouté avec succès'
        ]);
    }

//  public function update(Request $request, $id)
//     {
//         try {
//             $request->validate([
//                 'titre' => 'required|string',
//                 'type_document' => 'required|string',
//                 'statut' => 'required|in:Actif,Inactif',
//                 'signature' => 'nullable|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
//                 'user_id' => 'required|exists:users,id',
//             ]);

//             // Retrouver l'utilisateur par ID
//             $user = User::findOrFail($request->input('user_id'));

//             // Retrouver le signataire par ID
//             $signataire = Signataire::findOrFail($id);


//             $signataire->user_id = $user->id;
//             $signataire->titre = $request->input('titre');
//             $signataire->type_document = $request->input('type_document');
//             $signataire->statut = $request->input('statut');

//             if ($request->hasFile('signature')) {
//                 $file = $request->file('signature');
//                 $content = file_get_contents($file->path()); // Lire le contenu du fichier
//                 $signataire->signature = $content;
//             }

//             $signataire->save();

//             return response()->json(['success' => true, 'data' =>  $signataire,'message' => 'Signataire mis à jour avec succès']);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Erreur lors de la mise à jour du signataire.',
//                 'error' => $e->getMessage(),
//             ], Response::HTTP_INTERNAL_SERVER_ERROR);
//         }
//     }
   


public function update(Request $request, $id)
{
    try {
        $request->validate([
            'titre' => 'required|string',
            'type_document' => 'required|string',
            'statut' => 'required|in:Actif,Inactif',
            'user_id' => 'required|exists:users,uuid',
            'signature' => 'nullable|file|mimes:jpeg,png,jpg,JPG,svg|max:4048',
        ]);

        $uuid = $request->input('user_id');
        $user = User::where('uuid', $uuid)->firstOrFail();
        $signataire = Signataire::findOrFail($id);

        // Mise à jour des propriétés existantes de l'objet $signataire
        $signataire->user_id = $user->id;
        $signataire->titre = $request->input('titre');
        $signataire->type_document = $request->input('type_document');
        $signataire->statut = $request->input('statut');

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $content = base64_encode(file_get_contents($file->path()));
            $signataire->signature = $content;
        }

        $signataire->save();

        return response()->json([
            'success' => true,
            'data' => $signataire,
            'message' => 'Signataire mise à jour avec succès'
        ]);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Signataire non trouvé. Vérifiez l\'ID fourni.'
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors de la mise à jour du signataire.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// Laravel Controller



    public function show($id)
    {
        try {
            $signataire = Signataire::findOrFail($id);

            // Convertir le BLOB en base64
            $signataire->signature = base64_encode($signataire->signature);

            return response()->json([
                'success' => true,
                'data' => $signataire,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du signataire.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // public function show($uuid)
    // {
    //     try {
    //         // Retrouver le signataire par UUID
    //         $signataire = Signataire::where('uuid', $uuid)->firstOrFail();

    //         // Convertir le BLOB en base64
    //         $signataire->signature = base64_encode($signataire->signature);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $signataire,
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur lors de la récupération du signataire.',
    //             'error' => $e->getMessage(),
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    public function getRoleOrTitle($uuid)
    {
        // Recherchez l'utilisateur par UUID
        $user = User::where('uuid', $uuid)->first();

        if ($user) {
            // Utilisez Laravel Spatie pour récupérer le rôle de l'utilisateur
            $role = $user->getRoleNames()->first();

            // Vous pouvez également récupérer tous les rôles assignés à un utilisateur
            // $roles = $user->getRoleNames();

            return response()->json(['user_roles' => $role]);
        } else {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
    }
}
