@php
    $currentUser = auth()->user();
    $sessionUser = session('auth_user');

    $userRoleVal = $currentUser?->role instanceof \App\Enums\UserRole 
        ? $currentUser->role->value 
        : (string) ($currentUser?->role ?? ($sessionUser['role'] ?? ''));

    if (! $userRoleVal && $sessionUser) {
        $userRoleVal = $sessionUser['role'] ?? '';
    }

    $userRoleLabel = $currentUser?->role instanceof \App\Enums\UserRole 
        ? $currentUser->role->label() 
        : ucfirst($userRoleVal);

    $nimNip = $sessionUser['id'] ?? $currentUser?->nim_nip;

    if (in_array($userRoleVal, ['mahasiswa', 'ketua_tim'], true) && \App\Models\Tim::ketuaDisetujui($nimNip)) {
        $userRoleLabel = 'Ketua Tim';
    } elseif ($userRoleVal === 'ketua_tim') {
        $userRoleLabel = 'Ketua Tim';
    }

    $userName = $currentUser?->name ?? ($sessionUser['nama'] ?? 'Pengguna');

    // Badge role styling sesuai identitas sistem RuangLombaJTI:
    // Mahasiswa -> badge-primary (Biru)
    // Dosen     -> badge-warning (Kuning/Amber)
    // Admin     -> badge-danger  (Merah)
    $badgeClass = match ($userRoleVal) {
        'admin' => 'badge-danger',
        'dosen' => 'badge-warning',
        default => 'badge-primary',
    };
@endphp

<div class="topbar-user-dropdown" id="topbarUserDropdown">
    <button type="button"
            class="topbar-user-chip"
            id="topbarUserChipBtn"
            aria-haspopup="true"
            aria-expanded="false"
            title="Menu Profil Saya">
        <i class="bi bi-person-check-fill" style="color: #6EE7B7;"></i>
        <span>{{ $userName }}</span>
        <span class="badge {{ $badgeClass }}" style="font-size: 0.72rem; padding: 2px 8px;">
            {{ $userRoleLabel }}
        </span>
        <i class="bi bi-chevron-down topbar-user-arrow"></i>
    </button>
    <div class="topbar-dropdown-menu" id="topbarUserMenu" role="menu" aria-labelledby="topbarUserChipBtn">
        <a href="{{ route('profil.show') }}" class="topbar-dropdown-item" role="menuitem">
            <i class="bi bi-person-circle"></i>
            <span>Profil Saya</span>
        </a>
        <a href="{{ route('profil.show') }}#kata-sandi" class="topbar-dropdown-item" role="menuitem" id="topbarLinkKataSandi">
            <i class="bi bi-key-fill"></i>
            <span>Ganti Kata Sandi</span>
        </a>
    </div>
</div>
