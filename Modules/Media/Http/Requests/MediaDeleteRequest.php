<?php

namespace Modules\Media\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Auth\Access\AuthorizationException;

class MediaDeleteRequest extends MediaRequest {
    
    /**
     * Determine if the step is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return user_api()->isPermission("delete $this->entite");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = [
        ];
        return $rules;
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException($this->erreurMessageSuppression);
    }

}
