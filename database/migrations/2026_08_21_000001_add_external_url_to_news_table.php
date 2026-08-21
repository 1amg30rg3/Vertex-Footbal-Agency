<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('news', 'external_url')) {
            return;
        }

        Schema::table('news', function (Blueprint $table) {
            $table->string('external_url', 2048)->nullable()->after('cover_path');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('external_url');
        });
    }
};
