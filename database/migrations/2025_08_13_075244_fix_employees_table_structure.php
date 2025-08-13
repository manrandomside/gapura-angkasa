<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FIXED: Ensure employees table structure matches the model expectations
     */
    public function up(): void
    {
        // Check if employees table exists and needs modification
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                // FIXED: Ensure NIK is nullable and unique (not primary key)
                if (Schema::hasColumn('employees', 'nik')) {
                    // First remove any existing unique constraint
                    try {
                        $table->dropUnique(['nik']);
                    } catch (\Exception $e) {
                        // Ignore if constraint doesn't exist
                    }
                    
                    // Modify column to be nullable and add unique constraint
                    $table->string('nik', 20)->nullable()->unique()->change();
                } else {
                    // Add NIK column if it doesn't exist
                    $table->string('nik', 20)->nullable()->unique()->after('id');
                }
                
                // Ensure unit_id and sub_unit_id columns exist
                if (!Schema::hasColumn('employees', 'unit_id')) {
                    $table->unsignedBigInteger('unit_id')->nullable()->after('unit_organisasi');
                }
                
                if (!Schema::hasColumn('employees', 'sub_unit_id')) {
                    $table->unsignedBigInteger('sub_unit_id')->nullable()->after('unit_id');
                }
                
                // Ensure seragam column exists
                if (!Schema::hasColumn('employees', 'seragam')) {
                    $table->string('seragam')->nullable()->after('ukuran_sepatu');
                }
                
                // Ensure status column exists
                if (!Schema::hasColumn('employees', 'status')) {
                    $table->enum('status', ['active', 'inactive'])->default('active')->after('organization_id');
                }
            });
        } else {
            // Create employees table if it doesn't exist (should not happen)
            Schema::create('employees', function (Blueprint $table) {
                // Auto-increment ID as primary key
                $table->id();
                
                // NIK as nullable unique field (not primary key)
                $table->string('nik', 20)->nullable()->unique();
                
                // Required employee fields
                $table->string('nip', 20)->unique();
                $table->string('nama_lengkap');
                $table->string('lokasi_kerja')->nullable();
                $table->string('cabang')->nullable();
                $table->string('status_pegawai');
                $table->string('status_kerja')->default('Aktif');
                $table->string('provider')->nullable();
                $table->string('kode_organisasi', 20)->nullable();
                $table->string('unit_organisasi', 200)->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedBigInteger('sub_unit_id')->nullable();
                $table->string('nama_organisasi', 200)->nullable();
                $table->string('nama_jabatan', 200)->nullable();
                $table->string('jabatan', 200)->nullable();
                $table->string('unit_kerja_kontrak', 200)->nullable();
                
                // Dates
                $table->date('tmt_mulai_kerja')->nullable();
                $table->date('tmt_mulai_jabatan')->nullable();
                $table->date('tmt_berakhir_jabatan')->nullable();
                $table->date('tmt_berakhir_kerja')->nullable();
                $table->string('masa_kerja_bulan')->nullable();
                $table->string('masa_kerja_tahun')->nullable();
                
                // Personal information
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->enum('jenis_sepatu', ['Pantofel', 'Safety Shoes'])->nullable();
                $table->string('ukuran_sepatu')->nullable();
                $table->string('seragam')->nullable();
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->integer('usia')->nullable();
                $table->string('kota_domisili')->nullable();
                $table->text('alamat')->nullable();
                
                // Education
                $table->string('pendidikan')->nullable();
                $table->string('pendidikan_terakhir')->nullable();
                $table->string('instansi_pendidikan')->nullable();
                $table->string('jurusan')->nullable();
                $table->string('remarks_pendidikan')->nullable();
                $table->integer('tahun_lulus')->nullable();
                
                // Contact
                $table->string('handphone')->nullable();
                $table->string('no_telepon')->nullable();
                $table->string('email')->nullable();
                
                // Work details
                $table->string('kategori_karyawan')->nullable();
                $table->date('tmt_pensiun')->nullable();
                $table->string('grade')->nullable();
                $table->string('no_bpjs_kesehatan')->nullable();
                $table->string('no_bpjs_ketenagakerjaan')->nullable();
                $table->string('kelompok_jabatan')->nullable();
                $table->string('kelas_jabatan')->nullable();
                
                // Physical attributes
                $table->integer('weight')->nullable();
                $table->integer('height')->nullable();
                
                // Foreign keys
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('set null');
                
                // Status
                $table->enum('status', ['active', 'inactive'])->default('active');
                
                $table->timestamps();

                // Indexes for performance
                $table->index(['status_pegawai', 'status']);
                $table->index(['unit_organisasi']);
                $table->index(['nama_lengkap']);
                $table->index(['tmt_mulai_jabatan']);
                $table->index(['unit_id']);
                $table->index(['sub_unit_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration fixes structure, so down should revert to problematic state
        // We'll just add a comment that this should not be rolled back
        
        // DO NOT ROLLBACK - This migration fixes critical primary key issues
        // Rolling back will break the employee system
    }
};