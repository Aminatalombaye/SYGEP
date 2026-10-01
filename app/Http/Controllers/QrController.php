<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Support\Facades\Gate;

class QrController extends Controller
{
    public function scan(string $code)
    {
        $asset = Asset::with('category')->where('qr_code', $code)->first();

        if (! $asset) {
            return response()->view('qr.unknown', ['code' => $code], 404);
        }

        if (auth()->check() && Gate::allows('asset_show')) {
            return redirect()->route('admin.assets.show', $asset);
        }

        return view('qr.public', ['asset' => $asset]);
    }
}
