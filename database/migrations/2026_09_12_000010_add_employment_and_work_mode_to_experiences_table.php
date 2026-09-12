<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            // Jenis pekerjaan: full_time, part_time, contract, internship, freelance, part_time_internship
            $table->string('employment_type')->nullable()->after('type');
            // Sistem kerja: on_site, remote, hybrid
            $table->string('work_mode')->nullable()->after('employment_type');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['employment_type', 'work_mode']);
        });
    }
};
