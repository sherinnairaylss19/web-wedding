@extends('layouts.public')
@section('title', "Form Pemesanan – Ike's Gallery")
@section('eyebrow', 'Tanpa Daftar · Tanpa Login')
@section('judul', 'Pemesanan & Konsultasi')
@section('sub', 'Isi data pemesan dan detail acara. Setelah tersimpan, Anda bisa langsung berkonsultasi lewat WhatsApp.')
@section('content')
@php
    $field = fn($n) => $errors->has($n) ? 'border-red-500' : '';
    $daftar = $layanan->map(fn ($l) => ['id' => $l->id, 'nama' => $l->nama, 'kategori' => $l->kategori, 'harga' => (int) $l->harga, 'satuan' => $l->satuan])->values();
    $awal = array_values(old('items', [])) ?: [['layanan_id' => $dipilih, 'jumlah' => 1]];
    $errItems = collect($errors->get('items.*'))->flatten()->unique()->values();
@endphp
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 grid lg:grid-cols-3 gap-8 items-start">
    <form method="POST" action="{{ route('pemesanan.store') }}" class="lg:col-span-2 card p-6 sm:p-10 space-y-6">
        @csrf
        <div><h2 class="font-serif text-xl text-burgundy">Data Pemesan</h2><div class="h-px bg-gradient-to-r from-gold/60 to-transparent mt-2"></div></div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div><label class="lbl" for="nama">Nama Lengkap <span class="text-red-600">*</span></label><input id="nama" name="nama" value="{{ old('nama') }}" class="inp {{ $field('nama') }}" placeholder="Contoh: Ayu Pratiwi">@error('nama')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="lbl" for="no_hp">Nomor WhatsApp <span class="text-red-600">*</span></label><input id="no_hp" name="no_hp" value="{{ old('no_hp') }}" class="inp {{ $field('no_hp') }}" placeholder="08xx-xxxx-xxxx">@error('no_hp')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div><label class="lbl" for="alamat">Alamat</label><textarea id="alamat" name="alamat" rows="2" class="inp resize-none">{{ old('alamat') }}</textarea></div>

        <div class="pt-2"><h2 class="font-serif text-xl text-burgundy">Detail Acara</h2><div class="h-px bg-gradient-to-r from-gold/60 to-transparent mt-2"></div></div>

        <div>
            <label class="lbl">Layanan <span class="text-red-600">*</span></label>
            @if ($errors->has('items') || $errItems->isNotEmpty())
                <div class="rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-3 mb-3" role="alert">
                    @if ($errors->has('items'))<p>{{ $errors->first('items') }}</p>@endif
                    @foreach ($errItems as $m)<p>{{ $m }}</p>@endforeach
                </div>
            @endif
            <div id="rows" class="space-y-3"></div>
            <button type="button" id="addRow" class="mt-3 text-sm font-medium text-burgundy hover:text-gold-dark transition">＋ Tambah layanan lain</button>
            <p class="text-[11px] text-charcoal/50 mt-2">Paket Wedding dipesan per paket (jumlah 1). Untuk Catering, isi jumlah porsi atau box. Anda bisa menggabungkan beberapa layanan dalam satu pemesanan.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div><label class="lbl" for="tanggal_acara">Tanggal Acara <span class="text-red-600">*</span></label><input id="tanggal_acara" name="tanggal_acara" type="date" min="{{ today()->toDateString() }}" value="{{ old('tanggal_acara') }}" class="inp {{ $field('tanggal_acara') }}">@error('tanggal_acara')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="lbl" for="lokasi_acara">Lokasi Acara <span class="text-red-600">*</span></label><input id="lokasi_acara" name="lokasi_acara" value="{{ old('lokasi_acara') }}" class="inp {{ $field('lokasi_acara') }}" placeholder="Gedung / alamat acara">@error('lokasi_acara')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror</div>
        </div>
        <div><label class="lbl" for="catatan">Catatan Tambahan</label><textarea id="catatan" name="catatan" rows="3" class="inp resize-none" placeholder="Jumlah tamu, tema, atau kebutuhan khusus...">{{ old('catatan') }}</textarea></div>
        <button class="btn btn-p w-full py-4 tracking-wider border border-gold/40 shadow-lg shadow-burgundy/30">SIMPAN PEMESANAN</button>
    </form>

    <aside class="card p-6 lg:sticky lg:top-28"><p class="text-gold-dark tracking-[0.3em] text-xs uppercase mb-4">Layanan Dipilih</p><div id="panel"></div></aside>
