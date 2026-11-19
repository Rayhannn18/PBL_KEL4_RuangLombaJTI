<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\PengajuanBimbingan;
use App\Models\ProgresBabak;
use App\Models\Tim;
use App\Models\User;

class ProgresBabakPolicy
{
    /**
     * Ambil role dan identitas (NIM/NIP/ID) aktor yang sedang login.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function resolveActor(?User $user): array
    {
        if (session()->has('auth_user')) {
            $sess = session('auth_user');

            return [
                isset($sess['role']) ? (string) $sess['role'] : null,
                isset($sess['id']) ? (string) $sess['id'] : null,
            ];
        }

        if (! $user) {
            return [null, null];
        }

        $role = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
        $id = $user->nim_nip ?? (string) $user->id;

        return [$role, $id];
    }

    /**
     * Hanya Ketua Tim resmi pada pengajuan bimbingan berstatus disetujui yang boleh mengirim laporan.
     */
    public function create(?User $user, PengajuanBimbingan $pengajuan): bool
    {
        [$role, $actorId] = $this->resolveActor($user);

        if (! in_array($role, ['mahasiswa', 'ketua_tim'], true) || ! $actorId) {
            return false;
        }

        $pengajuan->loadMissing('tim');
        $tim = $pengajuan->tim;

        if (! $tim || $pengajuan->status !== 'disetujui' || $tim->status_tim !== 'disetujui') {
            return false;
        }

        return (string) $tim->nim === $actorId && Tim::ketuaDisetujui($actorId);
    }

    /**
     * Hanya Ketua Tim resmi tim tersebut yang boleh memperbaiki laporan progres babak.
     */
    public function update(?User $user, ProgresBabak $progres): bool
    {
        $progres->loadMissing('pengajuan.tim');
        $pengajuan = $progres->pengajuan;

        if (! $pengajuan) {
            return false;
        }

        return $this->create($user, $pengajuan);
    }

    /**
     * Hanya Dosen Pembimbing tim dengan pengajuan bimbingan berstatus disetujui yang boleh menyetujui/menolak.
     */
    public function respond(?User $user, ProgresBabak $progres): bool
    {
        [$role, $actorId] = $this->resolveActor($user);

        if ($role !== 'dosen' || ! $actorId) {
            return false;
        }

        $progres->loadMissing('pengajuan');
        $pengajuan = $progres->pengajuan;

        if (! $pengajuan || $pengajuan->status !== 'disetujui') {
            return false;
        }

        return (string) $pengajuan->nip === $actorId;
    }

    /**
     * Ketua, anggota tim diterima, dosen pembimbing tim (pengajuan disetujui), atau admin boleh melihat bukti.
     */
    public function viewBukti(?User $user, ProgresBabak $progres): bool
    {
        [$role, $actorId] = $this->resolveActor($user);

        if (! $role || ! $actorId) {
            return false;
        }

        if ($role === 'admin') {
            return true;
        }

        $progres->loadMissing('pengajuan.tim.anggotaDiterima');
        $pengajuan = $progres->pengajuan;

        if (! $pengajuan) {
            return false;
        }

        if ($role === 'dosen') {
            return $pengajuan->status === 'disetujui' && (string) $pengajuan->nip === $actorId;
        }

        if (in_array($role, ['mahasiswa', 'ketua_tim'], true)) {
            return (bool) $pengajuan->tim?->isAnggotaAtauKetua($actorId);
        }

        return false;
    }
}
