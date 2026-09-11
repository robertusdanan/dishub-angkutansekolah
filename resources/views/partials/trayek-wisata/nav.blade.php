{{--
    Port dari pages/trayekwisata/partials/nav.php.
    Props: $twNavSolid (bool, default false)
--}}
@php
    $twNavSolid = $twNavSolid ?? false;
    $twCurrent = '/'.request()->path();
    $twLinks = [
        ['label' => 'Beranda', 'href' => '/trayek-wisata'],
        ['label' => 'Rute', 'href' => '/trayek-wisata/rute'],
        ['label' => 'Destinasi', 'href' => '/trayek-wisata/destinasi'],
        ['label' => 'Jadwal', 'href' => '/trayek-wisata/jadwal'],
        ['label' => 'FAQ', 'href' => '/trayek-wisata/faq'],
    ];
    foreach ($twLinks as &$twLink) {
        $twLink['active'] = ($twLink['href'] === $twCurrent);
    }
    unset($twLink);
@endphp
@include('partials.site-header', [
    'links' => $twLinks,
    'solid' => $twNavSolid,
    'cta' => ['label' => 'Pesan Kursi', 'href' => '/trayek-wisata/jadwal'],
    'loginNext' => $twCurrent,
])
