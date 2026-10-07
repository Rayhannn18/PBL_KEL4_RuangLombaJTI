<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgresBabak extends Model
{
    use HasFactory;

    protected $table = 'progres_babak';
    protected $primaryKey = 'id_progres';

    protected $fillable = [
        'id_pengajuan',
        'babak',
        'tanggal_update',
        'hasil_akhir',
        'status_setuju',
    ];

    protected $casts = [
        'tanggal_update' => 'date',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanBimbingan::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function setujuiProgres()
    {
        $this->update(['status_setuju' => 'disetujui']);
        return $this;
    }

    public function isJuara(): bool
    {
        if (!$this->hasil_akhir) return false;
        $lower = strtolower($this->hasil_akhir);
        return str_contains($lower, 'juara') || str_contains($lower, 'gold') || str_contains($lower, 'silver') || str_contains($lower, 'bronze') || str_contains($lower, 'best');
    }
}
