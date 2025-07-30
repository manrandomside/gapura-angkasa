<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'instansi_pendidikan',
        'jurusan',
        'remarks_pendidikan',
        'tahun_lulus',
        'handphone',
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
     * Relationship dengan Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope untuk filter berdasarkan status aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk filter berdasarkan status pegawai
     */
    public function scopeByStatusPegawai($query, $status)
    {
        return $query->where('status_pegawai', $status);
    }

    /**
     * Scope untuk filter berdasarkan unit organisasi
     */
    public function scopeByUnitOrganisasi($query, $unit)
    {
        return $query->where('unit_organisasi', $unit);
    }

    /**
     * Scope untuk search berdasarkan nama, NIP, atau unit
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nama_lengkap', 'like', "%{$term}%")
              ->orWhere('nip', 'like', "%{$term}%")
              ->orWhere('unit_organisasi', 'like', "%{$term}%")
              ->orWhere('nama_jabatan', 'like', "%{$term}%");
        });
    }

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
        $names = explode(' ', $this->nama_lengkap);
        $initials = '';
        
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper($name[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        
        return $initials;
    }

    /**
     * Accessor untuk mendapatkan email default berdasarkan nama
     */
    public function getEmailDefaultAttribute()
    {
        $name = strtolower(str_replace(' ', '.', $this->nama_lengkap));
        $name = preg_replace('/[^a-z0-9.]/', '', $name);
        return $name . '@gapura.com';
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
     * Mutator untuk format handphone
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
            
            $this->attributes['handphone'] = $cleaned;
        } else {
            $this->attributes['handphone'] = null;
        }
    }

    /**
     * Static method untuk mendapatkan statistik karyawan
     */
    public static function getStatistics()
    {
        return [
            'total' => self::count(),
            'active' => self::active()->count(),
            'pegawai_tetap' => self::where('status_pegawai', 'PEGAWAI TETAP')->count(),
            'tad' => self::where('status_pegawai', 'TAD')->count(),
            'laki_laki' => self::where('jenis_kelamin', 'L')->count(),
            'perempuan' => self::where('jenis_kelamin', 'P')->count(),
        ];
    }

    /**
     * Static method untuk mendapatkan karyawan berdasarkan unit organisasi
     */
    public static function getByUnitOrganisasi()
    {
        return self::select('unit_organisasi', \DB::raw('count(*) as total'))
                   ->whereNotNull('unit_organisasi')
                   ->groupBy('unit_organisasi')
                   ->orderBy('total', 'desc')
                   ->get();
    }

    /**
     * Static method untuk mendapatkan karyawan yang akan pensiun dalam waktu dekat
     */
    public static function getUpcomingRetirement($months = 12)
    {
        $endDate = Carbon::now()->addMonths($months);
        
        return self::whereNotNull('tmt_pensiun')
                   ->whereBetween('tmt_pensiun', [Carbon::now(), $endDate])
                   ->orderBy('tmt_pensiun', 'asc')
                   ->get();
    }

    /**
     * Static method untuk validasi NIP unik
     */
    public static function isNipUnique($nip, $excludeId = null)
    {
        $query = self::where('nip', $nip);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->doesntExist();
    }
}