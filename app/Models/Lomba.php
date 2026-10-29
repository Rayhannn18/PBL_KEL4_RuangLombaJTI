<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Lomba extends Model
{
    use HasFactory;

    protected $table = 'lomba';

    protected $primaryKey = 'id_lomba';

    protected $fillable = [
        'nama_lomba',
        'kategori',
        'tingkat',
        'penyelenggara',
        'deskripsi',
        'persyaratan',
        'tenggat',
        'biaya',
        'benefit',
        'tautan_daftar',
        'status_verifikasi',
        'poster',
        'nim',
        'id_admin',
        'tipe_peserta',
        'mode_pelaksanaan',
        'poster_path',
        'buku_pedoman_path',
    ];

    protected $casts = [
        'tenggat' => 'date',
        'biaya' => 'integer',
    ];

    protected $appends = [
        'poster_url',
        'buku_pedoman_url',
    ];

    // Relations
    public function pengaju()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function verifikator()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function tim()
    {
        return $this->hasMany(Tim::class, 'id_lomba', 'id_lomba');
    }

    // Scopes for searching and filtering (Use Case: Cari & Filter Lomba)
    public function scopeTerverifikasi($query)
    {
        return $query->where('status_verifikasi', 'terverifikasi');
    }

    public function scopeMenunggu($query)
    {
        return $query->where('status_verifikasi', 'menunggu');
    }

    public function scopeFilter($query, array $filters)
    {
        // Keyword Search (Case-Insensitive untuk PostgreSQL & SQLite)
        $query->when($filters['search'] ?? ($filters['q'] ?? null), function ($q, $search) {
            $lower = '%'.strtolower($search).'%';
            $q->where(function ($sub) use ($lower) {
                $sub->whereRaw('LOWER(nama_lomba) LIKE ?', [$lower])
                    ->orWhereRaw('LOWER(penyelenggara) LIKE ?', [$lower])
                    ->orWhereRaw('LOWER(deskripsi) LIKE ?', [$lower]);
            });
        });

        // Kategori Filter
        $query->when($filters['kategori'] ?? null, function ($q, $kategori) {
            if (strtolower($kategori) !== 'semua' && ! empty($kategori)) {
                $q->where('kategori', $kategori);
            }
        });

        // Tingkat Filter (Case-Insensitive: Internasional, Nasional, Kampus, Regional)
        $query->when($filters['tingkat'] ?? null, function ($q, $tingkat) {
            if (strtolower($tingkat) !== 'semua' && ! empty($tingkat)) {
                $q->whereRaw('LOWER(tingkat) = ?', [strtolower($tingkat)]);
            }
        });

        // Biaya Filter (Gratis vs Berbayar)
        $query->when($filters['biaya'] ?? null, function ($q, $biaya) {
            if ($biaya === 'gratis') {
                $q->where('biaya', 0);
            } elseif ($biaya === 'berbayar') {
                $q->where('biaya', '>', 0);
            }
        });

        // Tipe Filter (Individu vs Kelompok/Tim)
        $query->when($filters['tipe'] ?? ($filters['tipe_peserta'] ?? null), function ($q, $tipe) {
            if (strtolower($tipe) !== 'semua' && ! empty($tipe)) {
                $lower = strtolower($tipe);
                if (Schema::hasColumn('lomba', 'tipe_peserta')) {
                    if ($lower === 'tim' || $lower === 'kelompok') {
                        $q->where(function ($sub) {
                            $sub->whereRaw('LOWER(tipe_peserta) = ?', ['kelompok'])
                                ->orWhereRaw('LOWER(tipe_peserta) = ?', ['tim']);
                        });
                    } else {
                        $q->whereRaw('LOWER(tipe_peserta) = ?', [$lower]);
                    }
                } elseif (Schema::hasColumn('lomba', 'tipe')) {
                    $q->whereRaw('LOWER(tipe) = ?', [$lower]);
                }
            }
        });

        // Mode Filter (Online, Offline, Hybrid)
        $query->when($filters['mode'] ?? ($filters['mode_pelaksanaan'] ?? null), function ($q, $mode) {
            if (strtolower($mode) !== 'semua' && ! empty($mode)) {
                $lower = strtolower($mode);
                if (Schema::hasColumn('lomba', 'mode_pelaksanaan')) {
                    $q->whereRaw('LOWER(mode_pelaksanaan) = ?', [$lower]);
                } elseif (Schema::hasColumn('lomba', 'mode')) {
                    $q->whereRaw('LOWER(mode) = ?', [$lower]);
                }
            }
        });

        // Tenggat Filter
        $query->when($filters['status_tenggat'] ?? ($filters['tenggat'] ?? null), function ($q, $status) {
            if ($status === 'buka') {
                $q->where('tenggat', '>=', Carbon::today());
            } elseif ($status === 'segera' || $status === 'segera_berakhir') {
                $q->whereBetween('tenggat', [Carbon::today(), Carbon::today()->addDays(7)]);
            } elseif ($status === 'tutup') {
                $q->where('tenggat', '<', Carbon::today());
            }
        });

        return $query;
    }

    // Helper methods matching Draw.io Class Diagram (+ verifikasi(), etc.)
    public function verifikasi(int $adminId, string $status = 'terverifikasi')
    {
        $this->update([
            'status_verifikasi' => $status,
            'id_admin' => $adminId,
        ]);

        return $this;
    }

    // Accessors
    public function getIsBukaAttribute(): bool
    {
        return $this->tenggat ? $this->tenggat->isFuture() || $this->tenggat->isToday() : false;
    }

    public function getSisaHariAttribute(): int
    {
        if (! $this->tenggat) {
            return 0;
        }

        return max(0, (int) Carbon::today()->diffInDays($this->tenggat, false));
    }

    public function getFormattedBiayaAttribute(): string
    {
        if ((int) $this->biaya === 0) {
            return 'Gratis';
        }

        return 'Rp '.number_format((int) $this->biaya, 0, ',', '.');
    }

    public function getModePelaksanaanLabelAttribute(): string
    {
        $mode = $this->mode_pelaksanaan ?? 'Online';

        return match (strtolower($mode)) {
            'online' => 'Online (Daring)',
            'offline' => 'Offline (Luring)',
            'hybrid' => 'Hybrid',
            default => ucfirst($mode),
        };
    }

    public function getPosterUrlAttribute(): ?string
    {
        if ($this->poster_path && Storage::disk('public')->exists($this->poster_path)) {
            return Storage::disk('public')->url($this->poster_path);
        }

        if ($this->poster) {
            if (filter_var($this->poster, FILTER_VALIDATE_URL)) {
                return $this->poster;
            }
            if (Storage::disk('public')->exists('lomba/poster/'.$this->poster)) {
                return Storage::disk('public')->url('lomba/poster/'.$this->poster);
            }
        }

        return null;
    }

    public function getBukuPedomanUrlAttribute(): ?string
    {
        if ($this->buku_pedoman_path && Storage::disk('public')->exists($this->buku_pedoman_path)) {
            return Storage::disk('public')->url($this->buku_pedoman_path);
        }

        return null;
    }
}
