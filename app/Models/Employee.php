<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    use HasFactory;

    /**
     * FIXED: Auto-increment ID sebagai primary key (bukan NIK)
     * GAPURA ANGKASA SDM System - Employee Model
     * FIXED: Primary key configuration untuk compatibility dengan database structure
     */
    
    protected $table = 'employees';
    
    // FIXED: Gunakan auto-increment ID sebagai primary key (default Laravel)
    protected $primaryKey = 'id';
    
    // FIXED: Primary key adalah auto-increment
    public $incrementing = true;
    
    // FIXED: Primary key bertipe integer
    protected $keyType = 'int';

    /**
     * FIXED: Fillable attributes - NIK sebagai unique field (bukan primary key)
     * Mempertahankan semua field yang diperlukan untuk seeder compatibility
     */
    protected $fillable = [
        'nik', // FIXED: NIK sebagai unique field (bukan primary key)
        'nip',
        'nama_lengkap',
        'lokasi_kerja',
        'cabang',
        'status_pegawai',
        'status_kerja',
        'provider',
        'kode_organisasi',
        'unit_organisasi',
        'unit_id',
        'sub_unit_id',
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
        'seragam',
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
        'no_telepon', // FIXED: Include untuk seeder compatibility
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

    /**
     * FIXED: Hidden attributes - Sembunyikan no_telepon dari JSON output
     * Field tetap ada di database untuk seeder, tapi tidak muncul di API/form
     */
    protected $hidden = [
        'no_telepon'
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
        'organization_id' => 'integer',
        'unit_id' => 'integer',
        'sub_unit_id' => 'integer'
    ];

    /**
     * FIXED: Override toArray untuk memastikan no_telepon tidak muncul
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Pastikan no_telepon tidak pernah muncul dalam output
        unset($array['no_telepon']);
        
        return $array;
    }

    /**
     * FIXED: Get the route key for the model menggunakan ID (bukan NIK)
     * Untuk backward compatibility, tetap bisa menggunakan NIK
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * FIXED: Search scope untuk mencari berdasarkan NIK atau ID
     * Method ini memungkinkan pencarian fleksibel berdasarkan identifier
     */
    public function scopeFindByIdentifier(Builder $query, $identifier)
    {
        // Jika identifier adalah NIK (16 digit), cari berdasarkan NIK
        if (preg_match('/^[0-9]{16}$/', $identifier)) {
            return $query->where('nik', $identifier);
        }
        
        // Jika identifier adalah ID (numeric), cari berdasarkan ID
        if (is_numeric($identifier)) {
            return $query->where('id', $identifier);
        }
        
        // Fallback: cari berdasarkan NIP
        return $query->where('nip', $identifier);
    }

    /**
     * Database indexing recommendations for optimal pagination performance
     * UPDATED: Include NIK indexing
     * Run these SQL commands for better performance with large datasets:
     * 
     * ALTER TABLE employees ADD INDEX idx_nik (nik);
     * ALTER TABLE employees ADD INDEX idx_status (status);
     * ALTER TABLE employees ADD INDEX idx_nama_lengkap (nama_lengkap);
     * ALTER TABLE employees ADD INDEX idx_nip (nip);
     * ALTER TABLE employees ADD INDEX idx_unit_organisasi (unit_organisasi);
     * ALTER TABLE employees ADD INDEX idx_status_pegawai (status_pegawai);
     * ALTER TABLE employees ADD INDEX idx_jenis_kelamin (jenis_kelamin);
     * ALTER TABLE employees ADD INDEX idx_jenis_sepatu (jenis_sepatu);
     * ALTER TABLE employees ADD INDEX idx_ukuran_sepatu (ukuran_sepatu);
     * ALTER TABLE employees ADD INDEX idx_kelompok_jabatan (kelompok_jabatan);
     * ALTER TABLE employees ADD INDEX idx_search_composite (nama_lengkap, nip, nik, unit_organisasi);
     * ALTER TABLE employees ADD INDEX idx_unit_id (unit_id);
     * ALTER TABLE employees ADD INDEX idx_sub_unit_id (sub_unit_id);
     * ALTER TABLE employees ADD INDEX idx_created_at (created_at);
     * ALTER TABLE employees ADD INDEX idx_updated_at (updated_at);
     */

    // =====================================================
    // CONSTANTS - FITUR BARU
    // =====================================================

    /**
     * KELOMPOK JABATAN CONSTANTS
     */
    const KELOMPOK_JABATAN = [
        'SUPERVISOR',
        'STAFF', 
        'MANAGER',
        'EXECUTIVE GENERAL MANAGER',
        'ACCOUNT EXECUTIVE/AE'
    ];

    /**
     * STATUS PEGAWAI CONSTANTS dengan TAD Split
     */
    const STATUS_PEGAWAI = [
        'PEGAWAI TETAP',
        'PKWT',
        'TAD PAKET SDM',
        'TAD PAKET PEKERJAAN'
    ];

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

    /**
     * Relasi ke Unit model
     * Untuk mendapatkan nama unit berdasarkan unit_id
     * UPDATED: For Employee History functionality
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Relasi ke SubUnit model  
     * Untuk mendapatkan nama sub unit berdasarkan sub_unit_id
     * UPDATED: For Employee History functionality
     */
    public function subUnit()
    {
        return $this->belongsTo(SubUnit::class, 'sub_unit_id');
    }

    // =====================================================
    // ENHANCED SCOPES UNTUK SEARCH DAN FILTER
    // =====================================================

    /**
     * Scope untuk filter berdasarkan status aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk mengambil karyawan yang baru ditambahkan
     * UPDATED: Untuk keperluan history dashboard
     */
    public function scopeRecentlyAdded($query, $days = 30)
    {
        return $query->where('created_at', '>=', Carbon::now()->subDays($days))
                     ->where('status', 'active')
                     ->orderBy('created_at', 'desc');
    }

    /**
     * Scope untuk mengambil karyawan yang ditambahkan hari ini
     * UPDATED: For Employee History dashboard widget
     */
    public function scopeAddedToday($query)
    {
        return $query->whereDate('created_at', Carbon::today())
                     ->where('status', 'active');
    }

    /**
     * Scope untuk mengambil karyawan dengan relasi lengkap
     * UPDATED: Untuk optimasi query pada history
     */
    public function scopeWithOrganizationInfo($query)
    {
        return $query->with(['unit', 'subUnit'])
                     ->select([
                         'id',
                         'nama_lengkap',
                         'unit_organisasi', 
                         'unit_id',
                         'sub_unit_id',
                         'jabatan',
                         'status_pegawai',
                         'created_at',
                         'updated_at'
                     ]);
    }

    /**
     * Scope untuk TAD employees (semua jenis) - FITUR BARU
     */
    public function scopeTAD($query)
    {
        return $query->whereIn('status_pegawai', ['TAD PAKET SDM', 'TAD PAKET PEKERJAAN']);
    }

    /**
     * Scope untuk Pegawai Tetap - FITUR BARU
     */
    public function scopePegawaiTetap($query)
    {
        return $query->where('status_pegawai', 'PEGAWAI TETAP');
    }

    /**
     * Scope untuk PKWT - FITUR BARU
     */
    public function scopePKWT($query)
    {
        return $query->where('status_pegawai', 'PKWT');
    }

    /**
     * FIXED: Enhanced Global Search Scope dengan error handling yang lebih baik
     */
    public function scopeGlobalSearch(Builder $query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        $searchTerm = '%' . trim($term) . '%';
        
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('nama_lengkap', 'like', $searchTerm)
              ->orWhere('nip', 'like', $searchTerm)
              ->orWhere('nik', 'like', $searchTerm)
              ->orWhere('jabatan', 'like', $searchTerm)
              ->orWhere('nama_jabatan', 'like', $searchTerm)
              ->orWhere('unit_organisasi', 'like', $searchTerm)
              ->orWhere('nama_organisasi', 'like', $searchTerm)
              ->orWhere('kelompok_jabatan', 'like', $searchTerm)
              ->orWhere('jenis_sepatu', 'like', $searchTerm)
              ->orWhere('ukuran_sepatu', 'like', $searchTerm)
              ->orWhere('seragam', 'like', $searchTerm)
              ->orWhere('kota_domisili', 'like', $searchTerm)
              ->orWhere('instansi_pendidikan', 'like', $searchTerm)
              ->orWhere('pendidikan_terakhir', 'like', $searchTerm)
              ->orWhere('pendidikan', 'like', $searchTerm)
              ->orWhere('jurusan', 'like', $searchTerm)
              ->orWhere('handphone', 'like', $searchTerm)
              ->orWhere('email', 'like', $searchTerm)
              ->orWhere('tempat_lahir', 'like', $searchTerm)
              ->orWhere('alamat', 'like', $searchTerm);
              
            // FIXED: Search dalam unit dan sub unit dengan error handling
            try {
                $q->orWhereHas('unit', function ($unitQuery) use ($searchTerm) {
                    $unitQuery->where('name', 'like', $searchTerm)
                             ->orWhere('code', 'like', $searchTerm);
                });
                
                $q->orWhereHas('subUnit', function ($subUnitQuery) use ($searchTerm) {
                    $subUnitQuery->where('name', 'like', $searchTerm)
                                ->orWhere('code', 'like', $searchTerm);
                });
            } catch (\Exception $e) {
                // Jika relationship belum ada atau error, lanjutkan tanpa unit/subunit search
                \Log::warning('Unit/SubUnit relationship search failed: ' . $e->getMessage());
            }
        });
    }

    /**
     * FIXED: Filter berdasarkan unit organisasi dengan case-insensitive
     */
    public function scopeByUnitOrganisasi(Builder $query, $unit)
    {
        if ($unit === 'all' || empty($unit)) {
            return $query;
        }
        
        return $query->where('unit_organisasi', $unit);
    }

    /**
     * FIXED: Filter berdasarkan unit dengan nama atau ID
     */
    public function scopeByUnit(Builder $query, $unitValue)
    {
        if ($unitValue === 'all' || empty($unitValue)) {
            return $query;
        }
        
        // Jika numeric, cari berdasarkan unit_id
        if (is_numeric($unitValue)) {
            return $query->where('unit_id', $unitValue);
        }
        
        // Jika bukan numeric, cari berdasarkan nama unit dalam relationship
        try {
            return $query->whereHas('unit', function ($unitQuery) use ($unitValue) {
                $unitQuery->where('name', $unitValue)
                         ->orWhere('code', $unitValue);
            });
        } catch (\Exception $e) {
            // Fallback: cari berdasarkan unit_organisasi jika relationship belum ada
            return $query->where('unit_organisasi', 'like', '%' . $unitValue . '%');
        }
    }

    /**
     * FIXED: Filter berdasarkan sub unit dengan nama atau ID
     */
    public function scopeBySubUnit(Builder $query, $subUnitValue)
    {
        if ($subUnitValue === 'all' || empty($subUnitValue)) {
            return $query;
        }
        
        // Jika numeric, cari berdasarkan sub_unit_id
        if (is_numeric($subUnitValue)) {
            return $query->where('sub_unit_id', $subUnitValue);
        }
        
        // Jika bukan numeric, cari berdasarkan nama sub unit dalam relationship
        try {
            return $query->whereHas('subUnit', function ($subUnitQuery) use ($subUnitValue) {
                $subUnitQuery->where('name', $subUnitValue)
                            ->orWhere('code', $subUnitValue);
            });
        } catch (\Exception $e) {
            // Fallback: return empty query jika relationship belum ada
            \Log::warning('SubUnit relationship search failed: ' . $e->getMessage());
            return $query->whereRaw('1 = 0'); // Return no results
        }
    }

    /**
     * Filter berdasarkan status pegawai
     */
    public function scopeByStatusPegawai(Builder $query, $status)
    {
        if ($status === 'all' || empty($status)) {
            return $query;
        }
        
        return $query->where('status_pegawai', $status);
    }

    /**
     * Filter berdasarkan kelompok jabatan
     */
    public function scopeByKelompokJabatan(Builder $query, $kelompokJabatan)
    {
        if ($kelompokJabatan === 'all' || empty($kelompokJabatan)) {
            return $query;
        }
        
        return $query->where('kelompok_jabatan', $kelompokJabatan);
    }

    /**
     * Filter berdasarkan jenis kelamin
     */
    public function scopeByJenisKelamin(Builder $query, $jenisKelamin)
    {
        if ($jenisKelamin === 'all' || empty($jenisKelamin)) {
            return $query;
        }
        
        return $query->where('jenis_kelamin', $jenisKelamin);
    }

    /**
     * FIXED: Filter berdasarkan jenis sepatu dengan case-insensitive
     */
    public function scopeByJenisSepatu(Builder $query, $jenisSepatu)
    {
        if ($jenisSepatu === 'all' || empty($jenisSepatu)) {
            return $query;
        }
        
        return $query->where('jenis_sepatu', $jenisSepatu);
    }

    /**
     * FIXED: Filter berdasarkan ukuran sepatu
     */
    public function scopeByUkuranSepatu(Builder $query, $ukuranSepatu)
    {
        if ($ukuranSepatu === 'all' || empty($ukuranSepatu)) {
            return $query;
        }
        
        return $query->where('ukuran_sepatu', $ukuranSepatu);
    }

    /**
     * Filter berdasarkan seragam
     */
    public function scopeBySeragam(Builder $query, $seragam)
    {
        if ($seragam === 'all' || empty($seragam)) {
            return $query;
        }
        
        return $query->where('seragam', $seragam);
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
     * UPDATED: 56 tahun
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
     * FIXED: Comprehensive filter scope untuk pagination dengan error handling
     */
    public function scopeApplyFilters(Builder $query, array $filters)
    {
        try {
            // Global search
            if (!empty($filters['search'])) {
                $query->globalSearch($filters['search']);
            }

            // Individual filters
            if (!empty($filters['status_pegawai'])) {
                $query->byStatusPegawai($filters['status_pegawai']);
            }

            if (!empty($filters['unit_organisasi'])) {
                $query->byUnitOrganisasi($filters['unit_organisasi']);
            }

            if (!empty($filters['unit_id'])) {
                $query->byUnit($filters['unit_id']);
            }

            if (!empty($filters['sub_unit_id'])) {
                $query->bySubUnit($filters['sub_unit_id']);
            }

            if (!empty($filters['jenis_kelamin'])) {
                $query->byJenisKelamin($filters['jenis_kelamin']);
            }

            if (!empty($filters['kelompok_jabatan'])) {
                $query->byKelompokJabatan($filters['kelompok_jabatan']);
            }

            if (!empty($filters['jenis_sepatu'])) {
                $query->byJenisSepatu($filters['jenis_sepatu']);
            }

            if (!empty($filters['ukuran_sepatu'])) {
                $query->byUkuranSepatu($filters['ukuran_sepatu']);
            }

            if (!empty($filters['seragam'])) {
                $query->bySeragam($filters['seragam']);
            }

            if (!empty($filters['pendidikan'])) {
                $query->byPendidikan($filters['pendidikan']);
            }

        } catch (\Exception $e) {
            \Log::error('Error applying filters: ' . $e->getMessage());
            // Return query tanpa filter jika ada error
        }

        return $query;
    }

    // =====================================================
    // ACCESSORS & MUTATORS
    // =====================================================

    /**
     * Accessor untuk mendapatkan struktur organisasi lengkap
     * UPDATED: Format "Unit Organisasi → Unit → Sub Unit" untuk Employee History
     */
    public function getOrganizationStructureAttribute()
    {
        $parts = [];
        
        if ($this->unit_organisasi) {
            $parts[] = $this->unit_organisasi;
        }
        
        if ($this->unit && $this->unit->name) {
            $parts[] = $this->unit->name;
        }
        
        if ($this->subUnit && $this->subUnit->name) {
            $parts[] = $this->subUnit->name;
        }
        
        return implode(' → ', $parts);
    }

    /**
     * REVISI: Enhanced TMT Pensiun calculation dengan logika baru (56 tahun)
     * Jika lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
     * Jika lahir diatas tanggal 10: pensiun 1 bulan berikutnya
     */
    public function setTanggalLahirAttribute($value)
    {
        $this->attributes['tanggal_lahir'] = $value;
        
        // REVISI: Auto calculate TMT Pensiun dengan logika baru dan umur
        if ($value) {
            $birthDate = Carbon::parse($value);
            $this->attributes['usia'] = $birthDate->age;
            
            // REVISI: Logika TMT Pensiun berdasarkan aturan baru
            // Jika lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
            // Jika lahir diatas tanggal 10: pensiun 1 bulan berikutnya
            $pensionYear = $birthDate->year + 56;
            
            if ($birthDate->day < 10) {
                // Lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
                $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
            } else {
                // Lahir diatas tanggal 10: pensiun 1 bulan berikutnya
                $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
                $pensionDate->addMonth(); // Tambah 1 bulan
            }
            
            $this->attributes['tmt_pensiun'] = $pensionDate->format('Y-m-d');
        }
    }

    /**
     * Mutator untuk NIK format validation
     */
    public function setNikAttribute($value)
    {
        // Clean NIK: hapus karakter non-digit dan pastikan 16 digit
        $cleanNik = preg_replace('/[^0-9]/', '', $value);
        $this->attributes['nik'] = $cleanNik;
    }

    /**
     * Get unit organisasi display name dengan unit dan sub unit
     */
    public function getUnitDisplayAttribute()
    {
        $display = $this->unit_organisasi;
        
        try {
            if ($this->unit) {
                $display .= ' - ' . $this->unit->name;
            }
            
            if ($this->subUnit) {
                $display .= ' - ' . $this->subUnit->name;
            }
        } catch (\Exception $e) {
            // Jika relationship error, return unit_organisasi saja
            \Log::warning('Unit display relationship error: ' . $e->getMessage());
        }
        
        return $display;
    }

    /**
     * Get full organizational structure untuk Employee History
     * UPDATED: Enhanced structure dengan error handling
     */
    public function getOrganizationalStructureAttribute()
    {
        try {
            return [
                'unit_organisasi' => $this->unit_organisasi,
                'unit' => $this->unit ? [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                    'code' => $this->unit->code,
                ] : null,
                'sub_unit' => $this->subUnit ? [
                    'id' => $this->subUnit->id,
                    'name' => $this->subUnit->name,
                    'code' => $this->subUnit->code,
                ] : null,
            ];
        } catch (\Exception $e) {
            \Log::warning('Organizational structure error: ' . $e->getMessage());
            return [
                'unit_organisasi' => $this->unit_organisasi,
                'unit' => null,
                'sub_unit' => null,
            ];
        }
    }

    /**
     * Accessor untuk mendapatkan jenis kelamin dalam format lengkap
     */
    public function getJenisKelaminLengkapAttribute()
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
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
     * UPDATED: Tidak ada fallback ke no_telepon karena field disembunyikan
     */
    public function getHandphoneFormattedAttribute()
    {
        if (empty($this->handphone)) {
            return '-';
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
        // Clean NIP: hapus karakter non-digit
        $cleanNip = preg_replace('/[^0-9]/', '', trim($value));
        $this->attributes['nip'] = $cleanNip;
    }

    /**
     * FIXED: Mutator untuk nama lengkap - HAPUS auto-uppercase
     * Sekarang nama akan disimpan sesuai dengan input asli pengguna
     */
    public function setNamaLengkapAttribute($value)
    {
        // Hanya trim spasi, tidak ada auto-uppercase
        $this->attributes['nama_lengkap'] = trim($value);
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
    // STATIC METHODS - TAD STATISTICS (FITUR BARU)
    // =====================================================

    /**
     * Get Total TAD (menggabungkan TAD Paket SDM + TAD Paket Pekerjaan) - FITUR BARU
     */
    public static function getTotalTAD()
    {
        return self::whereIn('status_pegawai', ['TAD PAKET SDM', 'TAD PAKET PEKERJAAN'])->count();
    }

    /**
     * Get TAD Paket SDM count - FITUR BARU
     */
    public static function getTADPaketSDM()
    {
        return self::where('status_pegawai', 'TAD PAKET SDM')->count();
    }

    /**
     * Get TAD Paket Pekerjaan count - FITUR BARU
     */
    public static function getTADPaketPekerjaan()
    {
        return self::where('status_pegawai', 'TAD PAKET PEKERJAAN')->count();
    }

    // =====================================================
    // STATIC METHODS - OPTIMIZED FOR DASHBOARD & PAGINATION
    // =====================================================

    /**
     * Get comprehensive statistics untuk dashboard
     * UPDATED: Tambah TAD Split dan Kelompok Jabatan
     */
    public static function getComprehensiveStatistics()
    {
        $tadPaketSDM = self::getTADPaketSDM();
        $tadPaketPekerjaan = self::getTADPaketPekerjaan();
        
        return [
            'total_employees' => self::count(),
            'active_employees' => self::active()->count(),
            'inactive_employees' => self::where('status', 'inactive')->count(),
            'pegawai_tetap' => self::where('status_pegawai', 'PEGAWAI TETAP')->count(),
            'pkwt' => self::where('status_pegawai', 'PKWT')->count(),
            'tad_total' => $tadPaketSDM + $tadPaketPekerjaan,
            'tad_paket_sdm' => $tadPaketSDM,
            'tad_paket_pekerjaan' => $tadPaketPekerjaan,
            'male_employees' => self::whereIn('jenis_kelamin', ['L', 'Laki-laki'])->count(),
            'female_employees' => self::whereIn('jenis_kelamin', ['P', 'Perempuan'])->count(),
            'shoe_statistics' => [
                'pantofel' => self::where('jenis_sepatu', 'Pantofel')->count(),
                'safety_shoes' => self::where('jenis_sepatu', 'Safety Shoes')->count(),
                'no_shoe_data' => self::whereNull('jenis_sepatu')->orWhere('jenis_sepatu', '')->count(),
            ],
            'uniform_statistics' => [
                'total_with_uniform' => self::whereNotNull('seragam')->where('seragam', '!=', '')->count(),
                'no_uniform_data' => self::whereNull('seragam')->orWhere('seragam', '')->count(),
            ],
            'by_organization' => self::getByUnitOrganisasi(),
            'by_education' => self::getByEducation(),
            'by_kelompok_jabatan' => self::getByKelompokJabatan(),
            'recent_hires_count' => self::recentHires()->count(),
            'upcoming_retirement_count' => self::upcomingRetirement()->count(),
        ];
    }

    /**
     * Get filter options untuk dropdown - optimized untuk pagination
     * UPDATED: Tambah kelompok jabatan options dan unit options
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

            'uniform_types' => self::whereNotNull('seragam')
                                  ->where('seragam', '!=', '')
                                  ->distinct()
                                  ->orderBy('seragam')
                                  ->pluck('seragam')
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

            'kelompok_jabatan' => self::whereNotNull('kelompok_jabatan')
                                     ->where('kelompok_jabatan', '!=', '')
                                     ->distinct()
                                     ->orderBy('kelompok_jabatan')
                                     ->pluck('kelompok_jabatan')
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
     * Get employees by kelompok jabatan dengan count - FITUR BARU
     */
    public static function getByKelompokJabatan()
    {
        return self::select('kelompok_jabatan', \DB::raw('count(*) as total'))
                   ->whereNotNull('kelompok_jabatan')
                   ->where('kelompok_jabatan', '!=', '')
                   ->where('status', 'active')
                   ->groupBy('kelompok_jabatan')
                   ->orderBy('total', 'desc')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'name' => $item->kelompok_jabatan,
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
     * Get uniform distribution
     */
    public static function getUniformDistribution()
    {
        return self::select('seragam', \DB::raw('count(*) as total'))
                   ->whereNotNull('seragam')
                   ->where('seragam', '!=', '')
                   ->where('status', 'active')
                   ->groupBy('seragam')
                   ->orderBy('seragam')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'type' => $item->seragam,
                           'count' => $item->total,
                       ];
                   });
    }

    /**
     * Pagination dengan filter dan search - method utama untuk controller
     * UPDATED: Load unit dan sub unit relationships
     */
    public static function paginateWithFilters(array $filters = [], int $perPage = 20)
    {
        return self::active()
                   ->with(['organization', 'unit', 'subUnit'])
                   ->applyFilters($filters)
                   ->orderBy('nama_lengkap', 'asc')
                   ->paginate($perPage)
                   ->withQueryString();
    }

    /**
     * Search suggestions untuk autocomplete
     * UPDATED: Include NIK in search results, use ID as identifier
     */
    public static function getSearchSuggestions($term, $limit = 10)
    {
        if (empty($term)) {
            return collect();
        }

        $searchTerm = '%' . $term . '%';
        
        return self::active()
                   ->with(['unit', 'subUnit'])
                   ->where(function ($q) use ($searchTerm) {
                       $q->where('nama_lengkap', 'like', $searchTerm)
                         ->orWhere('nip', 'like', $searchTerm)
                         ->orWhere('nik', 'like', $searchTerm)
                         ->orWhere('unit_organisasi', 'like', $searchTerm)
                         ->orWhere('nama_jabatan', 'like', $searchTerm);
                   })
                   ->limit($limit)
                   ->get(['id', 'nik', 'nip', 'nama_lengkap', 'unit_organisasi', 'nama_jabatan', 'unit_id', 'sub_unit_id'])
                   ->map(function ($employee) {
                       return [
                           'id' => $employee->id, // FIXED: Use ID as identifier
                           'nik' => $employee->nik,
                           'text' => $employee->nama_lengkap . ' (NIK: ' . $employee->nik . ', NIP: ' . $employee->nip . ')',
                           'subtitle' => $employee->unit_display . ' - ' . $employee->nama_jabatan,
                       ];
                   });
    }

    /**
     * FIXED: Helper method untuk mencari employee berdasarkan NIK
     */
    public static function findByNik($nik)
    {
        return static::where('nik', $nik)->first();
    }

    /**
     * Helper method untuk mendapatkan employee berdasarkan NIP
     */
    public static function findByNip($nip)
    {
        return static::where('nip', $nip)->first();
    }

    /**
     * FIXED: Validasi NIK unik - Updated untuk ID-based system
     */
    public static function isNikUnique($nik, $excludeId = null)
    {
        $query = self::where('nik', $nik);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId); // FIXED: exclude berdasarkan ID
        }
        
        return $query->doesntExist();
    }

    /**
     * FIXED: Validasi NIP unik - Updated untuk ID-based system
     */
    public static function isNipUnique($nip, $excludeId = null)
    {
        $query = self::where('nip', $nip);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId); // FIXED: exclude berdasarkan ID
        }
        
        return $query->doesntExist();
    }

    /**
     * FIXED: Bulk update status - Updated menggunakan ID array
     */
    public static function bulkUpdateStatus(array $ids, $status)
    {
        return self::whereIn('id', $ids)->update(['status' => $status]); // FIXED: use ID
    }

    /**
     * Get export data dengan filter
     * UPDATED: Load unit dan sub unit untuk export
     */
    public static function getExportData(array $filters = [])
    {
        return self::active()
                   ->with(['unit', 'subUnit'])
                   ->applyFilters($filters)
                   ->orderBy('nama_lengkap', 'asc')
                   ->get();
    }

    // =====================================================
    // UTILITY METHODS
    // =====================================================

    /**
     * Method untuk validasi NIK format Indonesia
     */
    public function validateNikFormat()
    {
        if (strlen($this->nik) !== 16) {
            return false;
        }

        if (!ctype_digit($this->nik)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate profile completion percentage
     * UPDATED: Tidak include no_telepon dalam perhitungan
     */
    public function getProfileCompletionPercentage()
    {
        $fields = [
            'nik', 'nip', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 
            'tanggal_lahir', 'alamat', 'handphone', 'email',
            'unit_organisasi', 'nama_jabatan', 'status_pegawai', 'kelompok_jabatan',
            'tmt_mulai_jabatan', 'pendidikan_terakhir', 'jenis_sepatu', 
            'ukuran_sepatu', 'seragam'
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
     * Get years to retirement - UPDATED: 56 tahun
     */
    public function getYearsToRetirement()
    {
        if (!$this->tmt_pensiun) {
            return null;
        }
        
        return Carbon::now()->diffInYears(Carbon::parse($this->tmt_pensiun));
    }

    /**
     * Check if employee is approaching retirement (dalam 1 tahun) - FITUR BARU
     */
    public function isApproachingRetirement()
    {
        if (!$this->tmt_pensiun) return false;
        
        return $this->tmt_pensiun <= Carbon::now()->addYear();
    }

    /**
     * Generate ID card data
     * UPDATED: Include NIK dan unit structure
     */
    public function getIdCardData()
    {
        return [
            'id' => $this->id, // FIXED: Include ID
            'nik' => $this->nik,
            'nip' => $this->nip,
            'nama_lengkap' => $this->nama_lengkap,
            'jabatan' => $this->nama_jabatan ?: $this->jabatan,
            'unit_organisasi' => $this->unit_organisasi,
            'unit_display' => $this->unit_display,
            'kelompok_jabatan' => $this->kelompok_jabatan,
            'initials' => $this->initials,
            'foto_url' => null, // Placeholder for future photo implementation
        ];
    }

    /**
     * FIXED: Method untuk mendapatkan informasi ringkas employee
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id, // FIXED: ID sebagai primary identifier
            'nik' => $this->nik,
            'nip' => $this->nip,
            'nama' => $this->nama_lengkap,
            'unit' => $this->unit_organisasi,
            'jabatan' => $this->nama_jabatan,
            'status' => $this->status_pegawai,
        ];
    }

    // =====================================================
    // BOOT METHOD - AUTO CALCULATIONS
    // =====================================================

    /**
     * REVISI: Boot method dengan logika TMT Pensiun yang diperbaiki
     */
    protected static function boot()
    {
        parent::boot();

        // Event ketika membuat employee baru
        static::creating(function ($employee) {
            // Set default values jika tidak ada
            if (empty($employee->status)) {
                $employee->status = 'active';
            }
            
            if (empty($employee->status_kerja)) {
                $employee->status_kerja = 'Aktif';
            }

            // Set default lokasi kerja dan cabang
            if (empty($employee->lokasi_kerja)) {
                $employee->lokasi_kerja = 'Bandar Udara Ngurah Rai';
            }
            
            if (empty($employee->cabang)) {
                $employee->cabang = 'Denpasar';
            }

            // Hitung usia otomatis jika ada tanggal lahir
            if ($employee->tanggal_lahir && empty($employee->usia)) {
                $birthDate = Carbon::parse($employee->tanggal_lahir);
                $employee->usia = $birthDate->age;
            }

            // REVISI: Hitung TMT Pensiun otomatis dengan logika baru (56 tahun)
            if ($employee->tanggal_lahir && empty($employee->tmt_pensiun)) {
                $birthDate = Carbon::parse($employee->tanggal_lahir);
                $pensionYear = $birthDate->year + 56;
                
                if ($birthDate->day < 10) {
                    // Lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
                    $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
                } else {
                    // Lahir diatas tanggal 10: pensiun 1 bulan berikutnya
                    $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
                    $pensionDate->addMonth(); // Tambah 1 bulan
                }
                
                $employee->tmt_pensiun = $pensionDate->format('Y-m-d');
            }
        });

        // Event ketika mengupdate employee
        static::updating(function ($employee) {
            // Update usia jika tanggal lahir berubah
            if ($employee->isDirty('tanggal_lahir') && $employee->tanggal_lahir) {
                $birthDate = Carbon::parse($employee->tanggal_lahir);
                $employee->usia = $birthDate->age;

                // REVISI: Update TMT Pensiun dengan logika baru juga
                $pensionYear = $birthDate->year + 56;
                
                if ($birthDate->day < 10) {
                    // Lahir dibawah tanggal 10: pensiun 1 pada bulan yang sama
                    $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
                } else {
                    // Lahir diatas tanggal 10: pensiun 1 bulan berikutnya
                    $pensionDate = Carbon::createFromDate($pensionYear, $birthDate->month, 1);
                    $pensionDate->addMonth(); // Tambah 1 bulan
                }
                
                $employee->tmt_pensiun = $pensionDate->format('Y-m-d');
            }
        });
    }
}