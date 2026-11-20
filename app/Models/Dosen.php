<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    protected $primaryKey = 'nip';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nip',
        'nama',
        'email_kampus',
        'password',
        'bidang_keahlian',
        'kuota_bimbingan',
    ];

    protected $hidden = [
        'password',
    ];

    public function pengajuanBimbingan()
    {
        return $this->hasMany(PengajuanBimbingan::class, 'nip', 'nip');
    }

    public function bimbinganAktif()
    {
        return $this->hasMany(PengajuanBimbingan::class, 'nip', 'nip')
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
