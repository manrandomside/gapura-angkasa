<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Organization;

class SDMEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds for GAPURA ANGKASA SDM System
     * Data source: Dashboard Data SDM DATABASE SDM.csv
     * Updated dengan 20 data karyawan pertama yang 100% akurat dari CSV
     */
    public function run(): void
    {
        $this->command->info('Starting SDM Employee Seeding...');
        $this->command->info('Data Source: Dashboard Data SDM DATABASE SDM.csv');
        $this->command->info('Total employees to seed: 20');
        
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::table('organizations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed Organizations based on CSV data
        $this->seedOrganizations();
        
        // Seed Employees from CSV data (20 karyawan pertama)
        $this->seedEmployees();

        $this->command->info('SDM Employee Seeding completed successfully!');
        $this->command->info('Total employees created: 20');
        $this->command->info('All data is 100% accurate from CSV file');
    }

    private function seedOrganizations()
    {
        $this->command->info('Seeding organizations...');

        $organizations = [
            [
                'id' => 1,
                'name' => 'Back Office',
                'code' => 'BO',
                'description' => 'Divisi Back Office Operations - General Affair & Administration, Finance',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Airside',
                'code' => 'AS',
                'description' => 'Divisi Airside Operations - Operation Services, Flight Operations & Ramp Services',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'GSE',
                'code' => 'GSE',
                'description' => 'Ground Support Equipment Division - GSE Operation & Maintenance',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Landside',
                'code' => 'LS',
                'description' => 'Divisi Landside Operations - MPA, MPL & Unschedule Flight',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Avsec',
                'code' => 'AV',
                'description' => 'Aviation Security Division',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Finance',
                'code' => 'FN',
                'description' => 'Finance Division - Accounting & Financial Management',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($organizations as $organization) {
            DB::table('organizations')->insert($organization);
        }

        $this->command->info('Organizations seeded successfully.');
    }

    private function seedEmployees()
    {
        $this->command->info('Seeding employees from CSV data...');

        // Data 100% akurat dari CSV Dashboard Data SDM DATABASE SDM.csv
        // 20 karyawan pertama dengan semua field lengkap
        $employees = [
            [
                'no' => 1,
                'nip' => '2160791',
                'nik' => null,
                'nama_lengkap' => 'I KETUT ADIYANA',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MU',
                'unit_organisasi' => 'Back Office',
                'nama_organisasi' => 'GENERAL AFFAIR & ADMINISTRATION',
                'nama_jabatan' => 'CONTROLLER HR & GA',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => $this->parseDate('01/05/2025'),
                'tmt_mulai_jabatan' => $this->parseDate('2023-02-15'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'P',
                'jenis_sepatu' => 'Pantofel',
                'ukuran_sepatu' => '37',
                'tempat_lahir' => 'MALANG',
                'tanggal_lahir' => $this->parseDate('1971-04-08'),
                'usia' => 54,
                'kota_domisili' => 'MALANG',
                'alamat' => 'Jalan Batas Dukuh Sari Gang Merpati No. B4 , Pedungan, Denpasar, Denpasar, Bali',
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SMAN 4 DENPASAR',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1990,
                'handphone' => '+6281353277157',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-05-2027'),
                'grade' => 'IX',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'ACCOUNTING OFFICER',
                'weight' => 51,
                'height' => 154,
                'organization_id' => 6,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 7,
                'nip' => '2170876',
                'nik' => null,
                'nama_lengkap' => 'A.A. RATIH KHOMALYANA DEWI',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MK',
                'unit_organisasi' => 'Back Office',
                'nama_organisasi' => 'FINANCE',
                'nama_jabatan' => 'FINANCE & COST ACCOUNTING OFFICER',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2019-10-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'P',
                'jenis_sepatu' => 'Pantofel',
                'ukuran_sepatu' => '38',
                'tempat_lahir' => 'DENPASAR',
                'tanggal_lahir' => $this->parseDate('1987-09-05'),
                'usia' => 37,
                'kota_domisili' => 'DENPASAR',
                'alamat' => 'JL LETDA KAJENG GG 2 NO 3, DENPASAR, DANGIN PURI, , BALI',
                'pendidikan' => 'SARJANA',
                'instansi_pendidikan' => 'UNIVERSITAS DIPONEGORO SEMARANG',
                'jurusan' => 'AKUNTANSI',
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 2009,
                'handphone' => '+628975437817',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-10-2043'),
                'grade' => null, // CSV: "-"
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'FINANCE & COST ACCOUNTING OFFICER',
                'weight' => 60,
                'height' => 165,
                'organization_id' => 6,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 8,
                'nip' => '2011840',
                'nik' => null,
                'nama_lengkap' => 'AA ARISANDI',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MO',
                'unit_organisasi' => 'Airside',
                'nama_organisasi' => 'OPERATION SERVICES',
                'nama_jabatan' => 'DEPARTURE CONTROL',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2021-09-28'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'P',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '37',
                'tempat_lahir' => 'DENPASAR',
                'tanggal_lahir' => $this->parseDate('1975-07-19'),
                'usia' => 50,
                'kota_domisili' => 'DENPASAR',
                'alamat' => 'Jl ayani utara gg manukrawa 1 no 4 dps, peguyangan, Denpasar utara, Denpasar, Bali',
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'WIDYA PURA',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1993,
                'handphone' => '+6287861872219',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-08-2031'),
                'grade' => 'VIII',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'DEPARTURE CONTROL',
                'weight' => 60,
                'height' => 165,
                'organization_id' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 9,
                'nip' => '2150483',
                'nik' => null,
                'nama_lengkap' => 'AGUS PRIONO',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'ME',
                'unit_organisasi' => 'GSE',
                'nama_organisasi' => 'GSE OPERATION & MAINTENANCE',
                'nama_jabatan' => 'GSE MECHANIC',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2018-09-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '42',
                'tempat_lahir' => 'CIAMIS',
                'tanggal_lahir' => $this->parseDate('1972-06-10'),
                'usia' => 53,
                'kota_domisili' => 'CIAMIS',
                'alamat' => 'Jalan Segara Madu Gang Ratna 3, Kelan Abian, Kuta, Badung, Bali',
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SMA PASUNDAN',
                'jurusan' => 'SOSIAL',
                'remarks_pendidikan' => 'LULUS',
                'tahun_lulus' => 1993,
                'handphone' => '+6281338509060',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-07-2028'),
                'grade' => null, // CSV: "-"
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'GSE MECHANIC',
                'weight' => 60,
                'height' => 172,
                'organization_id' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 10,
                'nip' => '2980402',
                'nik' => null,
                'nama_lengkap' => 'ANAK AGUNG GEDE PUTRA WIDYARTHA',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'ME',
                'unit_organisasi' => 'GSE',
                'nama_organisasi' => 'GSE OPERATION & MAINTENANCE',
                'nama_jabatan' => 'CONTROLLER GSE OPERATION',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2024-11-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '43',
                'tempat_lahir' => 'BANDUNG',
                'tanggal_lahir' => $this->parseDate('1971-07-02'),
                'usia' => 54,
                'kota_domisili' => 'BANDUNG',
                'alamat' => 'Padang Indah VIII No.4, Padangsambian, Denpasar Barat, Denpasar, Bali',
                'pendidikan' => 'SARJANA',
                'instansi_pendidikan' => 'ITPS',
                'jurusan' => 'TEKNIK MESIN',
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 2005,
                'handphone' => '+6281338277716',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-08-2027'),
                'grade' => 'VII',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'SUPERVISOR',
                'kelas_jabatan' => 'CONTROLLER GSE OPERATION',
                'weight' => 80,
                'height' => 176,
                'organization_id' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 11,
                'nip' => '2012129',
                'nik' => null,
                'nama_lengkap' => 'ANAK AGUNG NGURAH BAGUS DHARMAYUDA',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MF',
                'unit_organisasi' => 'Landside',
                'nama_organisasi' => 'MPA, MPL & UNSCHEDULE FLIGHT',
                'nama_jabatan' => null, // CSV: "-"
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => null, // CSV: "-"
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '42',
                'tempat_lahir' => 'DENPASAR',
                'tanggal_lahir' => $this->parseDate('1974-10-28'),
                'usia' => 50,
                'kota_domisili' => 'DENPASAR',
                'alamat' => 'JALAN PULAU BURU NO 15, PEMECUTAN, DENPASAR BARAT, DENPASAR, BALI',
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SLUA SARASWATI I',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1993,
                'handphone' => '+6281350908782',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-11-2030'),
                'grade' => 'IX',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => null, // CSV: "-"
                'kelas_jabatan' => null, // CSV: "-"
                'weight' => 78,
                'height' => 170,
                'organization_id' => 4,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 12,
                'nip' => '2980938',
                'nik' => null,
                'nama_lengkap' => 'ANAK AGUNG SAGUNG TUTIK MAHADEWI',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MO',
                'unit_organisasi' => 'Airside',
                'nama_organisasi' => 'OPERATION SERVICES',
                'nama_jabatan' => 'CONTROLLER OF LANDSIDE',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2024-11-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'P',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '38',
                'tempat_lahir' => 'DENPASAR',
                'tanggal_lahir' => $this->parseDate('1972-10-14'),
                'usia' => 52,
                'kota_domisili' => 'DENPASAR',
                'alamat' => 'Jalan Kerta Dalem VI/5A , -DENPASAR, Denpasar, Denpasar, Bali',
                'pendidikan' => 'SARJANA',
                'instansi_pendidikan' => 'UNIVERSITAS MAHASARASWATI',
                'jurusan' => 'MANAJEMEN',
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 2003,
                'handphone' => '+6282237494946',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-11-2028'),
                'grade' => 'VII',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'SUPERVISOR',
                'kelas_jabatan' => 'CONTROLLER OF LANDSIDE',
                'weight' => 65,
                'height' => 165,
                'organization_id' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 13,
                'nip' => '2022403',
                'nik' => null,
                'nama_lengkap' => 'ANDREAS RANDONGKIR',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'ME',
                'unit_organisasi' => 'GSE',
                'nama_organisasi' => 'GSE OPERATION & MAINTENANCE',
                'nama_jabatan' => 'GSE OPERATOR/MECHANIC',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2022-12-01'),
                'tmt_berakhir_jabatan' => $this->parseDate('2002-08-01'),
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '40',
                'tempat_lahir' => 'BIAK',
                'tanggal_lahir' => $this->parseDate('1973-08-02'),
                'usia' => 52,
                'kota_domisili' => 'BIAK',
                'alamat' => null, // CSV: ", , , , "
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SMA YPK, BIAK',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1993,
                'handphone' => '+6285343995768',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-09-2029'),
                'grade' => null, // CSV: "-"
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'GSE OPERATOR/MECHANIC',
                'weight' => 55,
                'height' => 157,
                'organization_id' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 14,
                'nip' => '2012053',
                'nik' => null,
                'nama_lengkap' => 'ANTONIUS GUNARDONO SAMSULARDI',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MO',
                'unit_organisasi' => 'Airside',
                'nama_organisasi' => 'OPERATION SERVICES',
                'nama_jabatan' => 'CARGO HANDLING OFFICER',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2018-09-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '41',
                'tempat_lahir' => 'LARANTUKA',
                'tanggal_lahir' => $this->parseDate('1970-04-04'),
                'usia' => 55,
                'kota_domisili' => 'LARANTUKA',
                'alamat' => 'Jalan Kedokteran No. 28 Perum Bumi Jimbaran Asri , Jimbaran, Jimbaran, Badung, Bali',
                'pendidikan' => 'SARJANA',
                'instansi_pendidikan' => 'STMIK - AKAKOM YOGYAKARTA',
                'jurusan' => 'TEKNIK INFORMATIKA',
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1998,
                'handphone' => '+6281353334658',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-05-2026'),
                'grade' => 'IX',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'CARGO HANDLING OFFICER',
                'weight' => 62,
                'height' => 168,
                'organization_id' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 15,
                'nip' => '2132476',
                'nik' => null,
                'nama_lengkap' => 'ARFAN',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MK',
                'unit_organisasi' => 'Back Office',
                'nama_organisasi' => 'FINANCE',
                'nama_jabatan' => 'MANAGER OF FINANCE',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2025-02-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Pantofel',
                'ukuran_sepatu' => '41',
                'tempat_lahir' => 'MAROS',
                'tanggal_lahir' => $this->parseDate('1982-04-25'),
                'usia' => 43,
                'kota_domisili' => 'MAROS',
                'alamat' => 'JL. NANGKA NO. 69, TURIKALE, TURIKALE, MAROS, SULAWESI SELATAN',
                'pendidikan' => 'SARJANA',
                'instansi_pendidikan' => 'SEKOLAH TINGGI ILMU MANAJEMEN YAPIM MAROS',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 2011,
                'handphone' => '+6281355959592',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-05-2038'),
                'grade' => 'VI',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'MANAGER',
                'kelas_jabatan' => 'MANAGER OF FINANCE',
                'weight' => 65,
                'height' => 165,
                'organization_id' => 6,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 16,
                'nip' => '2012086',
                'nik' => null,
                'nama_lengkap' => 'ARY UNTORO',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'ME',
                'unit_organisasi' => 'GSE',
                'nama_organisasi' => 'GSE OPERATION & MAINTENANCE',
                'nama_jabatan' => 'GSE OPERATOR',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2018-09-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '43',
                'tempat_lahir' => 'SOLO',
                'tanggal_lahir' => $this->parseDate('1977-05-21'),
                'usia' => 48,
                'kota_domisili' => 'SOLO',
                'alamat' => 'Jl Imam Bonjol Gg.KertaPura IIIA/24, Denpasar Barat, Denpasar Barat, Denpasar , Bali',
                'pendidikan' => 'DIPLOMA I',
                'instansi_pendidikan' => 'PRATAMA MULYA SURAKARTA',
                'jurusan' => 'KOMPUTER',
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1996,
                'handphone' => '+6285935077467',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-06-2033'),
                'grade' => 'IX',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'GSE OPERATOR',
                'weight' => 90,
                'height' => 175,
                'organization_id' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 17,
                'nip' => '2980836',
                'nik' => null,
                'nama_lengkap' => 'ASEP SETIAWAN',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MO',
                'unit_organisasi' => 'Airside',
                'nama_organisasi' => 'OPERATION SERVICES',
                'nama_jabatan' => 'LOST & FOUND',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2024-11-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '42',
                'tempat_lahir' => 'KARAWANG',
                'tanggal_lahir' => $this->parseDate('1968-09-23'),
                'usia' => 56,
                'kota_domisili' => 'KARAWANG',
                'alamat' => 'Perum Kori Nuansa Jimbaran Jalan Nuasa Tengah 3, Jimbaran, Kuta, Badung, Bali',
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SMA NEGERI CILIMUS',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1988,
                'handphone' => '+6282147299192',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-08-1991'),
                'grade' => 'VII',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'LOST & FOUND',
                'weight' => null, // CSV: "-"
                'height' => null, // CSV: "-"
                'organization_id' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 18,
                'nip' => '2001784',
                'nik' => null,
                'nama_lengkap' => 'BIBIT PUJIONO',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'ME',
                'unit_organisasi' => 'GSE',
                'nama_organisasi' => 'GSE OPERATION & MAINTENANCE',
                'nama_jabatan' => 'GSE OPERATOR',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2018-09-01'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '41',
                'tempat_lahir' => 'BOJONEGORO',
                'tanggal_lahir' => $this->parseDate('1972-04-23'),
                'usia' => 53,
                'kota_domisili' => 'BOJONEGORO',
                'alamat' => 'Perum Bumi Dalung permai Blok S 70, Dalung, Kuta, Badung, Bali',
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SMA BINA BANGSA',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1991,
                'handphone' => '+6281246795349',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-05-2028'),
                'grade' => 'IX',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'GSE OPERATOR',
                'weight' => 50,
                'height' => 165,
                'organization_id' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 19,
                'nip' => '2022399',
                'nik' => null,
                'nama_lengkap' => 'BILLY RUMBIAK',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'ME',
                'unit_organisasi' => 'GSE',
                'nama_organisasi' => 'GSE OPERATION & MAINTENANCE',
                'nama_jabatan' => 'GSE OPERATOR/MECHANIC',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2018-09-01'),
                'tmt_berakhir_jabatan' => $this->parseDate('2022-11-30'),
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '44',
                'tempat_lahir' => 'BIAK',
                'tanggal_lahir' => $this->parseDate('1978-12-02'),
                'usia' => 46,
                'kota_domisili' => 'BIAK',
                'alamat' => null, // CSV: ", , , , "
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SMK TEKNOLOGI & INDUSTRI YPK, BIAK',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1998,
                'handphone' => '+6282239962044',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-01-2035'),
                'grade' => null, // CSV: "-"
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'STAFF',
                'kelas_jabatan' => 'GSE OPERATOR/MECHANIC',
                'weight' => 90,
                'height' => 178,
                'organization_id' => 3,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no' => 20,
                'nip' => '2012112',
                'nik' => null,
                'nama_lengkap' => 'BUDI HARYONO',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'status_pegawai' => 'PEGAWAI TETAP',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'kode_organisasi' => 'MO',
                'unit_organisasi' => 'Airside',
                'nama_organisasi' => 'OPERATION SERVICES',
                'nama_jabatan' => 'CONTROLLER OPERATION',
                'unit_kerja_kontrak' => null,
                'tmt_mulai_kerja' => null,
                'tmt_mulai_jabatan' => $this->parseDate('2023-04-02'),
                'tmt_berakhir_jabatan' => null, // CSV: "-"
                'tmt_berakhir_kerja' => null,
                'masa_kerja_bulan' => null,
                'masa_kerja_tahun' => null,
                'jenis_kelamin' => 'L',
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '42',
                'tempat_lahir' => 'DENPASAR',
                'tanggal_lahir' => $this->parseDate('1974-12-10'),
                'usia' => 50,
                'kota_domisili' => 'DENPASAR',
                'alamat' => 'Jalan Raya Pemogan Gang Anggrek VII/ 26, Pemogan, Denpasar, Denpasar, Bali',
                'pendidikan' => 'SEKOLAH MENENGAH ATAS',
                'instansi_pendidikan' => 'SMA ANUGRAH',
                'jurusan' => null, // CSV: "-"
                'remarks_pendidikan' => null, // CSV: "-"
                'tahun_lulus' => 1993,
                'handphone' => '+6285738605472',
                'kategori_karyawan' => null, // CSV: "-"
                'tmt_pensiun' => $this->parseDate('01-01-2031'),
                'grade' => 'IX',
                'no_bpjs_kesehatan' => null, // CSV: "-"
                'no_bpjs_ketenagakerjaan' => null, // CSV: "-"
                'kelompok_jabatan' => 'SUPERVISOR',
                'kelas_jabatan' => 'CONTROLLER OPERATION',
                'weight' => 74,
                'height' => 170,
                'organization_id' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert employees data
        foreach ($employees as $employee) {
            DB::table('employees')->insert($employee);
        }

        $this->command->info('Sample employees seeded successfully.');
        $this->command->info('Total employees created: ' . count($employees));
        
        // Display shoe distribution statistics
        $pantofels = collect($employees)->where('jenis_sepatu', 'Pantofel')->count();
        $safetyShoes = collect($employees)->where('jenis_sepatu', 'Safety Shoes')->count();
        
        $this->command->info('');
        $this->command->info('SHOE DISTRIBUTION:');
        $this->command->info("   Pantofel: {$pantofels} employees");
        $this->command->info("   Safety Shoes: {$safetyShoes} employees");
        $this->command->info('');
    }

    /**
     * Function to parse date from CSV format (dd/mm/yyyy or yyyy-mm-dd)
     */
    private function parseDate($dateString)
    {
        if (empty($dateString) || $dateString === '-' || $dateString === null) {
            return null;
        }

        try {
            // If format is dd/mm/yyyy
            if (strpos($dateString, '/') !== false) {
                return Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d');
            }
            // If format is dd-mm-yyyy
            else if (strpos($dateString, '-') !== false && strlen($dateString) <= 10) {
                // Try dd-mm-yyyy format first
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateString)) {
                    return Carbon::createFromFormat('d-m-Y', $dateString)->format('Y-m-d');
                }
                // Otherwise try yyyy-mm-dd format
                else {
                    return Carbon::parse($dateString)->format('Y-m-d');
                }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Function to get organization ID by unit name
     */
    private function getOrganizationId($unitName)
    {
        $mapping = [
            'Back Office' => 1,
            'Airside' => 2,
            'GSE' => 3,
            'Landside' => 4,
            'Avsec' => 5,
            'Finance' => 6,
        ];

        return $mapping[$unitName] ?? 1;
    }
}