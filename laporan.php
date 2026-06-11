@extends('layouts.app')

@section('title', 'Laporan - Pojok Kafe')
@section('page-title', 'Laporan')

@push('styles')
<style>
    .tab-btn { transition: all 0.2s ease; }
    .tab-btn.active { background-color: #8e4a0e; color: #ffffff; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    .fade-in { animation: fadeIn 0.3s ease both; }
</style>
<div class="px-md pt-lg pb-[100px] space-y-lg max-w-2xl mx-auto">

    {{-- Header & Export --}}
    <div class="flex items-start justify-between">
        <div>
            <h2 class="font-headline-md font-bold text-on-surface">Laporan Penjualan</h2>
            <p class="font-label-md text-xs text-on-surface-variant">Data hingga {{ now()->translatedFormat('d F Y') }}</p>
        </div>
        <div class="flex gap-xs">
            <a href="{{ route('owner.laporan.export', ['format'=>'pdf', 'periode'=>request('periode','hari')]) }}"
               class="flex items-center gap-xs px-sm py-xs bg-error-container text-error rounded-lg font-label-md text-xs hover:opacity-80 transition-opacity">
                <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> PDF
            </a>
            <a href="{{ route('owner.laporan.export', ['format'=>'excel', 'periode'=>request('periode','hari')]) }}"
               class="flex items-center gap-xs px-sm py-xs bg-primary-container text-on-primary-container rounded-lg font-label-md text-xs hover:opacity-80 transition-opacity">
                <span class="material-symbols-outlined text-[16px]">table_chart</span> Excel
            </a>
        </div>
    </div>

    {{-- Filter Periode --}}
    <div class="flex gap-xs overflow-x-auto hide-scrollbar">
        @foreach(['hari'=>'Hari Ini','minggu'=>'Minggu Ini','bulan'=>'Bulan Ini','tahun'=>'Tahun Ini'] as $val => $label)
        <a href="{{ route('owner.laporan', ['periode'=>$val]) }}"
           class="tab-btn px-md py-xs rounded-full font-label-md text-sm whitespace-nowrap border border-outline-variant
                  {{ request('periode','hari') === $val ? 'active' : 'bg-surface text-on-surface-variant hover:bg-surface-container' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Kartu Ringkasan --}}
    <section class="grid grid-cols-2 gap-sm fade-in">
        <div class="bg-primary-container rounded-xl p-md shadow-sm text-on-primary-container flex flex-col justify-between h-[100px]">
            <div class="flex items-center gap-xs">
                <span class="material-symbols-outlined text-[18px]">payments</span>
                <span class="font-label-md text-xs">Total Pendapatan</span>
            </div>
            <p class="font-headline-md text-[16px] font-bold">
                Rp {{ number_format($ringkasan['pendapatan'] ?? 0, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-surface rounded-xl p-md border border-outline-variant shadow-sm flex flex-col justify-between h-[100px]">
            <div class="flex items-center gap-xs text-primary">
                <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                <span class="font-label-md text-xs text-on-surface">Transaksi</span>
            </div>
            <div>
                <p class="font-headline-md text-[16px] font-bold text-on-surface">{{ $ringkasan['total_transaksi'] ?? 0 }}</p>
                <p class="font-label-md text-[10px] text-on-surface-variant">rata-rata Rp {{ number_format($ringkasan['rata_rata'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="bg-surface rounded-xl p-md border border-outline-variant shadow-sm flex flex-col justify-between h-[100px]">
            <div class="flex items-center gap-xs text-tertiary">
                <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                <span class="font-label-md text-xs text-on-surface">Item Terjual</span>
            </div>
            <p class="font-headline-md text-[16px] font-bold text-on-surface">{{ $ringkasan['item_terjual'] ?? 0 }}</p>
        </div>
        <div class="bg-surface rounded-xl p-md border border-outline-variant shadow-sm flex flex-col justify-between h-[100px]">
            <div class="flex items-center gap-xs text-secondary">
                <span class="material-symbols-outlined text-[18px]">trending_up</span>
                <span class="font-label-md text-xs text-on-surface">Pertumbuhan</span>
            </div>
            <div class="flex items-end gap-xs">
                <p class="font-headline-md text-[16px] font-bold
                   {{ ($ringkasan['pertumbuhan'] ?? 0) >= 0 ? 'text-green-600' : 'text-error' }}">
                    {{ ($ringkasan['pertumbuhan'] ?? 0) >= 0 ? '+' : '' }}{{ $ringkasan['pertumbuhan'] ?? 0 }}%
                </p>
                <span class="font-label-md text-[10px] text-on-surface-variant pb-[2px]">vs periode lalu</span>
            </div>
        </div>
    </section>

    {{-- Grafik Pendapatan --}}
    <section class="bg-surface rounded-xl border border-outline-variant shadow-sm p-md space-y-sm fade-in">
        <h3 class="font-body-md font-semibold text-on-surface">Grafik Pendapatan</h3>
        <div class="flex items-end gap-[6px] h-[100px]">
            @php
                $labels = $grafik_labels ?? ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
                $values = $grafik_values ?? [320000,450000,280000,520000,410000,680000,390000];
                $maxV = max($values) ?: 1;
                $today = date('N') - 1;
            @endphp
            @foreach($values as $i => $v)
                <div class="flex-1 flex flex-col items-center gap-[3px]">
                    <div class="w-full rounded-t-sm"
                         style="height: {{ max(4, round($v / $maxV * 80)) }}px;
                                background: {{ $i === $today ? '#8e4a0e' : '#fcddc1' }};
                                transition: height 0.4s ease;">
                    </div>
                    <span class="font-label-md text-[9px] text-on-surface-variant">{{ $labels[$i] }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Produk Terlaris --}}
    <section class="bg-surface rounded-xl border border-outline-variant shadow-sm p-md space-y-sm fade-in">
        <h3 class="font-body-md font-semibold text-on-surface">Produk Terlaris</h3>
        @php
            $topProduk = $produk_terlaris ?? [
                ['nama'=>'Es Kopi Susu','qty'=>42,'pendapatan'=>756000,'pct'=>85],
                ['nama'=>'Cappuccino','qty'=>35,'pendapatan'=>770000,'pct'=>70],
                ['nama'=>'Matcha Latte','qty'=>28,'pendapatan'=>672000,'pct'=>56],
                ['nama'=>'Croissant Butter','qty'=>22,'pendapatan'=>550000,'pct'=>44],
                ['nama'=>'Teh Tarik','qty'=>18,'pendapatan'=>270000,'pct'=>36],
            ];
        @endphp
        @foreach($topProduk as $i => $p)
            <div class="space-y-[4px]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-xs">
                        <span class="w-5 h-5 rounded-full bg-primary-container text-on-primary-container text-[10px] font-bold flex items-center justify-center">
                            {{ $i + 1 }}
                        </span>
                        <span class="font-body-md text-sm text-on-surface font-medium">{{ is_array($p) ? $p['nama'] : $p->nama }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-label-md text-xs text-on-surface-variant">{{ is_array($p) ? $p['qty'] : $p->qty }} terjual</span>
                        <span class="font-label-md text-xs text-primary font-bold ml-sm">Rp {{ number_format(is_array($p) ? $p['pendapatan'] : $p->pendapatan, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="w-full bg-surface-container rounded-full h-[5px]">
                    <div class="h-[5px] rounded-full bg-primary transition-all duration-700"
                         style="width: {{ is_array($p) ? $p['pct'] : ($p->pct ?? 50) }}%"></div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- Tabel Riwayat Transaksi --}}
    <section class="bg-surface rounded-xl border border-outline-variant shadow-sm overflow-hidden fade-in">
        <div class="p-md border-b border-outline-variant flex items-center justify-between">
            <h3 class="font-body-md font-semibold text-on-surface">Riwayat Transaksi</h3>
            <span class="font-label-md text-xs text-on-surface-variant">{{ count($transaksi ?? []) }} transaksi</span>
        </div>

        {{-- Search & filter --}}
        <div class="px-md py-sm border-b border-outline-variant">
            <form method="GET" action="{{ route('owner.laporan') }}">
                <input type="hidden" name="periode" value="{{ request('periode','hari') }}">
                <div class="flex items-center gap-xs bg-surface-container rounded-lg px-sm py-xs">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">search</span>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Cari transaksi..."
                           class="flex-1 bg-transparent font-label-md text-sm text-on-surface placeholder-on-surface-variant/60
                                  focus:outline-none"/>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full">
                <thead>
                    <tr class="bg-surface-container-low text-left">
                        <th class="px-md py-sm font-label-md text-xs text-on-surface-variant">Kode</th>
                        <th class="px-sm py-sm font-label-md text-xs text-on-surface-variant">Waktu</th>
                        <th class="px-sm py-sm font-label-md text-xs text-on-surface-variant">Kasir</th>
                        <th class="px-sm py-sm font-label-md text-xs text-on-surface-variant text-right">Total</th>
                        <th class="px-sm py-sm font-label-md text-xs text-on-surface-variant text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @forelse($transaksi ?? [] as $trx)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-md py-sm">
                            <a href="{{ route('owner.transaksi.detail', $trx->id) }}"
                               class="font-label-md text-xs text-primary font-medium hover:underline">
                               #{{ $trx->kode }}
                            </a>
                        </td>
                        <td class="px-sm py-sm font-label-md text-xs text-on-surface-variant whitespace-nowrap">
                            {{ $trx->created_at->format('H:i') }}
                        </td>
                        <td class="px-sm py-sm font-label-md text-xs text-on-surface">{{ $trx->kasir->name ?? '-' }}</td>
                        <td class="px-sm py-sm font-label-md text-xs font-bold text-on-surface text-right whitespace-nowrap">
                            Rp {{ number_format($trx->total, 0, ',', '.') }}
                        </td>
                        <td class="px-sm py-sm text-center">
                            <span class="px-2 py-[2px] rounded-full text-[10px] font-medium
                                {{ $trx->status === 'selesai' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($trx->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    {{-- Placeholder rows --}}
                    @foreach([
                        ['TRX-001','08:15','Budi',125000,'selesai'],
                        ['TRX-002','09:32','Budi',78000,'selesai'],
                        ['TRX-003','10:45','Siti',204000,'selesai'],
                        ['TRX-004','11:20','Siti',56000,'selesai'],
                        ['TRX-005','13:10','Budi',185000,'selesai'],
                    ] as $row)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-md py-sm font-label-md text-xs text-primary font-medium">#{{ $row[0] }}</td>
                        <td class="px-sm py-sm font-label-md text-xs text-on-surface-variant">{{ $row[1] }}</td>
                        <td class="px-sm py-sm font-label-md text-xs text-on-surface">{{ $row[2] }}</td>
                        <td class="px-sm py-sm font-label-md text-xs font-bold text-on-surface text-right">Rp {{ number_format($row[3],0,',','.') }}</td>
                        <td class="px-sm py-sm text-center">
                            <span class="px-2 py-[2px] rounded-full text-[10px] font-medium bg-green-100 text-green-700">
                                {{ ucfirst($row[4]) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($transaksi) && $transaksi->hasPages())
        <div class="p-md border-t border-outline-variant">
            {{ $transaksi->appends(request()->query())->links() }}
        </div>
        @endif
    </section>
</div>
@endsection
