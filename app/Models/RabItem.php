<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    use HasFactory;

    protected $table = 'rab_item';

    protected $fillable = [
        'rab_pengajuan_id',
        'kategori',
        'uraian',
        'satuan',
        'jumlah_orang',
        'frekuensi',
        'tarif_satuan',
        'sifat',
        'subtotal',
        'alasan_melebihi',
        'bukti_link',
    ];

    protected $casts = [
        'jumlah_orang' => 'integer',
        'frekuensi' => 'integer',
        'tarif_satuan' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function rabPengajuan()
    {
        return $this->belongsTo(RabPengajuan::class, 'rab_pengajuan_id', 'id');
    }

    public static function defaultMetaForKategori(string $kategori): array
    {
        return match ($kategori) {
            'uang_harian_luar_kota' => [
                'label' => 'Uang Harian Luar Kota',
                'satuan' => 'OH',
                'sifat' => 'batas_tertinggi',
            ],
            'uang_harian_dalam_kota' => [
                'label' => 'Uang Harian Dalam Kota (> 8 Jam)',
                'satuan' => 'OH',
                'sifat' => 'batas_tertinggi',
            ],
            'penginapan' => [
                'label' => 'Penginapan (Tier Terendah)',
                'satuan' => 'OH',
                'sifat' => 'batas_tertinggi',
            ],
            'transport_terminal' => [
                'label' => 'Transport ke Terminal/Stasiun/Bandara',
                'satuan' => 'Orang/Kali',
                'sifat' => 'dapat_dilampaui',
            ],
            'transport_lokal' => [
                'label' => 'Transportasi Lokal (Dalam Kab/Kota PP)',
                'satuan' => 'Orang/Kali',
                'sifat' => 'dapat_dilampaui',
            ],
            'tiket_antar_kota' => [
                'label' => 'Tiket Perjalanan Antar Kota (PP)',
                'satuan' => 'Orang/Kali',
                'sifat' => 'at_cost',
            ],
            'biaya_pendaftaran' => [
                'label' => 'Biaya Pendaftaran / Registrasi Lomba',
                'satuan' => 'Paket',
                'sifat' => 'at_cost',
            ],
            default => [
                'label' => 'Komponen Biaya Lain-lain',
                'satuan' => 'Paket',
                'sifat' => 'at_cost',
            ],
        };
    }
}
