<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Sistem Kerja" digabung ke dalam "Tipe Pekerjaan":
     * pekerjaan remote disimpan sebagai employment_type = 'remote'.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('experiences', 'work_mode')) {
            return;
        }

        DB::table('experiences')
            ->where('work_mode', 'remote')
            ->update(['employment_type' => 'remote']);

        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn('work_mode');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('experiences', 'work_mode')) {
            return;
        }

        Schema::table('experiences', function (Blueprint $table) {
            $table->string('work_mode')->nullable()->after('employment_type');
        });
    }
};
