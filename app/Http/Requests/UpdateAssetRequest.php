<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Gate;
use App\Models\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
                Rule::unique('assets', 'serial_number')->whereNull('deleted_at')->ignore($this->route('asset')->id),
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
                'nullable',
            ],
            'date_achat' => [
                'required',
                'date_format:' . config('panel.date_format'),
                'before_or_equal:' . now()->format(config('panel.date_format')),
            ],
            'date_mise_en_service' => [
                'date_format:' . config('panel.date_format'),
                'nullable',
                'after_or_equal:date_achat',
            ],
            'modele' => [
                'string',
                'nullable',
            ],
            'fournisseurs.*' => [
                'required',
                'integer',
            ],
            'fournisseurs' => [
                'required',
                'array',
                'max:1',
            ],
            'bons.*' => [
                'required',
                'integer',
            ],
            'bons' => [
                'required',
                'array',
                'max:1',
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
