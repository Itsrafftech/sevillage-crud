@extends('layouts.internal')

@section('judul', 'Transaksi')

@section('konten')
    <h1>Transaksi Tiket &amp; Paket</h1>

    @if (session('peran') === 'tix')
        <div class="aksi-baris" style="margin-top:-.5rem; margin-bottom:1rem;">
            <a href="{{ route('internal.transaksi.create') }}" class="btn">+ Transaksi Baru</a>
        </div>
    @endif

    <div class="tabel-bungkus">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Waktu</th>
                    <th>Kanal</th>
                    <th>Metode</th>
                    <th>Ringkasan Item</th>
                    <th>Total</th>
                    @if (session('peran') === 'tix')
                        <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($transaksi as $t)
                    <tr>
                        <td>{{ $t->id_transaksi }}</td>
                        <td>{{ $t->nama_pelanggan }}</td>
                        <td>{{ $t->waktu_transaksi }}</td>
                        <td>{{ $t->kanal_pemesanan }}</td>
                        <td>{{ $t->metode_bayar }}</td>
                        <td>{{ $t->ringkasan }}</td>
                        <td>Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        @if (session('peran') === 'tix')
                            <td>
                                <div style="display:flex; gap:.4rem;">
                                    <a class="btn btn-kecil btn-outline" href="{{ route('internal.transaksi.edit', $t->id_transaksi) }}">Ubah</a>
                                    <a class="btn btn-kecil btn-bahaya" href="{{ route('internal.transaksi.konfirmasi_hapus', $t->id_transaksi) }}">Hapus</a>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8">Belum ada data transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
