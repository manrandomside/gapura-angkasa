<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom units_structure ke tabel organizations untuk menyimpan hierarki unit organisasi
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->json('units_structure')->nullable()->after('status')->comment('Struktur hierarki Unit -> Sub Unit dalam format JSON');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('units_structure');
        });
    }
};