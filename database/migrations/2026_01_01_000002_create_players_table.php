<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->json('first_name');
            $table->json('last_name');
            $table->string('photo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->string('position')->nullable();
            $table->json('specific_position')->nullable(); // e.g. "Central Midfielder"
            $table->string('preferred_foot')->nullable();
            $table->string('current_club')->nullable();
            $table->string('current_club_logo_path')->nullable();
            $table->date('contract_until')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            $table->json('playing_style')->nullable();
            $table->decimal('pitch_x', 5, 2)->nullable();
            $table->decimal('pitch_y', 5, 2)->nullable();

            $table->json('goals_short_term')->nullable();
            $table->json('goals_mid_term')->nullable();
            $table->json('goals_long_term')->nullable();
            $table->json('quote')->nullable();

            $table->string('status')->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
