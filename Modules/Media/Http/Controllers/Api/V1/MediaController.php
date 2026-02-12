<?php

namespace Modules\Media\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Modules\Media\Http\Requests\MediaDeleteRequest;
use Modules\Media\Repositories\MediaRepositoryEloquent;
use Modules\Model\Repositories\ModelRepository;

class MediaController extends \Modules\Media\Http\Controllers\MediaController {

    /**
     * @var PostRepository
     */
    protected $mediaRepositoryEloquent;

    public function __construct(MediaRepositoryEloquent $mediaRepositoryEloquent) {
        parent::__construct();
        $this->mediaRepositoryEloquent = $mediaRepositoryEloquent;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return Response
     */
    public function destroy(MediaDeleteRequest $request, $uuid)
    {
        $data = ["message" => __("Item non supprimé"),];
        $item = $this->mediaRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $modelType = $item->model_type;
        $cved = collect();//$this->modelRepository->findOrFail($item->model_id);
        //Suppression avec le fichier sur le disque
        if(!$cved->complete){   //Seulement si la phase n'est pas complétée
            (new $modelType())->find($item->model_id )->deleteMedia($item->id);
            
            $data = [
                "message" => __("Item supprimé avec succès"),
            ];
        }
        return reponse_json_transform($data);
    }
}
