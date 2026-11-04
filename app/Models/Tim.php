<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Tim extends Model
{
    use HasFactory;

    protected $table = 'tim';

    protected $primaryKey = 'id_tim';

    protected $fillable = [
        'nama_tim',
        'id_lomba',
        'nim',
        'kuota_anggota',
        'status_tim',
        'id_admin',
        'file_cv',
        'catatan',
    ];

    /**
     * Memoize hasil query ketuaDisetujui per NIM per request agar tidak query berulang.
     *
     * @var array<string, bool>
     */
    protected static array $ketuaDisetujuiCache = [];

    /**
     * Invalidate cache memoization ketika data model Tim berubah.
     */
    protected static function booted(): void
    {
        static::saved(function (Tim $tim): void {
            static::clearKetuaDisetujuiCache($tim->nim);
        });

        static::deleted(function (Tim $tim): void {
            static::clearKetuaDisetujuiCache($tim->nim);
        });
    }

    /**
     * Hapus cache ketuaDisetujui untuk NIM tertentu atau seluruhnya.
     */
    public static function clearKetuaDisetujuiCache(?string $nim = null): void
    {
        if ($nim === null) {
            static::$ketuaDisetujuiCache = [];
        } else {
            unset(static::$ketuaDisetujuiCache[$nim]);
        }
    }

    /**
     * Cek apakah NIM yang diberikan adalah ketua tim yang sudah disetujui.
     * Hanya relevan untuk mahasiswa. Hasilnya di-memoize per request.
     */
    public static function ketuaDisetujui(?string $nim): bool
    {
        if ($nim === null || $nim === '') {
            return false;
        }

        if (! array_key_exists($nim, static::$ketuaDisetujuiCache)) {
            static::$ketuaDisetujuiCache[$nim] = static::where('nim', $nim)
                ->where('status_tim', 'disetujui')
                ->exists();
        }

        return static::$ketuaDisetujuiCache[$nim];
    }

    /**
     * Dapatkan NIM mahasiswa yang sedang login (baik dari session auth_user maupun Auth::user).
     * Hanya mengembalikan NIM jika role user saat ini adalah mahasiswa.
     */
    public static function currentUserMahasiswaNim(): ?string
    {
        if (session()->has('auth_user')) {
            $sess = session('auth_user');
            if (($sess['role'] ?? null) === 'mahasiswa') {
                return $sess['id'] ?? null;
            }
        }

        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            $roleVal = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
            if ($roleVal === 'mahasiswa') {
                return $user->nim_nip;
            }
        }

        return null;
    }

    /**
     * Dapatkan label role tampilan pengguna yang sedang login.
     * Mengembalikan 'Ketua Tim' jika role mahasiswa dan memiliki tim berstatus 'disetujui',
     * atau label default jika bukan.
     */
    public static function dynamicRoleLabel(?string $defaultLabel = null): string
    {
        $nim = static::currentUserMahasiswaNim();
        if ($nim && static::ketuaDisetujui($nim)) {
            return 'Ketua Tim';
        }

        return $defaultLabel ?? 'Mahasiswa';
    }

    public function lomba()
    {
        return $this->belongsTo(Lomba::class, 'id_lomba', 'id_lomba');
    }

    public function ketua()
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function anggota()
    {
        return $this->hasMany(AnggotaTim::class, 'id_tim', 'id_tim');
    }

    public function anggotaDiterima()
    {
        return $this->hasMany(AnggotaTim::class, 'id_tim', 'id_tim')
            ->where('status_gabung', 'diterima');
    }

    public function pengajuanBimbingan()
    {
        return $this->hasMany(PengajuanBimbingan::class, 'id_tim', 'id_tim');
    }

    public function bimbinganDisetujui()
    {
        return $this->hasOne(PengajuanBimbingan::class, 'id_tim', 'id_tim')
            ->where('status', 'disetujui');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function getSisaKuotaAnggotaAttribute()
    {
        $diterimaCount = $this->relationLoaded('anggotaDiterima')
            ? $this->anggotaDiterima->count()
            : $this->anggotaDiterima()->count();

        return max(0, $this->kuota_anggota - $diterimaCount);
    }
}
