@extends('layouts.publik')

@section('judul', 'Cek Reservasi')

@section('konten')
    <h1>Cek Reservasi</h1>

    <div class="form-box">
        <form method="POST" action="{{ route('publik.cek.proses') }}">
            @csrf
            <div class="form-group">
                <label for="id_reservasi">Kode Reservasi</label>
                <input type="text" id="id_reservasi" name="id_reservasi" value="{{ old('id_reservasi', $reservasi->id_reservasi ?? '') }}" placeholder="Contoh: R-001" required>
            </div>
            <div class="form-group">
                <label for="no_telepon">Nomor Telepon</label>
                <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon', $telepon ?? '') }}" required>
            </div>
            <button type="submit" class="btn btn-blok">Cek Reservasi</button>
        </form>
    </div>

    @isset($pesanTidakDitemukan)
        <div class="notif notif-error" style="max-width:640px; margin:0 auto;">{{ $pesanTidakDitemukan }}</div>
    @endisset

    @isset($pesanSukses)
        <div class="notif notif-sukses" style="max-width:640px; margin:0 auto;">{{ $pesanSukses }}</div>
    @endisset

    @isset($pesanError)
        <div class="notif notif-error" style="max-width:640px; margin:0 auto;">{{ $pesanError }}</div>
    @endisset

    @if ($reservasi)
        <div class="form-box">
            <h2>Detail Reservasi</h2>
            <table style="margin:1rem 0;">
                <tr><th>Kode</th><td>{{ $reservasi->id_reservasi }}</td></tr>
                <tr><th>Nama</th><td>{{ $reservasi->nama_pelanggan }}</td></tr>
                <tr><th>Unit</th><td>{{ $reservasi->nama_layanan }} #{{ $reservasi->no_unit }}</td></tr>
                <tr><th>Check-in</th><td>{{ $reservasi->tgl_check_in }}</td></tr>
                <tr><th>Check-out</th><td>{{ $reservasi->tgl_check_out }}</td></tr>
                <tr><th>Jumlah Tamu</th><td>{{ $reservasi->jumlah_tamu }}</td></tr>
                <tr><th>Status</th><td>@include('partials.badge-status', ['status' => $reservasi->status_reservasi])</td></tr>
            </table>

            @if (in_array($reservasi->status_reservasi, ['Dipesan', 'Dibayar']))
                <form method="POST" action="{{ route('publik.cek.batal', $reservasi->id_reservasi) }}"
                      onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?');">
                    @csrf
                    <input type="hidden" name="no_telepon" value="{{ $telepon ?? '' }}">
                    <button type="submit" class="btn btn-bahaya">Batalkan Reservasi</button>
                </form>
            @endif
        </div>
    @endif
@endsection
