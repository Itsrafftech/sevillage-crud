@extends('layouts.internal')

@section('judul', 'Dashboard Finance')

@section('konten')
    <h1>Dashboard Finance</h1>

    <div class="grid-statistik">
        <div class="statistik">
            <div class="label">Total Pendapatan</div>
            <div class="nilai">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
        </div>
        <div class="statistik">
            <div class="label">Jumlah Transaksi</div>
            <div class="nilai">{{ $jumlahTransaksi }}</div>
        </div>
        <div class="statistik">
            <div class="label">Reservasi Dibayar</div>
            <div class="nilai">{{ $reservasiDibayar }}</div>
        </div>
        <div class="statistik">
            <div class="label">Reservasi Dipesan</div>
            <div class="nilai">{{ $reservasiDipesan }}</div>
        </div>
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
        <h3>Reservasi Berstatus Dipesan</h3>
        <div class="tabel-bungkus">
            <table>
                <thead>
                    <tr><th>Kode</th><th>Pelanggan</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($menungguBayar as $r)
                        <tr>
                            <td>{{ $r->id_reservasi }}</td>
                            <td>{{ $r->nama_pelanggan }}</td>
                            <td>{{ $r->nama_layanan }} #{{ $r->no_unit }}</td>
                            <td>{{ $r->tgl_check_in }}</td>
                            <td>{{ $r->tgl_check_out }}</td>
                            <td>
                                <form method="POST" action="{{ route('internal.reservasi.bayar', $r->id_reservasi) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-kecil">Tandai Dibayar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Tidak ada reservasi yang menunggu pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
