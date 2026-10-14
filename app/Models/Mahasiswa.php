<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';
    protected $primaryKey = 'nim';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nim',
        'nama',
        'email_kampus',
        'password',
        'prodi',
        'angkatan',
    ];

    protected $hidden = [
        'password',
    ];

    public function timKetua()
    {
        return $this->hasMany(Tim::class, 'nim', 'nim');
    }

    public function keanggotaan()
    {
        return $this->hasMany(AnggotaTim::class, 'nim', 'nim');
    }

    public function lombaDiajukan()
    {
        return $this->hasMany(Lomba::class, 'nim', 'nim');
    }
}
