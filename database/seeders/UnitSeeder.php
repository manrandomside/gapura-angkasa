<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\SubUnit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed units dan sub units untuk GAPURA ANGKASA SDM System
     */
    public function run(): void
    {
        // Data untuk Airside (lengkap sesuai permintaan)
        $airsideUnits = [
            [
                'name' => 'MO',
                'code' => 'MO',
                'unit_organisasi' => 'Airside',
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
                'unit_organisasi' => 'Airside',
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

        // Create Airside units dan sub units
        foreach ($airsideUnits as $unitData) {
            $unit = Unit::create([
                'name' => $unitData['name'],
                'code' => $unitData['code'],
                'unit_organisasi' => $unitData['unit_organisasi'],
                'description' => $unitData['description'],
                'is_active' => true
            ]);

            foreach ($unitData['sub_units'] as $subUnitName) {
                SubUnit::create([
                    'name' => $subUnitName,
                    'code' => strtoupper(str_replace([' ', '/'], ['_', '_'], $subUnitName)),
                    'unit_id' => $unit->id,
                    'description' => $subUnitName . ' - ' . $unitData['unit_organisasi'],
                    'is_active' => true
                ]);
            }
        }

        // Placeholder untuk unit organisasi lainnya (akan diisi kemudian)
        $otherUnitOrganisasi = ['EGM', 'GM', 'Landside', 'Back Office', 'SSQC', 'Ancillary'];
        
        foreach ($otherUnitOrganisasi as $unitOrg) {
            Unit::create([
                'name' => 'Placeholder Unit',
                'code' => 'PH_' . strtoupper(str_replace(' ', '_', $unitOrg)),
                'unit_organisasi' => $unitOrg,
                'description' => 'Placeholder unit untuk ' . $unitOrg . ' - akan diupdate kemudian',
                'is_active' => false // Set inactive karena placeholder
            ]);
        }

        $this->command->info('Units dan Sub Units berhasil di-seed!');
        $this->command->info('Airside units (MO & ME) dengan sub units lengkap telah dibuat.');
        $this->command->info('Unit organisasi lainnya dibuat sebagai placeholder (inactive).');
    }
}