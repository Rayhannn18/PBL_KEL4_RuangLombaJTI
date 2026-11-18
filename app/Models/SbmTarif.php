<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SbmTarif extends Model
{
    use HasFactory;

    protected $table = 'sbm_tarif';

    protected $fillable = [
        'kode',
        'uraian',
        'provinsi',
        'satuan',
        'tarif',
        'sifat',
        'tahun_anggaran',
        'sumber',
    ];

    protected $casts = [
        'tarif' => 'decimal:2',
        'tahun_anggaran' => 'integer',
    ];
}
