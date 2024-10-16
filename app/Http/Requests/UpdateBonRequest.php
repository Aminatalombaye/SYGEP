<?php

namespace App\Http\Requests;

use App\Models\Bon;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateBonRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('bon_edit');
    }

    public function rules()
    {
        return [
            'date_emission' => [
                'required',
                'date_format:' . config('panel.date_format'),
            ],
            'organisation' => [
                'string',
                'required',
                'unique:bons,organisation,' . request()->route('bon')->id,
            ],
            'reference_commande' => [
                'string',
                'required',
            ],
            'nom_destinataire' => [
                'string',
                'required',
            ],
            'bon' => [
                'string',
                'nullable',
            ],
            'date_livraison' => [
                'date_format:' . config('panel.date_format'),
                'nullable',
            ],
        ];
    }
}
