<?php
/*
=================================================================
  PETUNJUK INTEGRASI KE produk.php
=================================================================

LANGKAH 1 — Tambah tombol "Export Excel" di bagian HEADER
  Cari baris ini di produk.php:
    <div class="flex gap-3 flex-wrap">
  Tambahkan tombol berikut di dalam div tersebut (sejajar tombol lain):

LANGKAH 2 — Tambah panel export
  Cari baris </body> paling bawah, letakkan panel di atasnya.

LANGKAH 3 — Tambah JavaScript
  Tambahkan JS di dalam <script> terakhir yang sudah ada.

=================================================================
*/
?>


<!-- =============================================================
  [LANGKAH 1] TOMBOL — taruh di dalam <div class="flex gap-3 flex-wrap">
  di bagian HEADER (sejajar tombol Tambah Produk)
============================================================= -->

<button onclick="bukaPanel('export')"
  class="flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white font-bold px-5 py-2.5 rounded-2xl shadow-md border border-white/30 transition">
  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 3v12"/>
  </svg>
  Export Excel
</button>


<!-- =============================================================
  [LANGKAH 2] PANEL EXPORT — taruh tepat sebelum </body>
============================================================= -->

<div id="panel-export" class="panel-overlay fixed inset-0 z-50 flex justify-end">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px]" onclick="tutupPanel('export')"></div>

  <div class="panel-drawer relative bg-white w-full max-w-md h-full overflow-y-auto shadow-2xl flex flex-col">

    <!-- Header panel -->
    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-5 text-white flex items-center justify-between flex-shrink-0">
      <div>
        <p class="text-emerald-100 text-xs uppercase tracking-widest mb-0.5">Laporan Stok</p>
        <h2 class="text-xl font-bold">Export Excel 📊</h2>
      </div>
      <button onclick="tutupPanel('export')"
        class="w-9 h-9 rounded-full bg-white/20 hover:bg-white/30 flex items-center justify-center transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="p-6 flex-1 space-y-5 overflow-y-auto">

      <!-- ── JENIS ── -->
      <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
          Jenis Pergerakan Stok
        </p>
        <div class="grid grid-cols-3 gap-2">
          <button type="button" data-jenis="semua" onclick="setJenis(this)"
            class="export-jenis border-2 border-emerald-500 bg-emerald-50 text-emerald-700
                   rounded-xl py-2.5 text-sm font-semibold transition text-center leading-tight">
            📋<br><span class="text-xs">Semua</span>
          </button>
          <button type="button" data-jenis="masuk" onclick="setJenis(this)"
            class="export-jenis border-2 border-gray-200 bg-white text-gray-500
                   rounded-xl py-2.5 text-sm font-semibold transition text-center leading-tight">
            📥<br><span class="text-xs">Masuk</span>
          </button>
          <button type="button" data-jenis="keluar" onclick="setJenis(this)"
            class="export-jenis border-2 border-gray-200 bg-white text-gray-500
                   rounded-xl py-2.5 text-sm font-semibold transition text-center leading-tight">
            📤<br><span class="text-xs">Keluar</span>
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-1.5">
          Masuk = restock / tambah stok &nbsp;|&nbsp; Keluar = terjual / dikurangi
        </p>
      </div>

      <!-- ── TIPE PERIODE ── -->
      <div>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
          Pilih Periode
        </p>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" data-tipe="hari" onclick="setTipe(this)"
            class="export-tipe border-2 border-gray-200 bg-white text-gray-500
                   rounded-xl py-2.5 text-sm font-semibold transition">
            📅 Hari Ini
          </button>
          <button type="button" data-tipe="bulan" onclick="setTipe(this)"
            class="export-tipe border-2 border-emerald-500 bg-emerald-50 text-emerald-700
                   rounded-xl py-2.5 text-sm font-semibold transition">
            📆 Bulan Ini
          </button>
          <button type="button" data-tipe="tahun" onclick="setTipe(this)"
            class="export-tipe border-2 border-gray-200 bg-white text-gray-500
                   rounded-xl py-2.5 text-sm font-semibold transition">
            📂 Tahun Ini
          </button>
          <button type="button" data-tipe="custom" onclick="setTipe(this)"
            class="export-tipe border-2 border-gray-200 bg-white text-gray-500
                   rounded-xl py-2.5 text-sm font-semibold transition">
            🗓️ Pilih Sendiri
          </button>
        </div>
      </div>

      <!-- ── SUB-FORM: HARI ── -->
      <div id="xf-hari" class="hidden">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
        <input type="date" id="x-tanggal" value="<?= date('Y-m-d') ?>"
          class="form-input w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50"
          onchange="updatePreview()">
      </div>

      <!-- ── SUB-FORM: BULAN (default tampil) ── -->
      <div id="xf-bulan">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Bulan</label>
            <select id="x-bulan" onchange="updatePreview()"
              class="form-input w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50">
              <?php
              $nama_bulan = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                             '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                             '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
              $bln_ini = date('m');
              foreach ($nama_bulan as $val => $label):
              ?>
              <option value="<?= $val ?>" <?= $val === $bln_ini ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun</label>
            <select id="x-tahun-b" onchange="updatePreview()"
              class="form-input w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50">
              <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 5; $y--): ?>
              <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- ── SUB-FORM: TAHUN ── -->
      <div id="xf-tahun" class="hidden">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun</label>
        <select id="x-tahun" onchange="updatePreview()"
          class="form-input w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50">
          <?php for ($y = (int)date('Y'); $y >= (int)date('Y') - 5; $y--): ?>
          <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <!-- ── SUB-FORM: CUSTOM ── -->
      <div id="xf-custom" class="hidden">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" id="x-dari" value="<?= date('Y-m-01') ?>"
              onchange="updatePreview()"
              class="form-input w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" id="x-sampai" value="<?= date('Y-m-d') ?>"
              onchange="updatePreview()"
              class="form-input w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm bg-gray-50">
          </div>
        </div>
      </div>

      <!-- ── INFO KOLOM ── -->
      <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kolom dalam file Excel</p>
        <p class="text-xs text-gray-500 leading-relaxed">
          No · Tanggal · Jam · Nama Produk · Kategori · Jenis ·
          Qty · Stok Sebelum · Stok Sesudah · Harga Satuan · Total Nilai · Keterangan
        </p>
      </div>

      <!-- ── PREVIEW NAMA FILE ── -->
      <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
        <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider mb-1">
          File yang akan didownload
        </p>
        <p id="x-preview" class="text-sm font-bold text-emerald-800 break-all">
          Laporan_Semua_Stok_<?= date('F_Y') ?>.xls
        </p>
        <p class="text-xs text-emerald-600 mt-1">
          Format .xls · Bisa dibuka di Excel, LibreOffice, Google Sheets
        </p>
      </div>

    </div><!-- /p-6 -->

    <!-- Footer tombol -->
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/60 flex gap-3 flex-shrink-0">
      <button type="button" onclick="tutupPanel('export')"
        class="flex-1 border border-gray-200 text-gray-600 font-semibold py-3 rounded-xl
               hover:bg-gray-100 transition text-sm">
        Batal
      </button>
      <button type="button" onclick="doExport()"
        class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 rounded-xl
               transition text-sm shadow-sm flex items-center justify-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 3v12"/>
        </svg>
        Download .xls
      </button>
    </div>

  </div>
