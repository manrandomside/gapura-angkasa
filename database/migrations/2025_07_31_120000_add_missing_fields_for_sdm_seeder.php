<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing fields for SDMEmployeeSeeder compatibility
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add missing fields that exist in SDMEmployeeSeeder but not in current migration
            if (!Schema::hasColumn('employees', 'no')) {
                $table->integer('no')->nullable()->after('id')->comment('Nomor urut dari CSV');
            }
            
            if (!Schema::hasColumn('employees', 'nik')) {
                $table->string('nik')->nullable()->after('nip')->comment('NIK karyawan');
            }
            
            if (!Schema::hasColumn('employees', 'nama_organisasi')) {
                $table->string('nama_organisasi')->nullable()->after('unit_organisasi')->comment('Nama lengkap organisasi');
            }
            
            if (!Schema::hasColumn('employees', 'kode_organisasi')) {
                $table->string('kode_organisasi')->nullable()->after('provider')->comment('Kode organisasi');
            }
            
            if (!Schema::hasColumn('employees', 'unit_kerja_kontrak')) {
                $table->string('unit_kerja_kontrak')->nullable()->after('nama_jabatan')->comment('Unit kerja kontrak');
            }
            
            if (!Schema::hasColumn('employees', 'tmt_berakhir_jabatan')) {
                $table->date('tmt_berakhir_jabatan')->nullable()->after('tmt_mulai_jabatan')->comment('TMT berakhir jabatan');
            }
            
            if (!Schema::hasColumn('employees', 'tmt_berakhir_kerja')) {
                $table->date('tmt_berakhir_kerja')->nullable()->after('tmt_berakhir_jabatan')->comment('TMT berakhir kerja');
            }
            
            if (!Schema::hasColumn('employees', 'masa_kerja_bulan')) {
                $table->string('masa_kerja_bulan')->nullable()->after('tmt_berakhir_kerja')->comment('Masa kerja dalam bulan');
            }
            
            if (!Schema::hasColumn('employees', 'masa_kerja_tahun')) {
                $table->string('masa_kerja_tahun')->nullable()->after('masa_kerja_bulan')->comment('Masa kerja dalam tahun');
            }
            
            if (!Schema::hasColumn('employees', 'remarks_pendidikan')) {
                $table->string('remarks_pendidikan')->nullable()->after('jurusan')->comment('Keterangan pendidikan');
            }
            
            if (!Schema::hasColumn('employees', 'kategori_karyawan')) {
                $table->string('kategori_karyawan')->nullable()->after('handphone')->comment('Kategori karyawan');
            }
            
            if (!Schema::hasColumn('employees', 'tmt_pensiun')) {
                $table->date('tmt_pensiun')->nullable()->after('tmt_berakhir_kerja')->comment('TMT pensiun');
            }
            
            if (!Schema::hasColumn('employees', 'grade')) {
                $table->string('grade')->nullable()->after('kategori_karyawan')->comment('Grade karyawan');
            }
            
            if (!Schema::hasColumn('employees', 'kelompok_jabatan')) {
                $table->string('kelompok_jabatan')->nullable()->after('no_bpjs_ketenagakerjaan')->comment('Kelompok jabatan');
            }
            
            if (!Schema::hasColumn('employees', 'kelas_jabatan')) {
                $table->string('kelas_jabatan')->nullable()->after('kelompok_jabatan')->comment('Kelas jabatan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $columns = [
                'no', 'nik', 'nama_organisasi', 'kode_organisasi', 'unit_kerja_kontrak',
                'tmt_berakhir_jabatan', 'tmt_berakhir_kerja', 'masa_kerja_bulan', 
                'masa_kerja_tahun', 'remarks_pendidikan', 'kategori_karyawan', 
                'tmt_pensiun', 'grade', 'kelompok_jabatan', 'kelas_jabatan'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};