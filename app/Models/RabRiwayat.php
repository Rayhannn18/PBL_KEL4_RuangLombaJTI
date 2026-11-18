<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabRiwayat extends Model
{
    use HasFactory;

    protected $table = 'rab_riwayat';

    public $timestamps = false;

    protected $fillable = [
        'rab_pengajuan_id',
        'aksi',
        'status_dari',
        'status_ke',
        'catatan',
        'user_id',
        'total_anggaran',
        'created_at',
    ];

    protected $casts = [
        'total_anggaran' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function rabPengajuan()
    {
        return $this->belongsTo(RabPengajuan::class, 'rab_pengajuan_id', 'id');
    }
}
