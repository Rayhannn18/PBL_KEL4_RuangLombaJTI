<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tim extends Model
{
    use HasFactory;

    protected $table = 'tim';
    protected $primaryKey = 'id_tim';

    protected $fillable = [
        'nama_tim',
        'id_lomba',
        'nim',
        'kuota_anggota',
        'status_tim',
        'id_admin',
    ];

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'id_lomba', 'id_lomba');
    }

    public function ketua()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function anggota()
    {
        return $this->hasMany(AnggotaTim::class, 'id_tim', 'id_tim');
    }

    public function anggotaDiterima()
    {
        return $this->hasMany(AnggotaTim::class, 'id_tim', 'id_tim')
                    ->where('status_gabung', 'diterima');
    }

    public function pengajuanBimbingan()
    {
        return $this->hasMany(PengajuanBimbingan::class, 'id_tim', 'id_tim');
    }

    public function bimbinganDisetujui()
    {
        return $this->hasOne(PengajuanBimbingan::class, 'id_tim', 'id_tim')
                    ->where('status', 'disetujui');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function getSisaKuotaAnggotaAttribute()
    {
        return max(0, $this->kuota_anggota - $this->anggotaDiterima()->count());
    }
}
