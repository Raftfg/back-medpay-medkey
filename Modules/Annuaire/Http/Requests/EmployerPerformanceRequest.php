<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;

class EmployerPerformanceRequest extends BaseRequest
{
    protected $entite = "EmployerPerformance";

    protected function reglesCommunes()
    {
        return [
            'employers_id' => 'required|exists:employers,id',
            'services_id' => 'nullable|exists:services,id',
            'period_type' => 'required|in:monthly,quarterly,annual,custom',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'overall_score' => 'nullable|numeric|min:0|max:100',
            'criteria_scores' => 'nullable|array',
            'comments' => 'nullable|string',
        ];
    }
}

