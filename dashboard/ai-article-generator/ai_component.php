<!-- AI GENERATE BOX COMPONENT -->
<div class="ai-box">
    <h3>✨ Generate Artikel dengan AI</h3>
    <div class="ai-row">
        <input type="text" id="topikInput"
            placeholder="Contoh: Lumba-lumba spinner di Laut Banda">
        <select id="kategoriAI">
            <option value="biota">Biota</option>
            <option value="wisata">Wisata</option>
            <option value="konservasi">Konservasi</option>
            <option value="geologi">Geologi</option>
        </select>
    </div>
    <button class="btn-generate" id="generateBtn" type="button" onclick="generateArtikel()">
        ✨ Generate Artikel
    </button>
    <p id="statusMsg"></p>
</div>

<!-- PILIHAN GAMBAR PEXELS COMPONENT -->
<div id="sectionGambar" style="display:none;">
    <label>Pilih Gambar Artikel dari Pexels</label>
    <div id="pilihanGambar"></div>
    <p class="gambar-terpilih" id="labelGambarDipilih"></p>
</div>

<script>
async function generateArtikel() {
    const topik    = document.getElementById('topikInput').value.trim();
    const kategori = document.getElementById('kategoriAI').value;
    const btn      = document.getElementById('generateBtn');
    const status   = document.getElementById('statusMsg');

    if (!topik) {
        status.textContent = '⚠️ Masukkan topik dulu!';
        status.style.color = '#d97706';
        return;
    }

    btn.disabled    = true;
    btn.textContent = '⏳ Generating...';
    status.style.color = '#0369A1';
    status.textContent = 'Sedang menulis artikel, tunggu ~15 detik...';

    try {
        const fd = new FormData();
        fd.append('topik', topik);
        fd.append('kategori', kategori);

        // Path ke file generate.php yang baru saja dipindahkan
        const res  = await fetch('../ai-article-generator/generate.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        document.getElementById('judul').value   = data.judul   || '';
        document.getElementById('tanggal').value = data.tanggal || '';

        const selKategori = document.getElementById('kategori');
        if (data.kategori) selKategori.value = data.kategori;

        if (window.quillInstance && data.isi) {
            window.quillInstance.root.innerHTML =
                data.isi.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
        }

        let slugInput = document.getElementById('slugField');
        if (!slugInput) {
            slugInput = document.createElement('input');
            slugInput.type = 'hidden';
            slugInput.name = 'slug';
            slugInput.id   = 'slugField';
            document.getElementById('articleForm').appendChild(slugInput);
        }
        slugInput.value = data.slug || '';

        status.textContent = '✅ Artikel berhasil di-generate! Mencari gambar...';
        status.style.color = '#059669';

        if (data.keyword_gambar) await cariGambar(data.keyword_gambar);

    } catch (err) {
        status.textContent = '❌ Error: ' + err.message;
        status.style.color = '#dc2626';
    } finally {
        btn.disabled    = false;
        btn.textContent = '✨ Generate Artikel';
    }
}

async function cariGambar(keyword) {
    const status = document.getElementById('statusMsg');
    try {
        const fd = new FormData();
        fd.append('keyword', keyword);

        // Pastikan path ke cari_gambar.php benar
        const res  = await fetch('cari_gambar.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.error) throw new Error(data.error);

        const container = document.getElementById('pilihanGambar');
        container.innerHTML = '';

        data.photos.forEach(foto => {
            const div = document.createElement('div');
            div.className = 'foto-pilihan';
            div.innerHTML = `
                <img src="${foto.url_kecil}" alt="${foto.alt}"
                     onclick="pilihGambar('${foto.url}', '${foto.credit}', this)">
                <small>📷 ${foto.credit}</small>
            `;
            container.appendChild(div);
        });

        document.getElementById('sectionGambar').style.display = 'block';
        status.textContent = '✅ Selesai! Pilih foto lalu klik Simpan Artikel.';

    } catch (err) {
        status.textContent = '✅ Artikel siap! (Gambar gagal dimuat: ' + err.message + ')';
    }
}

function pilihGambar(url, credit, imgEl) {
    document.querySelectorAll('.foto-pilihan img').forEach(el => el.classList.remove('dipilih'));
    imgEl.classList.add('dipilih');
    document.getElementById('fieldGambarUrl').value    = url;
    document.getElementById('fieldGambarCredit').value = credit;
    document.getElementById('labelGambarDipilih').textContent = '✓ Foto dipilih — by ' + credit;
}
</script>