<?php

namespace Modules\Planning\Http\Requests;

use App\Http\Requests\BaseRequest;

class WorkScheduleDeleteRequest extends BaseRequest
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
