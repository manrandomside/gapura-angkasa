<?php

namespace App\Observers;

use App\Models\Employee;
use Carbon\Carbon;

class EmployeeObserver
{
    /**
     * Calculate masa kerja automatically
     */
    private function calculateMasaKerja($tmtMulaiKerja, $tmtBerakhirKerja = null)
    {
        if (!$tmtMulaiKerja || empty($tmtMulaiKerja)) {
            return "-";
        }

        try {
            $startDate = Carbon::parse($tmtMulaiKerja);
            $endDate = $tmtBerakhirKerja ? Carbon::parse($tmtBerakhirKerja) : Carbon::now('Asia/Makassar');

            if ($endDate < $startDate) {
                return "Tanggal berakhir sebelum tanggal mulai";
            }

            $interval = $startDate->diff($endDate);
            $years = $interval->y;
            $months = $interval->m;
            $days = $interval->d;

            if ($years > 0 && $months > 0) {
                return "{$years} tahun {$months} bulan";
            } else if ($years > 0) {
                return "{$years} tahun";
            } else if ($months > 0) {
                return "{$months} bulan";
            } else {
                return $days > 0 ? "Kurang dari 1 bulan" : "Belum ada masa kerja";
            }
        } catch (\Exception $e) {
            return "Error dalam perhitungan";
        }
    }

    /**
     * Handle the Employee "creating" event.
     */
    public function creating(Employee $employee)
    {
        if ($employee->tmt_mulai_kerja) {
            $employee->masa_kerja = $this->calculateMasaKerja(
                $employee->tmt_mulai_kerja,
                $employee->tmt_berakhir_kerja
            );
        }
    }

    /**
     * Handle the Employee "updating" event.
     */
    public function updating(Employee $employee)
    {
        if ($employee->tmt_mulai_kerja) {
            $employee->masa_kerja = $this->calculateMasaKerja(
                $employee->tmt_mulai_kerja,
                $employee->tmt_berakhir_kerja
            );
        }
    }
}