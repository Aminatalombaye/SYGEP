<?php

namespace App\Http\Requests;

use App\Models\MaintenanceRequest;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyMaintenanceRequestRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('maintenance_request_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:maintenance_requests,id',
        ];
    }
}
