<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('players', 'pitch_positions')) {
            Schema::table('players', function (Blueprint $table) {
                $table->json('pitch_positions')->nullable()->after('pitch_y');
            });
        }

        // Carry the single marker over so existing profiles keep their position.
        DB::table('players')
            ->whereNull('pitch_positions')
            ->whereNotNull('pitch_x')
            ->whereNotNull('pitch_y')
            ->orderBy('id')
            ->each(function ($player) {
                DB::table('players')->where('id', $player->id)->update([
                    'pitch_positions' => json_encode([
                        ['x' => (float) $player->pitch_x, 'y' => (float) $player->pitch_y],
                    ]),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('pitch_positions');
        });
    }
};
