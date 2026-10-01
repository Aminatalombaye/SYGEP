<?php

namespace App\Http\Requests;

use App\Models\ChefProjet;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreChefProjetRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('chef_projet_create');
    }

    public function rules()
    {
        return [
            'nom' => [
                'string',
                'required',
                'max:255',
            ],
            'prenom' => [
                'string',
                'required',
                'max:255',
            ],
            'adresse' => [
                'string',
                'nullable',
            ],
            'e_mail' => [
                'email',
                'nullable',
                'max:255',
                'required_without:telephone',
            ],
            'telephone' => [
                'string',
                'nullable',
                'max:40',
                'required_without:e_mail',
            ],
        ];
    }

    public function messages()
    {
        return [
            'telephone.required_without' => 'Indiquez au moins un téléphone ou un e-mail pour pouvoir le joindre.',
            'e_mail.required_without'    => 'Indiquez au moins un téléphone ou un e-mail pour pouvoir le joindre.',
        ];
    }
}
