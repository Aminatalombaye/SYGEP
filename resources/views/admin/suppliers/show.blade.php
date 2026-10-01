@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'supplier', 'index' => 'admin.suppliers.index', 'record' => $supplier, 'edit' => ['route' => 'admin.suppliers.edit', 'can' => 'supplier_edit']])
<div class="card">
    <div class="card-header">
        Détails
    </div>

    <div class="card-body">
        <div class="form-group">
            <table class="table sy-details">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.supplier.fields.id') }}
                        </th>
                        <td>
                            {{ $supplier->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.supplier.fields.name') }}
                        </th>
                        <td>
                            {{ $supplier->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.supplier.fields.contact') }}
                        </th>
                        <td>
                            {{ $supplier->contact }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection