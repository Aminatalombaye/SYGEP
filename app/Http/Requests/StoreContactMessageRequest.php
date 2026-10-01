<?php

namespace App\Http\Requests;

use App\Models\ContactMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:120'],
            'email'     => ['required', 'email', 'max:190'],
            'phone'     => ['nullable', 'string', 'max:30', 'regex:/^[0-9 +().-]+$/'],
            'structure' => ['nullable', 'string', 'max:190'],
            'subject'   => ['required', Rule::in(array_keys(ContactMessage::SUBJECTS))],
            'message'   => ['required', 'string', 'min:10', 'max:5000'],
            'website'   => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'      => 'nom complet',
            'email'     => 'adresse e-mail',
            'phone'     => 'téléphone',
            'structure' => 'structure / service',
            'subject'   => 'objet',
            'message'   => 'message',
        ];
    }

    public function messages(): array
    {
        return [
            'required'      => 'Le champ :attribute est obligatoire.',
            'email'         => "L'adresse e-mail n'est pas valide.",
            'max'           => 'Le champ :attribute est trop long (:max caractères maximum).',
            'min'           => 'Le champ :attribute doit contenir au moins :min caractères.',
            'in'            => "L'objet choisi n'est pas valide.",
            'phone.regex'   => "Le numéro de téléphone n'est pas valide.",
            'website.prohibited' => 'Envoi refusé.',
        ];
    }
}
