<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;

class EmployerPerformanceDeleteRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}

