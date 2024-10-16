<?php

namespace App\Http\Requests;

use App\Models\Attribution;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateAttributionRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('attribution_edit');
    }

    public function rules()
    {
        return [
            'nom' => [
                'string',
                'nullable',
            ],
            'type_atribution' => [
                'string',
                'nullable',
            ],
        ];
    }
}
