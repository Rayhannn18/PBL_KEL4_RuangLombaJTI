<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LombaDivisi extends Model
{
    use HasFactory;

    protected $table = 'lomba_divisi';

    protected $fillable = [
        'id_lomba',
        'nama_divisi',
        'kode_icon',
    ];

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'id_lomba', 'id_lomba');
    }

    public function tim()
    {
        return $this->hasMany(Tim::class, 'id_divisi', 'id');
    }
}
