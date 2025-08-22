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
     */
    public function run(): void
    {
        Log::info('UpdateEmployeeUnitsSeeder: Starting employee units update');

        // Mapping unit organisasi ke unit code
        $unitMapping = [
            'EGM' => 'EGM',
            'GM' => 'GM',
            'Airside' => ['MO', 'ME'],
            'Landside' => ['MF', 'MS'],
            'Back Office' => ['MU', 'MK'],
            'SSQC' => 'MQ',
            'Ancillary' => 'MB'
        ];

        try {
            // Get all employees yang belum memiliki unit_id
            $employees = Employee::whereNull('unit_id')
                ->orWhereNull('sub_unit_id')
                ->get();

            Log::info('UpdateEmployeeUnitsSeeder: Found employees to update', [
                'total_employees' => $employees->count()
            ]);

            $updatedCount = 0;
            $errorCount = 0;

            foreach ($employees as $employee) {
                try {
                    $updated = $this->updateEmployeeUnits($employee);
                    if ($updated) {
                        $updatedCount++;
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
                'error_count' => $errorCount
            ]);

            $this->command->info("UpdateEmployeeUnitsSeeder completed!");
            $this->command->info("Processed: {$employees->count()} employees");
            $this->command->info("Updated: {$updatedCount} employees");
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
     * Update individual employee with proper unit_id and sub_unit_id
     */
    private function updateEmployeeUnits(Employee $employee)
    {
        try {
            // Mapping berdasarkan unit_organisasi yang sudah ada di employee
            $unitCode = $this->getUnitCodeFromUnitOrganisasi($employee->unit_organisasi);
            
            if (!$unitCode) {
                Log::warning('UpdateEmployeeUnitsSeeder: No unit code found', [
                    'employee_id' => $employee->id,
                    'unit_organisasi' => $employee->unit_organisasi
                ]);
                return false;
            }

            // Cari unit berdasarkan code
            $unit = Unit::where('code', $unitCode)->first();
            
            if (!$unit) {
                Log::warning('UpdateEmployeeUnitsSeeder: Unit not found', [
                    'employee_id' => $employee->id,
                    'unit_code' => $unitCode,
                    'unit_organisasi' => $employee->unit_organisasi
                ]);
                return false;
            }

            // Update unit_id
            $employee->unit_id = $unit->id;

            // Cari sub_unit jika ada berdasarkan jabatan atau logic lain
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
                    'unit_code' => $unitCode
                ]);
            }

            // Save employee
            $employee->save();

            Log::info('UpdateEmployeeUnitsSeeder: Employee updated successfully', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->nama_lengkap,
                'unit_id' => $employee->unit_id,
                'sub_unit_id' => $employee->sub_unit_id,
                'unit_organisasi' => $employee->unit_organisasi
            ]);

            return true;

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
     * Get unit code from unit organisasi
     */
    private function getUnitCodeFromUnitOrganisasi($unitOrganisasi)
    {
        // Mapping untuk menentukan unit code berdasarkan unit organisasi
        $mapping = [
            'EGM' => 'EGM',
            'GM' => 'GM',
            'Airside' => 'MO', // Default ke MO untuk Airside
            'Landside' => 'MF', // Default ke MF untuk Landside 
            'Back Office' => 'MU', // Default ke MU untuk Back Office
            'SSQC' => 'MQ',
            'Ancillary' => 'MB'
        ];

        return $mapping[$unitOrganisasi] ?? null;
    }

    /**
     * Find appropriate sub unit for employee
     */
    private function findSubUnitForEmployee(Employee $employee, Unit $unit)
    {
        try {
            // Get all sub units for this unit
            $subUnits = SubUnit::where('unit_id', $unit->id)->get();

            if ($subUnits->isEmpty()) {
                return null; // Unit yang tidak memiliki sub unit (EGM, GM)
            }

            // Untuk sekarang, kita ambil sub unit pertama sebagai default
            // Bisa diimprove nanti dengan logic yang lebih complex berdasarkan jabatan
            $defaultSubUnit = $subUnits->first();

            Log::debug('UpdateEmployeeUnitsSeeder: Using default sub unit', [
                'employee_id' => $employee->id,
                'unit_id' => $unit->id,
                'sub_unit_id' => $defaultSubUnit->id,
                'sub_unit_name' => $defaultSubUnit->name,
                'total_sub_units' => $subUnits->count()
            ]);

            return $defaultSubUnit;

        } catch (\Exception $e) {
            Log::error('UpdateEmployeeUnitsSeeder: Error finding sub unit', [
                'employee_id' => $employee->id,
                'unit_id' => $unit->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }
}