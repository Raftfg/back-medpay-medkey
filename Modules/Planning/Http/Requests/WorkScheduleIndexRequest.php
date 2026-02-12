<?php

namespace Modules\Planning\Http\Requests;

use App\Http\Requests\BaseRequest;

class WorkScheduleIndexRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employer_id' => 'nullable|exists:employers,id',
            'service_id' => 'nullable|exists:services,id',
            'status' => 'nullable|in:draft,published,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
}
