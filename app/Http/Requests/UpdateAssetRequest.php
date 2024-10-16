<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateAssetRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('asset_edit');
    }

    public function rules()
    {
        return [
            'category_id' => [
                'required',
                'integer',
            ],
            'serial_number' => [
                'string',
                'nullable',
            ],
            'name' => [
                'string',
                'required',
            ],
            'photos' => [
                'array',
            ],
            'status_id' => [
                'required',
                'integer',
            ],
            'location_id' => [
                'required',
                'integer',
            ],
            'type' => [
                'string',
                'required',
            ],
            'date_achat' => [
                'required',
                'date_format:' . config('panel.date_format'),
            ],
            'date_mise_en_service' => [
                'date_format:' . config('panel.date_format'),
                'nullable',
            ],
            'modele' => [
                'string',
                'nullable',
            ],
            'fournisseurs.*' => [
                'integer',
            ],
            'fournisseurs' => [
                'required',
                'array',
            ],
            'bons.*' => [
                'integer',
            ],
            'bons' => [
                'required',
                'array',
            ],
            'assigned_to' => [
                'string',
                'nullable',
            ],
            'inventaire_codes.*' => [
                'integer',
            ],
            'inventaire_codes' => [
                'array',
            ],
        ];
    }
}
