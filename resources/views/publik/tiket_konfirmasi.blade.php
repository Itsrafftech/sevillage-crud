@extends('layouts.publik')

@section('judul', 'Pembelian Berhasil')

@section('konten')
    <div class="form-box">
        <h1>Pembelian Berhasil</h1>
        <table style="margin:1rem 0;">
            <tr><th>Kode Transaksi</th><td><strong>{{ $idTransaksi }}</strong></td></tr>
            <tr><th>Layanan</th><td>{{ $namaLayanan }}</td></tr>
            <tr><th>Jumlah</th><td>{{ $jumlah }}</td></tr>
            <tr><th>Total</th><td>Rp {{ number_format($total, 0, ',', '.') }}</td></tr>
        </table>
        <a href="{{ route('publik.beranda') }}" class="btn">Kembali ke Beranda</a>
    </div>
@endsection
