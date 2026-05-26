<?php

$xml = simplexml_load_file('kokteli.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
if (!$xml) {
    die('Greška pri učitavanju kokteli.xml');
}

// Emoji ikone po kategoriji
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
</head>
<body>

<header>
  <p class="hero-eyebrow">Kompletni priručnik</p>
  <h1>Koktel<span>Priručnik</span></h1>
  <p class="hero-sub">Sve što trebaš znati o klasičnim i modernim koktelima.</p>
  <div class="hero-line"></div>
</header>

<?php
  // prebrojavanje ukupno
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
</div>

<div class="cat-nav">
  <button class="cat-btn active" onclick="filterKat('sve', this)">Sve kategorije</button>
  <?php foreach ($xml->kategorija as $kat): ?>
    <button class="cat-btn" onclick="filterKat('<?= htmlspecialchars((string)$kat['naziv']) ?>', this)">
      <?= isset($ikone[(string)$kat['naziv']]) ? $ikone[(string)$kat['naziv']] . ' ' : '' ?>
      <?= htmlspecialchars((string)$kat['naziv']) ?>
    </button>
  <?php endforeach; ?>
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
</main>

<footer>
  <strong>Koktel Priručnik</strong> &mdash; <?= $ukupno_koktela ?> koktela u <?= $ukupno_kategorija ?> kategorija
</footer>

<script>
  const pretraga = document.getElementById('pretraga');
  const noResults = document.getElementById('noResults');

  pretraga.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    let imaRezultata = false;

    document.querySelectorAll('.koktel-card').forEach(card => {
      const naziv = card.dataset.naziv || '';
      const sastojci = card.dataset.sastojci || '';
      const match = naziv.includes(q) || sastojci.includes(q);
      card.style.display = match ? '' : 'none';
      if (match) imaRezultata = true;
    });

    // Sakrij prazne kategorije
    document.querySelectorAll('.kategorija').forEach(kat => {
      const vidljivi = [...kat.querySelectorAll('.koktel-card')].some(c => c.style.display !== 'none');
      kat.style.display = vidljivi ? '' : 'none';
    });

    noResults.style.display = imaRezultata ? 'none' : 'block';
  });

  function filterKat(naziv, btn) {
    // Reset pretragu
    pretraga.value = '';
    document.querySelectorAll('.koktel-card').forEach(c => c.style.display = '');
    noResults.style.display = 'none';

    // Aktivan gumb
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Filter kategorija
    document.querySelectorAll('.kategorija').forEach(kat => {
      if (naziv === 'sve') {
        kat.style.display = '';
      } else {
        kat.style.display = kat.dataset.kat === naziv ? '' : 'none';
      }
    });
  }
</script>

</body>
</html>
