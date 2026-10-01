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
            ],
            'reference_commande' => [
                'string',
                'required',
                'unique:bons,reference_commande,' . request()->route('bon')->id,
            ],
            'nom_destinataire' => [
                'string',
                'required',
            ],
            'bon' => [
                'string',
                'required',
            ],
            'date_livraison' => [
                'date_format:' . config('panel.date_format'),
                'nullable',
                'after_or_equal:date_emission',
            ],
        ];
    }
}
