<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add new fields for enhanced employee data management
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Status Kerja - will be auto-calculated based on tmt_berakhir_kerja
            if (!Schema::hasColumn('employees', 'status_kerja')) {
                $table->enum('status_kerja', ['Aktif', 'Non-Aktif', 'Pensiun', 'Mutasi'])
                      ->default('Non-Aktif')
                      ->after('status_pegawai')
                      ->comment('Status kerja karyawan - otomatis berdasarkan TMT berakhir kerja');
            }
            
            // TMT Akhir Jabatan - must be after tmt_mulai_jabatan
            if (!Schema::hasColumn('employees', 'tmt_akhir_jabatan')) {
                $table->date('tmt_akhir_jabatan')
                      ->nullable()
                      ->after('tmt_berakhir_jabatan')
                      ->comment('TMT Akhir Jabatan - harus diatas TMT Mulai Jabatan');
            }
            
            // Provider - dropdown selection
            if (!Schema::hasColumn('employees', 'provider')) {
                $table->enum('provider', [
                    'PT Gapura Angkasa',
                    'PT Air Box Personalia', 
                    'PT Finfleet Teknologi Indonesia',
                    'PT Mitra Angkasa Perdana',
                    'PT Safari Dharma Sakti',
                    'PT Grha Humanindo Management',
                    'PT Duta Griya Sarana',
                    'PT Aerotrans Wisata',
                    'PT Mandala Garda Nusantara',
                    'PT Kidora Mandiri Investama'
                ])->nullable()
                  ->after('kelompok_jabatan')
                  ->comment('Provider perusahaan');
            }
            
            // Unit Kerja Sesuai Kontrak - manual input
            if (!Schema::hasColumn('employees', 'unit_kerja_kontrak')) {
                $table->string('unit_kerja_kontrak')
                      ->nullable()
                      ->after('provider')
                      ->comment('Unit kerja sesuai kontrak - manual input');
            }
            
            // Grade - manual input
            if (!Schema::hasColumn('employees', 'grade')) {
                $table->string('grade')
                      ->nullable()
                      ->after('unit_kerja_kontrak')
                      ->comment('Grade karyawan - manual input');
            }
            
            // Lokasi Kerja - fixed value
            if (!Schema::hasColumn('employees', 'lokasi_kerja')) {
                $table->string('lokasi_kerja')
                      ->default('Bandar Udara Ngurah Rai')
                      ->after('grade')
                      ->comment('Lokasi kerja - fixed value');
            }
            
            // Cabang - fixed value  
            if (!Schema::hasColumn('employees', 'cabang')) {
                $table->string('cabang')
                      ->default('DPS')
                      ->after('lokasi_kerja')
                      ->comment('Cabang - fixed value');
            }
            
            // Masa Kerja - calculated field (will be computed automatically)
            if (!Schema::hasColumn('employees', 'masa_kerja')) {
                $table->string('masa_kerja')
                      ->nullable()
                      ->after('cabang')
                      ->comment('Masa kerja - calculated automatically based on tmt_mulai_kerja');
            }

            // Add indexes for performance
            $table->index(['status_kerja']);
            $table->index(['provider']);
            $table->index(['tmt_berakhir_kerja']);
            $table->index(['tmt_akhir_jabatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $columns = [
                'status_kerja',
                'tmt_akhir_jabatan', 
                'provider',
                'unit_kerja_kontrak',
                'grade',
                'lokasi_kerja',
                'cabang',
                'masa_kerja'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};