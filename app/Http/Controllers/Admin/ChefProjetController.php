<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyChefProjetRequest;
use App\Http\Requests\StoreChefProjetRequest;
use App\Http\Requests\UpdateChefProjetRequest;
use App\Models\ChefProjet;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChefProjetController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('chef_projet_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $chefProjets = ChefProjet::all();

        return view('admin.chefProjets.index', compact('chefProjets'));
    }

    public function create()
    {
        abort_if(Gate::denies('chef_projet_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.chefProjets.create');
    }

    public function store(StoreChefProjetRequest $request)
    {
        $chefProjet = ChefProjet::create($request->all());

        return redirect()->route('admin.chef-projets.index');
    }

    public function edit(ChefProjet $chefProjet)
    {
        abort_if(Gate::denies('chef_projet_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.chefProjets.edit', compact('chefProjet'));
    }

    public function update(UpdateChefProjetRequest $request, ChefProjet $chefProjet)
    {
        $chefProjet->update($request->all());

        return redirect()->route('admin.chef-projets.index');
    }

    public function show(ChefProjet $chefProjet)
    {
        abort_if(Gate::denies('chef_projet_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.chefProjets.show', compact('chefProjet'));
    }

    public function destroy(ChefProjet $chefProjet)
    {
        abort_if(Gate::denies('chef_projet_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $chefProjet->delete();

        return back();
    }

    public function massDestroy(MassDestroyChefProjetRequest $request)
    {
        $chefProjets = ChefProjet::find(request('ids'));

        foreach ($chefProjets as $chefProjet) {
            $chefProjet->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
