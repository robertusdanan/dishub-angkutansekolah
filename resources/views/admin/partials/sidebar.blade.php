{{--
    Port dari admin/sidebar.php.
    Props: $currentPage (string, default 'dashboard'), $currentModule (string|null)
--}}
@php
    $roles = app(\App\Services\Admin\AdminRoleService::class);

    $adminUser = session('admin_user', 'admin');
    $adminRole = session('admin_role', 'viewer');
    $adminRoleName = session('admin_role_name', $adminRole);
    $isSuperAdmin = $adminRole === 'superadmin';
    $isDishubta = $adminRole === 'dishubta';
    $isGuest = $adminRole === 'guest';
    $canAbsensi = $isSuperAdmin || $isDishubta;

    $base = '/admin';
    $imgBase = '/assets';

    $absensiSubmenusSuper = [
        ['id' => 'update_domisili', 'label' => 'Data Domisili', 'href' => $base.'/data-domisili'],
        ['id' => 'update_sekolah', 'label' => 'Data Sekolah', 'href' => $base.'/data-sekolah'],
    ];
    $absensiSubmenusBase = [
        ['id' => 'update_siswa', 'label' => 'Registrasi Siswa', 'href' => $base.'/registrasi-siswa'],
        ['id' => 'tambah_foto', 'label' => 'Tambah Absen Foto', 'href' => $base.'/tambah-absen-foto'],
    ];
    $absensiSubmenus = $isSuperAdmin ? array_merge($absensiSubmenusSuper, $absensiSubmenusBase) : $absensiSubmenusBase;

    $trayekSubmenus = [
        ['id' => 'update_trayek', 'label' => 'Data Trayek', 'href' => $base.'/data-trayek'],
        ['id' => 'update_map', 'label' => 'Rute Web', 'href' => $base.'/rute-map'],
        ['id' => 'update_driver', 'label' => 'Data Driver', 'href' => $base.'/data-driver'],
    ];

    $asdpMenuItems = [
        ['id' => 'asdp_daftar', 'label' => 'Daftar Lokasi ASDP', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="9" r="1.8"/><path d="M21 15l-5-5L5 21"/></svg>', 'href' => $base.'/asdp/daftar-lokasi'],
        ['id' => 'asdp_koordinat', 'label' => 'Lokasi ASDP', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>', 'href' => $base.'/asdp/koordinat'],
    ];

    $trayekWisataMenuItems = [
        ['id' => 'trayekwisata_dashboard', 'label' => 'Dashboard Trayek', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>', 'href' => $base.'/trayekwisata'],
        ['id' => 'trayekwisata_planner', 'label' => 'Pengaturan Trayek Tahunan', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>', 'href' => $base.'/trayekwisata/planner'],
        ['id' => 'trayekwisata_titik', 'label' => 'Titik Lokasi', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>', 'href' => $base.'/trayekwisata/titik'],
        ['id' => 'trayekwisata_armada', 'label' => 'Bus & Driver', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 6v6M2 12h19.6M2 12c0-3.3 0-5 .8-6.1S5 4 8.5 4h7c3.5 0 4.9.8 5.7 1.9S22 8.7 22 12v3.5c0 1.2 0 1.8-.4 2.2s-1 .4-2.2.4H4.6c-1.2 0-1.8 0-2.2-.4S2 16.7 2 15.5z"/></svg>', 'href' => $base.'/trayekwisata/armada'],
        ['id' => 'trayekwisata_pemesanan', 'label' => 'Pemesanan', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="16" y2="13"/></svg>', 'href' => $base.'/trayekwisata/pemesanan'],
        ['id' => 'trayekwisata_verifikasi', 'label' => 'Verifikasi Tiket', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="21" y1="14" x2="21" y2="21"/><line x1="14" y1="17.5" x2="21" y2="17.5"/></svg>', 'href' => $base.'/trayekwisata/verifikasi'],
    ];

    $balikGratisMenuItems = [
        ['id' => 'balikgratis_dashboard', 'label' => 'Dashboard Balik Gratis', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>', 'href' => $base.'/balikgratis'],
        ['id' => 'balikgratis_pengaturan', 'label' => 'Pengaturan Kuota Tahun Ini', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 13a7.6 7.6 0 0 0 0-2l2-1.4-2-3.4-2.3.7a7.6 7.6 0 0 0-1.7-1L15 3h-4l-.4 2.9a7.6 7.6 0 0 0-1.7 1l-2.3-.7-2 3.4L6.6 11a7.6 7.6 0 0 0 0 2l-2 1.4 2 3.4 2.3-.7a7.6 7.6 0 0 0 1.7 1L11 21h4l.4-2.9a7.6 7.6 0 0 0 1.7-1l2.3.7 2-3.4z"/></svg>', 'href' => $base.'/balikgratis/pengaturan'],
        ['id' => 'balikgratis_pemesanan', 'label' => 'Data Pemesanan', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h16v16H4z"/><line x1="8" y1="9" x2="16" y2="9"/><line x1="8" y1="13" x2="16" y2="13"/></svg>', 'href' => $base.'/balikgratis/pemesanan'],
        ['id' => 'balikgratis_verifikasi', 'label' => 'Verifikasi Tiket', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="21" y1="14" x2="21" y2="21"/><line x1="14" y1="17.5" x2="21" y2="17.5"/></svg>', 'href' => $base.'/balikgratis/verifikasi'],
    ];

    $menuItems = [
        ['id' => 'data_absensi', 'label' => 'Absensi', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>', 'href' => $base.'/data-absensi'],
        ['id' => 'report_foto', 'label' => 'Absensi Foto', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>', 'href' => $base.'/absensi-foto'],
        ['id' => 'operasional', 'label' => 'Rekap Operasional', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>', 'href' => $base.'/operasional'],
    ];

    $curPage = $currentPage ?? 'dashboard';
    $activeModule = $currentModule ?? session('admin_module');

    $allAbsensiIds = array_column(array_merge($absensiSubmenusBase, $absensiSubmenusSuper), 'id');
    $trayekIds = array_column($trayekSubmenus, 'id');
    $isInAbsensiGroup = in_array($curPage, $allAbsensiIds, true) || $curPage === 'update_data';
    $isInTrayekGroup = in_array($curPage, $trayekIds, true);
@endphp

<div class="adm-sidebar" id="admSidebar">
  <div class="adm-sidebar-header">
    <a href="" class="adm-brand">
      <img src="{{ $imgBase }}/dishub.png" alt="Logo" class="adm-brand-logo"/>
      <div class="adm-brand-text">
        <span class="adm-brand-name">DISHUB Tulungagung</span>
        <span class="adm-brand-accent">{{ $adminRoleName }}</span>
      </div>
    </a>
    <button class="adm-sidebar-close" id="sidebarClose" aria-label="Tutup menu">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>

  <nav class="adm-nav">
    @if ($roles->isPenggunaPublik())
    <a href="{{ $base }}/data-absensi" class="adm-nav-item {{ $curPage === 'data_absensi' ? 'active' : '' }}">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg></span>
      <span class="adm-nav-text">Data Absensi</span>
    </a>
    <a href="{{ $base }}/tiket-saya" class="adm-nav-item {{ $curPage === 'tiket_saya' ? 'active' : '' }}">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 12h18M3 12a2 2 0 0 0 2-2V8a2 2 0 0 0 2-2M3 12a2 2 0 0 1 2 2v2a2 2 0 0 1 2 2M21 12a2 2 0 0 1-2-2V8a2 2 0 0 1-2-2M21 12a2 2 0 0 0-2 2v2a2 2 0 0 0-2 2"/></svg></span>
      <span class="adm-nav-text">Tiket Saya</span>
    </a>
    <a href="{{ $base }}/profil-saya" class="adm-nav-item {{ $curPage === 'profil_saya' ? 'active' : '' }}">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg></span>
      <span class="adm-nav-text">Profil Saya</span>
    </a>
    <a href="/" class="adm-nav-item">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><polyline points="9 21 9 12 15 12 15 21"/></svg></span>
      <span class="adm-nav-text">Beranda</span>
    </a>
    <div class="adm-nav-divider"></div>
    <a href="/akun/keluar" class="adm-nav-item">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
      <span class="adm-nav-text">Keluar</span>
    </a>
    @else
    @unless ($isGuest)
    <a href="{{ $base }}/" class="adm-nav-item {{ $curPage === 'dashboard' ? 'active' : '' }}">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><path d="M14 17.5h7M17.5 14v7"/></svg></span>
      <span class="adm-nav-text">Dashboard</span>
    </a>
    @endunless

    @if ($activeModule === 'angkutansekolah')
    <div class="adm-nav-divider"></div>
    <div class="adm-nav-label">Angkutan Sekolah Gratis</div>

    @foreach ($menuItems as $item)
      @continue($isGuest && $item['id'] !== 'data_absensi')
    <a href="{{ $item['href'] }}" class="adm-nav-item {{ $curPage === $item['id'] ? 'active' : '' }}">
      <span class="adm-nav-icon">{!! $item['icon'] !!}</span>
      <span class="adm-nav-text">{{ $item['label'] }}</span>
    </a>
    @endforeach

    @if ($canAbsensi && !$isGuest)
    <div class="adm-nav-divider"></div>
    <div class="adm-nav-label">Update Data</div>

    <div class="adm-nav-group" id="navGroupAbsensi">
      <a href="#" class="adm-nav-group-toggle {{ $isInAbsensiGroup ? 'open group-active' : '' }}" id="toggleAbsensi" aria-expanded="{{ $isInAbsensiGroup ? 'true' : 'false' }}">
        <span class="adm-nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
        </span>
        <span class="adm-nav-text">Data Absensi</span>
        <span class="adm-nav-chevron" id="chevronAbsensi">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
        </span>
      </a>
      <div class="adm-submenu {{ $isInAbsensiGroup ? 'open' : '' }}" id="submenuAbsensi">
        <div class="adm-submenu-inner">
          @foreach ($absensiSubmenus as $sub)
          <a href="{{ $sub['href'] }}" class="adm-submenu-item {{ $curPage === $sub['id'] ? 'active' : '' }}">
            <span class="adm-submenu-dot"></span>
            <span>{{ $sub['label'] }}</span>
          </a>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    @if ($isSuperAdmin && !$isGuest)
    <div class="adm-nav-group" id="navGroupTrayek">
      <a href="#" class="adm-nav-group-toggle {{ $isInTrayekGroup ? 'open group-active' : '' }}" id="toggleTrayek" aria-expanded="{{ $isInTrayekGroup ? 'true' : 'false' }}">
        <span class="adm-nav-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <ellipse cx="12" cy="5" rx="9" ry="3"/>
            <path d="M21 12c0 1.66-4.03 3-9 3S3 13.66 3 12"/>
            <path d="M3 5v14c0 1.66 4.03 3 9 3s9-1.34 9-3V5"/>
          </svg>
        </span>
        <span class="adm-nav-text">Data Trayek</span>
        <span class="adm-nav-chevron" id="chevronTrayek">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
        </span>
      </a>
      <div class="adm-submenu {{ $isInTrayekGroup ? 'open' : '' }}" id="submenuTrayek">
        <div class="adm-submenu-inner">
          @foreach ($trayekSubmenus as $sub)
          <a href="{{ $sub['href'] }}" class="adm-submenu-item {{ $curPage === $sub['id'] ? 'active' : '' }}">
            <span class="adm-submenu-dot"></span>
            <span>{{ $sub['label'] }}</span>
          </a>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    @if (($isSuperAdmin || $isDishubta) && !$isGuest)
    <a href="{{ $base }}/rfid-writer" class="adm-nav-item {{ $curPage === 'rfid_writer' ? 'active' : '' }}">
      <span class="adm-nav-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <rect x="2" y="5" width="20" height="14" rx="3"/>
          <path d="M8.5 9.5 C8.5 9.5 7 11 7 12 C7 13 8.5 14.5 8.5 14.5"/>
          <path d="M15.5 9.5 C15.5 9.5 17 11 17 12 C17 13 15.5 14.5 15.5 14.5"/>
          <path d="M10.5 10.8 C10.5 10.8 9.8 11.3 9.8 12 C9.8 12.7 10.5 13.2 10.5 13.2"/>
          <path d="M13.5 10.8 C13.5 10.8 14.2 11.3 14.2 12 C14.2 12.7 13.5 13.2 13.5 13.2"/>
          <circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/>
        </svg>
      </span>
      <span class="adm-nav-text">RFID Writer</span>
    </a>
    @endif
    @endif {{-- end modul angkutansekolah --}}

    @if ($activeModule === 'asdp' && !$isGuest)
    <div class="adm-nav-divider"></div>
    <div class="adm-nav-label">Layanan ASDP</div>
    @foreach ($asdpMenuItems as $item)
      @continue(!$roles->canAccessMenu($item['id']))
    <a href="{{ $item['href'] }}" class="adm-nav-item {{ $curPage === $item['id'] ? 'active' : '' }}">
      <span class="adm-nav-icon">{!! $item['icon'] !!}</span>
      <span class="adm-nav-text">{{ $item['label'] }}</span>
    </a>
    @endforeach
    @endif

    @if ($activeModule === 'trayekwisata' && !$isGuest)
    <div class="adm-nav-divider"></div>
    <div class="adm-nav-label">Trayek Wisata</div>
    @foreach ($trayekWisataMenuItems as $item)
      @continue(!$roles->canAccessMenu($item['id']))
    <a href="{{ $item['href'] }}" class="adm-nav-item {{ $curPage === $item['id'] ? 'active' : '' }}">
      <span class="adm-nav-icon">{!! $item['icon'] !!}</span>
      <span class="adm-nav-text">{{ $item['label'] }}</span>
    </a>
    @endforeach
    @endif

    @if ($activeModule === 'balikgratis' && !$isGuest)
    <div class="adm-nav-divider"></div>
    <div class="adm-nav-label">Balik Gratis</div>
    @foreach ($balikGratisMenuItems as $item)
      @continue(!$roles->canAccessMenu($item['id']))
    <a href="{{ $item['href'] }}" class="adm-nav-item {{ $curPage === $item['id'] ? 'active' : '' }}">
      <span class="adm-nav-icon">{!! $item['icon'] !!}</span>
      <span class="adm-nav-text">{{ $item['label'] }}</span>
    </a>
    @endforeach
    @endif

    <div class="adm-nav-divider"></div>

    @unless ($isGuest)
    @php
        $canAkun = $isSuperAdmin || $roles->hasPermission('akun', 'view');
        $canRoles = $isSuperAdmin || $roles->hasPermission('manajemen_role', 'view');
    @endphp

    @if ($canAkun)
    <a href="{{ $base }}/akun" class="adm-nav-item {{ $curPage === 'akun' ? 'active' : '' }}">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
      <span class="adm-nav-text">Akun</span>
    </a>
    @endif

    @if ($canRoles)
    <a href="{{ $base }}/manajemen-role" class="adm-nav-item {{ $curPage === 'manajemen_role' ? 'active' : '' }}">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 15a6 6 0 0 0-6 6"/><path d="M12 15a6 6 0 0 1 6 6"/><circle cx="12" cy="7" r="4"/><path d="M4.2 19.5a9.97 9.97 0 0 1 2.3-3.2"/><path d="M19.8 19.5a9.97 9.97 0 0 0-2.3-3.2"/></svg></span>
      <span class="adm-nav-text">Manajemen Role</span>
    </a>
    @endif

    <a href="{{ $base }}/settings" class="adm-nav-item {{ $curPage === 'settings' ? 'active' : '' }}">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
      <span class="adm-nav-text">Pengaturan</span>
    </a>

    <div class="adm-nav-divider"></div>
    @endunless

    <a href="/" class="adm-nav-item" style="color:var(--text-3,#64748b);">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><polyline points="9 21 9 12 15 12 15 21"/></svg></span>
      <span class="adm-nav-text">Beranda</span>
    </a>

    @unless ($isGuest)
    <a href="{{ $base }}/logout" class="adm-nav-item adm-nav-logout">
      <span class="adm-nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
      <span class="adm-nav-text">Logout</span>
    </a>
    @endunless
    @endif {{-- isPenggunaPublik() --}}
  </nav>

  <div class="dev-credit" id="devCredit"></div>
  @include('partials.dev-credit-script', ['containerId' => 'devCredit'])
</div>

<div class="adm-overlay" id="admOverlay"></div>

<header class="adm-topbar" id="admTopbar">
  <button class="adm-hamburger" id="sidebarOpen" aria-label="Buka menu">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
  <a href="" class="adm-topbar-brand">
    <img src="{{ $imgBase }}/dishub.png" alt="Logo"/>
    <span>DISHUB <strong>Tulungagung</strong></span>
  </a>
  <div style="width:40px;"></div>
</header>

<script>
(function () {
  const sidebar  = document.getElementById('admSidebar');
  const overlay  = document.getElementById('admOverlay');
  const btnOpen  = document.getElementById('sidebarOpen');
  const btnClose = document.getElementById('sidebarClose');
  function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('show'); }
  function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('show'); }
  if (btnOpen)  btnOpen.addEventListener('click', openSidebar);
  if (btnClose) btnClose.addEventListener('click', closeSidebar);
  if (overlay)  overlay.addEventListener('click', closeSidebar);
})();

function makeSubmenuToggle(toggleId, chevronId, submenuId) {
  const toggle  = document.getElementById(toggleId);
  const chevron = document.getElementById(chevronId);
  const submenu = document.getElementById(submenuId);
  if (!toggle || !chevron || !submenu) return;
  chevron.addEventListener('click', function (e) {
    e.preventDefault(); e.stopPropagation();
    const isOpen = submenu.classList.contains('open');
    submenu.classList.toggle('open', !isOpen);
    toggle.classList.toggle('open', !isOpen);
    toggle.setAttribute('aria-expanded', String(!isOpen));
  });
  toggle.addEventListener('click', function (e) {
    e.preventDefault();
    const isOpen = submenu.classList.contains('open');
    submenu.classList.toggle('open', !isOpen);
    toggle.classList.toggle('open', !isOpen);
    toggle.setAttribute('aria-expanded', String(!isOpen));
  });
}
makeSubmenuToggle('toggleAbsensi', 'chevronAbsensi', 'submenuAbsensi');
if (document.getElementById('toggleTrayek')) {
  makeSubmenuToggle('toggleTrayek', 'chevronTrayek', 'submenuTrayek');
}
</script>