</section>
@endsection
@push('scripts')
<script>
(() => {
    const DATA = @json($daftar), AWAL = @json($awal), MAX = 10;
    const byId = Object.fromEntries(DATA.map(l => [String(l.id), l]));
    const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const rowsEl = document.getElementById('rows'), panel = document.getElementById('panel'), addBtn = document.getElementById('addRow');
    let seq = 0;

    const h = (tag, cls, attrs = {}) => {
        const e = document.createElement(tag);
        if (cls) e.className = cls;
        Object.entries(attrs).forEach(([k, v]) => e.setAttribute(k, v));
        return e;
    };

    function buildSelect(name, selected) {
        const s = h('select', 'inp flex-1 min-w-0', { name, 'aria-label': 'Layanan' });
        s.appendChild(new Option('— Pilih layanan —', ''));
        const groups = {};
        DATA.forEach(l => { (groups[l.kategori] = groups[l.kategori] || []).push(l); });
        Object.entries(groups).forEach(([kat, items]) => {
            const g = h('optgroup', '', { label: kat });
            items.forEach(l => g.appendChild(new Option((l.nama + ' (' + rp(l.harga) + ' ' + (l.satuan || '') + ')').replace(' )', ')'), l.id)));
            s.appendChild(g);
        });
        s.value = selected ? String(selected) : '';
        return s;
    }

    function addRow(v = {}) {
        const i = seq++, row = h('div', 'row flex flex-col sm:flex-row gap-3 sm:items-center');
        const sel = buildSelect('items[' + i + '][layanan_id]', v.layanan_id);
        const qty = h('input', 'inp sm:w-28', { type: 'number', min: 1, max: 100000, name: 'items[' + i + '][jumlah]', 'aria-label': 'Jumlah' });
        qty.value = v.jumlah || 1;
        const del = h('button', 'text-charcoal/40 hover:text-red-600 text-2xl leading-none px-2 self-end sm:self-auto', { type: 'button', 'aria-label': 'Hapus baris layanan' });
        del.textContent = '×';
        sel.onchange = () => { const l = byId[sel.value]; if (l) qty.value = l.kategori === 'Catering' ? 100 : 1; sync(); };
        qty.oninput = render;
        del.onclick = () => { row.remove(); sync(); };
        row.append(sel, qty, del);
        rowsEl.appendChild(row);
        sync();
    }

    function sync() {
        rowsEl.querySelectorAll('.row').forEach(r => {
            const l = byId[r.querySelector('select').value], q = r.querySelector('input');
            q.readOnly = !!l && l.kategori === 'Wedding';
            if (q.readOnly) q.value = 1;
        });
        const n = rowsEl.children.length;
        rowsEl.querySelectorAll('button').forEach(b => b.classList.toggle('invisible', n <= 1));
        addBtn.classList.toggle('hidden', n >= MAX);
        render();
    }

    function render() {
        // Gabungkan layanan yang sama, sama persis dengan aturan di server (Wedding selalu 1).
        const merged = {};
        rowsEl.querySelectorAll('.row').forEach(r => {
            const l = byId[r.querySelector('select').value];
            if (!l) return;
            const n = Math.max(1, parseInt(r.querySelector('input').value, 10) || 1);
            merged[l.id] = { l, n: l.kategori === 'Wedding' ? 1 : (merged[l.id] ? merged[l.id].n : 0) + n };
        });
        const items = Object.values(merged);
        panel.replaceChildren();
        if (!items.length) {
            const p = h('p', 'text-sm text-charcoal/60');
            p.textContent = 'Belum ada layanan dipilih. Pilih layanan pada formulir untuk melihat estimasi biaya.';
            panel.appendChild(p);
            return;
        }
        const ul = h('ul', 'space-y-4 text-sm');
        let total = 0;
        items.forEach(({ l, n }) => {
            const sub = l.harga * n; total += sub;
            const li = h('li', 'flex justify-between gap-3'), left = h('div'), right = h('span', 'whitespace-nowrap');
            const nm = h('p', 'font-medium text-burgundy'), dt = h('p', 'text-xs text-charcoal/60');
            nm.textContent = l.nama;
            dt.textContent = n + ' × ' + rp(l.harga) + (l.satuan ? ' ' + l.satuan : '');
            right.textContent = rp(sub);
            left.append(nm, dt); li.append(left, right); ul.appendChild(li);
        });
        const foot = h('div', 'mt-5 pt-4 border-t border-gold/30 flex justify-between items-baseline');
        const lbl = h('span', 'font-medium text-burgundy text-sm'), val = h('span', 'font-serif text-xl text-burgundy');
        lbl.textContent = 'Estimasi Total'; val.textContent = rp(total);
        foot.append(lbl, val);
        panel.append(ul, foot);
    }

    addBtn.onclick = () => { if (rowsEl.children.length < MAX) addRow({}); };
    (AWAL.length ? AWAL : [{}]).forEach(addRow);
})();
</script>
@endpush
