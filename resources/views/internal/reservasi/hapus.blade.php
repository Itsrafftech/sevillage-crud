@extends('layouts.internal')

@section('judul', 'Hapus Reservasi')

@section('konten')
    <h1>Hapus Reservasi</h1>

    <div class="form-box">
        <p>Apakah Anda yakin ingin menghapus reservasi berikut? Tindakan ini tidak dapat dibatalkan.</p>

        <table style="margin:1rem 0;">
            <tr><th>Kode</th><td>{{ $reservasi->id_reservasi }}</td></tr>
            <tr><th>Pelanggan</th><td>{{ $reservasi->nama_pelanggan }}</td></tr>
            <tr><th>Unit</th><td>{{ $reservasi->nama_layanan }} #{{ $reservasi->no_unit }}</td></tr>
            <tr><th>Check-in</th><td>{{ $reservasi->tgl_check_in }}</td></tr>
            <tr><th>Check-out</th><td>{{ $reservasi->tgl_check_out }}</td></tr>
            <tr><th>Status</th><td>@include('partials.badge-status', ['status' => $reservasi->status_reservasi])</td></tr>
        </table>

        <form method="POST" action="{{ route('internal.reservasi.destroy', $reservasi->id_reservasi) }}">
            @csrf
            @method('DELETE')
            <div class="aksi-baris">
                <button type="submit" class="btn btn-bahaya">Ya, Hapus</button>
                <a href="{{ route('internal.reservasi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
