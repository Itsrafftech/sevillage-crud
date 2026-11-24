@extends('layouts.internal')

@section('judul', 'Dashboard Ticketing')

@section('konten')
    <h1>Dashboard Ticketing Admin</h1>

    <div class="grid-statistik">
        <div class="statistik">
            <div class="label">Jumlah Transaksi</div>
            <div class="nilai">{{ $jumlahTransaksi }}</div>
        </div>
        <div class="statistik">
            <div class="label">Pendapatan Tiket &amp; Paket</div>
            <div class="nilai">Rp {{ number_format($pendapatan, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="panel">
        <h3>Transaksi per Kanal Pemesanan</h3>
        @php $maxKanal = $perKanal->max('jumlah') ?: 1; @endphp
        @foreach ($perKanal as $k)
            <div class="baris-batang">
                <div class="label-batang">{{ $k->kanal_pemesanan }}</div>
                <progress value="{{ $k->jumlah }}" max="{{ $maxKanal }}"></progress>
                <div class="nilai-batang">{{ $k->jumlah }}</div>
            </div>
        @endforeach
    </div>

    <div class="panel">
        <h3>Transaksi per Metode Bayar</h3>
        @php $maxMetode = $perMetode->max('jumlah') ?: 1; @endphp
        @foreach ($perMetode as $m)
            <div class="baris-batang">
                <div class="label-batang">{{ $m->metode_bayar }}</div>
                <progress value="{{ $m->jumlah }}" max="{{ $maxMetode }}"></progress>
                <div class="nilai-batang">{{ $m->jumlah }}</div>
            </div>
        @endforeach
    </div>

    <div class="panel">
        <h3>5 Transaksi Terbaru</h3>
        <div class="tabel-bungkus">
            <table>
                <thead>
                    <tr><th>Kode</th><th>Pelanggan</th><th>Waktu</th><th>Kanal</th><th>Metode</th></tr>
                </thead>
                <tbody>
                    @forelse ($terbaru as $t)
                        <tr>
                            <td>{{ $t->id_transaksi }}</td>
                            <td>{{ $t->nama_pelanggan }}</td>
                            <td>{{ $t->waktu_transaksi }}</td>
                            <td>{{ $t->kanal_pemesanan }}</td>
                            <td>{{ $t->metode_bayar }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Belum ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <a href="{{ route('internal.transaksi.create') }}" class="btn">+ Transaksi Baru</a>
@endsection
