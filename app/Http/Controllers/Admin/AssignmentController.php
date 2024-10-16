<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAssignmentRequest;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Attribution;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Service;


class AssignmentController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('assignment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignments = Assignment::with(['matieres', 'atributions'])->get();

        return view('admin.assignments.index', compact('assignments'));
    }

    public function create()
    {
        abort_if(Gate::denies('assignment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $matieres = Asset::pluck('serial_number', 'id');

        $atributions = Attribution::pluck('type_atribution', 'id');

        $services = Service::pluck('name', 'id');

        return view('admin.assignments.create', compact('atributions', 'matieres', 'services'));
    }

    public function store(StoreAssignmentRequest $request)
    {
        $assignment = Assignment::create($request->all());
        $assignment->matieres()->sync($request->input('matieres', []));
        $assignment->atributions()->sync($request->input('atributions', []));
        $assignment->services()->sync($request->input('services', []));


        return redirect()->route('admin.assignments.index');
    }

    public function edit(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $matieres = Asset::pluck('serial_number', 'id');

        $atributions = Attribution::pluck('type_atribution', 'id');

        $services = Service::pluck('name', 'id');

        $assignment->load('matieres', 'atributions', 'services');

        return view('admin.assignments.edit', compact('assignment', 'atributions', 'matieres', 'services'));
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $assignment->update($request->all());
        $assignment->matieres()->sync($request->input('matieres', []));
        $assignment->atributions()->sync($request->input('atributions', []));
        $assignment->services()->sync($request->input('services', []));

        return redirect()->route('admin.assignments.index');
    }

    public function show(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignment->load('matieres', 'atributions', 'services');

        return view('admin.assignments.show', compact('assignment'));
    }

    public function destroy(Assignment $assignment)
    {
        abort_if(Gate::denies('assignment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $assignment->delete();

        return back();
    }

    public function massDestroy(MassDestroyAssignmentRequest $request)
    {
        $assignments = Assignment::find(request('ids'));

        foreach ($assignments as $assignment) {
            $assignment->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
