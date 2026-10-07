<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaTim extends Model
{
    use HasFactory;

    protected $table = 'anggota_tim';
    protected $primaryKey = 'id_anggota';

    protected $fillable = [
        'id_tim',
        'nim',
        'status_gabung',
        'peran',
    ];

    public function tim()
    {
        return $this->belongsTo(Tim::class, 'id_tim', 'id_tim');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function setujuiAnggota()
    {
        $this->update(['status_gabung' => 'diterima']);
        return $this;
    }

    public function tolakAnggota()
    {
        $this->update(['status_gabung' => 'ditolak']);
        return $this;
    }
}
