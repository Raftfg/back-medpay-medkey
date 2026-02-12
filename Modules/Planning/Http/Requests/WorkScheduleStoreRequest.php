<?php

namespace Modules\Planning\Http\Requests;

class WorkScheduleStoreRequest extends WorkScheduleRequest
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
