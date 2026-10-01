<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('assignment_edit');
    }

    public function rules(): array
    {
        return [
            'location_id'        => ['nullable', 'integer', 'exists:asset_locations,id'],
            'type'               => ['nullable', 'string', 'in:'.implode(',', array_keys(\App\Models\Assignment::TYPES))],
            'assigned_at'        => ['required', 'date'],
            'expected_return_at' => [\Illuminate\Validation\Rule::requiredIf(fn () => ! in_array($this->input('type') ?: 'dotation', ['dotation', 'programme'], true)), 'nullable', 'date', 'after_or_equal:assigned_at'],
            'notes'              => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'expected_return_at.required'       => 'Indiquez la date de retour prévue pour ce type d\'affectation.',
            'expected_return_at.after_or_equal' => 'La date de retour prévue doit suivre la date d\'affectation.',
        ];
    }
}
