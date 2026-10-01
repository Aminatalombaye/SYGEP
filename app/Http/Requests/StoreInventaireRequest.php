<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreInventaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('inventaire_create');
    }

    public function rules(): array
    {
        return [
            'nom'         => ['required', 'string', 'max:255'],
            'starts_at'   => ['nullable', 'date'],
            'ends_at'     => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location_id' => ['nullable', 'integer', 'exists:asset_locations,id'],
            'service_id'  => ['nullable', 'integer', 'exists:services,id'],
            'category_id' => ['nullable', 'integer', 'exists:asset_categories,id'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'           => 'Donnez un nom à la campagne.',
            'ends_at.after_or_equal' => 'La date de fin doit suivre la date de début.',
        ];
    }
}
