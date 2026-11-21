@if(auth()->check() || session()->has('auth_user'))
    <x-footer-auth />
@else
    <x-footer-guest />
@endif
