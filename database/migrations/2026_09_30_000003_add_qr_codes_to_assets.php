<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $seen = [];

        DB::table('assets')->orderBy('id')->select('id', 'qr_code')->chunk(500, function ($assets) use (&$seen) {
            foreach ($assets as $asset) {
                $code = trim((string) $asset->qr_code);

                if ($code === '' || isset($seen[$code]) || strlen($code) > 60) {
                    $code = 'SYGEP-MAT-'.str_pad((string) $asset->id, 6, '0', STR_PAD_LEFT);
                    DB::table('assets')->where('id', $asset->id)->update(['qr_code' => $code]);
                }

                $seen[$code] = true;
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->string('qr_code', 60)->nullable()->change();
            $table->unique('qr_code');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropUnique(['qr_code']);
        });
    }
};
