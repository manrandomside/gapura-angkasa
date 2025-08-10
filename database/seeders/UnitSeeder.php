<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\SubUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed lengkap untuk semua Unit Organisasi GAPURA ANGKASA SDM System
     * Struktur: Unit Organisasi -> Unit -> Sub Unit (parent-child)
     */
    public function run(): void
    {
        $this->command->info('Seeding complete Unit Organisasi structure for GAPURA ANGKASA...');
        
        // Clear existing data safely (handle foreign key constraints)
        $this->clearExistingData();
        
        // 1. EGM - Unit: EGM, Sub Unit: tidak ada
        $this->createUnitWithoutSubUnits('EGM', 'EGM', 'Executive General Manager');
        
        // 2. GM - Unit: GM, Sub Unit: tidak ada  
        $this->createUnitWithoutSubUnits('GM', 'GM', 'General Manager');
        
        // 3. Airside - Unit: MO dan ME dengan sub units
        $this->createAirsideUnits();
        
        // 4. Landside - Unit: MF dan MS dengan sub units
        $this->createLandsideUnits();
        
        // 5. Back Office - Unit: MU dan MK dengan sub units
        $this->createBackOfficeUnits();
        
        // 6. SSQC - Unit: MQ dengan sub units
        $this->createSSQCUnits();
        
        // 7. Ancillary - Unit: MB dengan sub units
        $this->createAncillaryUnits();
        
        $this->displaySummary();
    }
    
    /**
     * Clear existing data safely dengan menangani foreign key constraints
     */
    private function clearExistingData()
    {
        $this->command->info('Clearing existing unit data...');
        
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Clear tables
            SubUnit::truncate();
            Unit::truncate();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->info('✓ Existing data cleared successfully');
            
        } catch (\Exception $e) {
            // Fallback: delete instead of truncate
            $this->command->warn('Truncate failed, using delete method...');
            
            // Update employees to remove references
            DB::table('employees')->whereNotNull('sub_unit_id')->update(['sub_unit_id' => null]);
            DB::table('employees')->whereNotNull('unit_id')->update(['unit_id' => null]);
            
            // Delete data
            SubUnit::query()->delete();
            Unit::query()->delete();
            
            // Reset auto increment
            DB::statement('ALTER TABLE sub_units AUTO_INCREMENT = 1;');
            DB::statement('ALTER TABLE units AUTO_INCREMENT = 1;');
            
            $this->command->info('✓ Existing data cleared using fallback method');
        }
    }
    
    /**
     * Create unit tanpa sub unit
     */
    private function createUnitWithoutSubUnits($unitOrganisasi, $unitName, $description)
    {
        Unit::create([
            'name' => $unitName,
            'code' => $unitName,
            'unit_organisasi' => $unitOrganisasi,
            'description' => $description,
            'is_active' => true
        ]);
        
        $this->command->info("✓ Created {$unitOrganisasi} unit: {$unitName}");
    }
    
    /**
     * Create Airside units dengan sub units
     */
    private function createAirsideUnits()
    {
        $airsideUnits = [
            [
                'name' => 'MO',
                'code' => 'MO',
                'description' => 'Movement Operations',
                'sub_units' => [
                    'Flops',
                    'Depco',
                    'Ramp',
                    'Load Control',
                    'Load Master',
                    'ULD Control',
                    'Cargo Import',
                    'Cargo Export'
                ]
            ],
            [
                'name' => 'ME',
                'code' => 'ME',
                'description' => 'Maintenance Equipment',
                'sub_units' => [
                    'GSE Operator P/B',
                    'GSE Operator A/C',
                    'GSE Maintenance',
                    'BTT Operator',
                    'Line Maintenance'
                ]
            ]
        ];
        
        foreach ($airsideUnits as $unitData) {
            $unit = Unit::create([
                'name' => $unitData['name'],
                'code' => $unitData['code'],
                'unit_organisasi' => 'Airside',
                'description' => $unitData['description'],
                'is_active' => true
            ]);
            
            foreach ($unitData['sub_units'] as $subUnitName) {
                SubUnit::create([
                    'name' => $subUnitName,
                    'code' => $this->generateSubUnitCode($subUnitName),
                    'unit_id' => $unit->id,
                    'description' => $subUnitName . ' - Airside ' . $unitData['name'],
                    'is_active' => true
                ]);
            }
            
            $subUnitCount = count($unitData['sub_units']);
            $this->command->info("✓ Created Airside unit: {$unitData['name']} with {$subUnitCount} sub units");
        }
    }
    
    /**
     * Create Landside units dengan sub units
     */
    private function createLandsideUnits()
    {
        $landsideUnits = [
            [
                'name' => 'MF',
                'code' => 'MF',
                'description' => 'Movement Flight',
                'sub_units' => [
                    'KLM',
                    'Qatar',
                    'Korean Air',
                    'Vietjet Air',
                    'Scoot',
                    'Thai Airways',
                    'China Airlines',
                    'China Southern',
                    'Indigo',
                    'Xiamen Air',
                    'Aero Dili',
                    'Jeju Air',
                    'Hongkong Airlines',
                    'Air Busan',
                    'Vietnam Airlines',
                    'Sichuan Airlines',
                    'Aeroflot',
                    'Charter Flight'
                ]
            ],
            [
                'name' => 'MS',
                'code' => 'MS',
                'description' => 'Movement Service',
                'sub_units' => [
                    'MPGA',
                    'QG',
                    'IP'
                ]
            ]
        ];
        
        foreach ($landsideUnits as $unitData) {
            $unit = Unit::create([
                'name' => $unitData['name'],
                'code' => $unitData['code'],
                'unit_organisasi' => 'Landside',
                'description' => $unitData['description'],
                'is_active' => true
            ]);
            
            foreach ($unitData['sub_units'] as $subUnitName) {
                SubUnit::create([
                    'name' => $subUnitName,
                    'code' => $this->generateSubUnitCode($subUnitName),
                    'unit_id' => $unit->id,
                    'description' => $subUnitName . ' - Landside ' . $unitData['name'],
                    'is_active' => true
                ]);
            }
            
            $subUnitCount = count($unitData['sub_units']);
            $this->command->info("✓ Created Landside unit: {$unitData['name']} with {$subUnitCount} sub units");
        }
    }
    
    /**
     * Create Back Office units dengan sub units
     */
    private function createBackOfficeUnits()
    {
        $backOfficeUnits = [
            [
                'name' => 'MU',
                'code' => 'MU',
                'description' => 'Management Unit',
                'sub_units' => [
                    'Human Resources & General Affair',
                    'Fasilitas & Sarana'
                ]
            ],
            [
                'name' => 'MK',
                'code' => 'MK',
                'description' => 'Management Keuangan',
                'sub_units' => [
                    'Accounting',
                    'Budgeting',
                    'Treassury',
                    'Tax'
                ]
            ]
        ];
        
        foreach ($backOfficeUnits as $unitData) {
            $unit = Unit::create([
                'name' => $unitData['name'],
                'code' => $unitData['code'],
                'unit_organisasi' => 'Back Office',
                'description' => $unitData['description'],
                'is_active' => true
            ]);
            
            foreach ($unitData['sub_units'] as $subUnitName) {
                SubUnit::create([
                    'name' => $subUnitName,
                    'code' => $this->generateSubUnitCode($subUnitName),
                    'unit_id' => $unit->id,
                    'description' => $subUnitName . ' - Back Office ' . $unitData['name'],
                    'is_active' => true
                ]);
            }
            
            $subUnitCount = count($unitData['sub_units']);
            $this->command->info("✓ Created Back Office unit: {$unitData['name']} with {$subUnitCount} sub units");
        }
    }
    
    /**
     * Create SSQC units dengan sub units
     */
    private function createSSQCUnits()
    {
        $ssqcUnits = [
            [
                'name' => 'MQ',
                'code' => 'MQ',
                'description' => 'Management Quality',
                'sub_units' => [
                    'Avsec',
                    'Safety Quality Control'
                ]
            ]
        ];
        
        foreach ($ssqcUnits as $unitData) {
            $unit = Unit::create([
                'name' => $unitData['name'],
                'code' => $unitData['code'],
                'unit_organisasi' => 'SSQC',
                'description' => $unitData['description'],
                'is_active' => true
            ]);
            
            foreach ($unitData['sub_units'] as $subUnitName) {
                SubUnit::create([
                    'name' => $subUnitName,
                    'code' => $this->generateSubUnitCode($subUnitName),
                    'unit_id' => $unit->id,
                    'description' => $subUnitName . ' - SSQC ' . $unitData['name'],
                    'is_active' => true
                ]);
            }
            
            $subUnitCount = count($unitData['sub_units']);
            $this->command->info("✓ Created SSQC unit: {$unitData['name']} with {$subUnitCount} sub units");
        }
    }
    
    /**
     * Create Ancillary units dengan sub units
     */
    private function createAncillaryUnits()
    {
        $ancillaryUnits = [
            [
                'name' => 'MB',
                'code' => 'MB',
                'description' => 'Management Business',
                'sub_units' => [
                    'GPL',
                    'GLC',
                    'Joumpa'
                ]
            ]
        ];
        
        foreach ($ancillaryUnits as $unitData) {
            $unit = Unit::create([
                'name' => $unitData['name'],
                'code' => $unitData['code'],
                'unit_organisasi' => 'Ancillary',
                'description' => $unitData['description'],
                'is_active' => true
            ]);
            
            foreach ($unitData['sub_units'] as $subUnitName) {
                SubUnit::create([
                    'name' => $subUnitName,
                    'code' => $this->generateSubUnitCode($subUnitName),
                    'unit_id' => $unit->id,
                    'description' => $subUnitName . ' - Ancillary ' . $unitData['name'],
                    'is_active' => true
                ]);
            }
            
            $subUnitCount = count($unitData['sub_units']);
            $this->command->info("✓ Created Ancillary unit: {$unitData['name']} with {$subUnitCount} sub units");
        }
    }
    
    /**
     * Generate code untuk sub unit
     */
    private function generateSubUnitCode($subUnitName)
    {
        return strtoupper(str_replace([' ', '/', '&'], ['_', '_', '_'], $subUnitName));
    }
    
    /**
     * Display summary hasil seeding
     */
    private function displaySummary()
    {
        $totalUnits = Unit::count();
        $totalSubUnits = SubUnit::count();
        $unitOrganisasiCount = Unit::distinct('unit_organisasi')->count();
        
        $this->command->info('');
        $this->command->info('================================================');
        $this->command->info('UNIT ORGANISASI SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->info('================================================');
        $this->command->info("Total Unit Organisasi: {$unitOrganisasiCount}");
        $this->command->info("Total Units: {$totalUnits}");
        $this->command->info("Total Sub Units: {$totalSubUnits}");
        $this->command->info('');
        
        // Display breakdown per unit organisasi
        $breakdown = Unit::selectRaw('unit_organisasi, COUNT(*) as unit_count')
            ->groupBy('unit_organisasi')
            ->orderBy('unit_organisasi')
            ->get();
            
        $this->command->info('BREAKDOWN PER UNIT ORGANISASI:');
        foreach ($breakdown as $item) {
            $subUnitCount = SubUnit::whereHas('unit', function($query) use ($item) {
                $query->where('unit_organisasi', $item->unit_organisasi);
            })->count();
            
            $this->command->info("- {$item->unit_organisasi}: {$item->unit_count} units, {$subUnitCount} sub units");
        }
        
        $this->command->info('');
        $this->command->info('Struktur parent-child siap digunakan untuk cascading dropdown!');
    }
}