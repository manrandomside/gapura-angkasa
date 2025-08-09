<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class OrganizationalUnitsHelper
{
    /**
     * Get all unit organisasi (parent level)
     */
    public static function getUnitOrganisasi(): array
    {
        return DB::table('organizations')
            ->where('status', 'active')
            ->whereNotNull('units_structure')
            ->pluck('name')
            ->toArray();
    }

    /**
     * Get units by unit organisasi
     */
    public static function getUnitsByOrganisasi(string $unitOrganisasi): array
    {
        $organization = DB::table('organizations')
            ->where('name', $unitOrganisasi)
            ->where('status', 'active')
            ->first();

        if (!$organization || !$organization->units_structure) {
            return [];
        }

        $structure = json_decode($organization->units_structure, true);
        return array_keys($structure['units'] ?? []);
    }

    /**
     * Get sub units by unit organisasi and unit code
     */
    public static function getSubUnits(string $unitOrganisasi, string $unitCode): array
    {
        $organization = DB::table('organizations')
            ->where('name', $unitOrganisasi)
            ->where('status', 'active')
            ->first();

        if (!$organization || !$organization->units_structure) {
            return [];
        }

        $structure = json_decode($organization->units_structure, true);
        return $structure['units'][$unitCode]['sub_units'] ?? [];
    }

    /**
     * Get complete hierarchy tree for specific organization
     */
    public static function getHierarchyTree(string $unitOrganisasi = null): array
    {
        $query = DB::table('organizations')
            ->where('status', 'active')
            ->whereNotNull('units_structure');

        if ($unitOrganisasi) {
            $query->where('name', $unitOrganisasi);
        }

        $organizations = $query->get();
        
        $tree = [];
        foreach ($organizations as $org) {
            $structure = json_decode($org->units_structure, true);
            $tree[$org->name] = $structure['units'] ?? [];
        }

        return $unitOrganisasi ? ($tree[$unitOrganisasi] ?? []) : $tree;
    }

    /**
     * Check if unit organisasi has sub units
     */
    public static function hasSubUnits(string $unitOrganisasi, string $unitCode): bool
    {
        $subUnits = self::getSubUnits($unitOrganisasi, $unitCode);
        return !empty($subUnits);
    }

    /**
     * Get formatted hierarchy for dropdown/select options
     */
    public static function getFormattedHierarchy(): array
    {
        $tree = self::getHierarchyTree();
        $formatted = [];
        
        foreach ($tree as $unitOrganisasi => $units) {
            $orgItem = [
                'label' => $unitOrganisasi,
                'value' => $unitOrganisasi,
                'type' => 'unit_organisasi',
                'children' => []
            ];
            
            foreach ($units as $unitCode => $unitData) {
                $unitItem = [
                    'label' => $unitCode,
                    'value' => $unitCode,
                    'type' => 'unit',
                    'parent' => $unitOrganisasi,
                    'children' => []
                ];
                
                foreach ($unitData['sub_units'] ?? [] as $subUnit) {
                    $unitItem['children'][] = [
                        'label' => $subUnit,
                        'value' => $subUnit,
                        'type' => 'sub_unit',
                        'parent_org' => $unitOrganisasi,
                        'parent_unit' => $unitCode,
                    ];
                }
                
                $orgItem['children'][] = $unitItem;
            }
            
            $formatted[] = $orgItem;
        }
        
        return $formatted;
    }

    /**
     * Validate if combination exists
     */
    public static function isValidCombination(string $unitOrganisasi, string $unitCode, ?string $subUnit = null): bool
    {
        $units = self::getUnitsByOrganisasi($unitOrganisasi);
        
        if (!in_array($unitCode, $units)) {
            return false;
        }

        if ($subUnit) {
            $subUnits = self::getSubUnits($unitOrganisasi, $unitCode);
            return in_array($subUnit, $subUnits);
        }

        return true;
    }

    /**
     * Get statistics for organizational units
     */
    public static function getStatistics(): array
    {
        $allHierarchy = self::getHierarchyTree();
        
        $totalOrganizations = count($allHierarchy);
        $totalUnits = 0;
        $totalSubUnits = 0;
        
        foreach ($allHierarchy as $units) {
            $totalUnits += count($units);
            foreach ($units as $unitData) {
                $totalSubUnits += count($unitData['sub_units'] ?? []);
            }
        }
        
        return [
            'total_unit_organisasi' => $totalOrganizations,
            'total_units' => $totalUnits,
            'total_sub_units' => $totalSubUnits,
        ];
    }
}