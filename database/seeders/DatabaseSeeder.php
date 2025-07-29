<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Organizations
        $organizations = [
            [
                'name' => 'Terminal 1',
                'code' => 'T1',
                'description' => 'Terminal 1 Bandara Internasional',
                'location' => 'Terminal 1',
                'status' => 'active'
            ],
            [
                'name' => 'Terminal 2',
                'code' => 'T2', 
                'description' => 'Terminal 2 Bandara Internasional',
                'location' => 'Terminal 2',
                'status' => 'active'
            ],
            [
                'name' => 'Terminal 3',
                'code' => 'T3',
                'description' => 'Terminal 3 Bandara Internasional', 
                'location' => 'Terminal 3',
                'status' => 'active'
            ],
            [
                'name' => 'Ground Handling',
                'code' => 'GH',
                'description' => 'Layanan Ground Handling',
                'location' => 'Area Apron',
                'status' => 'active'
            ],
            [
                'name' => 'Security',
                'code' => 'SEC',
                'description' => 'Divisi Keamanan Bandara',
                'location' => 'Seluruh Area',
                'status' => 'active'
            ],
            [
                'name' => 'Air Traffic Control',
                'code' => 'ATC',
                'description' => 'Kontrol Lalu Lintas Udara',
                'location' => 'Control Tower',
                'status' => 'active'
            ],
            [
                'name' => 'Maintenance',
                'code' => 'MTN',
                'description' => 'Pemeliharaan Fasilitas',
                'location' => 'Workshop',
                'status' => 'active'
            ],
            [
                'name' => 'Administration',
                'code' => 'ADM',
                'description' => 'Administrasi dan HR',
                'location' => 'Kantor Pusat',
                'status' => 'active'
            ]
        ];

        foreach ($organizations as $org) {
            Organization::create($org);
        }

        // Buat Employees
        $employees = [
            // Super Admin
            [
                'employee_id' => 'GA001',
                'name' => 'GusDek',
                'email' => 'admin@gapura.com',
                'phone' => '081234567890',
                'position' => 'System Administrator',
                'department' => 'IT',
                'hire_date' => '2020-01-15',
                'status' => 'active',
                'role' => 'super_admin',
                'organization_id' => 8 // Administration
            ],
            // Admin T1
            [
                'employee_id' => 'GA002',
                'name' => 'Ahmad Santoso',
                'email' => 'ahmad@gapura.com',
                'phone' => '081234567891',
                'position' => 'Terminal Manager',
                'department' => 'Operations',
                'hire_date' => '2020-03-01',
                'status' => 'active',
                'role' => 'admin',
                'organization_id' => 1 // Terminal 1
            ],
            // Admin T2
            [
                'employee_id' => 'GA003',
                'name' => 'Siti Rahayu',
                'email' => 'siti@gapura.com',
                'phone' => '081234567892',
                'position' => 'Terminal Manager',
                'department' => 'Operations',
                'hire_date' => '2020-03-15',
                'status' => 'active',
                'role' => 'admin',
                'organization_id' => 2 // Terminal 2
            ],
            // Staff Terminal 1
            [
                'employee_id' => 'GA004',
                'name' => 'Budi Prasetyo',
                'email' => 'budi@gapura.com',
                'phone' => '081234567893',
                'position' => 'Customer Service',
                'department' => 'Customer Relations',
                'hire_date' => '2021-01-10',
                'status' => 'active',
                'role' => 'staff',
                'organization_id' => 1
            ],
            [
                'employee_id' => 'GA005',
                'name' => 'Dewi Lestari',
                'email' => 'dewi@gapura.com',
                'phone' => '081234567894',
                'position' => 'Check-in Agent',
                'department' => 'Ground Services',
                'hire_date' => '2021-02-15',
                'status' => 'active',
                'role' => 'staff',
                'organization_id' => 1
            ],
            // Staff Terminal 2
            [
                'employee_id' => 'GA006',
                'name' => 'Eko Nugroho',
                'email' => 'eko@gapura.com',
                'phone' => '081234567895',
                'position' => 'Baggage Handler',
                'department' => 'Ground Handling',
                'hire_date' => '2021-03-01',
                'status' => 'active',
                'role' => 'staff',
                'organization_id' => 2
            ],
            [
                'employee_id' => 'GA007',
                'name' => 'Rina Sari',
                'email' => 'rina@gapura.com',
                'phone' => '081234567896',
                'position' => 'Information Desk',
                'department' => 'Customer Relations',
                'hire_date' => '2021-04-10',
                'status' => 'active',
                'role' => 'staff',
                'organization_id' => 2
            ],
            // Staff Terminal 3
            [
                'employee_id' => 'GA008',
                'name' => 'Agus Wijaya',
                'email' => 'agus@gapura.com',
                'phone' => '081234567897',
                'position' => 'Security Officer',
                'department' => 'Security',
                'hire_date' => '2021-05-15',
                'status' => 'active',
                'role' => 'staff',
                'organization_id' => 5
            ],
            // Tambahan staff untuk mencapai 202 total
        ];

        // Generate lebih banyak staff untuk mencapai 202 total
        $positions = ['Customer Service', 'Security Officer', 'Maintenance Staff', 'Ground Handler', 'Check-in Agent', 'Baggage Handler'];
        $departments = ['Customer Relations', 'Security', 'Maintenance', 'Ground Handling', 'Operations'];
        
        for ($i = 9; $i <= 202; $i++) {
            $employees[] = [
                'employee_id' => sprintf('GA%03d', $i),
                'name' => 'Karyawan ' . $i,
                'email' => 'karyawan' . $i . '@gapura.com',
                'phone' => '0812345678' . sprintf('%02d', ($i % 100)),
                'position' => $positions[array_rand($positions)],
                'department' => $departments[array_rand($departments)],
                'hire_date' => '2021-0' . (($i % 9) + 1) . '-' . sprintf('%02d', ($i % 28) + 1),
                'status' => ($i <= 195) ? 'active' : (($i % 2 == 0) ? 'inactive' : 'active'),
                'role' => 'staff',
                'organization_id' => (($i % 8) + 1)
            ];
        }

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}