</div>


<!-- =============================================================
  [LANGKAH 3] JAVASCRIPT — tambahkan ke <script> terakhir di produk.php
  (pastikan fungsi bukaPanel / tutupPanel sudah ada)
============================================================= -->
<script>
/* ── state export ─────────────────────────────────────────── */
var _xJenis = 'semua';
var _xTipe  = 'bulan';

/* ── helper toggle style tombol ──────────────────────────── */
function _aktifBtn(sel, btn) {
  document.querySelectorAll(sel).forEach(function(b) {
    b.classList.remove('border-emerald-500','bg-emerald-50','text-emerald-700');
    b.classList.add('border-gray-200','bg-white','text-gray-500');
  });
  btn.classList.remove('border-gray-200','bg-white','text-gray-500');
  btn.classList.add('border-emerald-500','bg-emerald-50','text-emerald-700');
}

/* ── pilih jenis ──────────────────────────────────────────── */
function setJenis(btn) {
  _xJenis = btn.dataset.jenis;
  _aktifBtn('.export-jenis', btn);
  updatePreview();
}

/* ── pilih tipe ───────────────────────────────────────────── */
function setTipe(btn) {
  _xTipe = btn.dataset.tipe;
  _aktifBtn('.export-tipe', btn);
  // sembunyikan semua sub-form dulu
  ['hari','bulan','tahun','custom'].forEach(function(t) {
    document.getElementById('xf-' + t).classList.add('hidden');
  });
  document.getElementById('xf-' + _xTipe).classList.remove('hidden');
  updatePreview();
}

/* ── update nama file preview ─────────────────────────────── */
function updatePreview() {
  var jenisMap = { semua:'Semua_Stok', masuk:'Produk_Masuk', keluar:'Produk_Keluar' };
  var periode  = '';

  if (_xTipe === 'hari') {
    var tgl = document.getElementById('x-tanggal').value;
    if (tgl) periode = tgl.split('-').reverse().join('-');
    else     periode = 'hari-ini';
  } else if (_xTipe === 'bulan') {
    var bln = document.getElementById('x-bulan').value;
    var thn = document.getElementById('x-tahun-b').value;
    periode = bln + '-' + thn;
  } else if (_xTipe === 'tahun') {
    periode = document.getElementById('x-tahun').value;
  } else {
    var dari   = document.getElementById('x-dari').value   || '';
    var sampai = document.getElementById('x-sampai').value || '';
    periode    = dari.split('-').reverse().join('-') + '_sd_' + sampai.split('-').reverse().join('-');
  }

  document.getElementById('x-preview').textContent =
    'Laporan_' + jenisMap[_xJenis] + '_' + periode + '.xls';
}

/* ── build URL & trigger download ─────────────────────────── */
function doExport() {
  var p = new URLSearchParams({ tipe: _xTipe, jenis: _xJenis });

  if (_xTipe === 'hari') {
    p.set('tanggal', document.getElementById('x-tanggal').value);
  } else if (_xTipe === 'bulan') {
    p.set('bulan', document.getElementById('x-bulan').value);
    p.set('tahun', document.getElementById('x-tahun-b').value);
  } else if (_xTipe === 'tahun') {
    p.set('tahun', document.getElementById('x-tahun').value);
  } else {
    p.set('dari',   document.getElementById('x-dari').value);
    p.set('sampai', document.getElementById('x-sampai').value);
  }

  var a = document.createElement('a');
  a.href = 'export_produk.php?' + p.toString();
  a.download = '';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

  setTimeout(function() { tutupPanel('export'); }, 500);
}
</script>