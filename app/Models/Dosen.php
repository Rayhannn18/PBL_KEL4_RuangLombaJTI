<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $primaryKey = 'nidn';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nidn',
        'nama',
        'email_kampus',
        'password',
        'bidang_keahlian',
        'kuota_bimbingan',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Accessor: $dosen->nip mengembalikan nilai kolom nidn.
     * Berguna untuk tampilan yang memakai istilah "NIP".
     */
    public function getNipAttribute(): ?string
    {
        return $this->attributes['nidn'] ?? null;
    }

    public function pengajuanBimbingan()
    {
        return $this->hasMany(PengajuanBimbingan::class, 'nidn', 'nidn');
    }

    public function bimbinganAktif()
    {
        return $this->hasMany(PengajuanBimbingan::class, 'nidn', 'nidn')
            ->where('status', 'disetujui');
    }

    public function getSisaKuotaAttribute()
    {
        $aktifCount = $this->relationLoaded('bimbinganAktif')
            ? $this->bimbinganAktif->count()
            : ($this->relationLoaded('pengajuanBimbingan')
                ? $this->pengajuanBimbingan->where('status', 'disetujui')->count()
                : $this->bimbinganAktif()->count());

        $kuota = $this->kuota_bimbingan ?? 0;

        return max(0, $kuota - $aktifCount);
    }
}
