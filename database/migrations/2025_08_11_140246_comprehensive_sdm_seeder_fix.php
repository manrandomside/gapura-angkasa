<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * COMPREHENSIVE FIX: Memastikan semua field yang dibutuhkan SDMEmployeeSeeder ada
     */
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                
                // Field yang wajib ada berdasarkan error SQL
                if (!Schema::hasColumn('employees', 'no')) {
                    $table->integer('no')->nullable()->after('id')->comment('Nomor urut dari CSV');
                }
                
                if (!Schema::hasColumn('employees', 'seragam')) {
                    $table->string('seragam', 50)->nullable()->after('ukuran_sepatu')->comment('Jenis seragam karyawan');
                }
                
                if (!Schema::hasColumn('employees', 'no_telepon')) {
                    $table->string('no_telepon')->nullable()->after('handphone')->comment('TEMPORARY: For seeder compatibility');
                }
                
                if (!Schema::hasColumn('employees', 'kategori_karyawan')) {
                    $table->string('kategori_karyawan')->nullable()->after('email')->comment('Kategori karyawan');
                }
                
                if (!Schema::hasColumn('employees', 'tmt_pensiun')) {
                    $table->date('tmt_pensiun')->nullable()->after('kategori_karyawan')->comment('TMT pensiun');
                }
                
                if (!Schema::hasColumn('employees', 'grade')) {
                    $table->string('grade', 50)->nullable()->after('tmt_pensiun')->comment('Grade karyawan');
                }
                
                if (!Schema::hasColumn('employees', 'no_bpjs_kesehatan')) {
                    $table->string('no_bpjs_kesehatan')->nullable()->after('grade')->comment('Nomor BPJS Kesehatan');
                }
                
                if (!Schema::hasColumn('employees', 'no_bpjs_ketenagakerjaan')) {
                    $table->string('no_bpjs_ketenagakerjaan')->nullable()->after('no_bpjs_kesehatan')->comment('Nomor BPJS Ketenagakerjaan');
                }
                
                if (!Schema::hasColumn('employees', 'kelompok_jabatan')) {
                    $table->string('kelompok_jabatan', 100)->nullable()->after('no_bpjs_ketenagakerjaan')->comment('Kelompok jabatan');
                }
                
                if (!Schema::hasColumn('employees', 'kelas_jabatan')) {
                    $table->string('kelas_jabatan', 100)->nullable()->after('kelompok_jabatan')->comment('Kelas jabatan');
                }
                
                if (!Schema::hasColumn('employees', 'weight')) {
                    $table->integer('weight')->nullable()->after('kelas_jabatan')->comment('Berat badan');
                }
                
                if (!Schema::hasColumn('employees', 'height')) {
                    $table->integer('height')->nullable()->after('weight')->comment('Tinggi badan');
                }
                
                if (!Schema::hasColumn('employees', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('height')->comment('ID Organisasi');
                }
                
                if (!Schema::hasColumn('employees', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active')->after('organization_id')->comment('Status karyawan');
                }

                // Pastikan field yang sudah ada memiliki ukuran yang cukup
                if (Schema::hasColumn('employees', 'jenis_sepatu')) {
                    $table->string('jenis_sepatu', 50)->nullable()->change();
                }
                
                if (Schema::hasColumn('employees', 'nama_organisasi')) {
                    $table->string('nama_organisasi', 200)->nullable()->change();
                }
                
                if (Schema::hasColumn('employees', 'kode_organisasi')) {
                    $table->string('kode_organisasi', 20)->nullable()->change();
                }
                
                if (Schema::hasColumn('employees', 'unit_kerja_kontrak')) {
                    $table->string('unit_kerja_kontrak', 200)->nullable()->change();
                }
                
                if (Schema::hasColumn('employees', 'masa_kerja_bulan')) {
                    $table->string('masa_kerja_bulan', 50)->nullable()->change();
                }
                
                if (Schema::hasColumn('employees', 'masa_kerja_tahun')) {
                    $table->string('masa_kerja_tahun', 50)->nullable()->change();
                }
                
                if (Schema::hasColumn('employees', 'pendidikan_terakhir')) {
                    $table->string('pendidikan_terakhir', 150)->nullable()->change();
                }
                
                if (Schema::hasColumn('employees', 'remarks_pendidikan')) {
                    $table->string('remarks_pendidikan', 500)->nullable()->change();
                }

                // Pastikan timestamps ada
                if (!Schema::hasColumn('employees', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $columnsToRemove = [
                    'no', 'seragam', 'no_telepon', 'kategori_karyawan', 'tmt_pensiun', 
                    'grade', 'no_bpjs_kesehatan', 'no_bpjs_ketenagakerjaan', 
                    'kelompok_jabatan', 'kelas_jabatan', 'weight', 'height', 
                    'organization_id', 'status'
                ];

                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('employees', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};