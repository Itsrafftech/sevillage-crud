@extends('layouts.internal')

@section('judul', 'Transaksi Baru')

@section('konten')
    <h1>Transaksi Baru</h1>

    <div class="form-box" style="max-width:640px;">
        <form method="POST" action="{{ route('internal.transaksi.store') }}">
            @csrf

            <div class="form-group">
                <label>Petugas</label>
                <input type="text" value="{{ session('nama') }} ({{ session('id_karyawan') }})" readonly disabled>
            </div>

            <div class="form-group">
                <label for="id_pelanggan">Pelanggan</label>
                <select id="id_pelanggan" name="id_pelanggan" required>
                    <option value="">-- Pilih pelanggan --</option>
                    @foreach ($pelangganList as $p)
                        <option value="{{ $p->id_pelanggan }}" @selected(old('id_pelanggan') == $p->id_pelanggan)>
                            {{ $p->nama ?? ($p->nama_rombongan ?? $p->id_pelanggan) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="id_layanan">Tiket / Paket</label>
                <select id="id_layanan" name="id_layanan" required>
                    <option value="">-- Pilih tiket/paket --</option>
                    @foreach ($layananList as $l)
                        <option value="{{ $l->id_layanan }}" @selected(old('id_layanan') == $l->id_layanan)>
                            [{{ $l->jenis_layanan }}] {{ $l->nama_layanan }}
                        </option>
                    @endforeach
                </select>
                <small class="bantuan">Harga akan dihitung otomatis berdasarkan tarif yang berlaku pada tanggal kunjungan. Paket minimal 20 orang.</small>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="jumlah">Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" value="{{ old('jumlah', 1) }}" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="tgl_kunjungan">Tanggal Kunjungan</label>
                    <input type="date" id="tgl_kunjungan" name="tgl_kunjungan" value="{{ old('tgl_kunjungan') }}" required>
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="metode_bayar">Metode Bayar</label>
                    <select id="metode_bayar" name="metode_bayar" required>
                        @foreach ($metodeList as $m)
                            <option value="{{ $m }}" @selected(old('metode_bayar') == $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="kanal_pemesanan">Kanal Pemesanan</label>
                    <select id="kanal_pemesanan" name="kanal_pemesanan" required>
                        @foreach ($kanalList as $k)
                            <option value="{{ $k }}" @selected(old('kanal_pemesanan') == $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="aksi-baris">
                <button type="submit" class="btn">Simpan</button>
                <a href="{{ route('internal.transaksi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
