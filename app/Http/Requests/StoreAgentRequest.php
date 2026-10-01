<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreAgentRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('agent_create');
    }

    public function rules()
    {
        return [
            'nom' => [
                'required', 
                'string',
                'max:255', 
            ],
            'prenom' => [
                'required', 
                'string',
                'max:255', 
            ],
            'adresse' => [
                'nullable',
                'string',
                'max:255', 
            ],
            'email' => [
                
                'string',
                'email', 
                'max:255', 
            ],
            'telephone' => [
                'required', 
                'string',
                'max:20', 
            ],
            'service_id' => [
                'required',
                'exists:services,id',
            ],
        ];
    }
}
