<?php

namespace App\Http\Requests;

use App\Models\Report;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreReportRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('report_create');
    }

    public function rules()
    {
        return [
            'title' => [
                'string',
                'required',
            ],
            'content' => [
                'required',
            ],
            'report_date' => [
                'required',
                'date_format:' . config('panel.date_format'),
            ],
            'projects.*' => [
                'integer',
            ],
            'projects' => [
                'required',
                'array',
            ],
        ];
    }
}
