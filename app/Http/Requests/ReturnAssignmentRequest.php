<?php

namespace App\Http\Requests;

use App\Models\Assignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ReturnAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('assignment_return');
    }

    public function rules(): array
    {
        return [
            'assets'      => ['required', 'array', 'min:1'],
            'assets.*'    => ['integer'],
            'condition'   => ['required', Rule::in(array_keys(array_diff_key(Assignment::CONDITIONS, ['transfert' => true])))],
            'returned_at' => ['required', 'date', 'before_or_equal:today'],
            'location_id' => ['nullable', 'integer', 'exists:asset_locations,id'],
            'notes'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'assets.required'             => 'Cochez au moins une matière à restituer.',
            'returned_at.before_or_equal' => 'La date de retour ne peut pas être dans le futur.',
        ];
    }
}
