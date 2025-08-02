<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RunSDMSeederOnly extends Seeder
{
    /**
     * Run ONLY SDMEmployeeSeeder with necessary prerequisites
     * This seeder DOES NOT modify SDMEmployeeSeeder data at all
     */
    public function run(): void
    {
        $this->command->info('Running SDM Employee Seeder ONLY...');
        $this->command->info('===============================================');
        
        // Step 1: Seed users first (required for system access)
        $this->seedUsers();
        
        // Step 2: Run SDMEmployeeSeeder (unchanged, with all 20 data)
        $this->command->info('Calling SDMEmployeeSeeder...');
        $this->call(SDMEmployeeSeeder::class);
        
        // Step 3: Display completion summary
        $this->displayResults();
    }

    /**
     * Seed essential users for system access
     */
    private function seedUsers()
    {
        $this->command->info('Creating essential user accounts...');
        
        // Clear users table
        DB::table('users')->truncate();
        
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
        ];

        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }

        $this->command->info('User accounts created successfully.');
    }

    /**
     * Display final results
     */
    private function displayResults()
    {
        // Get statistics
        $totalEmployees = DB::table('employees')->count();
        $totalOrganizations = DB::table('organizations')->count();
        $totalUsers = DB::table('users')->count();
        $activeEmployees = DB::table('employees')->where('status', 'active')->count();
        $pegawaiTetap = DB::table('employees')->where('status_pegawai', 'PEGAWAI TETAP')->count();
        $tad = DB::table('employees')->where('status_pegawai', 'TAD')->count();
        
        // Gender stats (handle both L/P formats)
        $laki = DB::table('employees')->where('jenis_kelamin', 'L')->count();
        $perempuan = DB::table('employees')->where('jenis_kelamin', 'P')->count();

        // Shoe distribution
        $pantofels = DB::table('employees')->where('jenis_sepatu', 'Pantofel')->count();
        $safetyShoes = DB::table('employees')->where('jenis_sepatu', 'Safety Shoes')->count();

        // Organization breakdown
        $orgBreakdown = DB::table('organizations as o')
            ->leftJoin('employees as e', 'o.id', '=', 'e.organization_id')
            ->select('o.name', 'o.code', DB::raw('COUNT(e.id) as employee_count'))
            ->groupBy('o.id', 'o.name', 'o.code')
            ->orderBy('employee_count', 'desc')
            ->get();

        // Display results
        $this->command->info('');
        $this->command->info('===============================================');
        $this->command->info('SDM EMPLOYEE SEEDER - EXECUTION COMPLETE');
        $this->command->info('===============================================');
        $this->command->info('');
        $this->command->info('FINAL STATISTICS:');
        $this->command->info("   Total Employees: {$totalEmployees}");
        $this->command->info("   Active Employees: {$activeEmployees}");
        $this->command->info("   Pegawai Tetap: {$pegawaiTetap}");
        $this->command->info("   TAD: {$tad}");
        $this->command->info("   Laki-laki: {$laki}");
        $this->command->info("   Perempuan: {$perempuan}");
        $this->command->info('');
        $this->command->info('SHOE DISTRIBUTION:');
        $this->command->info("   Pantofel: {$pantofels}");
        $this->command->info("   Safety Shoes: {$safetyShoes}");
        $this->command->info('');
        $this->command->info('ORGANIZATION BREAKDOWN:');
        foreach ($orgBreakdown as $org) {
            $this->command->info("   {$org->name} ({$org->code}): {$org->employee_count} employees");
        }
        $this->command->info('');
        $this->command->info('LOGIN CREDENTIALS:');
        $this->command->info('   GusDek (Super Admin): admin@gapura.com / password');
        $this->command->info('   System Admin: superadmin@gapura.com / superadmin123');
        $this->command->info('   Manager HR: manager.hr@gapura.com / manager123');
        $this->command->info('   Staff HR: staff.hr@gapura.com / staff123');
        $this->command->info('');
        $this->command->info('UI ACCESS:');
        $this->command->info('   Management Karyawan: /employees');
        $this->command->info('   Dashboard: /dashboard');
        $this->command->info('');
        
        if ($totalEmployees === 20) {
            $this->command->info('✅ SUCCESS: All 20 employees from SDMEmployeeSeeder loaded!');
        } else {
            $this->command->warn("⚠️  WARNING: Expected 20 employees, got {$totalEmployees}");
        }
        
        $this->command->info('Base color: white with green hover (#439454)');
        $this->command->info('===============================================');
    }
}