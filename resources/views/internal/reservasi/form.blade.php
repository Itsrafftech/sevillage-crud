@extends('layouts.internal')

@section('judul', $reservasi ? 'Ubah Reservasi' : 'Tambah Reservasi')

@section('konten')
    @php
        $unitTerpilih = $reservasi ? ($reservasi->id_layanan.'|'.$reservasi->no_unit) : old('unit');

        // Status yang boleh dipilih saat ubah mengikuti aturan transisi trigger database.
        $transisi = [
            'Dipesan' => ['Dipesan', 'Dibayar', 'Dibatalkan'],
            'Dibayar' => ['Dibayar', 'Check-in', 'Dibatalkan'],
            'Check-in' => ['Check-in', 'Selesai'],
            'Selesai' => ['Selesai'],
            'Dibatalkan' => ['Dibatalkan'],
        ];
        $opsiStatus = $reservasi ? ($transisi[$reservasi->status_reservasi] ?? $statusList) : $statusList;
    @endphp

    <h1>{{ $reservasi ? 'Ubah Reservasi '.$reservasi->id_reservasi : 'Tambah Reservasi' }}</h1>

    <div class="form-box" style="max-width:720px;">
        <form method="POST" action="{{ $reservasi ? route('internal.reservasi.update', $reservasi->id_reservasi) : route('internal.reservasi.store') }}">
            @csrf
            @if ($reservasi) @method('PUT') @endif

            <div class="form-group">
                <label>Kode Reservasi</label>
                <input type="text" value="{{ $reservasi ? $reservasi->id_reservasi : $idBaru }}" readonly disabled>
            </div>

            <div class="form-group">
                <label>Petugas</label>
                <input type="text" value="{{ session('nama') }} ({{ session('id_karyawan') }})" readonly disabled>
            </div>

            <div class="form-group">
                <label for="id_pelanggan">Pelanggan</label>
                <select id="id_pelanggan" name="id_pelanggan" required>
                    <option value="">-- Pilih pelanggan --</option>
                    @foreach ($pelangganList as $p)
                        <option value="{{ $p->id_pelanggan }}" @selected(old('id_pelanggan', $reservasi->id_pelanggan ?? null) == $p->id_pelanggan)>
                            {{ $p->nama }} ({{ $p->no_telepon }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="unit">Unit</label>
                <select id="unit" name="unit" required>
                    <option value="">-- Pilih unit --</option>
                    @foreach ($unitList as $u)
                        <option value="{{ $u->id_layanan }}|{{ $u->no_unit }}" @selected($unitTerpilih == $u->id_layanan.'|'.$u->no_unit)>
                            {{ $u->nama_layanan }} #{{ $u->no_unit }} ({{ $u->status_unit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="tgl_check_in">Tanggal Check-in</label>
                    <input type="date" id="tgl_check_in" name="tgl_check_in" value="{{ old('tgl_check_in', $reservasi->tgl_check_in ?? '') }}" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="tgl_check_out">Tanggal Check-out</label>
                    <input type="date" id="tgl_check_out" name="tgl_check_out" value="{{ old('tgl_check_out', $reservasi->tgl_check_out ?? '') }}" required>
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="jumlah_tamu">Jumlah Tamu</label>
                    <input type="number" id="jumlah_tamu" name="jumlah_tamu" min="1" value="{{ old('jumlah_tamu', $reservasi->jumlah_tamu ?? 1) }}" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="jenis_kendaraan">Jenis Kendaraan</label>
                    <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" value="{{ old('jenis_kendaraan', $reservasi->jenis_kendaraan ?? '') }}" placeholder="Mobil / Motor / Bus (opsional)">
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="kanal_pemesanan">Kanal Pemesanan</label>
                    <select id="kanal_pemesanan" name="kanal_pemesanan" required>
                        @foreach ($kanalList as $k)
                            <option value="{{ $k }}" @selected(old('kanal_pemesanan', $reservasi->kanal_pemesanan ?? 'WhatsApp') == $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="status_reservasi">Status</label>
                    <select id="status_reservasi" name="status_reservasi" required>
                        @foreach ($opsiStatus as $s)
                            <option value="{{ $s }}" @selected(old('status_reservasi', $reservasi->status_reservasi ?? 'Dipesan') == $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="aksi-baris">
                <button type="submit" class="btn">Simpan</button>
                <a href="{{ route('internal.reservasi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
