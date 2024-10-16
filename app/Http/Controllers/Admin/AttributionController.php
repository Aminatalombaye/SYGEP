<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyAttributionRequest;
use App\Http\Requests\StoreAttributionRequest;
use App\Http\Requests\UpdateAttributionRequest;
use App\Models\Attribution;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AttributionController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('attribution_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $attributions = Attribution::all();

        return view('admin.attributions.index', compact('attributions'));
    }

    public function create()
    {
        abort_if(Gate::denies('attribution_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.attributions.create');
    }

    public function store(StoreAttributionRequest $request)
    {
        $attribution = Attribution::create($request->all());

        return redirect()->route('admin.attributions.index');
    }

    public function edit(Attribution $attribution)
    {
        abort_if(Gate::denies('attribution_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.attributions.edit', compact('attribution'));
    }

    public function update(UpdateAttributionRequest $request, Attribution $attribution)
    {
        $attribution->update($request->all());

        return redirect()->route('admin.attributions.index');
    }

    public function show(Attribution $attribution)
    {
        abort_if(Gate::denies('attribution_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.attributions.show', compact('attribution'));
    }

    public function destroy(Attribution $attribution)
    {
        abort_if(Gate::denies('attribution_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $attribution->delete();

        return back();
    }

    public function massDestroy(MassDestroyAttributionRequest $request)
    {
        $attributions = Attribution::find(request('ids'));

        foreach ($attributions as $attribution) {
            $attribution->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
