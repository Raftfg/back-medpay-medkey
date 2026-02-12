<?php

namespace Modules\Media\Traits;

use Webpatser\Uuid\Uuid;

trait TeleverseTrait {

    /**
     * Sauvegarder les médias reçus depuis le frontend
     * 
     * @param type $item
     * @param type $documents
     * @param type $media_collection_name
     * @param type $media_disk
     * @param type $user_id
     */
    protected function saveMediaApiTenant($item, $document, $media_collection_name, $media_disk, $user_id = null) {
        try {
            if (!$user_id) {
                $user_id = auth() ? user_api()->id : null;
            }

            $path_parts = pathinfo($document->getClientOriginalName());
            $extension = $path_parts['extension'];
            $prefixeTenant = "";
            $nomFichier = $prefixeTenant . Uuid::generate() . "-" . $user_id . ".$extension";
            \Storage::putFileAs('tmp', $document, "$nomFichier");

            $chemin = 'tmp/' . $nomFichier;
            $cheminTemp = storage_path("app/$chemin");

            $media = $item->addMedia($cheminTemp)->toMediaCollection($media_collection_name, $media_disk);
            //$media->user_id = $user_id;
            //$media->save();
        } catch (\Exception $ex) {
            \Log::error($ex->getTraceAsString());
        }
    }

    /**
     * Sauvegarder les médias reçus depuis le frontend
     * 
     * @param type $item
     * @param type $documents
     * @param type $media_collection_name
     * @param type $media_disk
     * @param type $user_id
     */
    protected function saveMediasApiTenant($item, $documents, $media_collection_name, $media_disk, $user_id = null) {
        try {
            if (!$user_id) {
                $user_id = auth() ? user_api()->id : null;
            }

            $prefixeTenant = "";
            foreach ($documents as $document) {
                $path_parts = pathinfo($document->getClientOriginalName());
                $extension = $path_parts['extension'];
                $nomFichier = $prefixeTenant . Uuid::generate() . "-" . $user_id . ".$extension";
                \Storage::putFileAs('tmp', $document, "$nomFichier");

                $chemin = 'tmp/' . $nomFichier;
                $cheminTemp = storage_path("app/$chemin");
                
                $media = $item->addMedia($cheminTemp)->toMediaCollection($media_collection_name, $media_disk);
            }
        } catch (\Exception $ex) {
            \Log::error($ex->getTraceAsString());
        }
    }

    /**
     * Supprimer le média reçu depuis le frontend
     * 
     * @param type $item
     * @param type $documents
     * @param type $media_collection_name
     * @param type $media_disk
     * @param type $user_id
     */
    protected function destroyMediasApi($item, $documents, $media_collection_name, $media_disk, $user_id = null) {
        
    }

//    protected function saveMedias($item, $documentNames, $media_collection_name, $media_disk) {
//        //https://laraveldaily.com/multiple-file-upload-with-dropzone-js-and-laravel-medialibrary-package/
//        $mediasAssocies = $item->getMedia($media_collection_name);
//        $existMediasNames = [];
//        try {
//            if ($mediasAssocies && count($mediasAssocies) > 0) {
//                foreach ($mediasAssocies as $media) {
//                    if (!in_array($media->file_name, $documentNames)) {
//                        if ($media->user_id == user_web()->id) {  //supprimer seulement si c'est lui qui a téléversé
//                            $media->delete();
//                        }
//                    }
//                }
//
//                $existMediasNames = $mediasAssocies->pluck('file_name')->toArray();
//            }
//
//            foreach ($documentNames as $file) {
//                if (count($existMediasNames) === 0 || !in_array($file, $existMediasNames)) {
//                    $chemin = 'tmp/' . $file;
//                    //En mode édition, les anciens médias ne seront pas trouvées dans le temp, alors évirier l'exsitance du chemin d'abord
//                    if (!\Storage::disk('local')->exists($chemin)) {
//                        continue;
//                    }
//                    $media = $item->addMedia(storage_path("app/$chemin"))->toMediaCollection($media_collection_name, $media_disk);
//                    $media->user_id = user_web()->id;
//                    $media->save();
//                }
//            }
//        } catch (\Exception $ex) {
//            \Log::error($ex->getTraceAsString());
//        }
//    }

//    protected function associerMedias($item, $documentNames, $media_collection_name, $media_disk, $folder) {
//        //https://laraveldaily.com/multiple-file-upload-with-dropzone-js-and-laravel-medialibrary-package/
//        $mediasAssocies = $item->getMedia($media_collection_name);
//        $existMediasNames = [];
//        try {
//            //Supprimer les fichier existant mais qui ne sont plus dans le request actuel
//            if ($mediasAssocies && count($mediasAssocies) > 0) {
//                foreach ($mediasAssocies as $media) {
//                    if (!in_array($media->file_name, $documentNames)) {
//                        if (user_admin()->isSuperOrAdmin()) {  //supprimer si c'est un admin ou superadmin
//                            $media->delete();
//                        }
//                    }
//                }
//
//                $existMediasNames = $mediasAssocies->pluck('file_name')->toArray();
//            }
//
//            foreach ($documentNames as $file) {
//
//                if (count($existMediasNames) === 0 || !in_array($file, $existMediasNames)) {//si aucun ancien fichier n'existe ou si le fichier courant du request n'existait pas déjà 
//                    $chemin = $folder . $item->id . '-bp/' . $file;
//                    if (!\Storage::disk('local')->exists($chemin)) {
//                        continue;
//                    }
//                    $media = $item->addMedia(storage_path("app/$chemin"))
//                            ->preservingOriginal()
//                            ->toMediaCollection($media_collection_name, $media_disk);
//                }
//            }
//        } catch (\Exception $ex) {
//            \Log::error($ex->getTraceAsString());
//        }
//    }

}
