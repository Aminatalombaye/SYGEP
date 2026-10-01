<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('assignment_create');
    }

    public function rules(): array
    {
        return [
            'agent_id'           => ['nullable', 'integer', 'exists:agents,id', 'required_without:service_id'],
            'service_id'         => ['nullable', 'integer', 'exists:services,id'],
            'location_id'        => ['nullable', 'integer', 'exists:asset_locations,id'],
            'type'               => ['nullable', 'string', 'in:'.implode(',', array_keys(\App\Models\Assignment::TYPES))],
            'assigned_at'        => ['required', 'date'],
            'expected_return_at' => [\Illuminate\Validation\Rule::requiredIf(fn () => ! in_array($this->input('type') ?: 'dotation', ['dotation', 'programme'], true)), 'nullable', 'date', 'after_or_equal:assigned_at'],
            'assets'             => ['required', 'array', 'min:1'],
            'assets.*'           => ['integer', 'distinct', 'exists:assets,id'],
            'notes'              => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'agent_id'           => 'agent',
            'service_id'         => 'service',
            'location_id'        => 'emplacement',
            'assigned_at'        => 'date d\'affectation',
            'expected_return_at' => 'date de retour prévue',
            'assets'             => 'matières',
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.required_without'         => 'Choisissez un agent ou un service bénéficiaire.',
            'assets.required'                   => 'Sélectionnez au moins une matière.',
            'expected_return_at.required'       => 'Indiquez la date de retour prévue pour ce type d\'affectation.',
            'expected_return_at.after_or_equal' => 'La date de retour prévue doit suivre la date d\'affectation.',
        ];
    }
}
