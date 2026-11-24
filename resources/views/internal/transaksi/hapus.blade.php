@extends('layouts.internal')

@section('judul', 'Hapus Transaksi')

@section('konten')
    <h1>Hapus Transaksi</h1>

    <div class="form-box">
        <p>Apakah Anda yakin ingin menghapus transaksi berikut? Seluruh tiket dan detail transaksi terkait juga akan terhapus. Tindakan ini tidak dapat dibatalkan.</p>

        <table style="margin:1rem 0;">
            <tr><th>Kode</th><td>{{ $transaksi->id_transaksi }}</td></tr>
            <tr><th>Pelanggan</th><td>{{ $transaksi->nama_pelanggan }}</td></tr>
            <tr><th>Waktu</th><td>{{ $transaksi->waktu_transaksi }}</td></tr>
            <tr><th>Kanal</th><td>{{ $transaksi->kanal_pemesanan }}</td></tr>
            <tr><th>Metode</th><td>{{ $transaksi->metode_bayar }}</td></tr>
        </table>

        <form method="POST" action="{{ route('internal.transaksi.destroy', $transaksi->id_transaksi) }}">
            @csrf
            @method('DELETE')
            <div class="aksi-baris">
                <button type="submit" class="btn btn-bahaya">Ya, Hapus</button>
                <a href="{{ route('internal.transaksi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
