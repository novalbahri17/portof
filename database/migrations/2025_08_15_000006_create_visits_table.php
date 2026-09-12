<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('ip', 45);
            $table->string('country', 100)->nullable();
            $table->string('country_code', 5)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('device')->default('desktop'); // desktop, mobile, tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('referrer')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('visited_at');
            $table->index(['visited_at']);
            $table->index(['path']);
            $table->index(['country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
