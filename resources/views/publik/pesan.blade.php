@extends('layouts.publik')

@section('judul', 'Pesan Penginapan')

@section('konten')
    <h1>Pesan Penginapan</h1>

    <div class="form-box">
        <form method="POST" action="{{ route('publik.pesan.simpan') }}">
            @csrf

            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required>
            </div>

            <div class="form-group">
                <label for="no_telepon">Nomor Telepon</label>
                <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" placeholder="08xxxxxxxxxx" required>
                <small class="bantuan">8-15 digit angka, tanpa spasi/strip.</small>
            </div>

            <div class="form-group">
                <label for="id_layanan">Tipe Penginapan</label>
                <select id="id_layanan" name="id_layanan" required>
                    <option value="">-- Pilih tipe --</option>
                    @foreach ($tipeList as $t)
                        <option value="{{ $t->id_layanan }}" @selected(old('id_layanan', $tipeTerpilih) == $t->id_layanan)>
                            {{ $t->nama_layanan }} (maks {{ $t->kapasitas }} orang)
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="tgl_check_in">Check-in</label>
                    <input type="date" id="tgl_check_in" name="tgl_check_in" value="{{ old('tgl_check_in') }}" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="tgl_check_out">Check-out</label>
                    <input type="date" id="tgl_check_out" name="tgl_check_out" value="{{ old('tgl_check_out') }}" required>
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label for="jumlah_tamu">Jumlah Tamu</label>
                    <input type="number" id="jumlah_tamu" name="jumlah_tamu" min="1" value="{{ old('jumlah_tamu', 1) }}" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label for="jenis_kendaraan">Kendaraan (opsional)</label>
                    <input type="text" id="jenis_kendaraan" name="jenis_kendaraan" value="{{ old('jenis_kendaraan') }}" placeholder="Mobil / Motor / Bus">
                </div>
            </div>

            <small class="bantuan">Unit akan dipilihkan otomatis oleh sistem sesuai ketersediaan.</small>
            <button type="submit" class="btn btn-blok" style="margin-top:1rem;">Pesan Sekarang</button>
        </form>
    </div>
@endsection
