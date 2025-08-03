<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'nik',
        'nama_lengkap',
        'lokasi_kerja',
        'cabang',
        'status_pegawai',
        'status_kerja',
        'provider',
        'kode_organisasi',
        'unit_organisasi',
        'nama_organisasi',
        'nama_jabatan',
        'jabatan',
        'unit_kerja_kontrak',
        'tmt_mulai_kerja',
        'tmt_mulai_jabatan',
        'tmt_berakhir_jabatan',
        'tmt_berakhir_kerja',
        'masa_kerja_bulan',
        'masa_kerja_tahun',
        'jenis_kelamin',
        'jenis_sepatu',
        'ukuran_sepatu',
        'tempat_lahir',
        'tanggal_lahir',
        'usia',
        'kota_domisili',
        'alamat',
        'pendidikan',
        'pendidikan_terakhir',
        'instansi_pendidikan',
        'jurusan',
        'remarks_pendidikan',
        'tahun_lulus',
        'handphone',
        'no_telepon',
        'email',
        'kategori_karyawan',
        'tmt_pensiun',
        'grade',
        'no_bpjs_kesehatan',
        'no_bpjs_ketenagakerjaan',
        'kelompok_jabatan',
        'kelas_jabatan',
        'weight',
        'height',
        'organization_id',
        'status'
    ];

    protected $casts = [
        'tmt_mulai_kerja' => 'date',
        'tmt_mulai_jabatan' => 'date',
        'tmt_berakhir_jabatan' => 'date',
        'tmt_berakhir_kerja' => 'date',
        'tanggal_lahir' => 'date',
        'tmt_pensiun' => 'date',
        'tahun_lulus' => 'integer',
        'usia' => 'integer',
        'weight' => 'integer',
        'height' => 'integer',
    ];

    /**
     * Database indexing recommendations for optimal pagination performance
     * Run these SQL commands for better performance with large datasets:
     * 
     * ALTER TABLE employees ADD INDEX idx_status (status);
     * ALTER TABLE employees ADD INDEX idx_nama_lengkap (nama_lengkap);
     * ALTER TABLE employees ADD INDEX idx_nip (nip);
     * ALTER TABLE employees ADD INDEX idx_unit_organisasi (unit_organisasi);
     * ALTER TABLE employees ADD INDEX idx_status_pegawai (status_pegawai);
     * ALTER TABLE employees ADD INDEX idx_jenis_kelamin (jenis_kelamin);
     * ALTER TABLE employees ADD INDEX idx_jenis_sepatu (jenis_sepatu);
     * ALTER TABLE employees ADD INDEX idx_ukuran_sepatu (ukuran_sepatu);
     * ALTER TABLE employees ADD INDEX idx_search_composite (nama_lengkap, nip, unit_organisasi);
     */

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Relationship dengan Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // =====================================================
    // SCOPES - OPTIMIZED FOR PAGINATION
    // =====================================================

    /**
     * Scope untuk filter berdasarkan status aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Enhanced Global Search Scope - Optimized untuk pagination
     * Mencari di semua field penting dan mendukung pencarian global
     */
    public function scopeGlobalSearch(Builder $query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        $searchTerm = '%' . $term . '%';
        
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('nama_lengkap', 'like', $searchTerm)
              ->orWhere('nip', 'like', $searchTerm)
              ->orWhere('nik', 'like', $searchTerm)
              ->orWhere('jabatan', 'like', $searchTerm)
              ->orWhere('nama_jabatan', 'like', $searchTerm)
              ->orWhere('unit_organisasi', 'like', $searchTerm)
              ->orWhere('nama_organisasi', 'like', $searchTerm)
              ->orWhere('jenis_sepatu', 'like', $searchTerm)
              ->orWhere('ukuran_sepatu', 'like', $searchTerm)
              ->orWhere('kota_domisili', 'like', $searchTerm)
              ->orWhere('instansi_pendidikan', 'like', $searchTerm)
              ->orWhere('pendidikan_terakhir', 'like', $searchTerm)
              ->orWhere('pendidikan', 'like', $searchTerm)
              ->orWhere('jurusan', 'like', $searchTerm)
              ->orWhere('handphone', 'like', $searchTerm)
              ->orWhere('email', 'like', $searchTerm)
              ->orWhere('tempat_lahir', 'like', $searchTerm)
              ->orWhere('alamat', 'like', $searchTerm);
        });
    }

    /**
     * Scope untuk filter berdasarkan status pegawai
     */
    public function scopeByStatusPegawai(Builder $query, $status)
    {
        if ($status === 'all' || empty($status)) {
            return $query;
        }
        
        return $query->where('status_pegawai', $status);
    }

    /**
     * Scope untuk filter berdasarkan unit organisasi
     */
    public function scopeByUnitOrganisasi(Builder $query, $unit)
    {
        if ($unit === 'all' || empty($unit)) {
            return $query;
        }
        
        return $query->where('unit_organisasi', $unit);
    }

    /**
     * Scope untuk filter berdasarkan jenis kelamin
     */
    public function scopeByJenisKelamin(Builder $query, $gender)
    {
        if ($gender === 'all' || empty($gender)) {
            return $query;
        }
        
        return $query->where('jenis_kelamin', $gender);
    }

    /**
     * Scope untuk filter berdasarkan jenis sepatu
     */
    public function scopeByJenisSepatu(Builder $query, $shoeType)
    {
        if ($shoeType === 'all' || empty($shoeType)) {
            return $query;
        }
        
        return $query->where('jenis_sepatu', $shoeType);
    }

    /**
     * Scope untuk filter berdasarkan ukuran sepatu
     */
    public function scopeByUkuranSepatu(Builder $query, $shoeSize)
    {
        if ($shoeSize === 'all' || empty($shoeSize)) {
            return $query;
        }
        
        return $query->where('ukuran_sepatu', $shoeSize);
    }

    /**
     * Scope untuk filter berdasarkan tahun lulus
     */
    public function scopeByTahunLulus(Builder $query, $year)
    {
        if (empty($year)) {
            return $query;
        }
        
        return $query->where('tahun_lulus', $year);
    }

    /**
     * Scope untuk filter berdasarkan pendidikan
     */
    public function scopeByPendidikan(Builder $query, $education)
    {
        if ($education === 'all' || empty($education)) {
            return $query;
        }
        
        return $query->where(function ($q) use ($education) {
            $q->where('pendidikan_terakhir', $education)
              ->orWhere('pendidikan', $education);
        });
    }

    /**
     * Scope untuk karyawan yang akan pensiun dalam rentang waktu tertentu
     */
    public function scopeUpcomingRetirement(Builder $query, $months = 12)
    {
        $endDate = Carbon::now()->addMonths($months);
        
        return $query->whereNotNull('tmt_pensiun')
                    ->whereBetween('tmt_pensiun', [Carbon::now(), $endDate]);
    }

    /**
     * Scope untuk karyawan yang baru bergabung
     */
    public function scopeRecentHires(Builder $query, $months = 6)
    {
        $startDate = Carbon::now()->subMonths($months);
        
        return $query->where(function ($q) use ($startDate) {
            $q->where('tmt_mulai_kerja', '>=', $startDate)
              ->orWhere('created_at', '>=', $startDate);
        });
    }

    /**
     * Comprehensive filter scope untuk pagination - handles semua filter sekaligus
     */
    public function scopeApplyFilters(Builder $query, array $filters)
    {
        // Global search
        if (!empty($filters['search'])) {
            $query->globalSearch($filters['search']);
        }

        // Status pegawai filter
        if (!empty($filters['status_pegawai'])) {
            $query->byStatusPegawai($filters['status_pegawai']);
        }

        // Unit organisasi filter
        if (!empty($filters['unit_organisasi'])) {
            $query->byUnitOrganisasi($filters['unit_organisasi']);
        }

        // Jenis kelamin filter
        if (!empty($filters['jenis_kelamin'])) {
            $query->byJenisKelamin($filters['jenis_kelamin']);
        }

        // Jenis sepatu filter
        if (!empty($filters['jenis_sepatu'])) {
            $query->byJenisSepatu($filters['jenis_sepatu']);
        }

        // Ukuran sepatu filter
        if (!empty($filters['ukuran_sepatu'])) {
            $query->byUkuranSepatu($filters['ukuran_sepatu']);
        }

        // Pendidikan filter
        if (!empty($filters['pendidikan'])) {
            $query->byPendidikan($filters['pendidikan']);
        }

        return $query;
    }

    // =====================================================
    // ACCESSORS & MUTATORS
    // =====================================================

    /**
     * Accessor untuk mendapatkan nama lengkap dengan format title case
     */
    public function getNamaLengkapFormattedAttribute()
    {
        return ucwords(strtolower($this->nama_lengkap));
    }

    /**
     * Accessor untuk mendapatkan jenis kelamin dalam format lengkap
     */
    public function getJenisKelaminLengkapAttribute()
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Accessor untuk mendapatkan status pegawai dengan format yang lebih rapi
     */
    public function getStatusPegawaiFormattedAttribute()
    {
        return ucwords(strtolower($this->status_pegawai));
    }

    /**
     * Accessor untuk mendapatkan umur berdasarkan tanggal lahir
     */
    public function getUmurAttribute()
    {
        if (!$this->tanggal_lahir) {
            return $this->usia ?? null;
        }
        
        return Carbon::parse($this->tanggal_lahir)->age;
    }

    /**
     * Accessor untuk mendapatkan masa kerja dalam format yang lebih rapi
     */
    public function getMasaKerjaFormattedAttribute()
    {
        if ($this->tmt_mulai_kerja) {
            $diff = Carbon::parse($this->tmt_mulai_kerja)->diff(Carbon::now());
            $years = $diff->y;
            $months = $diff->m;
            
            $result = [];
            if ($years > 0) {
                $result[] = $years . ' tahun';
            }
            if ($months > 0) {
                $result[] = $months . ' bulan';
            }
            
            return !empty($result) ? implode(' ', $result) : '0 bulan';
        }
        
        return $this->masa_kerja_tahun && $this->masa_kerja_bulan 
            ? $this->masa_kerja_tahun . ', ' . $this->masa_kerja_bulan
            : null;
    }

    /**
     * Accessor untuk mendapatkan inisial nama
     */
    public function getInitialsAttribute()
    {
        if (empty($this->nama_lengkap)) {
            return '??';
        }

        $names = explode(' ', $this->nama_lengkap);
        $initials = '';
        
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper($name[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        
        return $initials ?: '??';
    }

    /**
     * Accessor untuk mendapatkan email default berdasarkan nama
     */
    public function getEmailDefaultAttribute()
    {
        if (!empty($this->email)) {
            return $this->email;
        }

        $name = strtolower(str_replace(' ', '.', $this->nama_lengkap));
        $name = preg_replace('/[^a-z0-9.]/', '', $name);
        return $name . '@gapura.com';
    }

    /**
     * Accessor untuk format handphone yang lebih readable
     */
    public function getHandphoneFormattedAttribute()
    {
        if (empty($this->handphone)) {
            return $this->no_telepon ?: '-';
        }

        $phone = $this->handphone;
        
        // Format +628123456789 menjadi +62 812-3456-789
        if (preg_match('/^\+62(\d{3})(\d{4})(\d{3,4})$/', $phone, $matches)) {
            return '+62 ' . $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }
        
        return $phone;
    }

    /**
     * Accessor untuk status aktif/tidak aktif
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Accessor untuk cek apakah akan pensiun dalam 12 bulan
     */
    public function getIsNearRetirementAttribute()
    {
        if (!$this->tmt_pensiun) {
            return false;
        }
        
        $monthsToRetirement = Carbon::now()->diffInMonths(Carbon::parse($this->tmt_pensiun));
        return $monthsToRetirement <= 12;
    }

    /**
     * Mutator untuk format NIP
     */
    public function setNipAttribute($value)
    {
        $this->attributes['nip'] = trim($value);
    }

    /**
     * Mutator untuk format nama lengkap
     */
    public function setNamaLengkapAttribute($value)
    {
        $this->attributes['nama_lengkap'] = strtoupper(trim($value));
    }

    /**
     * Mutator untuk format handphone dengan auto-format Indonesia
     */
    public function setHandphoneAttribute($value)
    {
        if ($value) {
            // Hapus karakter non-digit kecuali +
            $cleaned = preg_replace('/[^+0-9]/', '', $value);
            
            // Jika dimulai dengan 08, ubah menjadi +628
            if (substr($cleaned, 0, 2) === '08') {
                $cleaned = '+628' . substr($cleaned, 2);
            }
            // Jika dimulai dengan 8 (tanpa 0), ubah menjadi +628
            elseif (substr($cleaned, 0, 1) === '8' && substr($cleaned, 0, 2) !== '+6') {
                $cleaned = '+628' . substr($cleaned, 1);
            }
            // Jika dimulai dengan 628, tambahkan +
            elseif (substr($cleaned, 0, 3) === '628') {
                $cleaned = '+' . $cleaned;
            }
            // Jika dimulai dengan 62, tambahkan +
            elseif (substr($cleaned, 0, 2) === '62' && substr($cleaned, 0, 3) !== '+62') {
                $cleaned = '+' . $cleaned;
            }
            
            $this->attributes['handphone'] = $cleaned;
        } else {
            $this->attributes['handphone'] = null;
        }
    }

    /**
     * Mutator untuk email dengan validasi format
     */
    public function setEmailAttribute($value)
    {
        if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->attributes['email'] = strtolower(trim($value));
        } else {
            $this->attributes['email'] = null;
        }
    }

    // =====================================================
    // STATIC METHODS - OPTIMIZED FOR DASHBOARD & PAGINATION
    // =====================================================

    /**
     * Get comprehensive statistics untuk dashboard
     */
    public static function getComprehensiveStatistics()
    {
        return [
            'total_employees' => self::count(),
            'active_employees' => self::active()->count(),
            'inactive_employees' => self::where('status', 'inactive')->count(),
            'pegawai_tetap' => self::where('status_pegawai', 'PEGAWAI TETAP')->count(),
            'tad' => self::where('status_pegawai', 'TAD')->count(),
            'male_employees' => self::whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
            'female_employees' => self::whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
            'shoe_statistics' => [
                'pantofel' => self::where('jenis_sepatu', 'Pantofel')->count(),
                'safety_shoes' => self::where('jenis_sepatu', 'Safety Shoes')->count(),
                'no_shoe_data' => self::whereNull('jenis_sepatu')->orWhere('jenis_sepatu', '')->count(),
            ],
            'by_organization' => self::getByUnitOrganisasi(),
            'by_education' => self::getByEducation(),
            'recent_hires_count' => self::recentHires()->count(),
            'upcoming_retirement_count' => self::upcomingRetirement()->count(),
        ];
    }

    /**
     * Get filter options untuk dropdown - optimized untuk pagination
     */
    public static function getFilterOptions()
    {
        return [
            'units' => self::whereNotNull('unit_organisasi')
                          ->where('unit_organisasi', '!=', '')
                          ->distinct()
                          ->orderBy('unit_organisasi')
                          ->pluck('unit_organisasi')
                          ->filter()
                          ->values(),
            
            'shoe_types' => self::whereNotNull('jenis_sepatu')
                               ->where('jenis_sepatu', '!=', '')
                               ->distinct()
                               ->orderBy('jenis_sepatu')
                               ->pluck('jenis_sepatu')
                               ->filter()
                               ->values(),
            
            'shoe_sizes' => self::whereNotNull('ukuran_sepatu')
                               ->where('ukuran_sepatu', '!=', '')
                               ->distinct()
                               ->orderByRaw('CAST(ukuran_sepatu AS UNSIGNED)')
                               ->pluck('ukuran_sepatu')
                               ->filter()
                               ->values(),
            
            'education_levels' => self::select(\DB::raw('COALESCE(pendidikan_terakhir, pendidikan) as education'))
                                     ->whereNotNull(\DB::raw('COALESCE(pendidikan_terakhir, pendidikan)'))
                                     ->distinct()
                                     ->orderBy('education')
                                     ->pluck('education')
                                     ->filter()
                                     ->values(),
            
            'positions' => self::select(\DB::raw('COALESCE(nama_jabatan, jabatan) as position'))
                              ->whereNotNull(\DB::raw('COALESCE(nama_jabatan, jabatan)'))
                              ->distinct()
                              ->orderBy('position')
                              ->pluck('position')
                              ->filter()
                              ->values(),
        ];
    }

    /**
     * Get employees by unit organisasi dengan count
     */
    public static function getByUnitOrganisasi()
    {
        return self::select('unit_organisasi', \DB::raw('count(*) as total'))
                   ->whereNotNull('unit_organisasi')
                   ->where('unit_organisasi', '!=', '')
                   ->where('status', 'active')
                   ->groupBy('unit_organisasi')
                   ->orderBy('total', 'desc')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'name' => $item->unit_organisasi,
                           'count' => $item->total,
                       ];
                   });
    }

    /**
     * Get employees by education level dengan count
     */
    public static function getByEducation()
    {
        return self::select(
                       \DB::raw('COALESCE(pendidikan_terakhir, pendidikan) as education'),
                       \DB::raw('count(*) as total')
                   )
                   ->whereNotNull(\DB::raw('COALESCE(pendidikan_terakhir, pendidikan)'))
                   ->where('status', 'active')
                   ->groupBy(\DB::raw('COALESCE(pendidikan_terakhir, pendidikan)'))
                   ->orderBy('total', 'desc')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'name' => $item->education,
                           'count' => $item->total,
                       ];
                   });
    }

    /**
     * Get shoe size distribution
     */
    public static function getShoeSizeDistribution()
    {
        return self::select('ukuran_sepatu', \DB::raw('count(*) as total'))
                   ->whereNotNull('ukuran_sepatu')
                   ->where('ukuran_sepatu', '!=', '')
                   ->where('status', 'active')
                   ->groupBy('ukuran_sepatu')
                   ->orderByRaw('CAST(ukuran_sepatu AS UNSIGNED)')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'size' => $item->ukuran_sepatu,
                           'count' => $item->total,
                       ];
                   });
    }

    /**
     * Pagination dengan filter dan search - method utama untuk controller
     */
    public static function paginateWithFilters(array $filters = [], int $perPage = 20)
    {
        return self::active()
                   ->with('organization')
                   ->applyFilters($filters)
                   ->orderBy('nama_lengkap', 'asc')
                   ->paginate($perPage)
                   ->withQueryString();
    }

    /**
     * Search suggestions untuk autocomplete
     */
    public static function getSearchSuggestions($term, $limit = 10)
    {
        if (empty($term)) {
            return collect();
        }

        $searchTerm = '%' . $term . '%';
        
        return self::active()
                   ->where(function ($q) use ($searchTerm) {
                       $q->where('nama_lengkap', 'like', $searchTerm)
                         ->orWhere('nip', 'like', $searchTerm)
                         ->orWhere('unit_organisasi', 'like', $searchTerm)
                         ->orWhere('nama_jabatan', 'like', $searchTerm);
                   })
                   ->limit($limit)
                   ->get(['id', 'nip', 'nama_lengkap', 'unit_organisasi', 'nama_jabatan'])
                   ->map(function ($employee) {
                       return [
                           'id' => $employee->id,
                           'text' => $employee->nama_lengkap . ' (' . $employee->nip . ')',
                           'subtitle' => $employee->unit_organisasi . ' - ' . $employee->nama_jabatan,
                       ];
                   });
    }

    /**
     * Validasi NIP unik - optimized
     */
    public static function isNipUnique($nip, $excludeId = null)
    {
        $query = self::where('nip', $nip);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->doesntExist();
    }

    /**
     * Bulk update status
     */
    public static function bulkUpdateStatus(array $ids, $status)
    {
        return self::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Get export data dengan filter
     */
    public static function getExportData(array $filters = [])
    {
        return self::active()
                   ->applyFilters($filters)
                   ->orderBy('nama_lengkap', 'asc')
                   ->get();
    }

    // =====================================================
    // UTILITY METHODS
    // =====================================================

    /**
     * Calculate profile completion percentage
     */
    public function getProfileCompletionPercentage()
    {
        $fields = [
            'nip', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 
            'tanggal_lahir', 'alamat', 'handphone', 'email',
            'unit_organisasi', 'nama_jabatan', 'status_pegawai',
            'tmt_mulai_jabatan', 'pendidikan_terakhir', 'jenis_sepatu', 'ukuran_sepatu'
        ];

        $completedFields = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($fields)) * 100);
    }

    /**
     * Get work duration in human readable format
     */
    public function getWorkDuration()
    {
        if (!$this->tmt_mulai_kerja) {
            return null;
        }
        
        return Carbon::parse($this->tmt_mulai_kerja)->diffForHumans(null, true);
    }

    /**
     * Get years to retirement
     */
    public function getYearsToRetirement()
    {
        if (!$this->tmt_pensiun) {
            return null;
        }
        
        return Carbon::now()->diffInYears(Carbon::parse($this->tmt_pensiun));
    }

    /**
     * Generate ID card data
     */
    public function getIdCardData()
    {
        return [
            'nip' => $this->nip,
            'nama_lengkap' => $this->nama_lengkap,
            'jabatan' => $this->nama_jabatan ?: $this->jabatan,
            'unit_organisasi' => $this->unit_organisasi,
            'initials' => $this->initials,
            'foto_url' => null, // Placeholder for future photo implementation
        ];
    }
}