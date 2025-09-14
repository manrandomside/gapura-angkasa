<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\SubUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateEmployeeUnitsSeeder extends Seeder
{
    /**
     * Update existing employees with proper unit_id and sub_unit_id
     * GAPURA ANGKASA Airport SDM System
     * UPDATED: Sesuai dengan perubahan struktur database unit (name = code)
     */
    public function run(): void
    {
        Log::info('UpdateEmployeeUnitsSeeder: Starting employee units update (FIXED VERSION)');

        try {
            // Get all employees yang perlu diupdate
            $employees = Employee::whereNull('unit_id')
                ->orWhereNull('sub_unit_id')
                ->get();

            Log::info('UpdateEmployeeUnitsSeeder: Found employees to update', [
                'total_employees' => $employees->count()
            ]);

            $updatedCount = 0;
            $errorCount = 0;
            $skippedCount = 0;

            foreach ($employees as $employee) {
                try {
                    $result = $this->updateEmployeeUnits($employee);
                    if ($result === 'updated') {
                        $updatedCount++;
                    } elseif ($result === 'skipped') {
                        $skippedCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('UpdateEmployeeUnitsSeeder: Error updating employee', [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->nama_lengkap,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('UpdateEmployeeUnitsSeeder: Update completed', [
                'total_processed' => $employees->count(),
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount,
                'error_count' => $errorCount
            ]);

            $this->command->info("UpdateEmployeeUnitsSeeder completed!");
            $this->command->info("Processed: {$employees->count()} employees");
            $this->command->info("Updated: {$updatedCount} employees");
            $this->command->info("Skipped: {$skippedCount} employees");
            $this->command->info("Errors: {$errorCount} employees");

        } catch (\Exception $e) {
            Log::error('UpdateEmployeeUnitsSeeder: Fatal error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->command->error("UpdateEmployeeUnitsSeeder failed: " . $e->getMessage());
        }
    }

    /**
     * UPDATED: Update individual employee dengan double strategy approach
     */
    private function updateEmployeeUnits(Employee $employee)
    {
        try {
            Log::debug('UpdateEmployeeUnitsSeeder: Processing employee', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->nama_lengkap,
                'unit_organisasi' => $employee->unit_organisasi,
                'kode_organisasi' => $employee->kode_organisasi,
                'current_unit_id' => $employee->unit_id,
                'current_sub_unit_id' => $employee->sub_unit_id
            ]);

            $unit = null;

            // STRATEGY 1: Gunakan kode_organisasi untuk direct mapping (prioritas utama)
            if ($employee->kode_organisasi) {
                $unitCode = $this->getUnitCodeFromKodeOrganisasi($employee->kode_organisasi);
                if ($unitCode) {
                    $unit = $this->findUnitByCode($unitCode);
                    if ($unit) {
                        Log::debug('UpdateEmployeeUnitsSeeder: Found unit via kode_organisasi', [
                            'employee_id' => $employee->id,
                            'kode_organisasi' => $employee->kode_organisasi,
                            'unit_code' => $unitCode,
                            'unit_id' => $unit->id,
                            'unit_name' => $unit->name
                        ]);
                    }
                }
            }

            // STRATEGY 2: Fallback ke unit_organisasi jika kode_organisasi tidak berhasil
            if (!$unit && $employee->unit_organisasi) {
                $unitCode = $this->getUnitCodeFromUnitOrganisasi($employee->unit_organisasi);
                if ($unitCode) {
                    $unit = $this->findUnitByCode($unitCode);
                    if ($unit) {
                        Log::debug('UpdateEmployeeUnitsSeeder: Found unit via unit_organisasi fallback', [
                            'employee_id' => $employee->id,
                            'unit_organisasi' => $employee->unit_organisasi,
                            'unit_code' => $unitCode,
                            'unit_id' => $unit->id,
                            'unit_name' => $unit->name
                        ]);
                    }
                }
            }

            if (!$unit) {
                Log::warning('UpdateEmployeeUnitsSeeder: No unit found for employee', [
                    'employee_id' => $employee->id,
                    'unit_organisasi' => $employee->unit_organisasi,
                    'kode_organisasi' => $employee->kode_organisasi
                ]);
                return 'skipped';
            }

            // Update unit_id
            $employee->unit_id = $unit->id;

            // Cari dan update sub_unit_id
            $subUnit = $this->findSubUnitForEmployee($employee, $unit);
            
            if ($subUnit) {
                $employee->sub_unit_id = $subUnit->id;
                Log::debug('UpdateEmployeeUnitsSeeder: Found sub unit', [
                    'employee_id' => $employee->id,
                    'sub_unit_id' => $subUnit->id,
                    'sub_unit_name' => $subUnit->name
                ]);
            } else {
                // Untuk unit yang tidak memiliki sub unit (EGM, GM)
                $employee->sub_unit_id = null;
                Log::debug('UpdateEmployeeUnitsSeeder: No sub unit (normal for this unit)', [
                    'employee_id' => $employee->id,
                    'unit_code' => $unit->name
                ]);
            }

            // Save employee
            $employee->save();

            Log::info('UpdateEmployeeUnitsSeeder: Employee updated successfully', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->nama_lengkap,
                'unit_id' => $employee->unit_id,
                'unit_code' => $unit->name,
                'sub_unit_id' => $employee->sub_unit_id,
                'unit_organisasi' => $employee->unit_organisasi,
                'kode_organisasi' => $employee->kode_organisasi
            ]);

            return 'updated';

        } catch (\Exception $e) {
            Log::error('UpdateEmployeeUnitsSeeder: Error updating employee units', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * UPDATED: Get unit code dari kode_organisasi (strategi utama)
     */
    private function getUnitCodeFromKodeOrganisasi($kodeOrganisasi)
    {
        // Direct mapping dari kode_organisasi ke unit code
        $mapping = [
            'EGM' => 'EGM',
            'GM' => 'GM', 
            'MO' => 'MO',   // Movement Operations
            'ME' => 'ME',   // Maintenance Equipment  
            'MF' => 'MF',   // Movement Facilitation
            'MS' => 'MS',   // Maintenance System
            'MU' => 'MU',   // Management Unit
            'MK' => 'MK',   // Management Keuangan
            'MQ' => 'MQ',   // Management Quality (SSQC)
            'MB' => 'MB'    // Management Business (Ancillary)
        ];

        $result = $mapping[$kodeOrganisasi] ?? null;
        
        Log::debug('UpdateEmployeeUnitsSeeder: Kode organisasi mapping', [
            'kode_organisasi' => $kodeOrganisasi,
            'mapped_unit_code' => $result
        ]);

        return $result;
    }

    /**
     * UPDATED: Get unit code dari unit_organisasi (fallback strategy)
     */
    private function getUnitCodeFromUnitOrganisasi($unitOrganisasi)
    {
        // Mapping unit organisasi ke default unit code
        $mapping = [
            'EGM' => 'EGM',
            'GM' => 'GM',
            'Airside' => 'MO',      // Default ke Movement Operations untuk Airside
            'Landside' => 'MF',     // Default ke Movement Facilitation untuk Landside 
            'Back Office' => 'MU',  // Default ke Management Unit untuk Back Office
            'SSQC' => 'MQ',         // Management Quality
            'Ancillary' => 'MB'     // Management Business
        ];

        $result = $mapping[$unitOrganisasi] ?? null;
        
        Log::debug('UpdateEmployeeUnitsSeeder: Unit organisasi mapping', [
            'unit_organisasi' => $unitOrganisasi,
            'mapped_unit_code' => $result
        ]);

        return $result;
    }

    /**
     * UPDATED: Find unit by code dengan enhanced debugging
     */
    private function findUnitByCode($unitCode)
    {
        if (!$unitCode) {
            return null;
        }

        // UPDATED: Sekarang unit.name berisi kode unit yang sama dengan unit.code
        // Jadi bisa pakai name atau code, keduanya berisi nilai yang sama
        $unit = Unit::where('code', $unitCode)
                   ->orWhere('name', $unitCode)
                   ->first();

        if ($unit) {
            Log::debug('UpdateEmployeeUnitsSeeder: Unit found', [
                'search_code' => $unitCode,
                'found_unit_id' => $unit->id,
                'found_unit_code' => $unit->code,
                'found_unit_name' => $unit->name,
                'unit_organisasi' => $unit->unit_organisasi
            ]);
        } else {
            Log::warning('UpdateEmployeeUnitsSeeder: Unit not found', [
                'search_code' => $unitCode
            ]);

            // Debug: List semua units yang tersedia
            $availableUnits = Unit::select('id', 'name', 'code', 'unit_organisasi')->get();
            Log::debug('UpdateEmployeeUnitsSeeder: Available units', [
                'units' => $availableUnits->toArray()
            ]);
        }

        return $unit;
    }

    /**
     * UPDATED: Enhanced sub unit finding dengan better logic
     */
    private function findSubUnitForEmployee(Employee $employee, Unit $unit)
    {
        try {
            // Get all sub units for this unit
            $subUnits = SubUnit::where('unit_id', $unit->id)
                               ->where('is_active', true)
                               ->get();

            if ($subUnits->isEmpty()) {
                Log::debug('UpdateEmployeeUnitsSeeder: No sub units found for unit', [
                    'unit_id' => $unit->id,
                    'unit_code' => $unit->name
                ]);
                return null; // Unit yang tidak memiliki sub unit (EGM, GM)
            }

            // TODO: Improved logic berdasarkan jabatan atau criteria lain
            // Untuk saat ini, gunakan sub unit pertama sebagai default
            $defaultSubUnit = $subUnits->first();

            Log::debug('UpdateEmployeeUnitsSeeder: Using default sub unit', [
                'employee_id' => $employee->id,
                'unit_id' => $unit->id,
                'unit_code' => $unit->name,
                'sub_unit_id' => $defaultSubUnit->id,
                'sub_unit_name' => $defaultSubUnit->name,
                'total_sub_units' => $subUnits->count(),
                'available_sub_units' => $subUnits->pluck('name')->toArray()
            ]);

            return $defaultSubUnit;

        } catch (\Exception $e) {
            Log::error('UpdateEmployeeUnitsSeeder: Error finding sub unit', [
                'employee_id' => $employee->id,
                'unit_id' => $unit->id,
                'unit_code' => $unit->name ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * NEW: Helper method untuk debug dan validasi
     */
    private function validateDatabaseStructure()
    {
        try {
            $this->command->info("Validating database structure...");

            // Check units table
            $unitsCount = Unit::count();
            $this->command->info("Total units: {$unitsCount}");

            // Check if unit.name = unit.code setelah perubahan UnitSeeder
            $inconsistentUnits = Unit::whereColumn('name', '!=', 'code')->get();
            if ($inconsistentUnits->count() > 0) {
                $this->command->warn("Found units with inconsistent name/code:");
                foreach ($inconsistentUnits as $unit) {
                    $this->command->warn("  - ID: {$unit->id}, Name: '{$unit->name}', Code: '{$unit->code}'");
                }
            } else {
                $this->command->info("All units have consistent name/code values");
            }

            // Check employees without proper unit assignment
            $employeesWithoutUnit = Employee::whereNull('unit_id')->count();
            $employeesWithoutSubUnit = Employee::whereNull('sub_unit_id')->count();

            $this->command->info("Employees without unit_id: {$employeesWithoutUnit}");
            $this->command->info("Employees without sub_unit_id: {$employeesWithoutSubUnit}");

        } catch (\Exception $e) {
            $this->command->error("Database validation failed: " . $e->getMessage());
        }
    }
}