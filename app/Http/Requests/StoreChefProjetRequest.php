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
                'nullable',
            ],
            'prenom' => [
                'string',
                'nullable',
            ],
            'adresse' => [
                'string',
                'nullable',
            ],
            'e_mail' => [
                'string',
                'nullable',
            ],
            'telephone' => [
                'string',
                'nullable',
            ],
        ];
    }
}
