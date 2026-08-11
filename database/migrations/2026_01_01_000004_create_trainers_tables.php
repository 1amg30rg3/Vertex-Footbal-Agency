<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('first_name');
            $table->json('last_name');
            $table->json('role')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->json('bio')->nullable();                // "მცირე აღწერა" — rich text
            $table->string('nationality')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'sort_order']);
        });

        Schema::create('trainer_work_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained()->cascadeOnDelete();
            $table->string('organization');
            $table->string('logo_path')->nullable();
            $table->json('title')->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->json('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['trainer_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_work_entries');
        Schema::dropIfExists('trainers');
    }
};
