<?php

namespace Modules\Annuaire\Http\Requests;

class EmployerPerformanceStoreRequest extends EmployerPerformanceRequest
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

