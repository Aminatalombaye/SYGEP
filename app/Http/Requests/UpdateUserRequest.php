<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('user_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'email' => [
                'required',
                'unique:users,email,' . request()->route('user')->id,
            ],
            'service_id' => [
                'nullable',
                'integer',
                'exists:services,id',
            ],
            'roles.*' => [
                'integer',
            ],
            'roles' => [
                'required',
                'array',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('service_id') || ! is_array($this->input('roles'))) {
                return;
            }

            $needsService = Role::whereIn('id', $this->input('roles'))
                ->whereHas('permissions', fn ($q) => $q->where('title', 'perimetre_service'))
                ->exists();

            if ($needsService) {
                $validator->errors()->add('service_id', 'Le service de rattachement est obligatoire pour ce rôle.');
            }
        });
    }
}
