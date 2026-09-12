<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('challenge')->nullable()->after('description');
            $table->text('solution')->nullable()->after('challenge');
            $table->text('results')->nullable()->after('solution');
            $table->json('features')->nullable()->after('tags');
            $table->json('gallery')->nullable()->after('features');
            $table->json('technologies')->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'challenge',
                'solution',
                'results',
                'features',
                'gallery',
                'technologies',
            ]);
        });
    }
};
