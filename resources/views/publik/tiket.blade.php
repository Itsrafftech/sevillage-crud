@extends('layouts.publik')

@section('judul', 'Beli Tiket & Paket')

@section('konten')
    <h1>Beli Tiket Masuk / Paket Wisata</h1>

    <div class="form-box">
        <form method="POST" action="{{ route('publik.tiket.simpan') }}">
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
                <label for="id_layanan">Tiket / Paket</label>
                <select id="id_layanan" name="id_layanan" required>
                    <option value="">-- Pilih --</option>
                    @foreach ($layananList as $l)
                        <option value="{{ $l->id_layanan }}" @selected(old('id_layanan', $layananTerpilih) == $l->id_layanan)>
                            [{{ $l->jenis_layanan }}] {{ $l->nama_layanan }}
                            @if ($l->jenis_layanan === 'Paket') (min {{ $l->min_pax }} orang) @endif
                            - Weekday Rp {{ number_format($l->tarif_weekday ?? 0, 0, ',', '.') }} / Weekend Rp {{ number_format($l->tarif_weekend ?? 0, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
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

            <div class="form-group">
                <label for="metode_bayar">Metode Bayar</label>
                <select id="metode_bayar" name="metode_bayar" required>
                    <option value="Transfer" @selected(old('metode_bayar') == 'Transfer')>Transfer</option>
                    <option value="Debit" @selected(old('metode_bayar') == 'Debit')>Debit</option>
                </select>
            </div>

            <button type="submit" class="btn btn-blok">Beli Sekarang</button>
        </form>
    </div>
@endsection
