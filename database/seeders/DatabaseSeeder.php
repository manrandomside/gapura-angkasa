<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Starting database seeding for GAPURA ANGKASA SDM System...');
        
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::table('organizations')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🗑️  Cleared existing data');

        // Call SDM Employee Seeder (based on actual CSV data from GAPURA ANGKASA)
        $this->command->info('📊 Seeding organizations and employees from CSV data...');
        $this->call([
            SDMEmployeeSeeder::class,
        ]);

        // Create users for authentication (Super Admin, Admin, Staff)
        $this->command->info('👥 Creating user accounts for different roles...');
        
        $users = [
            [
                'name' => 'GusDek',
                'email' => 'admin@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('superadmin123'),
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Manager HR & GA',
                'email' => 'manager.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('manager123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff HR',
                'email' => 'staff.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Controller HR & GA',
                'email' => 'controller.hr@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('controller123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        // Get statistics for comprehensive display
        $totalEmployees = DB::table('employees')->count();
        $totalOrganizations = DB::table('organizations')->count();
        $activeEmployees = DB::table('employees')->where('status', 'active')->count();
        $pegawaiTetap = DB::table('employees')->where('status_pegawai', 'PEGAWAI TETAP')->count();
        $tad = DB::table('employees')->where('status_pegawai', 'TAD')->count();
        $laki = DB::table('employees')->where('jenis_kelamin', 'L')->count();
        $perempuan = DB::table('employees')->where('jenis_kelamin', 'P')->count();

        // Get organization breakdown
        $orgBreakdown = DB::table('organizations as o')
            ->leftJoin('employees as e', 'o.id', '=', 'e.organization_id')
            ->select('o.name', 'o.code', DB::raw('COUNT(e.id) as employee_count'))
            ->groupBy('o.id', 'o.name', 'o.code')
            ->orderBy('o.name')
            ->get();

        // Get shoe types breakdown
        $pantofels = DB::table('employees')->where('jenis_sepatu', 'Pantofel')->count();
        $safetyShoes = DB::table('employees')->where('jenis_sepatu', 'Safety Shoes')->count();

        $this->command->info('');
        $this->command->info('✅ GAPURA ANGKASA SDM DATABASE SEEDED SUCCESSFULLY!');
        $this->command->info('');
        $this->command->info('🏢 BANDAR UDARA NGURAH RAI - DPS');
        $this->command->info('═══════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('📊 EMPLOYEE STATISTICS:');
        $this->command->info("   📈 Total Employees: {$totalEmployees}");
        $this->command->info("   ✅ Active Employees: {$activeEmployees}");
        $this->command->info("   ❌ Inactive Employees: " . ($totalEmployees - $activeEmployees));
        $this->command->info("   👔 Pegawai Tetap: {$pegawaiTetap}");
        $this->command->info("   🏷️  TAD (Tenaga Alih Daya): {$tad}");
        $this->command->info("   👨 Laki-laki: {$laki}");
        $this->command->info("   👩 Perempuan: {$perempuan}");
        $this->command->info('');
        $this->command->info('👞 SHOE DISTRIBUTION:');
        $this->command->info("   👞 Pantofel: {$pantofels}");
        $this->command->info("   🥾 Safety Shoes: {$safetyShoes}");
        $this->command->info('');
        $this->command->info('🏢 ORGANIZATION BREAKDOWN:');
        foreach ($orgBreakdown as $org) {
            $this->command->info("   {$org->name} ({$org->code}): {$org->employee_count} employees");
        }
        $this->command->info('');
        $this->command->info('🔐 LOGIN CREDENTIALS:');
        $this->command->info('   GusDek (Super Admin): admin@gapura.com / password');
        $this->command->info('   Super Admin: superadmin@gapura.com / superadmin123');
        $this->command->info('   Manager HR: manager.hr@gapura.com / manager123');
        $this->command->info('   Staff HR: staff.hr@gapura.com / staff123');
        $this->command->info('   Controller HR: controller.hr@gapura.com / controller123');
        $this->command->info('');
        $this->command->info('📋 EMPLOYEE DATA INCLUDES:');
        $this->command->info('   ✓ NIP (Employee ID Numbers)');
        $this->command->info('   ✓ Personal Info (Name, Gender, Birth Date, Age)');
        $this->command->info('   ✓ Job Information (Position, Department, TMT Jabatan)');
        $this->command->info('   ✓ Contact Info (Phone, Address, Domicile)');
        $this->command->info('   ✓ Education Background (Level, Institution, Major)');
        $this->command->info('   ✓ Shoe Types (Pantofel & Safety Shoes with sizes)');
        $this->command->info('   ✓ BPJS Information (Health & Employment)');
        $this->command->info('   ✓ Work Experience (Start Date, Years of Service)');
        $this->command->info('   ✓ Physical Data (Height, Weight)');
        $this->command->info('   ✓ Organizational Structure (8 Units)');
        $this->command->info('');
        $this->command->info('🌐 ACCESS POINTS:');
        $this->command->info('   🔗 Dashboard: /dashboard');
        $this->command->info('   👥 Total Karyawan: /total-karyawan');
        $this->command->info('   ⚙️  Management Karyawan: /management-karyawan');
        $this->command->info('   ➕ Tambah Karyawan: /management-karyawan/create');
        $this->command->info('   📥 Import Data: /management-karyawan/import');
        $this->command->info('');
        $this->command->info('🚀 System is ready! Visit your Laravel application to manage employees.');
        $this->command->info('   Base URL with proper routes for GAPURA ANGKASA SDM System');
        $this->command->info('');
    }
}