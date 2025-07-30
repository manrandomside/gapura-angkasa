<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip')->unique();
            $table->string('nik')->nullable();
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
            $table->string('instansi_pendidikan')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('remarks_pendidikan')->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('handphone')->nullable();
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

            // Indexes for performance
            $table->index(['status_pegawai', 'status']);
            $table->index(['unit_organisasi']);
            $table->index(['nama_lengkap']);
            $table->index(['tmt_mulai_jabatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};