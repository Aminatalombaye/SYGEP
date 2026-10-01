<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PeriodicReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class PeriodicReportController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('periodic_report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.periodicReports.index', [
            'sections' => PeriodicReport::SECTIONS,
            'periods'  => PeriodicReport::PERIODS,
        ]);
    }

    public function show(Request $request, PeriodicReport $report)
    {
        abort_if(Gate::denies('periodic_report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'periode'    => ['required', Rule::in(array_keys(PeriodicReport::PERIODS))],
            'du'         => ['nullable', 'required_if:periode,personnalisee', 'date'],
            'au'         => ['nullable', 'required_if:periode,personnalisee', 'date'],
            'sections'   => ['required', 'array', 'min:1'],
            'sections.*' => [Rule::in(array_keys(PeriodicReport::SECTIONS))],
        ], [
            'sections.required' => 'Choisissez au moins une rubrique.',
            'du.required_if'    => 'Indiquez le début de la période.',
            'au.required_if'    => 'Indiquez la fin de la période.',
        ]);

        [$from, $to, $label] = PeriodicReport::range($data['periode'], $data['du'] ?? null, $data['au'] ?? null);

        return view('admin.periodicReports.print', [
            'from'     => $from,
            'to'       => $to,
            'label'    => $label,
            'sections' => array_intersect_key(PeriodicReport::SECTIONS, array_flip($data['sections'])),
            'data'     => $report->build($from, $to, $data['sections']),
            'contact'  => config('panel.contact'),
        ]);
    }
}
