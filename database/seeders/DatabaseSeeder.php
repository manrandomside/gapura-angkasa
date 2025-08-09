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
     * UPDATED: Menambahkan support untuk organizational units hierarchy
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

        // Seed employees (main data) - menggunakan SDMEmployeeSeeder yang sudah ada
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
     * Seed organizations for GAPURA ANGKASA structure (UPDATED dengan semua unit organisasi)
     */
    private function seedOrganizations()
    {
        $this->command->info('Seeding organizations...');

        $organizations = [
            [
                'id' => 1,
                'name' => 'EGM',
                'code' => 'EGM',
                'description' => 'Executive General Manager',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'GM',
                'code' => 'GM',
                'description' => 'General Manager',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Airside',
                'code' => 'AS',
                'description' => 'Divisi Airside Operations - Flight Operations & Ramp Services',
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
                'name' => 'Back Office',
                'code' => 'BO',
                'description' => 'Divisi Back Office Operations - General Affair & Administration',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'SSQC',
                'code' => 'SSQC',
                'description' => 'Safety Security Quality Control',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Ancillary',
                'code' => 'ANC',
                'description' => 'Ancillary Services',
                'location' => 'Bandar Udara Ngurah Rai',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('organizations')->insert($organizations);
        $this->command->info('Organizations seeded successfully.');

        // TAMBAHAN: Seed organizational units hierarchy
        $this->seedOrganizationalUnitsData();
    }

    /**
     * TAMBAHAN: Seed organizational units hierarchy dalam tabel yang sama
     * Menggunakan field JSON untuk menyimpan struktur hierarki
     */
    private function seedOrganizationalUnitsData()
    {
        $this->command->info('Setting up organizational units hierarchy...');
        
        // Cek apakah ada kolom untuk menyimpan struktur hierarki
        if (!DB::getSchemaBuilder()->hasColumn('organizations', 'units_structure')) {
            // Tambahkan kolom untuk menyimpan struktur hierarki
            DB::statement('ALTER TABLE organizations ADD COLUMN units_structure JSON NULL');
        }

        // Data struktur organisasi sesuai kebutuhan GAPURA ANGKASA
        $organizationalHierarchy = [
            'EGM' => [
                'units' => [
                    'EGM' => ['sub_units' => []] // Tidak ada sub unit
                ]
            ],
            'GM' => [
                'units' => [
                    'GM' => ['sub_units' => []] // Tidak ada sub unit
                ]
            ],
            'Airside' => [
                'units' => [
                    'MO' => [
                        'sub_units' => [
                            'Flops', 'Depco', 'Ramp', 'Load Control', 
                            'Load Master', 'ULD Control', 'Cargo Import', 'Cargo Export'
                        ]
                    ],
                    'ME' => [
                        'sub_units' => [
                            'GSE Operator P/B', 'GSE Operator A/C', 'GSE Maintenance',
                            'BTT Operator', 'Line Maintenance'
                        ]
                    ]
                ]
            ],
            'Landside' => [
                'units' => [
                    'MF' => [
                        'sub_units' => [
                            'KLM', 'Qatar', 'Korean Air', 'Vietjet Air', 'Scoot', 'Thai Airways',
                            'China Airlines', 'China Southern', 'Indigo', 'Xiamen Air', 'Aero Dili',
                            'Jeju Air', 'Hongkong Airlines', 'Air Busan', 'Vietnam Airlines',
                            'Sichuan Airlines', 'Aeroflot', 'Charter Flight'
                        ]
                    ],
                    'MS' => [
                        'sub_units' => ['MPGA', 'QG', 'IP']
                    ]
                ]
            ],
            'Back Office' => [
                'units' => [
                    'MU' => [
                        'sub_units' => ['Human Resources & General Affair', 'Fasilitas & Sarana']
                    ],
                    'MK' => [
                        'sub_units' => ['Accounting', 'Budgeting', 'Treassury', 'Tax']
                    ]
                ]
            ],
            'SSQC' => [
                'units' => [
                    'MQ' => [
                        'sub_units' => ['Avsec', 'Safety Quality Control']
                    ]
                ]
            ],
            'Ancillary' => [
                'units' => [
                    'MB' => [
                        'sub_units' => ['GPL', 'GLC', 'Joumpa']
                    ]
                ]
            ]
        ];

        // Update setiap organization dengan struktur hierarkinya
        foreach ($organizationalHierarchy as $orgName => $structure) {
            DB::table('organizations')
                ->where('name', $orgName)
                ->update([
                    'units_structure' => json_encode($structure),
                    'updated_at' => now()
                ]);
        }

        $this->command->info('Organizational units hierarchy seeded successfully.');
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('superadmin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manager HR & GA',
                'email' => 'manager.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('manager123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff HR',
                'email' => 'staff.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('staff123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Controller HR & GA',
                'email' => 'controller.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('controller123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
        $this->command->info('User accounts created successfully.');
    }

    /**
     * Seed employees using SDMEmployeeSeeder
     */
    private function seedEmployees()
    {
        $this->command->info('Seeding employees using SDMEmployeeSeeder...');
        $this->call(SDMEmployeeSeeder::class);
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
        
        // Handle both L/P format
        $laki = DB::table('employees')->where('jenis_kelamin', 'L')->count();
        $perempuan = DB::table('employees')->where('jenis_kelamin', 'P')->count();

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
        $this->command->info('   - Organizational Structure (7 Units)');
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
        $this->command->info('   Organizational Units: /api/organizational-units');
        $this->command->info('   Health Check: /utilities/health-check');
        $this->command->info('');
        $this->command->info('DEVELOPMENT TOOLS (local only):');
        $this->command->info('   Test Database: /dev/test-database');
        $this->command->info('   Test Seeder: /dev/test-seeder');
        $this->command->info('   Clear Cache: /utilities/clear-cache');
        $this->command->info('   Route List: /dev/routes');
        $this->command->info('');
        $this->command->info('ORGANIZATIONAL STRUCTURE ADDED:');
        $this->command->info('   EGM -> EGM (no sub units)');
        $this->command->info('   GM -> GM (no sub units)');
        $this->command->info('   Airside -> MO, ME (with sub units)');
        $this->command->info('   Landside -> MF, MS (with sub units)');
        $this->command->info('   Back Office -> MU, MK (with sub units)');
        $this->command->info('   SSQC -> MQ (with sub units)');
        $this->command->info('   Ancillary -> MB (with sub units)');
        $this->command->info('');
        $this->command->info('System is ready for use!');
        $this->command->info('Visit your Laravel application to manage employees.');
        $this->command->info('Base color: white with green hover (#439454)');
        $this->command->info('================================================');
        $this->command->info('');
    }
}