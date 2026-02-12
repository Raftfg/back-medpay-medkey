<?php

namespace Modules\Planning\Http\Requests;

use App\Http\Requests\BaseRequest;

class WorkScheduleRequest extends BaseRequest
{
    protected $entite = "WorkSchedule";

    public function reglesCommunes()
    {
        return [
            'employer_id' => 'nullable|exists:employers,id',
            'service_id' => 'nullable|exists:services,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period_type' => 'nullable|in:weekly,monthly,custom',
            'status' => 'nullable|in:draft,published,archived',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }
}
