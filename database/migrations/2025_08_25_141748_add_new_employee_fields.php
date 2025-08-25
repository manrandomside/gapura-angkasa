<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add new employee fields as requested
     * File: database/migrations/2025_08_25_120000_add_new_employee_fields.php
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // NEW FIELD: Provider - dropdown dengan pilihan perusahaan
            if (!Schema::hasColumn('employees', 'provider')) {
                $table->string('provider')->nullable()->after('status_kerja')->comment('Provider perusahaan');
            }
            
            // NEW FIELD: Unit Kerja Sesuai Kontrak - manual input
            if (!Schema::hasColumn('employees', 'unit_kerja_kontrak')) {
                $table->string('unit_kerja_kontrak')->nullable()->after('nama_jabatan')->comment('Unit kerja sesuai kontrak');
            }
            
            // NEW FIELD: Grade - manual input
            if (!Schema::hasColumn('employees', 'grade')) {
                $table->string('grade')->nullable()->after('kelompok_jabatan')->comment('Grade karyawan');
            }
            
            // NEW FIELD: TMT Akhir Jabatan - date field dengan validasi harus setelah TMT Mulai Jabatan
            if (!Schema::hasColumn('employees', 'tmt_akhir_jabatan')) {
                $table->date('tmt_akhir_jabatan')->nullable()->after('tmt_mulai_jabatan')->comment('TMT akhir jabatan');
            }
            
            // NEW FIELD: TMT Berakhir Kerja - date field dengan validasi harus setelah TMT Mulai Kerja
            if (!Schema::hasColumn('employees', 'tmt_berakhir_kerja')) {
                $table->date('tmt_berakhir_kerja')->nullable()->after('tmt_akhir_jabatan')->comment('TMT berakhir kerja');
            }
            
            // NEW FIELD: Status Kerja - otomatis berdasarkan TMT Berakhir Kerja
            if (!Schema::hasColumn('employees', 'status_kerja')) {
                $table->enum('status_kerja', ['Aktif', 'Non-Aktif', 'Pensiun', 'Mutasi'])
                      ->default('Non-Aktif')
                      ->after('status_pegawai')
                      ->comment('Status kerja otomatis berdasarkan TMT berakhir kerja');
            }
            
            // NEW FIELD: Masa Kerja - calculated field, read only
            if (!Schema::hasColumn('employees', 'masa_kerja')) {
                $table->string('masa_kerja')->nullable()->after('masa_kerja_tahun')->comment('Masa kerja calculated field');
            }
            
            // NEW FIELD: Lokasi Kerja - fixed value "Bandar Udara Ngurah Rai"
            if (!Schema::hasColumn('employees', 'lokasi_kerja')) {
                $table->string('lokasi_kerja')->default('Bandar Udara Ngurah Rai')->after('nama_lengkap')->comment('Lokasi kerja fixed value');
            } else {
                // Update existing records yang belum ada nilai lokasi_kerja
                \DB::table('employees')->whereNull('lokasi_kerja')->update(['lokasi_kerja' => 'Bandar Udara Ngurah Rai']);
            }
            
            // NEW FIELD: Cabang - fixed value "DPS"
            if (!Schema::hasColumn('employees', 'cabang')) {
                $table->string('cabang')->default('DPS')->after('lokasi_kerja')->comment('Cabang fixed value');
            } else {
                // Update existing records yang belum ada nilai cabang
                \DB::table('employees')->whereNull('cabang')->update(['cabang' => 'DPS']);
            }

            // Add index untuk performance pada field yang sering diquery
            if (!Schema::hasIndex('employees', ['status_kerja'])) {
                $table->index('status_kerja');
            }
            
            if (!Schema::hasIndex('employees', ['provider'])) {
                $table->index('provider');
            }
            
            if (!Schema::hasIndex('employees', ['grade'])) {
                $table->index('grade');
            }
            
            if (!Schema::hasIndex('employees', ['tmt_berakhir_kerja'])) {
                $table->index('tmt_berakhir_kerja');
            }
        });

        // Update data yang sudah ada untuk field baru
        $this->updateExistingData();
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop new columns (hati-hati dengan data)
            if (Schema::hasColumn('employees', 'provider')) {
                $table->dropColumn('provider');
            }
            
            if (Schema::hasColumn('employees', 'unit_kerja_kontrak')) {
                $table->dropColumn('unit_kerja_kontrak');
            }
            
            if (Schema::hasColumn('employees', 'grade')) {
                $table->dropColumn('grade');
            }
            
            if (Schema::hasColumn('employees', 'tmt_akhir_jabatan')) {
                $table->dropColumn('tmt_akhir_jabatan');
            }
            
            if (Schema::hasColumn('employees', 'tmt_berakhir_kerja')) {
                $table->dropColumn('tmt_berakhir_kerja');
            }
            
            if (Schema::hasColumn('employees', 'status_kerja')) {
                $table->dropColumn('status_kerja');
            }
            
            if (Schema::hasColumn('employees', 'masa_kerja')) {
                $table->dropColumn('masa_kerja');
            }

            // Drop indexes
            if (Schema::hasIndex('employees', ['status_kerja'])) {
                $table->dropIndex(['status_kerja']);
            }
            
            if (Schema::hasIndex('employees', ['provider'])) {
                $table->dropIndex(['provider']);
            }
            
            if (Schema::hasIndex('employees', ['grade'])) {
                $table->dropIndex(['grade']);
            }
            
            if (Schema::hasIndex('employees', ['tmt_berakhir_kerja'])) {
                $table->dropIndex(['tmt_berakhir_kerja']);
            }
        });
    }

    /**
     * Update existing employee data dengan field baru
     */
    private function updateExistingData()
    {
        try {
            // Update semua employee yang belum ada lokasi_kerja dan cabang
            \DB::table('employees')
                ->whereNull('lokasi_kerja')
                ->orWhereNull('cabang')
                ->update([
                    'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                    'cabang' => 'DPS',
                    'updated_at' => now()
                ]);

            // Update status_kerja untuk existing employees berdasarkan tmt_berakhir_kerja
            $employees = \DB::table('employees')
                ->whereNotNull('tmt_berakhir_kerja')
                ->get();

            foreach ($employees as $employee) {
                $today = \Carbon\Carbon::now('Asia/Makassar');
                $endDate = \Carbon\Carbon::parse($employee->tmt_berakhir_kerja);
                
                $statusKerja = $today->lte($endDate) ? 'Aktif' : 'Non-Aktif';
                
                \DB::table('employees')
                    ->where('id', $employee->id)
                    ->update([
                        'status_kerja' => $statusKerja,
                        'updated_at' => now()
                    ]);
            }

            // Calculate masa_kerja untuk existing employees
            $employeesWithTmt = \DB::table('employees')
                ->whereNotNull('tmt_mulai_kerja')
                ->get();

            foreach ($employeesWithTmt as $employee) {
                $startDate = \Carbon\Carbon::parse($employee->tmt_mulai_kerja);
                
                // Gunakan tmt_berakhir_kerja jika ada, atau hari ini jika belum berakhir
                $endDate = $employee->tmt_berakhir_kerja 
                           ? \Carbon\Carbon::parse($employee->tmt_berakhir_kerja) 
                           : \Carbon\Carbon::now('Asia/Makassar');
                
                $years = $endDate->diffInYears($startDate);
                $months = $endDate->diffInMonths($startDate) % 12;

                // Adjust untuk tanggal yang belum lewat di bulan ini
                if ($endDate->day < $startDate->day) {
                    $months--;
                    if ($months < 0) {
                        $years--;
                        $months += 12;
                    }
                }

                $masaKerja = '';
                if ($years > 0 && $months > 0) {
                    $masaKerja = "{$years} tahun {$months} bulan";
                } elseif ($years > 0) {
                    $masaKerja = "{$years} tahun";
                } elseif ($months > 0) {
                    $masaKerja = "{$months} bulan";
                } else {
                    $masaKerja = "Kurang dari 1 bulan";
                }
                
                \DB::table('employees')
                    ->where('id', $employee->id)
                    ->update([
                        'masa_kerja' => $masaKerja,
                        'updated_at' => now()
                    ]);
            }

            \Log::info('Successfully updated existing employee data with new fields');

        } catch (\Exception $e) {
            \Log::error('Error updating existing employee data: ' . $e->getMessage());
            throw $e;
        }
    }
};