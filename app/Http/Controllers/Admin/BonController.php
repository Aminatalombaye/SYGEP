<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyBonRequest;
use App\Http\Requests\StoreBonRequest;
use App\Http\Requests\UpdateBonRequest;
use App\Models\Bon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BonController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('bon_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bons = Bon::all();

        return view('admin.bons.index', compact('bons'));
    }

    public function create()
    {
        abort_if(Gate::denies('bon_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.bons.create');
    }

    public function store(StoreBonRequest $request)
    {
        $bon = Bon::create($request->all());

        return redirect()->route('admin.bons.index');
    }

    public function edit(Bon $bon)
    {
        abort_if(Gate::denies('bon_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.bons.edit', compact('bon'));
    }

    public function update(UpdateBonRequest $request, Bon $bon)
    {
        $bon->update($request->all());

        return redirect()->route('admin.bons.index');
    }

    public function show(Bon $bon)
    {
        abort_if(Gate::denies('bon_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.bons.show', compact('bon'));
    }

    public function destroy(Bon $bon)
    {
        abort_if(Gate::denies('bon_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $bon->delete();

        return back();
    }

    public function massDestroy(MassDestroyBonRequest $request)
    {
        $bons = Bon::find(request('ids'));

        foreach ($bons as $bon) {
            $bon->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
