<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\SubUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * UPDATED: Enhanced Unit Seeder dengan konsistensi kode unit 
     * CRITICAL: Field name sekarang berisi kode unit untuk konsistensi sistem
     * Struktur: Unit Organisasi -> Unit -> Sub Unit (parent-child)
     */
    public function run(): void
    {
        Log::info('UnitSeeder: Starting comprehensive unit structure seeding for GAPURA ANGKASA');
        $this->command->info('Starting Unit Organisasi seeding for GAPURA ANGKASA SDM System...');
        
        try {
            // Clear existing data safely (handle foreign key constraints)
            $this->clearExistingData();
            
            // Create unit structure step by step
            $this->createUnitStructure();
            
            // Validate created data
            $this->validateCreatedData();
            
            // Display comprehensive summary
            $this->displaySummary();
            
            Log::info('UnitSeeder: Successfully completed unit structure seeding');
            
        } catch (\Exception $e) {
            Log::error('UnitSeeder: Fatal error during seeding', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->command->error('UnitSeeder failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * FIXED: Clear existing data safely dengan enhanced error handling
     */
    private function clearExistingData()
    {
        $this->command->info('Clearing existing unit data...');
        Log::info('UnitSeeder: Clearing existing data');
        
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Clear sub units first (child records)
            SubUnit::truncate();
            Log::info('UnitSeeder: SubUnits table truncated');
            
            // Clear units (parent records)
            Unit::truncate();
            Log::info('UnitSeeder: Units table truncated');
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->info('✓ Existing data cleared successfully');
            Log::info('UnitSeeder: Existing data cleared using truncate method');
            
        } catch (\Exception $e) {
            // Fallback: delete instead of truncate
            $this->command->warn('Truncate failed, using fallback delete method...');
            Log::warning('UnitSeeder: Truncate failed, using delete method', [
                'error' => $e->getMessage()
            ]);
            
            try {
                // Update employees to remove foreign key references
                DB::table('employees')->whereNotNull('sub_unit_id')->update(['sub_unit_id' => null]);
                DB::table('employees')->whereNotNull('unit_id')->update(['unit_id' => null]);
                Log::info('UnitSeeder: Removed foreign key references from employees');
                
                // Delete data with cascade
                SubUnit::query()->delete();
                Unit::query()->delete();
                
                // Reset auto increment
                DB::statement('ALTER TABLE sub_units AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE units AUTO_INCREMENT = 1;');
                
                $this->command->info('✓ Existing data cleared using fallback method');
                Log::info('UnitSeeder: Existing data cleared using delete method');
                
            } catch (\Exception $fallbackError) {
                Log::error('UnitSeeder: Both truncate and delete methods failed', [
                    'truncate_error' => $e->getMessage(),
                    'delete_error' => $fallbackError->getMessage()
                ]);
                throw $fallbackError;
            }
        }
    }
    
    /**
     * UPDATED: Create complete unit structure dengan konsistensi kode
     */
    private function createUnitStructure()
    {
        $this->command->info('Creating unit structure...');
        
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
    }
    
    /**
     * UPDATED: Create unit tanpa sub unit dengan kode unit konsisten
     */
    private function createUnitWithoutSubUnits($unitOrganisasi, $unitCode, $description)
    {
        try {
            $unit = Unit::create([
                'name' => $unitCode, // UPDATED: Field name sekarang berisi kode unit
                'code' => $unitCode,
                'unit_organisasi' => $unitOrganisasi,
                'description' => $description,
                'is_active' => true
            ]);
            
            Log::info("UnitSeeder: Created unit without sub units", [
                'unit_id' => $unit->id,
                'unit_organisasi' => $unitOrganisasi,
                'unit_name' => $unitCode, // Log menggunakan kode
                'unit_code' => $unitCode
            ]);
            
            $this->command->info("✓ Created {$unitOrganisasi} unit: {$unitCode}");
            
        } catch (\Exception $e) {
            Log::error("UnitSeeder: Failed to create unit {$unitCode}", [
                'unit_organisasi' => $unitOrganisasi,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * UPDATED: Create Airside units dengan kode unit konsisten
     */
    private function createAirsideUnits()
    {
        $airsideUnits = [
            [
                'name' => 'MO', // UPDATED: Name field berisi kode unit
                'code' => 'MO',
                'description' => 'Movement Operations Unit - Airside',
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
                'name' => 'ME', // UPDATED: Name field berisi kode unit
                'code' => 'ME',
                'description' => 'Maintenance Equipment Unit - Airside',
                'sub_units' => [
                    'GSE Operator P/B',
                    'GSE Operator A/C',
                    'GSE Maintenance',
                    'BTT Operator',
                    'Line Maintenance'
                ]
            ]
        ];
        
        $this->createUnitsWithSubUnits('Airside', $airsideUnits);
    }
    
    /**
     * UPDATED: Create Landside units dengan kode unit konsisten
     */
    private function createLandsideUnits()
    {
        $landsideUnits = [
            [
                'name' => 'MF', // UPDATED: Name field berisi kode unit
                'code' => 'MF',
                'description' => 'Movement Flight Unit - Landside',
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
                'name' => 'MS', // UPDATED: Name field berisi kode unit
                'code' => 'MS',
                'description' => 'Movement Service Unit - Landside',
                'sub_units' => [
                    'MPGA',
                    'QG',
                    'IP'
                ]
            ]
        ];
        
        $this->createUnitsWithSubUnits('Landside', $landsideUnits);
    }
    
    /**
     * UPDATED: Create Back Office units dengan kode unit konsisten
     */
    private function createBackOfficeUnits()
    {
        $backOfficeUnits = [
            [
                'name' => 'MU', // UPDATED: Name field berisi kode unit
                'code' => 'MU',
                'description' => 'Management Unit - Back Office',
                'sub_units' => [
                    'Human Resources & General Affair',
                    'Fasilitas & Sarana'
                ]
            ],
            [
                'name' => 'MK', // UPDATED: Name field berisi kode unit
                'code' => 'MK',
                'description' => 'Management Keuangan Unit - Back Office',
                'sub_units' => [
                    'Accounting',
                    'Budgeting',
                    'Treassury',
                    'Tax'
                ]
            ]
        ];
        
        $this->createUnitsWithSubUnits('Back Office', $backOfficeUnits);
    }
    
    /**
     * UPDATED: Create SSQC units dengan kode unit konsisten
     */
    private function createSSQCUnits()
    {
        $ssqcUnits = [
            [
                'name' => 'MQ', // UPDATED: Name field berisi kode unit
                'code' => 'MQ',
                'description' => 'Safety Security Quality Control Unit',
                'sub_units' => [
                    'Avsec',
                    'Safety Quality Control'
                ]
            ]
        ];
        
        $this->createUnitsWithSubUnits('SSQC', $ssqcUnits);
    }
    
    /**
     * UPDATED: Create Ancillary units dengan kode unit konsisten
     */
    private function createAncillaryUnits()
    {
        $ancillaryUnits = [
            [
                'name' => 'MB', // UPDATED: Name field berisi kode unit
                'code' => 'MB',
                'description' => 'Management Business Unit - Ancillary',
                'sub_units' => [
                    'GPL',
                    'GLC',
                    'Joumpa'
                ]
            ]
        ];
        
        $this->createUnitsWithSubUnits('Ancillary', $ancillaryUnits);
    }
    
    /**
     * UPDATED: Helper method dengan kode unit konsisten
     */
    private function createUnitsWithSubUnits($unitOrganisasi, $unitsData)
    {
        foreach ($unitsData as $unitData) {
            try {
                // Create unit dengan name berisi kode unit
                $unit = Unit::create([
                    'name' => $unitData['name'], // Sekarang berisi kode unit
                    'code' => $unitData['code'],
                    'unit_organisasi' => $unitOrganisasi,
                    'description' => $unitData['description'],
                    'is_active' => true
                ]);
                
                Log::info("UnitSeeder: Created unit", [
                    'unit_id' => $unit->id,
                    'unit_organisasi' => $unitOrganisasi,
                    'unit_name' => $unitData['name'], // Log kode unit
                    'unit_code' => $unitData['code']
                ]);
                
                // Create sub units
                $createdSubUnits = 0;
                foreach ($unitData['sub_units'] as $subUnitName) {
                    try {
                        $subUnit = SubUnit::create([
                            'name' => $subUnitName,
                            'code' => $this->generateSubUnitCode($subUnitName),
                            'unit_id' => $unit->id,
                            'description' => $subUnitName . ' - ' . $unitOrganisasi . ' ' . $unitData['name'],
                            'is_active' => true
                        ]);
                        
                        $createdSubUnits++;
                        
                        Log::debug("UnitSeeder: Created sub unit", [
                            'sub_unit_id' => $subUnit->id,
                            'sub_unit_name' => $subUnitName,
                            'unit_id' => $unit->id,
                            'unit_code' => $unitData['name'] // Log kode unit
                        ]);
                        
                    } catch (\Exception $subUnitError) {
                        Log::error("UnitSeeder: Failed to create sub unit", [
                            'sub_unit_name' => $subUnitName,
                            'unit_id' => $unit->id,
                            'error' => $subUnitError->getMessage()
                        ]);
                        // Continue with other sub units instead of failing completely
                    }
                }
                
                $expectedSubUnits = count($unitData['sub_units']);
                $this->command->info("✓ Created {$unitOrganisasi} unit: {$unitData['name']} with {$createdSubUnits}/{$expectedSubUnits} sub units");
                
                if ($createdSubUnits !== $expectedSubUnits) {
                    $this->command->warn("  Warning: Only {$createdSubUnits} of {$expectedSubUnits} sub units created successfully");
                }
                
            } catch (\Exception $unitError) {
                Log::error("UnitSeeder: Failed to create unit", [
                    'unit_organisasi' => $unitOrganisasi,
                    'unit_name' => $unitData['name'],
                    'error' => $unitError->getMessage()
                ]);
                
                $this->command->error("✗ Failed to create {$unitOrganisasi} unit: {$unitData['name']}");
                throw $unitError;
            }
        }
    }
    
    /**
     * FIXED: Generate code untuk sub unit dengan better handling
     */
    private function generateSubUnitCode($subUnitName)
    {
        // Clean and format sub unit name for code
        $code = strtoupper(str_replace([' ', '/', '&', '-', '.'], ['_', '_', '_', '_', '_'], $subUnitName));
        
        // Limit length untuk database compatibility
        if (strlen($code) > 50) {
            $code = substr($code, 0, 50);
        }
        
        return $code;
    }
    
    /**
     * UPDATED: Validate created data dengan konsistensi kode unit
     */
    private function validateCreatedData()
    {
        $this->command->info('Validating created data...');
        Log::info('UnitSeeder: Starting data validation');
        
        $errors = [];
        
        // Validate units count
        $expectedUnits = [
            'EGM' => 1,
            'GM' => 1,
            'Airside' => 2,
            'Landside' => 2,
            'Back Office' => 2,
            'SSQC' => 1,
            'Ancillary' => 1
        ];
        
        foreach ($expectedUnits as $unitOrganisasi => $expectedCount) {
            $actualCount = Unit::where('unit_organisasi', $unitOrganisasi)->count();
            if ($actualCount !== $expectedCount) {
                $errors[] = "Unit Organisasi '{$unitOrganisasi}': expected {$expectedCount} units, got {$actualCount}";
            }
        }
        
        // Validate sub units exist for units that should have them
        $unitsWithoutSubUnits = ['EGM', 'GM'];
        foreach ($unitsWithoutSubUnits as $unitOrganisasi) {
            $subUnitCount = SubUnit::whereHas('unit', function($query) use ($unitOrganisasi) {
                $query->where('unit_organisasi', $unitOrganisasi);
            })->count();
            
            if ($subUnitCount > 0) {
                $errors[] = "Unit Organisasi '{$unitOrganisasi}' should not have sub units, but has {$subUnitCount}";
            }
        }
        
        // Validate sub units exist for units that should have them
        $unitsWithSubUnits = ['Airside', 'Landside', 'Back Office', 'SSQC', 'Ancillary'];
        foreach ($unitsWithSubUnits as $unitOrganisasi) {
            $subUnitCount = SubUnit::whereHas('unit', function($query) use ($unitOrganisasi) {
                $query->where('unit_organisasi', $unitOrganisasi);
            })->count();
            
            if ($subUnitCount === 0) {
                $errors[] = "Unit Organisasi '{$unitOrganisasi}' should have sub units, but has none";
            }
        }
        
        // Check for orphaned sub units
        $orphanedSubUnits = SubUnit::whereDoesntHave('unit')->count();
        if ($orphanedSubUnits > 0) {
            $errors[] = "Found {$orphanedSubUnits} orphaned sub units without parent units";
        }
        
        // UPDATED: Validate bahwa field name berisi kode unit
        $unitsWithInconsistentName = Unit::whereRaw('name != code')->count();
        if ($unitsWithInconsistentName > 0) {
            $errors[] = "Found {$unitsWithInconsistentName} units with inconsistent name and code fields";
        }
        
        if (!empty($errors)) {
            Log::error('UnitSeeder: Data validation failed', ['errors' => $errors]);
            
            $this->command->error('Data validation failed:');
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
            
            throw new \Exception('Unit seeder validation failed: ' . implode('; ', $errors));
        }
        
        $this->command->info('✓ Data validation passed');
        Log::info('UnitSeeder: Data validation passed successfully');
    }
    
    /**
     * UPDATED: Display comprehensive summary dengan unit code consistency
     */
    private function displaySummary()
    {
        $totalUnits = Unit::count();
        $totalSubUnits = SubUnit::count();
        $unitOrganisasiCount = Unit::distinct('unit_organisasi')->count();
        
        $this->command->info('');
        $this->command->info('=================================================================');
        $this->command->info('GAPURA ANGKASA UNIT ORGANISASI SEEDING COMPLETED SUCCESSFULLY!');
        $this->command->info('=================================================================');
        $this->command->info("Total Unit Organisasi: {$unitOrganisasiCount}");
        $this->command->info("Total Units: {$totalUnits}");
        $this->command->info("Total Sub Units: {$totalSubUnits}");
        $this->command->info('');
        
        // Display detailed breakdown per unit organisasi
        $breakdown = Unit::selectRaw('unit_organisasi, COUNT(*) as unit_count')
            ->groupBy('unit_organisasi')
            ->orderBy('unit_organisasi')
            ->get();
            
        $this->command->info('DETAILED BREAKDOWN PER UNIT ORGANISASI:');
        $this->command->info('---------------------------------------------');
        
        foreach ($breakdown as $item) {
            $subUnitCount = SubUnit::whereHas('unit', function($query) use ($item) {
                $query->where('unit_organisasi', $item->unit_organisasi);
            })->count();
            
            $units = Unit::where('unit_organisasi', $item->unit_organisasi)->get();
            $this->command->info("• {$item->unit_organisasi}: {$item->unit_count} units, {$subUnitCount} sub units");
            
            foreach ($units as $unit) {
                $unitSubUnits = SubUnit::where('unit_id', $unit->id)->count();
                $this->command->info("  └─ {$unit->name} ({$unit->code}): {$unitSubUnits} sub units");
            }
        }
        
        $this->command->info('');
        $this->command->info('✓ Unit structure ready with consistent unit codes!');
        $this->command->info('✓ Field name and code consistency established!');
        $this->command->info('✓ Cascading dropdown functionality enabled!');
        $this->command->info('✓ Parent-child relationships established!');
        $this->command->info('');
        
        // Log final summary
        Log::info('UnitSeeder: Seeding completed successfully with unit code consistency', [
            'total_unit_organisasi' => $unitOrganisasiCount,
            'total_units' => $totalUnits,
            'total_sub_units' => $totalSubUnits,
            'breakdown' => $breakdown->toArray(),
            'unit_code_consistency' => 'name field now contains unit codes'
        ]);
    }
}