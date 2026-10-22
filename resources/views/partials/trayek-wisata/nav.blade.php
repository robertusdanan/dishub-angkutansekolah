{{--
    Port dari pages/trayekwisata/partials/nav.php.
    Props: $twNavSolid (bool, default false)
--}}
@php
    $twNavSolid = $twNavSolid ?? false;
    $twCurrent = '/'.request()->path();
    $twLinks = [
        ['label' => 'Destinasi', 'href' => '/trayek-wisata/destinasi'],
        ['label' => 'Jadwal', 'href' => '/trayek-wisata/jadwal'],
        ['label' => 'FAQ', 'href' => '/trayek-wisata/faq'],
        ['label' => 'Pesan Kursi', 'href' => '/trayek-wisata/jadwal', 'cta' => true],
    ];
    foreach ($twLinks as &$twLink) {
        $twLink['active'] = ($twLink['href'] === $twCurrent);
    }
    unset($twLink);
@endphp
@include('partials.site-header', [
    'links' => $twLinks,
    'solid' => $twNavSolid,
    'cta' => null,
    'loginNext' => $twCurrent,
])
