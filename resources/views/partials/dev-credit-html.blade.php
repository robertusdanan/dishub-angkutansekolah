{{--
    Port dari render_dev_credit_html() di core/footer.php (varian server-side,
    beda dari dev-credit-script.blade.php yang inject via JS). Dipakai
    halaman listlink RFID/QR.
    Props: $anchorId (default 'devCreditLink')
--}}
@php
    $anchorId = $anchorId ?? 'devCreditLink';
    $credit = app(\App\Services\SiteCredit\SiteCreditService::class)->get();
@endphp
@if (!empty($credit['link']) && !empty($credit['developer']))
{{ $credit['text'] }} <a href="{{ $credit['link'] }}" target="_blank" rel="noopener noreferrer" @if($anchorId !== '') id="{{ $anchorId }}" @endif>{{ $credit['developer'] }}</a>
@endif
