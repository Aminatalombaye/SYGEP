<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyInventaireRequest;
use App\Http\Requests\StoreInventaireRequest;
use App\Http\Requests\UpdateInventaireRequest;
use App\Models\Inventaire;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InventaireController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('inventaire_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventaires = Inventaire::all();

        return view('admin.inventaires.index', compact('inventaires'));
    }

    public function create()
    {
        abort_if(Gate::denies('inventaire_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.inventaires.create');
    }

    public function store(StoreInventaireRequest $request)
    {
        $inventaire = Inventaire::create($request->all());

        return redirect()->route('admin.inventaires.index');
    }

    public function edit(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.inventaires.edit', compact('inventaire'));
    }

    public function update(UpdateInventaireRequest $request, Inventaire $inventaire)
    {
        $inventaire->update($request->all());

        return redirect()->route('admin.inventaires.index');
    }

    public function show(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.inventaires.show', compact('inventaire'));
    }

    public function destroy(Inventaire $inventaire)
    {
        abort_if(Gate::denies('inventaire_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventaire->delete();

        return back();
    }

    public function massDestroy(MassDestroyInventaireRequest $request)
    {
        $inventaires = Inventaire::find(request('ids'));

        foreach ($inventaires as $inventaire) {
            $inventaire->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
