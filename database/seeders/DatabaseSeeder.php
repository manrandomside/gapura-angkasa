<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for GAPURA ANGKASA SDM System.
     */
    public function run(): void
    {
        $this->command->info('Starting GAPURA ANGKASA SDM Database Seeding...');
        $this->command->info('================================================');

        // Clear existing data safely
        $this->clearExistingData();

        // Seed organizations first
        $this->seedOrganizations();

        // Seed users for authentication
        $this->seedUsers();

        // Seed employees (main data)
        $this->seedEmployees();

        // Display completion summary
        $this->displayCompletionSummary();
    }

    /**
     * Clear existing data with foreign key constraint handling
     */
    private function clearExistingData()
    {
        $this->command->info('Clearing existing data...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::table('organizations')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Existing data cleared successfully.');
    }

    /**
     * Seed organizations for GAPURA ANGKASA structure
     */
    private function seedOrganizations()
    {
        $this->command->info('Seeding organizations...');

        $organizations = [
            [
                'id' => 1,
                'name' => 'Back Office',
                'code' => 'BO',
                'description' => 'Divisi Back Office Operations - General Affair & Administration',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Airside',
                'code' => 'AS',
                'description' => 'Divisi Airside Operations - Flight Operations & Ramp Services',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Ground Support Equipment',
                'code' => 'GSE',
                'description' => 'Ground Support Equipment Division',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Aviation Security',
                'code' => 'AVSEC',
                'description' => 'Aviation Security Division',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Load Control',
                'code' => 'LC',
                'description' => 'Load Control Division',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Ramp Dispatch',
                'code' => 'RD',
                'description' => 'Ramp Dispatch Division',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Security Officer',
                'code' => 'SEC',
                'description' => 'Security Officer Division',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Operations',
                'code' => 'OPS',
                'description' => 'General Operations Division',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('organizations')->insert($organizations);
        $this->command->info('Organizations seeded successfully.');
    }

    /**
     * Seed users with different roles for the system
     */
    private function seedUsers()
    {
        $this->command->info('Creating user accounts...');
        
        $users = [
            [
                'name' => 'GusDek',
                'email' => 'admin@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('superadmin123'),
                'role' => 'super_admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manager HR & GA',
                'email' => 'manager.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('manager123'),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff HR',
                'email' => 'staff.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Controller HR & GA',
                'email' => 'controller.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('controller123'),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
        $this->command->info('User accounts created successfully.');
    }

    /**
     * Seed employee data based on GAPURA ANGKASA actual data
     */
    private function seedEmployees()
    {
        $this->command->info('Seeding employee data...');

        $employees = [
            [
                'nip' => '2201048',
                'nama_lengkap' => 'A.A GEDE AGUNG WIRAJAYA',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Denpasar',
                'tanggal_lahir' => '1985-03-15',
                'usia' => Carbon::parse('1985-03-15')->age,
                'alamat' => 'Jl. Raya Denpasar No. 123',
                'no_telepon' => '081234567890',
                'handphone' => '081234567890',
                'email' => 'agung.wirajaya@gapura.com',
                'unit_organisasi' => 'Airside',
                'jabatan' => 'LOAD CONTROL',
                'nama_jabatan' => 'LOAD CONTROL',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2021-03-10',
                'tmt_mulai_kerja' => '2021-03-10',
                'pendidikan_terakhir' => 'S1',
                'pendidikan' => 'S1',
                'instansi_pendidikan' => 'Universitas Udayana',
                'jurusan' => 'Teknik Mesin',
                'tahun_lulus' => 2008,
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '42',
                'kota_domisili' => 'Denpasar',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2012117',
                'nama_lengkap' => 'A.A NGURAH GEDE AGUNG DHARMA PUTRA',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Gianyar',
                'tanggal_lahir' => '1987-06-20',
                'usia' => Carbon::parse('1987-06-20')->age,
                'alamat' => 'Jl. Sunset Road No. 456',
                'no_telepon' => '081234567891',
                'handphone' => '081234567891',
                'email' => 'dharma.putra@gapura.com',
                'unit_organisasi' => 'Airside',
                'jabatan' => 'RAMP DISPATCH',
                'nama_jabatan' => 'RAMP DISPATCH',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2022-06-01',
                'tmt_mulai_kerja' => '2022-06-01',
                'pendidikan_terakhir' => 'D3',
                'pendidikan' => 'D3',
                'instansi_pendidikan' => 'Politeknik Negeri Bali',
                'jurusan' => 'Teknik Penerbangan',
                'tahun_lulus' => 2010,
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '41',
                'kota_domisili' => 'Gianyar',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2012124',
                'nama_lengkap' => 'A.A. AYU CANDRAWATI',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Badung',
                'tanggal_lahir' => '1990-09-12',
                'usia' => Carbon::parse('1990-09-12')->age,
                'alamat' => 'Jl. Bypass Ngurah Rai No. 789',
                'no_telepon' => '081234567892',
                'handphone' => '081234567892',
                'email' => 'ayu.candrawati@gapura.com',
                'unit_organisasi' => 'Airside',
                'jabatan' => 'LOAD CONTROL',
                'nama_jabatan' => 'LOAD CONTROL',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2021-09-15',
                'tmt_mulai_kerja' => '2021-09-15',
                'pendidikan_terakhir' => 'S1',
                'pendidikan' => 'S1',
                'instansi_pendidikan' => 'Universitas Udayana',
                'jurusan' => 'Manajemen',
                'tahun_lulus' => 2013,
                'jenis_sepatu' => 'Pantofel',
                'ukuran_sepatu' => '37',
                'kota_domisili' => 'Badung',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2150791',
                'nama_lengkap' => 'I KETUT ADIYANA',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Tabanan',
                'tanggal_lahir' => '1983-02-28',
                'usia' => Carbon::parse('1983-02-28')->age,
                'alamat' => 'Jl. Imam Bonjol No. 321',
                'no_telepon' => '081234567893',
                'handphone' => '081234567893',
                'email' => 'ketut.adiyana@gapura.com',
                'unit_organisasi' => 'Back Office',
                'jabatan' => 'CONTROLLER HR & GA',
                'nama_jabatan' => 'CONTROLLER HR & GA',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2023-02-15',
                'tmt_mulai_kerja' => '2023-02-15',
                'pendidikan_terakhir' => 'S1',
                'pendidikan' => 'S1',
                'instansi_pendidikan' => 'Universitas Mahasaraswati',
                'jurusan' => 'Psikologi',
                'tahun_lulus' => 2006,
                'jenis_sepatu' => 'Pantofel',
                'ukuran_sepatu' => '43',
                'kota_domisili' => 'Tabanan',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2019023',
                'nama_lengkap' => 'I KOMANG AGUS WIRAWAN',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Klungkung',
                'tanggal_lahir' => '1988-11-05',
                'usia' => Carbon::parse('1988-11-05')->age,
                'alamat' => 'Jl. Gatot Subroto No. 654',
                'no_telepon' => '081234567894',
                'handphone' => '081234567894',
                'email' => 'agus.wirawan@gapura.com',
                'unit_organisasi' => 'Avsec',
                'jabatan' => 'SECURITY OFFICER',
                'nama_jabatan' => 'SECURITY OFFICER',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2019-02-15',
                'tmt_mulai_kerja' => '2019-02-15',
                'pendidikan_terakhir' => 'SMA',
                'pendidikan' => 'SMA',
                'instansi_pendidikan' => 'SMA Negeri 1 Klungkung',
                'jurusan' => 'IPA',
                'tahun_lulus' => 2007,
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '42',
                'kota_domisili' => 'Klungkung',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2015089',
                'nama_lengkap' => 'I MADE SURYA DINATA',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Denpasar',
                'tanggal_lahir' => '1985-01-18',
                'usia' => Carbon::parse('1985-01-18')->age,
                'alamat' => 'Jl. Teuku Umar No. 987',
                'no_telepon' => '081234567895',
                'handphone' => '081234567895',
                'email' => 'surya.dinata@gapura.com',
                'unit_organisasi' => 'GSE',
                'jabatan' => 'OPERATOR GSE',
                'nama_jabatan' => 'OPERATOR GSE',
                'status_pegawai' => 'TAD',
                'tmt_mulai_jabatan' => '2023-01-10',
                'tmt_mulai_kerja' => '2023-01-10',
                'pendidikan_terakhir' => 'D3',
                'pendidikan' => 'D3',
                'instansi_pendidikan' => 'Politeknik Negeri Bali',
                'jurusan' => 'Teknik Mesin',
                'tahun_lulus' => 2008,
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '41',
                'kota_domisili' => 'Denpasar',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2012140',
                'nama_lengkap' => 'I NYOMAN JOHN SUPARTA',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Buleleng',
                'tanggal_lahir' => '1986-12-03',
                'usia' => Carbon::parse('1986-12-03')->age,
                'alamat' => 'Jl. Diponegoro No. 147',
                'no_telepon' => '081234567896',
                'handphone' => '081234567896',
                'email' => 'john.suparta@gapura.com',
                'unit_organisasi' => 'Airside',
                'jabatan' => 'RAMP DISPATCH',
                'nama_jabatan' => 'RAMP DISPATCH',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2022-01-15',
                'tmt_mulai_kerja' => '2022-01-15',
                'pendidikan_terakhir' => 'S1',
                'pendidikan' => 'S1',
                'instansi_pendidikan' => 'Universitas Pendidikan Ganesha',
                'jurusan' => 'Teknik Informatika',
                'tahun_lulus' => 2009,
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '44',
                'kota_domisili' => 'Buleleng',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2018045',
                'nama_lengkap' => 'NI PUTU SARI DEWI',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Badung',
                'tanggal_lahir' => '1992-04-22',
                'usia' => Carbon::parse('1992-04-22')->age,
                'alamat' => 'Jl. Raya Kuta No. 258',
                'no_telepon' => '081234567897',
                'handphone' => '081234567897',
                'email' => 'sari.dewi@gapura.com',
                'unit_organisasi' => 'Back Office',
                'jabatan' => 'STAFF ADMINISTRASI',
                'nama_jabatan' => 'STAFF ADMINISTRASI',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2020-04-15',
                'tmt_mulai_kerja' => '2020-04-15',
                'pendidikan_terakhir' => 'D3',
                'pendidikan' => 'D3',
                'instansi_pendidikan' => 'STIKOM Bali',
                'jurusan' => 'Sistem Informasi',
                'tahun_lulus' => 2015,
                'jenis_sepatu' => 'Pantofel',
                'ukuran_sepatu' => '36',
                'kota_domisili' => 'Badung',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2020156',
                'nama_lengkap' => 'KADEK RINA ASTUTI',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Gianyar',
                'tanggal_lahir' => '1993-07-14',
                'usia' => Carbon::parse('1993-07-14')->age,
                'alamat' => 'Jl. Hayam Wuruk No. 369',
                'no_telepon' => '081234567898',
                'handphone' => '081234567898',
                'email' => 'rina.astuti@gapura.com',
                'unit_organisasi' => 'Load Control',
                'jabatan' => 'LOAD CONTROL OFFICER',
                'nama_jabatan' => 'LOAD CONTROL OFFICER',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2021-07-01',
                'tmt_mulai_kerja' => '2021-07-01',
                'pendidikan_terakhir' => 'S1',
                'pendidikan' => 'S1',
                'instansi_pendidikan' => 'Universitas Udayana',
                'jurusan' => 'Ekonomi',
                'tahun_lulus' => 2016,
                'jenis_sepatu' => 'Pantofel',
                'ukuran_sepatu' => '38',
                'kota_domisili' => 'Gianyar',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nip' => '2019078',
                'nama_lengkap' => 'WAYAN BAGUS SANTIKA',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Denpasar',
                'tanggal_lahir' => '1989-10-30',
                'usia' => Carbon::parse('1989-10-30')->age,
                'alamat' => 'Jl. Gajah Mada No. 741',
                'no_telepon' => '081234567899',
                'handphone' => '081234567899',
                'email' => 'bagus.santika@gapura.com',
                'unit_organisasi' => 'Avsec',
                'jabatan' => 'SECURITY SUPERVISOR',
                'nama_jabatan' => 'SECURITY SUPERVISOR',
                'status_pegawai' => 'PEGAWAI TETAP',
                'tmt_mulai_jabatan' => '2020-10-15',
                'tmt_mulai_kerja' => '2020-10-15',
                'pendidikan_terakhir' => 'S1',
                'pendidikan' => 'S1',
                'instansi_pendidikan' => 'Universitas Udayana',
                'jurusan' => 'Hukum',
                'tahun_lulus' => 2012,
                'jenis_sepatu' => 'Safety Shoes',
                'ukuran_sepatu' => '42',
                'kota_domisili' => 'Denpasar',
                'status' => 'active',
                'status_kerja' => 'Aktif',
                'provider' => 'PT Gapura Angkasa',
                'lokasi_kerja' => 'Bandar Udara Ngurah Rai',
                'cabang' => 'DPS',
                'organization_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('employees')->insert($employees);
        $this->command->info('Employee data seeded successfully.');
    }

    /**
     * Display completion summary with system information
     */
    private function displayCompletionSummary()
    {
        // Get statistics for display
        $totalEmployees = DB::table('employees')->count();
        $totalOrganizations = DB::table('organizations')->count();
        $totalUsers = DB::table('users')->count();
        $activeEmployees = DB::table('employees')->where('status', 'active')->count();
        $pegawaiTetap = DB::table('employees')->where('status_pegawai', 'PEGAWAI TETAP')->count();
        $tad = DB::table('employees')->where('status_pegawai', 'TAD')->count();
        
        // Handle both L/P and Laki-laki/Perempuan formats
        $laki = DB::table('employees')
            ->where('jenis_kelamin', 'L')
            ->orWhere('jenis_kelamin', 'Laki-laki')
            ->count();
        
        $perempuan = DB::table('employees')
            ->where('jenis_kelamin', 'P')
            ->orWhere('jenis_kelamin', 'Perempuan')
            ->count();

        // Get organization breakdown with employee count
        $orgBreakdown = DB::table('organizations as o')
            ->leftJoin('employees as e', 'o.id', '=', 'e.organization_id')
            ->select('o.name', 'o.code', DB::raw('COUNT(e.id) as employee_count'))
            ->groupBy('o.id', 'o.name', 'o.code')
            ->orderBy('o.name')
            ->get();

        // Get shoe types breakdown
        $pantofels = DB::table('employees')->where('jenis_sepatu', 'Pantofel')->count();
        $safetyShoes = DB::table('employees')->where('jenis_sepatu', 'Safety Shoes')->count();

        // Display completion summary
        $this->command->info('');
        $this->command->info('================================================');
        $this->command->info('GAPURA ANGKASA SDM SYSTEM - SEEDING COMPLETE');
        $this->command->info('================================================');
        $this->command->info('');
        $this->command->info('BANDAR UDARA NGURAH RAI - DPS');
        $this->command->info('-----------------------------------------------');
        $this->command->info('');
        $this->command->info('EMPLOYEE STATISTICS:');
        $this->command->info("   Total Employees: {$totalEmployees}");
        $this->command->info("   Active Employees: {$activeEmployees}");
        $this->command->info("   Inactive Employees: " . ($totalEmployees - $activeEmployees));
        $this->command->info("   Pegawai Tetap: {$pegawaiTetap}");
        $this->command->info("   TAD (Tenaga Alih Daya): {$tad}");
        $this->command->info("   Laki-laki: {$laki}");
        $this->command->info("   Perempuan: {$perempuan}");
        $this->command->info('');
        $this->command->info('SHOE DISTRIBUTION:');
        $this->command->info("   Pantofel: {$pantofels}");
        $this->command->info("   Safety Shoes: {$safetyShoes}");
        $this->command->info('');
        $this->command->info('ORGANIZATION STRUCTURE:');
        $this->command->info("   Total Organizations: {$totalOrganizations}");
        foreach ($orgBreakdown as $org) {
            $this->command->info("   {$org->name} ({$org->code}): {$org->employee_count} employees");
        }
        $this->command->info('');
        $this->command->info('SYSTEM USERS:');
        $this->command->info("   Total Users: {$totalUsers}");
        $this->command->info('');
        $this->command->info('LOGIN CREDENTIALS:');
        $this->command->info('   GusDek (Super Admin): admin@gapura.com / password');
        $this->command->info('   System Admin: superadmin@gapura.com / superadmin123');
        $this->command->info('   Manager HR: manager.hr@gapura.com / manager123');
        $this->command->info('   Staff HR: staff.hr@gapura.com / staff123');
        $this->command->info('   Controller HR: controller.hr@gapura.com / controller123');
        $this->command->info('');
        $this->command->info('EMPLOYEE DATA INCLUDES:');
        $this->command->info('   - NIP (Employee ID Numbers)');
        $this->command->info('   - Personal Info (Name, Gender, Birth Date, Age)');
        $this->command->info('   - Job Information (Position, Department, TMT Jabatan)');
        $this->command->info('   - Contact Info (Phone, Email, Address)');
        $this->command->info('   - Education Background (Level, Institution, Major)');
        $this->command->info('   - Shoe Types (Pantofel & Safety Shoes with sizes)');
        $this->command->info('   - BPJS Information (Health & Employment)');
        $this->command->info('   - Work Experience (Start Date, Years of Service)');
        $this->command->info('   - Physical Data (Height, Weight)');
        $this->command->info('   - Organizational Structure (8 Units)');
        $this->command->info('');
        $this->command->info('ACCESS POINTS:');
        $this->command->info('   Dashboard: /dashboard');
        $this->command->info('   Management Karyawan: /employees');
        $this->command->info('   Tambah Karyawan: /employees/create');
        $this->command->info('   Organisasi: /organisasi');
        $this->command->info('   Laporan: /laporan');
        $this->command->info('   Pengaturan: /pengaturan');
        $this->command->info('');
        $this->command->info('API ENDPOINTS:');
        $this->command->info('   Dashboard Statistics: /api/dashboard/statistics');
        $this->command->info('   Employee Search: /api/employees/search');
        $this->command->info('   Employee Statistics: /api/employees/statistics');
        $this->command->info('   Health Check: /utilities/health-check');
        $this->command->info('');
        $this->command->info('DEVELOPMENT TOOLS (local only):');
        $this->command->info('   Test Database: /dev/test-database');
        $this->command->info('   Test Seeder: /dev/test-seeder');
        $this->command->info('   Clear Cache: /utilities/clear-cache');
        $this->command->info('   Route List: /dev/routes');
        $this->command->info('');
        $this->command->info('System is ready for use!');
        $this->command->info('Visit your Laravel application to manage employees.');
        $this->command->info('Base color: white with green hover (#439454)');
        $this->command->info('================================================');
        $this->command->info('');
    }
}