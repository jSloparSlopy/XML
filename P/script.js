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

    document.querySelectorAll('.kategorija').forEach(kat => {
      const vidljivi = [...kat.querySelectorAll('.koktel-card')].some(c => c.style.display !== 'none');
      kat.style.display = vidljivi ? '' : 'none';
    });

    noResults.style.display = imaRezultata ? 'none' : 'block';
  });

  function filterKat(naziv, btn) {
    pretraga.value = '';
    document.querySelectorAll('.koktel-card').forEach(c => c.style.display = '');
    noResults.style.display = 'none';

    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.kategorija').forEach(kat => {
      if (naziv === 'sve') {
        kat.style.display = '';
      } else {
        kat.style.display = kat.dataset.kat === naziv ? '' : 'none';
      }
    });
  }

  async function pretražiApi() {
    const q = document.getElementById('pretraga').value.trim();
    if (!q) return;

    const res = await fetch(`api_pretraga.php?q=${encodeURIComponent(q)}`);
    const data = await res.json();

    if (data.greska) {
        alert(data.greska);
        return;
    }

    if (data.izvor === 'lokalno') {
        alert('Koktel već postoji lokalno!');
        return;
    }

    alert('Koktel "' + data.naziv + '" spremljen iz API-ja!');
    location.reload();
  }

  function scrollKat(amount) {
    document.getElementById('catNav').scrollBy({ left: amount, behavior: 'smooth' });
  }
