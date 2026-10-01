<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class QrController extends Controller
{
    public function scanner()
    {
        abort_if(Gate::denies('asset_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.qr.scanner');
    }

    public function lookup(Request $request)
    {
        abort_if(Gate::denies('asset_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $value = trim((string) $request->input('code'));

        if (preg_match('#/q/([^/?\s]+)#', $value, $m)) {
            $value = urldecode($m[1]);
        }

        $asset = Asset::where('qr_code', $value)
            ->orWhere('serial_number', $value)
            ->first();

        if (! $asset) {
            return back()->withInput()->with('qr_error', 'Aucune matière ne correspond à « '.$value.' ».');
        }

        return redirect()->route('admin.assets.show', $asset);
    }

    public function svg(Request $request, Asset $asset)
    {
        abort_if(Gate::denies('asset_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $svg = (string) $asset->qrSvg((int) $request->query('taille', 480));

        $response = response($svg, 200, ['Content-Type' => 'image/svg+xml']);

        if ($request->boolean('telecharger')) {
            $response->header('Content-Disposition', 'attachment; filename="'.$asset->qr_code.'.svg"');
        }

        return $response;
    }

    public function label(Asset $asset)
    {
        abort_if(Gate::denies('asset_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $asset->load('category');

        return view('admin.qr.label', compact('asset'));
    }
}
