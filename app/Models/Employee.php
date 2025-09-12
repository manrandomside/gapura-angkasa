<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

// FIXED: Import Unit dan SubUnit model yang diperlukan untuk History Modal
use App\Models\Unit;
use App\Models\SubUnit;
use App\Models\Organization;

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
     * UPDATED: Fillable attributes dengan field baru yang ditambahkan
     * Mempertahankan semua field yang diperlukan untuk seeder compatibility
     */
    protected $fillable = [
        'nik', // FIXED: NIK sebagai unique field (bukan primary key)
        'nip',
        'nama_lengkap',
        'lokasi_kerja',
        'cabang',
        'status_pegawai',
        'status_kerja', // NEW: Status kerja otomatis
        'provider', // NEW: Provider perusahaan
        'kode_organisasi',
        'unit_organisasi',
        'unit_id',
        'sub_unit_id',
        'nama_organisasi',
        'nama_jabatan',
        'jabatan',
        'unit_kerja_kontrak', // NEW: Unit kerja sesuai kontrak
        'tmt_mulai_kerja',
        'tmt_mulai_jabatan',
        'tmt_akhir_jabatan', // NEW: TMT akhir jabatan
        'tmt_berakhir_jabatan',
        'tmt_berakhir_kerja',
        'masa_kerja_bulan',
        'masa_kerja_tahun',
        'masa_kerja', // NEW: Masa kerja calculated field
        'grade', // NEW: Grade karyawan
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

    /**
     * UPDATED: Cast attributes dengan field baru
     */
    protected $casts = [
        'tmt_mulai_kerja' => 'date',
        'tmt_mulai_jabatan' => 'date',
        'tmt_akhir_jabatan' => 'date', // NEW
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
     * UPDATED: Appends untuk History Modal dan Unit Display - CRITICAL
     * Menambahkan accessor yang diperlukan untuk History Modal dan format unit
     */
    protected $appends = [
        'organizational_structure',
        'unit_display',
        'unit_organisasi_formatted',
        'unit_display_formatted',
        'initials'
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

    // =====================================================
    // CONSTANTS - UPDATED WITH NEW FIELDS
    // =====================================================

    /**
     * UPDATED: KELOMPOK JABATAN CONSTANTS - Added GENERAL MANAGER and NON
     */
    const KELOMPOK_JABATAN = [
        'ACCOUNT EXECUTIVE/AE',
        'EXECUTIVE GENERAL MANAGER',
        'GENERAL MANAGER',
        'MANAGER',
        'STAFF',
        'SUPERVISOR',
        'NON'
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

    /**
     * NEW: Provider options constant
     */
    const PROVIDER_OPTIONS = [
        'PT Gapura Angkasa',
        'PT Air Box Personalia',
        'PT Finfleet Teknologi Indonesia',
        'PT Mitra Angkasa Perdana',
        'PT Safari Dharma Sakti',
        'PT Grha Humanindo Management',
        'PT Duta Griya Sarana',
        'PT Aerotrans Wisata',
        'PT Mandala Garda Nusantara',
        'PT Kidora Mandiri Investama'
    ];

    /**
     * NEW: Status kerja options constant
     */
    const STATUS_KERJA_OPTIONS = [
        'Aktif',
        'Non-Aktif',
        'Pensiun',
        'Mutasi'
    ];

    // =====================================================
    // ENHANCED RELATIONSHIPS - FIXED FOR HISTORY FUNCTIONALITY
    // =====================================================

    /**
     * FIXED: Relationship dengan Organization dengan better error handling
     */
    public function organization()
    {
        try {
            return $this->belongsTo(Organization::class, 'organization_id', 'id');
        } catch (\Exception $e) {
            Log::warning('Employee organization relationship error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * FIXED: Enhanced Unit relationship dengan comprehensive error handling
     * Employee belongs to Unit - CRITICAL untuk history functionality
     */
    public function unit()
    {
        try {
            return $this->belongsTo(Unit::class, 'unit_id', 'id');
        } catch (\Exception $e) {
            Log::warning('Employee unit relationship error: ' . $e->getMessage(), [
                'employee_id' => $this->id ?? 'unknown',
                'unit_id' => $this->unit_id ?? 'null'
            ]);
            return null;
        }
    }

    /**
     * FIXED: Enhanced SubUnit relationship dengan comprehensive error handling  
     * Employee belongs to SubUnit - CRITICAL untuk history functionality
     */
    public function subUnit()
    {
        try {
            return $this->belongsTo(SubUnit::class, 'sub_unit_id', 'id');
        } catch (\Exception $e) {
            Log::warning('Employee subUnit relationship error: ' . $e->getMessage(), [
                'employee_id' => $this->id ?? 'unknown',
                'sub_unit_id' => $this->sub_unit_id ?? 'null'
            ]);
            return null;
        }
    }

    // =====================================================
    // NEW UNIT DISPLAY ACCESSORS - CONSISTENCY WITH DASHBOARD
    // =====================================================

    /**
     * UPDATED: Unit display mapping untuk format (XX) Nama Unit - konsisten dengan DashboardController
     * Mapping berdasarkan field kode_organisasi dari seeder
     */
    private function getUnitDisplayMapping()
    {
        return [
            'EGM' => 'EGM',
            'GM' => 'GM',
            'MO' => '(MO) Movement Operations',
            'ME' => '(ME) Maintenance Equipment',
            'MF' => '(MF) Movement Flight',
            'MS' => '(MS) Movement Service',
            'MU' => '(MU) Management Unit',
            'MK' => '(MK) Management Keuangan',
            'MQ' => '(MQ) Management Quality',
            'MB' => '(MB) Management Business',
        ];
    }

    /**
     * UPDATED: Legacy unit code mapping untuk backward compatibility
     * Digunakan jika kode_organisasi tidak tersedia
     */
    private function getLegacyUnitCodeMapping()
    {
        return [
            'Airside' => [
                'Movement Operations' => 'MO',
                'Maintenance Equipment' => 'ME',
            ],
            'Landside' => [
                'Movement Flight' => 'MF',
                'Movement Service' => 'MS',
            ],
            'Back Office' => [
                'Management Keuangan' => 'MK',
                'Management Unit' => 'MU',
            ],
            'SSQC' => [
                'Management Quality' => 'MQ',
            ],
            'Ancillary' => [
                'Management Business' => 'MB',
            ],
        ];
    }

    /**
     * NEW: Accessor untuk unit organisasi dengan format kode - konsisten dengan dashboard
     * Priority 1: kode_organisasi, Priority 2: legacy mapping, Priority 3: original value
     */
    public function getUnitOrganisasiFormattedAttribute()
    {
        try {
            // Priority 1: Gunakan kode_organisasi untuk mapping langsung
            if (!empty($this->kode_organisasi)) {
                $mapping = $this->getUnitDisplayMapping();
                $formatted = $mapping[$this->kode_organisasi] ?? null;
                
                if ($formatted) {
                    Log::debug('Unit organisasi formatted using kode_organisasi', [
                        'employee_id' => $this->id,
                        'kode_organisasi' => $this->kode_organisasi,
                        'formatted' => $formatted
                    ]);
                    return $formatted;
                }
            }

            // Priority 2: Gunakan legacy mapping jika ada unit relationship
            if (!empty($this->unit_organisasi) && $this->relationLoaded('unit') && $this->unit) {
                $legacyMapping = $this->getLegacyUnitCodeMapping();
                
                if (isset($legacyMapping[$this->unit_organisasi][$this->unit->name])) {
                    $code = $legacyMapping[$this->unit_organisasi][$this->unit->name];
                    $formatted = "({$code}) {$this->unit->name}";
                    
                    Log::debug('Unit organisasi formatted using legacy mapping', [
                        'employee_id' => $this->id,
                        'unit_organisasi' => $this->unit_organisasi,
                        'unit_name' => $this->unit->name,
                        'formatted' => $formatted
                    ]);
                    return $formatted;
                }
            }

            // Priority 3: Fallback ke unit_organisasi original
            Log::debug('Unit organisasi using fallback', [
                'employee_id' => $this->id,
                'unit_organisasi' => $this->unit_organisasi
            ]);
            return $this->unit_organisasi ?: 'Unit tidak tersedia';

        } catch (\Exception $e) {
            Log::warning('Unit organisasi formatted accessor error: ' . $e->getMessage(), [
                'employee_id' => $this->id ?? 'unknown'
            ]);
            return $this->unit_organisasi ?: 'Unit tidak tersedia';
        }
    }

    /**
     * UPDATED: Accessor untuk unit display lengkap dengan format yang benar
     * Unit organisasi tetap original, unit diformat dengan kode
     */
    public function getUnitDisplayFormattedAttribute()
    {
        try {
            $parts = [];

            // Part 1: Unit organisasi (tetap original, tidak diformat)
            if ($this->unit_organisasi) {
                $parts[] = $this->unit_organisasi;
            }

            // Part 2: Unit dengan format kode (menggunakan unit_formatted)
            $unitFormatted = $this->unit_formatted;
            if ($unitFormatted && $unitFormatted !== 'Unit tidak tersedia' && $unitFormatted !== $this->unit_organisasi) {
                $parts[] = $unitFormatted;
            }

            // Part 3: Sub unit name (tetap original)
            if ($this->relationLoaded('subUnit') && $this->subUnit) {
                $parts[] = $this->subUnit->name;
            }

            // Gabungkan dengan separator
            $fullDisplay = implode(' > ', $parts);
            
            // Tambahkan nama organisasi jika tersedia dan berbeda
            if (!empty($this->nama_organisasi) && 
                $this->nama_organisasi !== $this->unit_organisasi &&
                !str_contains($fullDisplay, $this->nama_organisasi)) {
                $fullDisplay .= ' - ' . $this->nama_organisasi;
            }

            Log::debug('Unit display formatted accessor result', [
                'employee_id' => $this->id,
                'kode_organisasi' => $this->kode_organisasi,
                'unit_organisasi' => $this->unit_organisasi,
                'unit_formatted' => $unitFormatted,
                'sub_unit_name' => $this->subUnit->name ?? null,
                'nama_organisasi' => $this->nama_organisasi,
                'formatted_result' => $fullDisplay
            ]);

            return $fullDisplay ?: 'Unit tidak tersedia';

        } catch (\Exception $e) {
            Log::warning('Unit display formatted accessor error: ' . $e->getMessage(), [
                'employee_id' => $this->id ?? 'unknown'
            ]);
            return $this->unit_organisasi ?: 'Unit tidak tersedia';
        }
    }

    // =====================================================
    // NEW FIELD METHODS - AUTO CALCULATION
    // =====================================================

    /**
     * NEW: Calculate masa kerja from TMT mulai kerja
     */
    public function calculateMasaKerja()
    {
        if (!$this->tmt_mulai_kerja) {
            return null;
        }

        $startDate = Carbon::parse($this->tmt_mulai_kerja);
        $endDate = Carbon::now('Asia/Makassar');
        
        $years = $endDate->diffInYears($startDate);
        $months = $endDate->diffInMonths($startDate) % 12;

        if ($years > 0 && $months > 0) {
            return "{$years} tahun {$months} bulan";
        } elseif ($years > 0) {
            return "{$years} tahun";
        } elseif ($months > 0) {
            return "{$months} bulan";
        } else {
            return "Kurang dari 1 bulan";
        }
    }

    /**
     * NEW: Calculate status kerja based on tmt_berakhir_kerja
     */
    public function calculateStatusKerja()
    {
        if (!$this->tmt_berakhir_kerja) {
            return 'Non-Aktif';
        }

        $today = Carbon::now('Asia/Makassar');
        $endDate = Carbon::parse($this->tmt_berakhir_kerja);

        if ($today->lte($endDate)) {
            return 'Aktif';
        } else {
            return 'Non-Aktif';
        }
    }

    /**
     * NEW: Update calculated fields automatically
     */
    public function updateCalculatedFields()
    {
        // Set fixed values
        $this->lokasi_kerja = 'Bandar Udara Ngurah Rai';
        $this->cabang = 'DPS';

        // Calculate masa kerja
        if ($this->tmt_mulai_kerja) {
            $this->masa_kerja = $this->calculateMasaKerja();
        }

        // Auto-set status kerja based on tmt_berakhir_kerja
        $this->status_kerja = $this->calculateStatusKerja();
    }

    // =====================================================
    // NEW SCOPES FOR NEW FIELDS
    // =====================================================

    /**
     * NEW: Scope for active employees by status_kerja
     */
    public function scopeStatusKerjaAktif($query)
    {
        return $query->where('status_kerja', 'Aktif');
    }

    /**
     * NEW: Scope for employees by provider
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * NEW: Scope for employees by grade
     */
    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade', $grade);
    }

    // =====================================================
    // NEW MUTATORS FOR VALIDATION
    // =====================================================

    /**
     * NEW: Mutator to ensure tmt_akhir_jabatan is after tmt_mulai_jabatan
     */
    public function setTmtAkhirJabatanAttribute($value)
    {
        if ($value && $this->tmt_mulai_jabatan) {
            $mulaiJabatan = Carbon::parse($this->tmt_mulai_jabatan);
            $akhirJabatan = Carbon::parse($value);
            
            if ($akhirJabatan->lte($mulaiJabatan)) {
                throw new \InvalidArgumentException('TMT Akhir Jabatan harus diatas tanggal TMT Mulai Jabatan');
            }
        }
        
        $this->attributes['tmt_akhir_jabatan'] = $value;
    }

    /**
     * NEW: Mutator to ensure tmt_berakhir_kerja is after tmt_mulai_kerja
     */
    public function setTmtBerakhirKerjaAttribute($value)
    {
        if ($value && $this->tmt_mulai_kerja) {
            $mulaiKerja = Carbon::parse($this->tmt_mulai_kerja);
            $berakhirKerja = Carbon::parse($value);
            
            if ($berakhirKerja->lte($mulaiKerja)) {
                throw new \InvalidArgumentException('TMT Berakhir Kerja harus diatas tanggal TMT Mulai Kerja');
            }
        }
        
        $this->attributes['tmt_berakhir_kerja'] = $value;
    }

    /**
     * NEW: Accessor for formatted masa kerja
     */
    public function getMasaKerjaFormattedAttribute()
    {
        return $this->masa_kerja ?: $this->calculateMasaKerja();
    }

    // =====================================================
    // ENHANCED ACCESSORS - CRITICAL FOR HISTORY MODAL
    // =====================================================

    /**
     * FIXED: Enhanced organizational structure accessor 
     * CRITICAL: Digunakan oleh DashboardController untuk history modal
     */
    public function getOrganizationalStructureAttribute()
    {
        try {
            $structure = [
                'unit_organisasi' => $this->unit_organisasi_formatted, // Use formatted version
                'unit' => null,
                'sub_unit' => null,
                'full_structure' => null
            ];

            // Try to get unit from relationship
            if ($this->relationLoaded('unit') && $this->unit) {
                $structure['unit'] = [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                    'code' => $this->unit->code ?? null
                ];
            } else if ($this->unit_id) {
                // Fallback: load unit if not already loaded
                try {
                    $unit = Unit::find($this->unit_id);
                    if ($unit) {
                        $structure['unit'] = [
                            'id' => $unit->id,
                            'name' => $unit->name,
                            'code' => $unit->code ?? null
                        ];
                    }
                } catch (\Exception $unitError) {
                    Log::debug('Unit fallback loading failed: ' . $unitError->getMessage());
                }
            }

            // Try to get sub unit from relationship
            if ($this->relationLoaded('subUnit') && $this->subUnit) {
                $structure['sub_unit'] = [
                    'id' => $this->subUnit->id,
                    'name' => $this->subUnit->name,
                    'code' => $this->subUnit->code ?? null
                ];
            } else if ($this->sub_unit_id) {
                // Fallback: load sub unit if not already loaded
                try {
                    $subUnit = SubUnit::find($this->sub_unit_id);
                    if ($subUnit) {
                        $structure['sub_unit'] = [
                            'id' => $subUnit->id,
                            'name' => $subUnit->name,
                            'code' => $subUnit->code ?? null
                        ];
                    }
                } catch (\Exception $subUnitError) {
                    Log::debug('SubUnit fallback loading failed: ' . $subUnitError->getMessage());
                }
            }

            // Build full structure string for display using formatted unit organisasi
            $fullStructureParts = [];
            
            if ($structure['unit_organisasi']) {
                $fullStructureParts[] = $structure['unit_organisasi'];
            }
            
            if ($structure['unit'] && !empty($structure['unit']['name'])) {
                // Only add unit name if not already covered in formatted unit organisasi
                if (!str_contains($structure['unit_organisasi'], $structure['unit']['name'])) {
                    $fullStructureParts[] = $structure['unit']['name'];
                }
            }
            
            if ($structure['sub_unit'] && !empty($structure['sub_unit']['name'])) {
                $fullStructureParts[] = $structure['sub_unit']['name'];
            }
            
            $structure['full_structure'] = implode(' > ', $fullStructureParts);
            
            // Ensure we always have at least unit_organisasi
            if (empty($structure['full_structure']) && $structure['unit_organisasi']) {
                $structure['full_structure'] = $structure['unit_organisasi'];
            }
            
            // Final fallback
            if (empty($structure['full_structure'])) {
                $structure['full_structure'] = 'Struktur organisasi tidak tersedia';
            }

            Log::debug('Organizational structure accessor called', [
                'employee_id' => $this->id,
                'full_structure' => $structure['full_structure']
            ]);

            return $structure;

        } catch (\Exception $e) {
            Log::warning('Organizational structure accessor error: ' . $e->getMessage(), [
                'employee_id' => $this->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'unit_organisasi' => $this->unit_organisasi ?? 'Tidak tersedia',
                'unit' => null,
                'sub_unit' => null,
                'full_structure' => $this->unit_organisasi ?? 'Struktur organisasi tidak tersedia'
            ];
        }
    }

    /**
     * UPDATED: Enhanced unit display attribute dengan better fallback dan format kode
     */
    public function getUnitDisplayAttribute()
    {
        try {
            // Use formatted unit organisasi as base
            $display = $this->unit_organisasi_formatted ?? 'Unit tidak tersedia';
            
            if ($this->relationLoaded('unit') && $this->unit) {
                // Only add unit name if not already covered in formatted unit organisasi
                if (!str_contains($display, $this->unit->name)) {
                    $display .= ' > ' . $this->unit->name;
                }
            } else if ($this->unit_id) {
                try {
                    $unit = Unit::find($this->unit_id);
                    if ($unit && !str_contains($display, $unit->name)) {
                        $display .= ' > ' . $unit->name;
                    }
                } catch (\Exception $e) {
                    Log::debug('Unit display fallback error: ' . $e->getMessage());
                }
            }
            
            if ($this->relationLoaded('subUnit') && $this->subUnit) {
                $display .= ' > ' . $this->subUnit->name;
            } else if ($this->sub_unit_id) {
                try {
                    $subUnit = SubUnit::find($this->sub_unit_id);
                    if ($subUnit) {
                        $display .= ' > ' . $subUnit->name;
                    }
                } catch (\Exception $e) {
                    Log::debug('SubUnit display fallback error: ' . $e->getMessage());
                }
            }
            
            return $display;
            
        } catch (\Exception $e) {
            Log::warning('Unit display error: ' . $e->getMessage());
            return $this->unit_organisasi ?? 'Unit tidak tersedia';
        }
    }

    /**
     * FIXED: Enhanced initials accessor untuk history modal
     */
    public function getInitialsAttribute()
    {
        if (empty($this->nama_lengkap)) {
            return 'N';
        }

        try {
            $words = explode(' ', trim($this->nama_lengkap));
            
            if (count($words) === 1) {
                return strtoupper(substr($words[0], 0, 1));
            }
            
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } catch (\Exception $e) {
            Log::debug('Initials accessor error: ' . $e->getMessage(), [
                'employee_id' => $this->id ?? 'unknown',
                'nama_lengkap' => $this->nama_lengkap
            ]);
            return 'N';
        }
    }

    /**
     * FIXED: Get employee's full name safely
     */
    public function getFullNameAttribute()
    {
        return $this->nama_lengkap ?? 'Nama tidak tersedia';
    }

    /**
     * FIXED: Get employee's job title safely
     */
    public function getJobTitleAttribute()
    {
        return $this->jabatan ?? $this->nama_jabatan ?? 'Jabatan tidak tersedia';
    }

    /**
     * FIXED: Check if employee is recently added (within 30 days)
     */
    public function getIsRecentlyAddedAttribute()
    {
        if (!$this->created_at) {
            return false;
        }
        
        return $this->created_at->isAfter(now()->subDays(30));
    }

    /**
     * FIXED: Get days since employee was added
     */
    public function getDaysSinceAddedAttribute()
    {
        if (!$this->created_at) {
            return null;
        }
        
        return $this->created_at->diffInDays(now());
    }

    /**
     * FIXED: Get formatted creation date
     */
    public function getFormattedCreatedDateAttribute()
    {
        if (!$this->created_at) {
            return 'Tanggal tidak tersedia';
        }
        
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * FIXED: Get relative creation date (e.g., "2 days ago")
     */
    public function getRelativeCreatedDateAttribute()
    {
        if (!$this->created_at) {
            return 'Tanggal tidak tersedia';
        }
        
        return $this->created_at->diffForHumans();
    }

    // =====================================================
    // ENHANCED SCOPES UNTUK HISTORY MODAL - CRITICAL
    // =====================================================

    /**
     * FIXED: Scope untuk employees dalam periode tertentu - CRITICAL untuk history
     */
    public function scopeCreatedBetween(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * FIXED: Scope untuk employees yang baru ditambahkan (30 hari terakhir) - CRITICAL
     */
    public function scopeRecentlyAdded(Builder $query, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk filter berdasarkan status aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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
              ->orWhere('alamat', 'like', $searchTerm)
              ->orWhere('provider', 'like', $searchTerm) // NEW: Include provider in search
              ->orWhere('unit_kerja_kontrak', 'like', $searchTerm) // NEW: Include unit_kerja_kontrak in search
              ->orWhere('grade', 'like', $searchTerm); // NEW: Include grade in search
              
            // FIXED: Search dalam unit dan sub unit dengan enhanced error handling
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
                Log::debug('Unit/SubUnit relationship search failed: ' . $e->getMessage());
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
     * FIXED: Filter berdasarkan unit dengan nama atau ID - Enhanced untuk history
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
            Log::debug('Unit scope relationship error: ' . $e->getMessage());
        }
        
        // Fallback: cari berdasarkan unit_organisasi
        return $query->where('unit_organisasi', 'like', '%' . $unitValue . '%');
    }

    /**
     * FIXED: Filter berdasarkan sub unit dengan nama atau ID - Enhanced untuk history
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
            Log::debug('SubUnit scope relationship error: ' . $e->getMessage());
        }
        
        // Fallback: return empty query jika relationship belum ada
        return $query->whereRaw('1 = 0'); // Return no results
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
     * UPDATED: Comprehensive filter scope untuk pagination dengan error handling dan field baru
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

            // NEW: Filters for new fields
            if (!empty($filters['provider'])) {
                $query->byProvider($filters['provider']);
            }

            if (!empty($filters['status_kerja'])) {
                $query->where('status_kerja', $filters['status_kerja']);
            }

            if (!empty($filters['grade'])) {
                $query->byGrade($filters['grade']);
            }

        } catch (\Exception $e) {
            Log::error('Error applying filters: ' . $e->getMessage());
            // Return query tanpa filter jika ada error
        }

        return $query;
    }

    // =====================================================
    // OTHER ACCESSORS & MUTATORS (unchanged from original)
    // =====================================================

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
     * UPDATED: Get comprehensive statistics untuk dashboard dengan field baru
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
            
            // NEW: Status kerja statistics
            'status_kerja_aktif' => self::where('status_kerja', 'Aktif')->count(),
            'status_kerja_non_aktif' => self::where('status_kerja', 'Non-Aktif')->count(),
            'status_kerja_pensiun' => self::where('status_kerja', 'Pensiun')->count(),
            'status_kerja_mutasi' => self::where('status_kerja', 'Mutasi')->count(),
            
            // NEW: Provider statistics
            'by_provider' => self::getByProvider(),
            
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
     * NEW: Get employees by provider dengan count
     */
    public static function getByProvider()
    {
        return self::select('provider', \DB::raw('count(*) as total'))
                   ->whereNotNull('provider')
                   ->where('provider', '!=', '')
                   ->where('status', 'active')
                   ->groupBy('provider')
                   ->orderBy('total', 'desc')
                   ->get()
                   ->map(function ($item) {
                       return [
                           'name' => $item->provider,
                           'count' => $item->total,
                       ];
                   });
    }

    /**
     * UPDATED: Get filter options untuk dropdown dengan field baru
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
            
            // NEW: Filter options for new fields
            'providers' => self::whereNotNull('provider')
                              ->where('provider', '!=', '')
                              ->distinct()
                              ->orderBy('provider')
                              ->pluck('provider')
                              ->filter()
                              ->values(),
            
            'grades' => self::whereNotNull('grade')
                           ->where('grade', '!=', '')
                           ->distinct()
                           ->orderBy('grade')
                           ->pluck('grade')
                           ->filter()
                           ->values(),
            
            'status_kerja' => self::STATUS_KERJA_OPTIONS,
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
     * ENHANCED: Pagination dengan filter dan search - method utama untuk controller
     * UPDATED: Load unit dan sub unit relationships untuk history functionality
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
     * ENHANCED: Get export data dengan filter dan proper relationship loading
     * UPDATED: Load unit dan sub unit untuk export dengan history support
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
     * UPDATED: Calculate profile completion percentage dengan field baru
     */
    public function getProfileCompletionPercentage()
    {
        $fields = [
            'nik', 'nip', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 
            'tanggal_lahir', 'alamat', 'handphone', 'email',
            'unit_organisasi', 'nama_jabatan', 'status_pegawai', 'kelompok_jabatan',
            'tmt_mulai_jabatan', 'pendidikan_terakhir', 'jenis_sepatu', 
            'ukuran_sepatu', 'seragam', 'provider', 'grade'
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
     * UPDATED: Generate ID card data dengan field baru
     */
    public function getIdCardData()
    {
        return [
            'id' => $this->id, // FIXED: Include ID
            'nik' => $this->nik,
            'nip' => $this->nip,
            'nama_lengkap' => $this->nama_lengkap,
            'jabatan' => $this->nama_jabatan ?: $this->jabatan,
            'unit_organisasi' => $this->unit_organisasi_formatted, // Use formatted version
            'unit_display' => $this->unit_display_formatted, // Use formatted version
            'kelompok_jabatan' => $this->kelompok_jabatan,
            'provider' => $this->provider,
            'grade' => $this->grade,
            'lokasi_kerja' => $this->lokasi_kerja,
            'cabang' => $this->cabang,
            'initials' => $this->initials,
            'foto_url' => null, // Placeholder for future photo implementation
        ];
    }

    /**
     * UPDATED: Method untuk mendapatkan informasi ringkas employee dengan field baru
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id, // FIXED: ID sebagai primary identifier
            'nik' => $this->nik,
            'nip' => $this->nip,
            'nama' => $this->nama_lengkap,
            'unit' => $this->unit_organisasi_formatted, // Use formatted version
            'jabatan' => $this->nama_jabatan,
            'status' => $this->status_pegawai,
            'status_kerja' => $this->status_kerja,
            'provider' => $this->provider,
            'grade' => $this->grade,
            'masa_kerja' => $this->masa_kerja,
        ];
    }

    // =====================================================
    // UPDATED BOOT METHOD - AUTO CALCULATIONS WITH NEW FIELDS
    // =====================================================

    /**
     * UPDATED: Boot method dengan logika untuk field baru
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
                $employee->status_kerja = 'Non-Aktif';
            }

            // NEW: Set default lokasi kerja dan cabang
            if (empty($employee->lokasi_kerja)) {
                $employee->lokasi_kerja = 'Bandar Udara Ngurah Rai';
            }
            
            if (empty($employee->cabang)) {
                $employee->cabang = 'DPS';
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

            // NEW: Auto-calculate masa kerja and status kerja
            $employee->updateCalculatedFields();
        });

        // ENHANCED: Event ketika employee dibuat (untuk history tracking)
        static::created(function ($employee) {
            try {
                Log::info('EMPLOYEE CREATED: New employee added to system', [
                    'employee_id' => $employee->id,
                    'nik' => $employee->nik,
                    'nama_lengkap' => $employee->nama_lengkap,
                    'unit_organisasi' => $employee->unit_organisasi,
                    'unit_organisasi_formatted' => $employee->unit_organisasi_formatted,
                    'unit_id' => $employee->unit_id,
                    'sub_unit_id' => $employee->sub_unit_id,
                    'provider' => $employee->provider,
                    'grade' => $employee->grade,
                    'status_kerja' => $employee->status_kerja,
                    'created_at' => $employee->created_at
                ]);
            } catch (\Exception $e) {
                Log::warning('Employee creation logging failed: ' . $e->getMessage());
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

            // NEW: Auto-update calculated fields when relevant fields change
            if ($employee->isDirty(['tmt_mulai_kerja', 'tmt_berakhir_kerja'])) {
                $employee->updateCalculatedFields();
            }
        });

        // ENHANCED: Event ketika employee di-update (untuk history tracking)
        static::updated(function ($employee) {
            try {
                Log::info('EMPLOYEE UPDATED: Employee data modified', [
                    'employee_id' => $employee->id,
                    'nik' => $employee->nik,
                    'nama_lengkap' => $employee->nama_lengkap,
                    'unit_organisasi_formatted' => $employee->unit_organisasi_formatted,
                    'updated_at' => $employee->updated_at
                ]);
            } catch (\Exception $e) {
                Log::warning('Employee update logging failed: ' . $e->getMessage());
            }
        });
    }
}