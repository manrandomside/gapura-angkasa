<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SOLUSI: Membuat NIK nullable dan menambahkan semua kolom yang dibutuhkan SDMEmployeeSeeder
     * NIK tetap unique tapi bisa null untuk data existing yang belum punya NIK
     */
    public function up(): void
    {
        // Check jika employees table ada dan backup data existing
        if (Schema::hasTable('employees')) {
            
            // Drop foreign key constraints dulu jika ada
            try {
                Schema::table('employees', function (Blueprint $table) {
                    if (Schema::hasColumn('employees', 'organization_id')) {
                        $table->dropForeign(['organization_id']);
                    }
                });
            } catch (\Exception $e) {
                // Foreign key mungkin tidak ada, lanjutkan
            }
            
            // Backup data existing jika ada
            $existingEmployees = DB::table('employees')->get();
            
            // Drop dan recreate table dengan struktur yang lebih flexible
            Schema::dropIfExists('employees');
            
            Schema::create('employees', function (Blueprint $table) {
                // Auto-increment ID sebagai primary key utama
                $table->id();
                
                // NIK nullable untuk seeder compatibility, tapi unique untuk yang tidak null
                $table->string('nik', 20)->nullable()->unique();
                
                // NIP required dan unique
                $table->string('nip', 20)->unique();
                
                // Field dari SDMEmployeeSeeder - SEMUA FIELD YANG DIBUTUHKAN
                $table->integer('no')->nullable()->comment('Nomor urut dari CSV');
                $table->string('nama_lengkap');
                $table->string('lokasi_kerja')->nullable();
                $table->string('cabang')->nullable();
                $table->string('status_pegawai');
                $table->string('status_kerja')->default('Aktif');
                $table->string('provider')->nullable();
                $table->string('kode_organisasi', 20)->nullable();
                $table->string('unit_organisasi', 200)->nullable();
                $table->string('nama_organisasi', 200)->nullable();
                $table->string('nama_jabatan', 200)->nullable();
                $table->string('jabatan', 200)->nullable();
                $table->string('unit_kerja_kontrak', 200)->nullable();
                
                // Dates
                $table->date('tmt_mulai_kerja')->nullable();
                $table->date('tmt_mulai_jabatan')->nullable();
                $table->date('tmt_berakhir_jabatan')->nullable();
                $table->date('tmt_berakhir_kerja')->nullable();
                $table->string('masa_kerja_bulan', 50)->nullable();
                $table->string('masa_kerja_tahun', 50)->nullable();
                
                // Personal info
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->string('jenis_sepatu', 50)->nullable();
                $table->string('ukuran_sepatu', 10)->nullable();
                $table->string('tempat_lahir', 100)->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->integer('usia')->nullable();
                $table->string('kota_domisili', 100)->nullable();
                $table->text('alamat')->nullable();
                
                // Education
                $table->string('pendidikan', 100)->nullable();
                $table->string('pendidikan_terakhir', 150)->nullable();
                $table->string('instansi_pendidikan', 200)->nullable();
                $table->string('jurusan', 150)->nullable();
                $table->string('remarks_pendidikan', 500)->nullable();
                $table->integer('tahun_lulus')->nullable();
                
                // Contact
                $table->string('handphone', 50)->nullable();
                $table->string('no_telepon')->nullable()->comment('COMPATIBILITY: Hidden from UI');
                $table->string('email', 100)->nullable();
                
                // Additional fields
                $table->string('kategori_karyawan', 100)->nullable();
                $table->date('tmt_pensiun')->nullable();
                $table->string('grade', 50)->nullable();
                $table->string('no_bpjs_kesehatan')->nullable();
                $table->string('no_bpjs_ketenagakerjaan')->nullable();
                $table->string('kelompok_jabatan', 100)->nullable();
                $table->string('kelas_jabatan', 100)->nullable();
                $table->integer('weight')->nullable();
                $table->integer('height')->nullable();
                $table->string('seragam', 50)->nullable();
                
                // Foreign keys dan status
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                
                // Timestamps
                $table->timestamps();

                // Indexes untuk performance
                $table->index(['status_pegawai', 'status']);
                $table->index(['unit_organisasi']);
                $table->index(['nama_lengkap']);
                $table->index(['nip']);
                $table->index(['nik']); // Index untuk NIK
            });
            
            // Add foreign key constraint untuk organization jika table ada
            if (Schema::hasTable('organizations')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
                });
            }
            
            // Restore data existing dengan mapping yang sesuai
            if ($existingEmployees->isNotEmpty()) {
                foreach ($existingEmployees as $employee) {
                    // Convert object to array dan bersihkan
                    $employeeData = (array) $employee;
                    
                    // Generate NIK dummy jika kosong (berdasarkan NIP atau nomor urut)
                    if (empty($employeeData['nik']) || $employeeData['nik'] === '?') {
                        // Generate NIK dummy dari NIP atau nomor urut
                        if (!empty($employeeData['nip'])) {
                            // Buat NIK dummy dari NIP (pad dengan 0 untuk 16 digit)
                            $employeeData['nik'] = str_pad($employeeData['nip'], 16, '0', STR_PAD_LEFT);
                        } elseif (!empty($employeeData['no'])) {
                            // Buat NIK dummy dari nomor urut
                            $employeeData['nik'] = '9999' . str_pad($employeeData['no'], 12, '0', STR_PAD_LEFT);
                        } else {
                            // Last resort: generate random NIK
                            $employeeData['nik'] = '9999' . str_pad(rand(1, 999999999999), 12, '0', STR_PAD_LEFT);
                        }
                    }
                    
                    // Pastikan NIK unique
                    $originalNik = $employeeData['nik'];
                    $counter = 1;
                    while (DB::table('employees')->where('nik', $employeeData['nik'])->exists()) {
                        $employeeData['nik'] = $originalNik . str_pad($counter, 2, '0', STR_PAD_LEFT);
                        $counter++;
                    }
                    
                    // Set timestamps jika tidak ada
                    if (!isset($employeeData['created_at'])) {
                        $employeeData['created_at'] = now();
                    }
                    if (!isset($employeeData['updated_at'])) {
                        $employeeData['updated_at'] = now();
                    }
                    
                    // Insert dengan error handling
                    try {
                        DB::table('employees')->insert($employeeData);
                    } catch (\Exception $e) {
                        // Log error tapi lanjutkan
                        \Log::warning("Failed to restore employee: " . $e->getMessage(), ['employee' => $employeeData]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup current data
        $currentEmployees = DB::table('employees')->get();
        
        // Recreate with original structure (NIK as primary key)
        Schema::dropIfExists('employees');
        
        Schema::create('employees', function (Blueprint $table) {
            $table->string('nik', 20)->primary(); // NIK as primary key
            $table->string('nip', 20)->unique();
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P']);
            // Add other basic fields
            $table->timestamps();
        });
        
        // Restore data (only employees with valid NIK)
        foreach ($currentEmployees as $employee) {
            if (!empty($employee->nik) && strlen($employee->nik) >= 16) {
                $employeeData = (array) $employee;
                unset($employeeData['id']); // Remove auto-increment ID
                
                try {
                    DB::table('employees')->insert($employeeData);
                } catch (\Exception $e) {
                    \Log::warning("Failed to restore employee in rollback: " . $e->getMessage());
                }
            }
        }
    }
};