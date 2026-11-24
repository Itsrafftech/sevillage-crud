@extends('layouts.internal')

@section('judul', 'Reservasi Penginapan')

@section('konten')
    <h1>Reservasi Penginapan</h1>

    @if (session('peran') === 'fo')
        <div class="aksi-baris" style="margin-top:-.5rem; margin-bottom:1rem;">
            <a href="{{ route('internal.reservasi.create') }}" class="btn">+ Tambah Reservasi</a>
        </div>
    @endif

    <div class="tabel-bungkus">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Pelanggan</th>
                    <th>Unit</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Malam</th>
                    <th>Tamu</th>
                    <th>Status</th>
                    @if (session('peran') === 'fo')
                        <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $r)
                    <tr>
                        <td>{{ $r->id_reservasi }}</td>
                        <td>{{ $r->nama_pelanggan }}</td>
                        <td>{{ $r->nama_layanan }} #{{ $r->no_unit }}</td>
                        <td>{{ $r->tgl_check_in }}</td>
                        <td>{{ $r->tgl_check_out }}</td>
                        <td>{{ $r->lama_menginap }}</td>
                        <td>{{ $r->jumlah_tamu }}</td>
                        <td>@include('partials.badge-status', ['status' => $r->status_reservasi])</td>
                        @if (session('peran') === 'fo')
                            <td>
                                <div style="display:flex; gap:.4rem;">
                                    <a class="btn btn-kecil btn-outline" href="{{ route('internal.reservasi.edit', $r->id_reservasi) }}">Ubah</a>
                                    <a class="btn btn-kecil btn-bahaya" href="{{ route('internal.reservasi.konfirmasi_hapus', $r->id_reservasi) }}">Hapus</a>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="9">Belum ada data reservasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
