<?php

$xml = simplexml_load_file('kokteli.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
if (!$xml) {
    die('Greška pri učitavanju kokteli.xml');
}

$ikone = [
    'Kokteli s Ginom'                        => '🌿',
    'Kokteli s Whiskeyjem i Bourbonom'       => '🥃',
    'Kokteli s Rumom'                        => '🌴',
    'Kokteli s Vodkom'                       => '❄️',
    'Kokteli s Tequilom i Mezcalom'          => '🇲🇽',
    'Kokteli s Brandyjem i Konjakom'         => '🍇',
    'Pjenušavi, Lagani i Gorki (Aperitivo)'  => '🥂',
    'Ostali Svjetski Specijaliteti'          => '🌍',
    'Ostali Slavni Klasici'                  => '🌀',
    'Dodatni Kokteli s Whiskeyjem'           => '🥃',
    'Dodatni Kokteli s Ginom'               => '🌿',
];
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Koktel Priručnik</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="kokteli.css">
  <script src="script.js"></script>
</head>
<body>

<header>
  <p class="hero-eyebrow">Kompletni priručnik</p>
  <h1>Koktel<span>Priručnik</span></h1>
  <p class="hero-sub">Sve što trebaš znati o klasičnim i modernim koktelima.</p>
  <div class="hero-line"></div>
</header>

<?php
  $ukupno_koktela = 0;
  $ukupno_kategorija = count($xml->kategorija);
  foreach ($xml->kategorija as $kat) {
      $ukupno_koktela += count($kat->koktel);
  }
?>

<div class="stats">
  <div class="stat">
    <div class="stat-num"><?= $ukupno_koktela ?></div>
    <div class="stat-label">Koktela</div>
  </div>
  <div class="stat">
    <div class="stat-num"><?= $ukupno_kategorija ?></div>
    <div class="stat-label">Kategorija</div>
  </div>
  <div class="stat">
    <div class="stat-num">65+</div>
    <div class="stat-label">Sastojaka</div>
  </div>
</div>

<div class="search-wrap">
  <input type="text" id="pretraga" placeholder="Pretraži koktel ili sastojak..." autocomplete="off">
  <button id="btnApi" onclick="pretražiApi()">🍸 Pretraži API</button>
</div>

<div class="cat-nav-wrap">
  <button class="cat-arrow left" onclick="scrollKat(-200)">&#8249;</button>

  <div class="cat-nav" id="catNav">

    <button class="cat-btn active" onclick="filterKat('sve', this)">Sve kategorije</button>

    <?php if (file_exists('kokteli_api.json') && !empty(json_decode(file_get_contents('kokteli_api.json'), true))): ?>
      <button class="cat-btn" onclick="filterKat('API', this)">🌐 API Kokteli</button>
    <?php endif; ?>

    <?php foreach ($xml->kategorija as $kat): ?>
      <button class="cat-btn" onclick="filterKat('<?= htmlspecialchars((string)$kat['naziv']) ?>', this)">
        <?= isset($ikone[(string)$kat['naziv']]) ? $ikone[(string)$kat['naziv']] . ' ' : '' ?>
        <?= htmlspecialchars((string)$kat['naziv']) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <button class="cat-arrow right" onclick="scrollKat(200)">&#8250;</button>
</div>

<main>
  <div class="no-results" id="noResults">
    <span>🍸</span>
    Nema koktela koji odgovaraju pretrazi.
  </div>

  <?php foreach ($xml->kategorija as $kat):
    $naziv_kat = (string)$kat['naziv'];
    $ikona = $ikone[$naziv_kat] ?? '🍹';
    $broj = count($kat->koktel);
  ?>
  <section class="kategorija" data-kat="<?= htmlspecialchars($naziv_kat) ?>">
    <div class="kat-header">
      <span class="kat-ikona"><?= $ikona ?></span>
      <h2 class="kat-naziv"><?= htmlspecialchars($naziv_kat) ?></h2>
      <span class="kat-broj"><?= $broj ?> koktela</span>
    </div>
    <div class="grid">
      <?php foreach ($kat->koktel as $k):
        $id = (int)$k['id'];
        $naziv = (string)$k->naziv;
        $opis = (string)$k->opis;
        $sastojci = $k->sastojci->sastojak;
        $max_prikaz = 4;
        $ostatak = count($sastojci) - $max_prikaz;
      ?>
      <div class="koktel-card"
           data-naziv="<?= strtolower(htmlspecialchars($naziv)) ?>"
           data-sastojci="<?= strtolower(htmlspecialchars(implode(' ', array_map(fn($s) => (string)$s, (array)$sastojci)))) ?>">
        <div class="card-top">
          <div class="card-id">No. <?= str_pad($id, 2, '0', STR_PAD_LEFT) ?></div>
          <div class="card-naziv"><?= htmlspecialchars($naziv) ?></div>
          <div class="card-opis"><?= htmlspecialchars($opis) ?></div>
        </div>
        <div class="card-divider"></div>
        <div class="card-bottom">
          <div class="sastojci-label">Sastojci</div>
          <?php
          $i = 0;
          foreach ($sastojci as $s):
            if ($i >= $max_prikaz) break;
            $kol = (string)$s['kolicina'];
            $ime = (string)$s;
          ?>
          <div class="sastojak-row">
            <span class="sastojak-kol"><?= htmlspecialchars($kol) ?></span>
            <span class="sastojak-ime"><?= htmlspecialchars($ime) ?></span>
          </div>
          <?php $i++; endforeach; ?>
          <?php if ($ostatak > 0): ?>
            <div class="card-more">+ <?= $ostatak ?> još...</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>


  <?php
  $jsonPath = 'kokteli_api.json';
  if (file_exists($jsonPath)) {
      $apiKokteli = json_decode(file_get_contents($jsonPath), true);
      if (!empty($apiKokteli)):
  ?>
  <section class="kategorija" data-kat="API">
      <div class="kat-header">
          <span class="kat-ikona">🌐</span>
          <h2 class="kat-naziv">Kokteli s API-ja</h2>
          <span class="kat-broj"><?= count($apiKokteli) ?> koktela</span>
      </div>
      <div class="grid">
          <?php foreach ($apiKokteli as $i => $k): ?>
          <div class="koktel-card"
               data-naziv="<?= strtolower(htmlspecialchars($k['naziv'])) ?>"
               data-sastojci="<?= strtolower(htmlspecialchars(implode(' ', array_map(fn($s) => $s['ime'], $k['sastojci'])))) ?>">
              <div class="card-top">
                  <div class="card-id">API · <?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
                  <div class="card-naziv"><?= htmlspecialchars($k['naziv']) ?></div>
                  <div class="card-opis"><?= htmlspecialchars(mb_substr($k['opis'], 0, 100)) ?>...</div>
              </div>
              <div class="card-divider"></div>
              <div class="card-bottom">
                  <div class="sastojci-label">Sastojci</div>
                  <?php foreach (array_slice($k['sastojci'], 0, 4) as $s): ?>
                  <div class="sastojak-row">
                      <span class="sastojak-kol"><?= htmlspecialchars($s['kolicina']) ?></span>
                      <span class="sastojak-ime"><?= htmlspecialchars($s['ime']) ?></span>
                  </div>
                  <?php endforeach; ?>
                  <?php if (count($k['sastojci']) > 4): ?>
                  <div class="card-more">+ <?= count($k['sastojci']) - 4 ?> još...</div>
                  <?php endif; ?>
              </div>
          </div>
          <?php endforeach; ?>
      </div>
  </section>
  <?php endif; } ?>
</main>

<footer>
  <strong>Koktel Priručnik</strong> &mdash; <?= $ukupno_koktela ?> koktela u <?= $ukupno_kategorija ?> kategorija<br>
  <h4>Kolegij: Podatkovna povezanost i digitalna infrastruktura, 4.semestar<br> Autor: Jan Šlopar &mdash; 0246123378 <br></h4>
  <strong><a href="https://github.com/unknownSlopy/XML/tree/main/P">GitHub repozitorij</a></strong>
</footer>

</body>
</html>