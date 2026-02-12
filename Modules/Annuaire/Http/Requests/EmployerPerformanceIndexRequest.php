<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;

class EmployerPerformanceIndexRequest extends BaseRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employers_id' => 'nullable|integer',
            'services_id' => 'nullable|integer',
            'period_type' => 'nullable|in:monthly,quarterly,annual,custom',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }
}

