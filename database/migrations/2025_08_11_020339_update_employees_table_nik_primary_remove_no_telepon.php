<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah struktur employees: NIK menjadi primary key, NIP tetap required tapi tidak primary
     * GAPURA ANGKASA SDM System - Employee Structure Update
     */
    public function up(): void
    {
        // Backup data existing jika ada
        $existingEmployees = DB::table('employees')->get();
        
        // Drop tabel dan buat ulang dengan struktur baru
        Schema::dropIfExists('employees');
        
        Schema::create('employees', function (Blueprint $table) {
            // NIK sebagai primary key (string, bukan auto-increment)
            $table->string('nik', 20)->primary();
            
            // NIP tetap required dan unique, tapi bukan primary
            $table->string('nip', 20)->unique();
            
            // Data pribadi
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->integer('usia')->nullable();
            $table->string('kota_domisili')->nullable();
            $table->text('alamat')->nullable();
            
            // Data kontak - TANPA no_telepon (dihapus sesuai permintaan)
            $table->string('handphone')->nullable();
            $table->string('email')->nullable();
            
            // Data pekerjaan
            $table->string('lokasi_kerja')->nullable();
            $table->string('cabang')->nullable();
            $table->string('status_pegawai');
            $table->string('status_kerja')->default('Aktif');
            $table->string('provider')->nullable();
            $table->string('kode_organisasi')->nullable();
            $table->string('unit_organisasi')->nullable();
            $table->string('nama_organisasi')->nullable();
            $table->string('nama_jabatan')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('unit_kerja_kontrak')->nullable();
            $table->enum('kelompok_jabatan', [
                'SUPERVISOR', 
                'STAFF', 
                'MANAGER', 
                'EXECUTIVE GENERAL MANAGER', 
                'ACCOUNT EXECUTIVE/AE'
            ])->nullable();
            
            // Data tanggal pekerjaan
            $table->date('tmt_mulai_kerja')->nullable();
            $table->date('tmt_mulai_jabatan')->nullable();
            $table->date('tmt_berakhir_jabatan')->nullable();
            $table->date('tmt_berakhir_kerja')->nullable();
            $table->string('masa_kerja_bulan')->nullable();
            $table->string('masa_kerja_tahun')->nullable();
            $table->date('tmt_pensiun')->nullable();
            
            // Data pendidikan
            $table->string('pendidikan')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('instansi_pendidikan')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('remarks_pendidikan')->nullable();
            $table->integer('tahun_lulus')->nullable();
            
            // Data fisik dan tambahan
            $table->enum('jenis_sepatu', ['Pantofel', 'Safety Shoes'])->nullable();
            $table->string('ukuran_sepatu')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('height')->nullable();
            $table->string('kategori_karyawan')->nullable();
            $table->string('grade')->nullable();
            $table->string('kelas_jabatan')->nullable();
            
            // Data BPJS
            $table->string('no_bpjs_kesehatan')->nullable();
            $table->string('no_bpjs_ketenagakerjaan')->nullable();
            
            // Foreign keys dan status
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('set null');
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Timestamps
            $table->timestamps();

            // Indexes untuk performance (selain primary key NIK)
            $table->index(['status_pegawai', 'status']);
            $table->index(['unit_organisasi']);
            $table->index(['nama_lengkap']);
            $table->index(['tmt_mulai_jabatan']);
            $table->index(['nip']); // Index untuk NIP meskipun bukan primary
        });
        
        // Restore data existing jika ada (dengan mapping yang sesuai)
        if ($existingEmployees->isNotEmpty()) {
            foreach ($existingEmployees as $employee) {
                // Skip jika NIK kosong, karena sekarang NIK adalah primary key
                if (empty($employee->nik)) {
                    continue;
                }
                
                // Convert object to array dan hapus field yang tidak dibutuhkan
                $employeeData = (array) $employee;
                unset($employeeData['id']); // Hapus auto-increment id lama
                
                // Insert dengan NIK sebagai primary key
                try {
                    DB::table('employees')->insert($employeeData);
                } catch (\Exception $e) {
                    // Log error tapi lanjutkan dengan data lainnya
                    \Log::warning("Failed to migrate employee: " . $e->getMessage(), ['employee' => $employeeData]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup data current
        $currentEmployees = DB::table('employees')->get();
        
        // Drop dan buat ulang dengan struktur lama
        Schema::dropIfExists('employees');
        
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // Kembali ke auto-increment primary key
            $table->string('nip')->unique();
            $table->string('nik')->nullable(); // NIK kembali nullable
            $table->string('nama_lengkap');
            $table->string('lokasi_kerja')->nullable();
            $table->string('cabang')->nullable();
            $table->string('status_pegawai');
            $table->string('status_kerja')->default('Aktif');
            $table->string('provider')->nullable();
            $table->string('kode_organisasi')->nullable();
            $table->string('unit_organisasi')->nullable();
            $table->string('nama_organisasi')->nullable();
            $table->string('nama_jabatan')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('unit_kerja_kontrak')->nullable();
            $table->date('tmt_mulai_kerja')->nullable();
            $table->date('tmt_mulai_jabatan')->nullable();
            $table->date('tmt_berakhir_jabatan')->nullable();
            $table->date('tmt_berakhir_kerja')->nullable();
            $table->string('masa_kerja_bulan')->nullable();
            $table->string('masa_kerja_tahun')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->enum('jenis_sepatu', ['Pantofel', 'Safety Shoes'])->nullable();
            $table->string('ukuran_sepatu')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->integer('usia')->nullable();
            $table->string('kota_domisili')->nullable();
            $table->text('alamat')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('instansi_pendidikan')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('remarks_pendidikan')->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('handphone')->nullable();
            $table->string('no_telepon')->nullable(); // Restore no_telepon field
            $table->string('email')->nullable();
            $table->string('kategori_karyawan')->nullable();
            $table->date('tmt_pensiun')->nullable();
            $table->string('grade')->nullable();
            $table->string('no_bpjs_kesehatan')->nullable();
            $table->string('no_bpjs_ketenagakerjaan')->nullable();
            $table->string('kelompok_jabatan')->nullable();
            $table->string('kelas_jabatan')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('height')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('set null');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Restore indexes
            $table->index(['status_pegawai', 'status']);
            $table->index(['unit_organisasi']);
            $table->index(['nama_lengkap']);
            $table->index(['tmt_mulai_jabatan']);
        });
        
        // Restore data dengan auto-increment id
        if ($currentEmployees->isNotEmpty()) {
            foreach ($currentEmployees as $employee) {
                $employeeData = (array) $employee;
                DB::table('employees')->insert($employeeData);
            }
        }
    }
};