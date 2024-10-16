<?php

namespace App\Http\Requests;

use App\Models\Infrastructure;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateInfrastructureRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('infrastructure_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'description' => [
                'string',
                'required',
            ],
            'status' => [
                'string',
                'required',
            ],
            'location' => [
                'string',
                'required',
            ],
            'construction_date' => [
                'required',
                'date_format:' . config('panel.date_format'),
            ],
            'depreciation_plan' => [
                'string',
                'nullable',
            ],
            'type' => [
                'string',
                'nullable',
            ],
        ];
    }
}
