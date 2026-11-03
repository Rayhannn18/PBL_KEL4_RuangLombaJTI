<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    protected $table = 'logbook';

    protected $primaryKey = 'id_logbook';

    protected $fillable = [
        'id_pengajuan',
        'tanggal',
        'materi',
        'tindak_lanjut',
        'status_validasi',
        'catatan_dosen',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanBimbingan::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function validasi(string $status = 'disetujui', ?string $catatanDosen = null)
    {
        $this->update([
            'status_validasi' => $status,
            'catatan_dosen' => $catatanDosen,
        ]);

        return $this;
    }
}
