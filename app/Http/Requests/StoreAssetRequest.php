<?php

namespace App\Http\Requests;

use App\Models\Asset;
use Gate;
use App\Models\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;

class StoreAssetRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('asset_create');
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
                Rule::unique('assets', 'serial_number')->whereNull('deleted_at'),
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
                // « Affecté » vient d'une affectation, « En réparation » d'une demande de maintenance.
                Rule::notIn(array_merge(AssetStatus::idsFor(AssetStatus::ASSIGNED), AssetStatus::idsFor(AssetStatus::REPAIR))),
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
                'nullable',
                'integer',
            ],
            'fournisseurs' => [
                'nullable',
                'array',
                'max:1',
            ],
            'bons.*' => [
                'nullable',
                'integer',
            ],
            'bons' => [
                'nullable',
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
