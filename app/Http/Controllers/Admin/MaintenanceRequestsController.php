<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMaintenanceRequestRequest;
use App\Http\Requests\StoreMaintenanceRequestRequest;
use App\Http\Requests\UpdateMaintenanceRequestRequest;
use App\Models\MaintenanceRequest;
use App\Models\Task;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceRequestsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('maintenance_request_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenanceRequests = MaintenanceRequest::with(['requests'])->get();

        return view('admin.maintenanceRequests.index', compact('maintenanceRequests'));
    }

    public function create()
    {
        abort_if(Gate::denies('maintenance_request_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $requests = Task::pluck('name', 'id');

        return view('admin.maintenanceRequests.create', compact('requests'));
    }

    public function store(StoreMaintenanceRequestRequest $request)
    {
        $maintenanceRequest = MaintenanceRequest::create($request->all());
        $maintenanceRequest->requests()->sync($request->input('requests', []));

        return redirect()->route('admin.maintenance-requests.index');
    }

    public function edit(MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $requests = Task::pluck('name', 'id');

        $maintenanceRequest->load('requests');

        return view('admin.maintenanceRequests.edit', compact('maintenanceRequest', 'requests'));
    }

    public function update(UpdateMaintenanceRequestRequest $request, MaintenanceRequest $maintenanceRequest)
    {
        $maintenanceRequest->update($request->all());
        $maintenanceRequest->requests()->sync($request->input('requests', []));

        return redirect()->route('admin.maintenance-requests.index');
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenanceRequest->load('requests');

        return view('admin.maintenanceRequests.show', compact('maintenanceRequest'));
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        abort_if(Gate::denies('maintenance_request_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $maintenanceRequest->delete();

        return back();
    }

    public function massDestroy(MassDestroyMaintenanceRequestRequest $request)
    {
        $maintenanceRequests = MaintenanceRequest::find(request('ids'));

        foreach ($maintenanceRequests as $maintenanceRequest) {
            $maintenanceRequest->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
