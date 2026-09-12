<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Dashboard - Admin Dishub Tulungagung</title>
<link rel="canonical" href="{{ url('/admin/') }}"/>
<link rel="icon" href="/favicon.ico"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/admin/admin-shell.css">

<style>
  .module-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; max-width: 980px; margin-top: 6px; }
  .module-card { position: relative; display: flex; flex-direction: column; gap: 14px; padding: 24px; text-decoration: none; transition: transform var(--tr), box-shadow var(--tr), border-color var(--tr); }
  .module-card:hover { transform: translateY(-3px); border-color: var(--accent); box-shadow: 0 10px 30px rgba(15,23,42,0.10); }
  .module-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--accent), var(--accent-2)); color: #fff; flex-shrink: 0; }
  .module-title { font-size: 15.5px; font-weight: 700; color: var(--text-1); }
  .module-desc  { font-size: 13px; color: var(--text-4); line-height: 1.5; }
  .module-cta { margin-top: auto; display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--accent); }
  .module-card:hover .module-cta svg { transform: translateX(3px); }
  .module-cta svg { transition: transform var(--tr); }
  .module-card-placeholder { display: flex; flex-direction: column; gap: 10px; padding: 24px; align-items: flex-start; color: var(--text-4); border: 1.5px dashed var(--border); border-radius: 14px; }
  .module-card-placeholder .module-icon { background: var(--surface-2); color: var(--text-4); }
  .module-card-placeholder .module-title { color: var(--text-3); }
  .dash-hint { font-size: 12.5px; color: var(--text-4); margin-top: 22px; }
</style>
</head>
<body>

<div class="adm-shell">

  @include('admin.partials.sidebar', ['currentPage' => 'dashboard', 'currentModule' => null])

  <main class="adm-main">
    <div class="adm-content">

      <div class="adm-page-header">
        <div>
          <h1 class="adm-page-title">Dashboard</h1>
          <p class="adm-page-subtitle">Pilih modul yang ingin dibuka - Dinas Perhubungan Kab. Tulungagung</p>
        </div>
      </div>

      <div class="module-grid">
        @foreach ($modules as $m)
        <a href="{{ $m['href'] }}" class="card module-card">
          <div class="module-icon">{!! $m['icon'] !!}</div>
          <div>
            <div class="module-title">{{ $m['label'] }}</div>
            <div class="module-desc">{{ $m['description'] }}</div>
          </div>
          <div class="module-cta">
            Buka modul
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </div>
        </a>
        @endforeach

        @unless ($isGuest)
        <div class="module-card-placeholder">
          <div class="module-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div class="module-title">Modul lainnya</div>
          <div class="module-desc" style="color:var(--text-4);">Akan hadir menyusul seiring aplikasi berkembang.</div>
        </div>
        @endunless
      </div>

      <p class="dash-hint">Setiap login akan selalu kembali ke halaman ini terlebih dahulu, apa pun modul yang terakhir dipilih.</p>

    </div>
  </main>
</div>

<script>
(function () {
  const sidebar  = document.getElementById('admSidebar');
  const overlay  = document.getElementById('admOverlay');
  const openBtn  = document.getElementById('sidebarOpen');
  const closeBtn = document.getElementById('sidebarClose');
  function open()  { sidebar.classList.add('open');  overlay.classList.add('show'); document.body.style.overflow = 'hidden'; }
  function close() { sidebar.classList.remove('open'); overlay.classList.remove('show'); document.body.style.overflow = ''; }
  if (openBtn)  openBtn.addEventListener('click', open);
  if (closeBtn) closeBtn.addEventListener('click', close);
  if (overlay)  overlay.addEventListener('click', close);
})();
</script>

</body>
</html>
