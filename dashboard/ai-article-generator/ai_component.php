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

<script src="/PJBL-main/dashboard/ai-article-generator/js/ai_component.js"></script>