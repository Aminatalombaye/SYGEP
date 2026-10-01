<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreUserAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('user_alert_create');
    }

    public function rules(): array
    {
        return [
            'alert_text' => ['required', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:120'],
            'alert_link'  => ['nullable', 'required_if:destination,custom', 'url', 'max:255'],
            'audience'   => ['required', 'in:tous,roles,users'],
            'roles'      => ['required_if:audience,roles', 'array'],
            'roles.*'    => ['integer', 'exists:roles,id'],
            'users'      => ['required_if:audience,users', 'array'],
            'users.*'    => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'alert_text.required' => 'Le message est obligatoire.',
            'alert_link.url'         => 'Le lien doit être une adresse complète (https://…).',
            'alert_link.required_if' => 'Saisissez l\'adresse personnalisée.',
            'roles.required_if'   => 'Choisissez au moins un rôle.',
            'users.required_if'   => 'Choisissez au moins un destinataire.',
        ];
    }
}
