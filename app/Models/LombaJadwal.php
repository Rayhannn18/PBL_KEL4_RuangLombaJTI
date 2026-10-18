<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LombaJadwal extends Model
{
    use HasFactory;

    protected $table = 'lomba_jadwal';

    protected $fillable = [
        'id_lomba',
        'nama_tahap',
        'tanggal_mulai',
        'tanggal_selesai',
        'urutan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'urutan'          => 'integer',
    ];

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'id_lomba', 'id_lomba');
    }

    /**
     * Hitung status tahapan jadwal secara dinamis berdasarkan tanggal sekarang:
     * - 'sedang_berjalan' jika hari ini berada di antara tanggal_mulai dan tanggal_selesai
     * - 'selesai' jika hari ini sudah melewati tanggal_selesai
     * - 'akan_datang' jika hari ini belum mencapai tanggal_mulai
     */
    public function getStatusAttribute(): string
    {
        $today = Carbon::today();

        if ($this->tanggal_selesai && $today->gt($this->tanggal_selesai)) {
            return 'selesai';
        }

        if ($this->tanggal_mulai && $this->tanggal_selesai && $today->betweenIncluded($this->tanggal_mulai, $this->tanggal_selesai)) {
            return 'sedang_berjalan';
        }

        return 'akan_datang';
    }

    public function getStatusLabelAttribute(): ?string
    {
        return match ($this->status) {
            'sedang_berjalan' => 'sedang berjalan',
            'selesai'         => 'selesai',
            'akan_datang'     => null,
            default           => null,
        };
    }

    public function getWaktuFormattedAttribute(): string
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai) {
            return '-';
        }

        $mulai = $this->tanggal_mulai;
        $selesai = $this->tanggal_selesai;

        if ($mulai->day === 1 && $selesai->isLastOfMonth() && $mulai->isSameMonth($selesai)) {
            return $mulai->translatedFormat('F Y');
        }

        if ($mulai->isSameMonth($selesai) && $mulai->isSameYear($selesai)) {
            return $mulai->format('j') . ' – ' . $selesai->translatedFormat('j F Y');
        }

        return $mulai->translatedFormat('j M Y') . ' – ' . $selesai->translatedFormat('j M Y');
    }
}
