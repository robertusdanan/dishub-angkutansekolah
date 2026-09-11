{{--
    Port dari render_dev_credit_script() di core/footer.php (blob eval yang
    sudah didekode). Dipakai di site-footer.blade.php (homepage & modul lain
    yang pakai footer terpusat) MAUPUN halaman dengan footer custom sendiri
    (mis. ASDP) yang punya <div id="..."> kredit terpisah.

    Props: $containerId (default 'devCredit'), $anchorId (default 'devCreditLink'), $anchorClass (default '')
--}}
@php
    $containerId = $containerId ?? 'devCredit';
    $anchorId = $anchorId ?? 'devCreditLink';
    $anchorClass = $anchorClass ?? '';
    $credit = app(\App\Services\SiteCredit\SiteCreditService::class)->get();
@endphp
@if (!empty($credit['link']) && !empty($credit['developer']))
<script>
(() => {
  const d = s => atob(s);
  const creditText = d("{{ base64_encode($credit['text']) }}");
  const developer  = d("{{ base64_encode($credit['developer']) }}");
  const link       = d("{{ base64_encode($credit['link']) }}");
  const container = document.getElementById("{{ $containerId }}");
  if (!container) return;
  const a = document.createElement("a");
  a.href = link;
  a.target = "_blank";
  a.rel = "noopener noreferrer";
  @if ($anchorId !== '')
  a.id = "{{ $anchorId }}";
  @endif
  @if ($anchorClass !== '')
  a.className = "{{ $anchorClass }}";
  @endif
  a.textContent = developer;
  container.append(document.createTextNode(creditText + " "));
  container.appendChild(a);
})();
</script>
@endif
