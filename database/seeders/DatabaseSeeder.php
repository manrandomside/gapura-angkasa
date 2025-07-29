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
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('employees')->truncate();
        DB::table('organizations')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed Organizations
        $organizations = [
            [
                'id' => 1,
                'name' => 'Divisi IT',
                'code' => 'IT',
                'description' => 'Divisi Teknologi Informasi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Divisi Operations',
                'code' => 'OPS',
                'description' => 'Divisi Operasional Bandara',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Divisi Security',
                'code' => 'SEC',
                'description' => 'Divisi Keamanan Bandara',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Divisi Customer Service',
                'code' => 'CS',
                'description' => 'Divisi Layanan Pelanggan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Divisi Ground Handling',
                'code' => 'GH',
                'description' => 'Divisi Penanganan Darat',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Divisi Finance',
                'code' => 'FIN',
                'description' => 'Divisi Keuangan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'Divisi HR',
                'code' => 'HR',
                'description' => 'Divisi Sumber Daya Manusia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'Divisi Cargo',
                'code' => 'CGO',
                'description' => 'Divisi Kargo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('organizations')->insert($organizations);

        // Generate realistic employee names
        $firstNames = [
            'Ahmad', 'Budi', 'Sari', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hadi',
            'Indra', 'Joko', 'Kartika', 'Liana', 'Maya', 'Nanda', 'Omar', 'Putri',
            'Qori', 'Rina', 'Sinta', 'Tono', 'Udin', 'Vera', 'Wati', 'Yudi', 'Zara',
            'Agus', 'Bambang', 'Citra', 'Dian', 'Eri', 'Fitri', 'Gunawan', 'Heru',
            'Ika', 'Juki', 'Kiki', 'Linda', 'Mira', 'Nina', 'Oki', 'Prita',
            'Rizki', 'Sandi', 'Tari', 'Umi', 'Vina', 'Wawan', 'Yani', 'Zaki'
        ];

        $lastNames = [
            'Pratama', 'Sari', 'Wijaya', 'Lestari', 'Putra', 'Dewi', 'Santoso', 'Wati',
            'Kusuma', 'Indah', 'Nugroho', 'Sinta', 'Handoko', 'Rahayu', 'Setiawan', 'Fitri',
            'Kurniawan', 'Maharani', 'Susanto', 'Anggraini', 'Permana', 'Safitri', 'Gunawan', 'Novita',
            'Firmansyah', 'Puspita', 'Hermawan', 'Lestari', 'Wirawan', 'Kartini', 'Hakim', 'Savitri',
            'Darmawan', 'Permatasari', 'Nugraha', 'Ratnasari', 'Wibowo', 'Melati', 'Surya', 'Ningrum'
        ];

        $positions = [
            'Staff IT', 'Supervisor IT', 'Manager IT', 'System Administrator', 'Network Engineer',
            'Ground Handling Officer', 'Supervisor Operations', 'Manager Operations', 'Flight Coordinator',
            'Security Officer', 'Security Supervisor', 'Chief Security', 'CCTV Operator',
            'Customer Service Agent', 'Supervisor CS', 'Manager CS', 'Information Desk Officer',
            'Finance Staff', 'Accounting Officer', 'Finance Manager', 'Budget Analyst',
            'HR Staff', 'HR Specialist', 'HR Manager', 'Recruitment Officer',
            'Cargo Handler', 'Cargo Supervisor', 'Cargo Manager', 'Warehouse Officer'
        ];

        // Generate employees
        $employees = [];
        $employeeCounter = 1;

        foreach ($organizations as $org) {
            // Generate 20-30 employees per organization
            $employeeCount = rand(20, 30);
            
            for ($i = 0; $i < $employeeCount; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = $firstName . ' ' . $lastName;
                
                // Generate employee ID with organization code
                $employeeId = $org['code'] . str_pad($employeeCounter, 3, '0', STR_PAD_LEFT);
                
                // Random hire date within last 5 years
                $hireDate = Carbon::now()->subDays(rand(30, 1825));
                
                // 98% active employees (as shown in dashboard)
                $status = (rand(1, 100) <= 98) ? 'active' : 'inactive';
                
                $employees[] = [
                    'employee_id' => $employeeId,
                    'name' => $fullName,
                    'email' => strtolower(str_replace(' ', '.', $fullName)) . '@gapura.com',
                    'phone' => '08' . rand(1000000000, 9999999999),
                    'position' => $positions[array_rand($positions)],
                    'organization_id' => $org['id'],
                    'hire_date' => $hireDate,
                    'salary' => rand(3000000, 15000000),
                    'status' => $status,
                    'address' => 'Jl. Bandara No. ' . rand(1, 999) . ', Jakarta',
                    'birth_date' => Carbon::now()->subYears(rand(22, 55))->subDays(rand(1, 365)),
                    'gender' => (rand(0, 1) ? 'male' : 'female'),
                    'created_at' => $hireDate,
                    'updated_at' => now(),
                ];
                
                $employeeCounter++;
            }
        }

        // Insert employees in chunks for better performance
        $chunks = array_chunk($employees, 50);
        foreach ($chunks as $chunk) {
            DB::table('employees')->insert($chunk);
        }

        // Create users for authentication (Super Admin, Admin, Staff)
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
                'name' => 'Manager IT',
                'email' => 'manager.it@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff Operations',
                'email' => 'staff.ops@gapura.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Created ' . count($employees) . ' employees across ' . count($organizations) . ' organizations');
        $this->command->info('Created ' . count($users) . ' users with different roles');
        $this->command->info('Login credentials:');
        $this->command->info('Super Admin: admin@gapura.com / password');
        $this->command->info('Admin: manager.it@gapura.com / password');
        $this->command->info('Staff: staff.ops@gapura.com / password');
    }
}