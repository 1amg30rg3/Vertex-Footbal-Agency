<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('editor')->after('password');
            $table->string('theme')->default('dark')->after('role');
            $table->string('avatar_path')->nullable()->after('theme');
            $table->timestamp('last_login_at')->nullable()->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'theme', 'avatar_path', 'last_login_at']);
        });
    }
};
