<?php
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
if (!$q) {
    echo json_encode(['greska' => 'Nema pojma']);
    exit;
}

$xml = simplexml_load_file('kokteli.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
foreach ($xml->kategorija as $kat) {
    foreach ($kat->koktel as $k) {
        if (stripos((string)$k->naziv, $q) !== false) {
            echo json_encode(['izvor' => 'lokalno', 'naziv' => (string)$k->naziv]);
            exit;
        }
    }
}

$jsonPath = 'kokteli_api.json';
$postojeci = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

foreach ($postojeci as $k) {
    if (stripos($k['naziv'], $q) !== false) {
        echo json_encode(['izvor' => 'lokalno', 'naziv' => $k['naziv']]);
        exit;
    }
}

$url = 'https://www.thecocktaildb.com/api/json/v1/1/search.php?s=' . urlencode($q);
$odgovor = file_get_contents($url);
$podaci = json_decode($odgovor, true);

if (!$podaci['drinks']) {
    echo json_encode(['greska' => 'Koktel nije pronađen na API-ju']);
    exit;
}

$d = $podaci['drinks'][0];

$sastojci = [];
for ($i = 1; $i <= 15; $i++) {
    $ime = $d["strIngredient$i"] ?? '';
    $kol = $d["strMeasure$i"] ?? '';
    if (!empty(trim($ime))) {
        $sastojci[] = ['ime' => trim($ime), 'kolicina' => trim($kol)];
    }
}

$novi = [
    'naziv'    => $d['strDrink'],
    'opis'     => $d['strInstructions'] ?? '',
    'kategorija' => $d['strCategory'] ?? 'API',
    'sastojci' => $sastojci,
    'slika'    => $d['strDrinkThumb'] ?? ''
];

$postojeci[] = $novi;
file_put_contents($jsonPath, json_encode($postojeci, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['izvor' => 'api', 'naziv' => $novi['naziv']]);