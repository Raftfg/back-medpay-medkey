<?php

namespace Modules\Annuaire\Http\Requests;

class EmployerPerformanceUpdateRequest extends EmployerPerformanceRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return $this->reglesCommunes();
    }
}

