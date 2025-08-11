<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan field no_telepon sementara untuk seeder compatibility
     * Field ini bisa dihapus setelah seeder berhasil dijalankan
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'no_telepon')) {
                $table->string('no_telepon')->nullable()->after('handphone')->comment('TEMPORARY: For seeder compatibility - can be removed after seeding');
            }
        });
    }

    /**
     * Menghapus field no_telepon
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'no_telepon')) {
                $table->dropColumn('no_telepon');
            }
        });
    }
};