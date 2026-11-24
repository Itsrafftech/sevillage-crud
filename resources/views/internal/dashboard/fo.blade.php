@extends('layouts.internal')

@section('judul', 'Dashboard Front Office')

@section('konten')
    <h1>Dashboard Front Office</h1>

    <div class="grid-statistik">
        <div class="statistik">
            <div class="label">Menunggu Pembayaran</div>
            <div class="nilai">{{ $menungguBayar }}</div>
        </div>
        <div class="statistik">
            <div class="label">Dibayar, Belum Check-in</div>
            <div class="nilai">{{ $dibayarBelumCheckin }}</div>
        </div>
        <div class="statistik">
            <div class="label">Sedang Menginap</div>
            <div class="nilai">{{ $sedangMenginap }}</div>
        </div>
        <div class="statistik">
            <div class="label">Unit Perawatan</div>
            <div class="nilai">{{ $unitPerawatan }}</div>
        </div>
    </div>

    <div class="panel">
        <h3>Perlu Ditindaklanjuti</h3>
        <div class="tabel-bungkus">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Unit</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($perluDitindaklanjuti as $r)
                        <tr>
                            <td>{{ $r->id_reservasi }}</td>
                            <td>{{ $r->nama_pelanggan }}</td>
                            <td>{{ $r->nama_layanan }} #{{ $r->no_unit }}</td>
                            <td>{{ $r->tgl_check_in }}</td>
                            <td>{{ $r->tgl_check_out }}</td>
                            <td>@include('partials.badge-status', ['status' => $r->status_reservasi])</td>
                            <td><a class="btn btn-kecil btn-outline" href="{{ route('internal.reservasi.edit', $r->id_reservasi) }}">Ubah</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Tidak ada reservasi yang perlu ditindaklanjuti.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
