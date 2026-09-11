{{--
    Port dari pages/balikgratis/partials/nav.php.
    Props: $bgTahun
--}}
@include('partials.site-header', [
    'links' => [
        ['label' => 'Beranda', 'href' => '/'],
        ['label' => 'Balik Gratis '.(int) $bgTahun, 'href' => '/balikgratis', 'active' => true],
    ],
    'solid' => true,
    'loginNext' => '/balikgratis',
])
