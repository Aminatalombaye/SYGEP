<?php

namespace App\Http\Requests;

use App\Models\MaintenanceRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreMaintenanceRequestRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('maintenance_request_create');
    }

    public function rules()
    {
        return [
            'description' => [
                'string',
                'required',
            ],
            'status' => [
                'string',
                'required',
            ],
            'created_by' => [
                'string',
                'required',
            ],
            'requests.*' => [
                'integer',
            ],
            'requests' => [
                'array',
            ],
        ];
    }
}
