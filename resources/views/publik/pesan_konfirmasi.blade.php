@extends('layouts.publik')

@section('judul', 'Reservasi Berhasil')

@section('konten')
    <div class="form-box">
        <h1>Reservasi Berhasil Dibuat</h1>
        <table style="margin:1rem 0;">
            <tr><th>Kode Reservasi</th><td><strong>{{ $idReservasi }}</strong></td></tr>
            <tr><th>Unit</th><td>{{ $namaLayanan }} #{{ $noUnit }}</td></tr>
            <tr><th>Check-in</th><td>{{ $tglCheckIn }}</td></tr>
            <tr><th>Check-out</th><td>{{ $tglCheckOut }}</td></tr>
            <tr><th>Status</th><td>@include('partials.badge-status', ['status' => 'Dipesan'])</td></tr>
        </table>
        <p style="font-size:.9rem; color:#555;">Simpan kode reservasi dan nomor telepon Anda untuk mengecek atau membatalkan reservasi ini di halaman "Cek Reservasi".</p>
        <a href="{{ route('publik.beranda') }}" class="btn">Kembali ke Beranda</a>
    </div>
@endsection
