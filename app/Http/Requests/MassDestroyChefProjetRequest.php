<?php

namespace App\Http\Requests;

use App\Models\ChefProjet;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class MassDestroyChefProjetRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('chef_projet_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'ids'   => 'required|array',
            'ids.*' => 'exists:chef_projets,id',
        ];
    }
}
