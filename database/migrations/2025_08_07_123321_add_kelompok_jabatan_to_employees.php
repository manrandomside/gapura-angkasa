<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan field kelompok_jabatan untuk GAPURA ANGKASA SDM System
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Cek apakah kolom kelompok_jabatan sudah ada
            if (!Schema::hasColumn('employees', 'kelompok_jabatan')) {
                // Jika belum ada, tambahkan sebagai enum
                $table->enum('kelompok_jabatan', [
                    'SUPERVISOR', 
                    'STAFF', 
                    'MANAGER', 
                    'EXECUTIVE GENERAL MANAGER', 
                    'ACCOUNT EXECUTIVE/AE'
                ])->nullable()->after('status_pegawai');
            }

            // Update status_pegawai untuk menambahkan TAD Split
            if (!Schema::hasColumn('employees', 'status_pegawai_new')) {
                $table->enum('status_pegawai_new', [
                    'PEGAWAI TETAP',
                    'PKWT', 
                    'TAD PAKET SDM',
                    'TAD PAKET PEKERJAAN'
                ])->after('status_pegawai');
            }
        });

        // Copy data dari status_pegawai ke status_pegawai_new jika kolom baru dibuat
        if (Schema::hasColumn('employees', 'status_pegawai_new')) {
            DB::statement("UPDATE employees SET status_pegawai_new = CASE 
                WHEN status_pegawai = 'TAD' THEN 'TAD PAKET SDM'
                ELSE status_pegawai 
            END");

            Schema::table('employees', function (Blueprint $table) {
                // Drop kolom lama dan rename yang baru
                $table->dropColumn('status_pegawai');
            });

            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('status_pegawai_new', 'status_pegawai');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop kelompok_jabatan jika ada
            if (Schema::hasColumn('employees', 'kelompok_jabatan')) {
                $table->dropColumn('kelompok_jabatan');
            }

            // Revert status_pegawai back to original enum
            if (!Schema::hasColumn('employees', 'status_pegawai_old')) {
                $table->enum('status_pegawai_old', [
                    'PEGAWAI TETAP',
                    'PKWT', 
                    'TAD'
                ])->after('status_pegawai');
            }
        });

        // Convert TAD split back to single TAD jika diperlukan
        if (Schema::hasColumn('employees', 'status_pegawai_old')) {
            DB::statement("UPDATE employees SET status_pegawai_old = CASE 
                WHEN status_pegawai IN ('TAD PAKET SDM', 'TAD PAKET PEKERJAAN') THEN 'TAD'
                ELSE status_pegawai 
            END");

            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('status_pegawai');
            });

            Schema::table('employees', function (Blueprint $table) {
                $table->renameColumn('status_pegawai_old', 'status_pegawai');
            });
        }
    }
};