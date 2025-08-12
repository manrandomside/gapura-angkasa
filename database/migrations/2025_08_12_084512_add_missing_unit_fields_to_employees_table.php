<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FIXED: Menambahkan kolom unit_id dan sub_unit_id yang hilang
     * GAPURA ANGKASA SDM System - Emergency Migration Fix
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Cek apakah kolom unit_id sudah ada, jika belum tambahkan
            if (!Schema::hasColumn('employees', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('unit_organisasi');
                $table->index('unit_id');
            }
            
            // Cek apakah kolom sub_unit_id sudah ada, jika belum tambahkan
            if (!Schema::hasColumn('employees', 'sub_unit_id')) {
                $table->unsignedBigInteger('sub_unit_id')->nullable()->after('unit_id');
                $table->index('sub_unit_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'unit_id')) {
                $table->dropIndex(['unit_id']);
                $table->dropColumn('unit_id');
            }
            
            if (Schema::hasColumn('employees', 'sub_unit_id')) {
                $table->dropIndex(['sub_unit_id']);
                $table->dropColumn('sub_unit_id');
            }
        });
    }
};