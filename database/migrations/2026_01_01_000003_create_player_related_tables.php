<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->json('label');
            $table->unsignedTinyInteger('value')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['player_id', 'sort_order']);
        });

        Schema::create('player_career_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('club_name');
            $table->string('club_logo_path')->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->string('category')->nullable();
            $table->json('league')->nullable();
            $table->json('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['player_id', 'sort_order']);
        });

        Schema::create('player_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->json('text');
            $table->string('year')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['player_id', 'sort_order']);
        });

        Schema::create('player_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('label');                        // "2024/2025"
            $table->string('club_name')->nullable();
            $table->unsignedSmallInteger('matches_played')->default(0);
            $table->unsignedSmallInteger('goals')->default(0);
            $table->unsignedSmallInteger('assists')->default(0);
            $table->unsignedInteger('minutes_played')->default(0);
            $table->unsignedTinyInteger('starting_pct')->default(0);
            $table->unsignedTinyInteger('substitute_pct')->default(0);
            $table->unsignedTinyInteger('not_in_squad_pct')->default(0);
            $table->boolean('is_current')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['player_id', 'sort_order']);
        });

        Schema::create('player_season_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_season_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('goals')->default(0);
            $table->unsignedSmallInteger('assists')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['player_season_id', 'sort_order']);
        });

        Schema::create('player_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->json('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['player_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_photos');
        Schema::dropIfExists('player_season_months');
        Schema::dropIfExists('player_seasons');
        Schema::dropIfExists('player_achievements');
        Schema::dropIfExists('player_career_entries');
        Schema::dropIfExists('player_skills');
    }
};
