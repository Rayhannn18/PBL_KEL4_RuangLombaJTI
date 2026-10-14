<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanBimbingan extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_bimbingan';
    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'id_tim',
        'nidn',
        'tgl_pengajuan',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tgl_pengajuan' => 'date',
    ];

    public function tim()
    {
        return $this->belongsTo(Tim::class, 'id_tim', 'id_tim');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'nidn', 'nidn');
    }

    public function logbook()
    {
        return $this->hasMany(Logbook::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function progresBabak()
    {
        return $this->hasMany(ProgresBabak::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function setujui()
    {
        $this->update(['status' => 'disetujui']);
        return $this;
    }

    public function tolak(string $catatan = '')
    {
        $this->update([
            'status' => 'ditolak',
            'catatan' => $catatan
        ]);
        return $this;
    }
}
