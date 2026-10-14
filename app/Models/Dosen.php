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
        return max(0, $this->kuota_bimbingan - $this->bimbinganAktif()->count());
    }
}
