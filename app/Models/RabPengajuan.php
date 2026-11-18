<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabPengajuan extends Model
{
    use HasFactory;

    protected $table = 'rab_pengajuan';

    protected $fillable = [
        'tim_id',
        'lomba_id',
        'babak',
        'tanggal_mulai',
        'tanggal_selesai',
        'kota_tujuan',
        'provinsi_tujuan',
        'jumlah_peserta',
        'total_anggaran',
        'status',
        'link_dokumen',
        'catatan_dosen',
        'diisi_terakhir_oleh',
        'divalidasi_oleh',
        'divalidasi_pada',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_peserta' => 'integer',
        'total_anggaran' => 'decimal:2',
        'divalidasi_pada' => 'datetime',
    ];

    public function tim()
    {
        return $this->belongsTo(Tim::class, 'tim_id', 'id_tim');
    }

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'lomba_id', 'id_lomba');
    }

    public function items()
    {
        return $this->hasMany(RabItem::class, 'rab_pengajuan_id', 'id');
    }

    public function riwayat()
    {
        return $this->hasMany(RabRiwayat::class, 'rab_pengajuan_id', 'id')
            ->orderBy('created_at', 'desc');
    }

    public function pengisiTerakhir()
    {
        return $this->belongsTo(Mahasiswa::class, 'diisi_terakhir_oleh', 'nim');
    }

    public function validatorDosen()
    {
        return $this->belongsTo(Dosen::class, 'divalidasi_oleh', 'nidn');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'perlu_revisi'], true);
    }

    public function labelBabak(): string
    {
        return match ($this->babak) {
            'semi_final' => 'Semi Final',
            'final' => 'Final',
            default => ucfirst((string) $this->babak),
        };
    }

    public function labelStatus(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'menunggu_validasi' => 'Menunggu Validasi Dosen',
            'perlu_revisi' => 'Perlu Revisi',
            'disetujui' => 'Disetujui Dosen',
            default => ucfirst((string) $this->status),
        };
    }
}
