{{--
    Port dari core/nav_account.php (render_nav_account()).
    Props: $loginNext (string|null)
--}}
@php
    $isPengguna = (bool) session('akun_publik_id');
    $navUser = $isPengguna ? (session('akun_publik_nama') ?? 'Pengguna') : '';
    $navIsLoggedIn = (bool) session('admin_logged_in');
    $navIsGuest = $navIsLoggedIn && session('admin_role') === 'guest';
    if (!$isPengguna) {
        $navUser = session('admin_user', '');
    }
@endphp

@if ($isPengguna)
    @php
        $initial = mb_substr($navUser, 0, 1);
        $firstName = trim(explode(' ', trim($navUser))[0] ?? $navUser);
        $displayName = $firstName !== '' ? $firstName : $navUser;
        $uid = 'navAccountDD1';
    @endphp
    <div class="nav-account-wrap" id="{{ $uid }}-wrap">
        <button type="button" class="nav-account nav-account-toggle" id="{{ $uid }}-toggle" aria-haspopup="true" aria-expanded="false">
            <span class="nav-account-avatar">{{ $initial }}</span>
            <span class="nav-account-name">{{ $displayName }}</span>
            <svg class="nav-account-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="nav-account-menu" id="{{ $uid }}-menu" role="menu">
            <a href="/admin/data-absensi" class="nav-account-menu-item" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                <span>Data Absensi</span>
            </a>
            <a href="/admin/tiket-saya" class="nav-account-menu-item" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h18M3 12a2 2 0 0 0 2-2V8a2 2 0 0 0 2-2M3 12a2 2 0 0 1 2 2v2a2 2 0 0 1 2 2M21 12a2 2 0 0 1-2-2V8a2 2 0 0 1-2-2M21 12a2 2 0 0 0-2 2v2a2 2 0 0 0-2 2"/></svg>
                <span>Tiket Saya</span>
            </a>
            <a href="/admin/profil-saya" class="nav-account-menu-item" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>
                <span>Profil Saya</span>
            </a>
            <div class="nav-account-menu-divider"></div>
            <a href="/akun/keluar" class="nav-account-menu-item nav-account-menu-item-danger" role="menuitem">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Keluar</span>
            </a>
        </div>
    </div>
    @once
        <style>
            .nav-account-wrap { position: relative; display: inline-flex; }
            .nav-account-toggle { cursor: pointer; font-family: inherit; font-size: inherit; }
            .nav-account-chevron { flex-shrink: 0; opacity: .85; transition: transform .18s ease; }
            .nav-account-wrap.open .nav-account-chevron { transform: rotate(180deg); }
            .nav-account-menu {
                position: absolute; top: calc(100% + 10px); right: 0; left: auto;
                min-width: 208px; max-width: calc(100vw - 24px);
                background: #fff; border-radius: 14px;
                border: 1px solid rgba(15,23,42,.06);
                box-shadow: 0 18px 40px rgba(15,23,42,.22), 0 2px 8px rgba(15,23,42,.08);
                padding: 8px; z-index: 999;
                opacity: 0; visibility: hidden; pointer-events: none;
                transform: translateY(-6px) scale(.98);
                transition: opacity .16s ease, transform .16s ease, visibility .16s;
            }
            .nav-account-wrap.open .nav-account-menu {
                opacity: 1; visibility: visible; pointer-events: auto;
                transform: translateY(0) scale(1);
            }
            .nav-account-menu-item {
                display: flex; align-items: center; gap: 10px;
                padding: 9px 12px; border-radius: 9px;
                font-size: 13.5px; font-weight: 600; color: #1e293b;
                text-decoration: none; white-space: nowrap;
            }
            .nav-account-menu-item svg { flex-shrink: 0; color: #64748b; }
            .nav-account-menu-item:hover { background: #f1f5f9; }
            .nav-account-menu-item-danger { color: #dc2626; }
            .nav-account-menu-item-danger svg { color: #dc2626; }
            .nav-account-menu-divider { height: 1px; background: #e2e8f0; margin: 6px 4px; }
        </style>
        <script>
            (function () {
                document.querySelectorAll('.nav-account-toggle').forEach(function (toggle) {
                    var wrap = toggle.closest('.nav-account-wrap');
                    if (!wrap) return;
                    function closeMenu() { wrap.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); }
                    function openMenu()  { wrap.classList.add('open');    toggle.setAttribute('aria-expanded', 'true'); }
                    toggle.addEventListener('click', function (e) {
                        e.preventDefault(); e.stopPropagation();
                        wrap.classList.contains('open') ? closeMenu() : openMenu();
                    });
                    document.addEventListener('click', function (e) {
                        if (!wrap.contains(e.target)) closeMenu();
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape') closeMenu();
                    });
                });
            })();
        </script>
    @endonce
@elseif ($navIsLoggedIn)
    @php
        $initial = mb_substr($navUser, 0, 1);
        $firstName = trim(explode(' ', trim($navUser))[0] ?? $navUser);
        $displayName = $firstName !== '' ? $firstName : $navUser;
    @endphp
    <a href="/admin/" class="nav-account{{ $navIsGuest ? ' is-guest' : '' }}">
        <span class="nav-account-avatar">{{ $initial }}</span>
        <span class="nav-account-name">{{ $displayName }}</span>
    </a>
@else
    @php
        $next = $loginNext ?? request()->getRequestUri();
        $url = '/admin/login?next='.rawurlencode($next);
    @endphp
    <a href="{{ $url }}" class="nav-link nav-login">Login</a>
@endif
