<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TransferAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('assignment_create') && Gate::allows('assignment_return');
    }

    public function rules(): array
    {
        return [
            'agent_id'           => ['nullable', 'integer', 'exists:agents,id', 'required_without:service_id'],
            'service_id'         => ['nullable', 'integer', 'exists:services,id'],
            'location_id'        => ['nullable', 'integer', 'exists:asset_locations,id'],
            'type'               => ['nullable', 'string', 'in:'.implode(',', array_keys(\App\Models\Assignment::TYPES))],
            'assigned_at'        => ['required', 'date'],
            'expected_return_at' => ['nullable', 'date', 'after_or_equal:assigned_at'],
            'notes'              => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.required_without' => 'Choisissez le nouvel agent ou service bénéficiaire.',
        ];
    }
}
