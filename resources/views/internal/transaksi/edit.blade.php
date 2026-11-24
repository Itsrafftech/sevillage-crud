@extends('layouts.internal')

@section('judul', 'Ubah Transaksi')

@section('konten')
    <h1>Ubah Transaksi {{ $transaksi->id_transaksi }}</h1>

    <div class="panel">
        <h3>Item Transaksi (tidak dapat diubah di sini)</h3>
        <div class="tabel-bungkus">
            <table>
                <thead><tr><th>Layanan</th><th>Jumlah</th><th>Harga Satuan</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($items as $i)
                        <tr>
                            <td>{{ $i->nama_layanan }}</td>
                            <td>{{ $i->jumlah }}</td>
                            <td>Rp {{ number_format($i->harga_satuan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($i->jumlah * $i->harga_satuan, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p style="font-size:.85rem; color:#666; margin-top:.6rem;">Catatan: hapus transaksi ini lalu buat ulang bila item/jumlah/tanggal salah.</p>
    </div>

    <div class="form-box" style="max-width:640px;">
        <form method="POST" action="{{ route('internal.transaksi.update', $transaksi->id_transaksi) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="id_pelanggan">Pelanggan</label>
                <select id="id_pelanggan" name="id_pelanggan" required>
                    @foreach ($pelangganList as $p)
                        <option value="{{ $p->id_pelanggan }}" @selected(old('id_pelanggan', $transaksi->id_pelanggan) == $p->id_pelanggan)>
                            {{ $p->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="metode_bayar">Metode Bayar</label>
                <select id="metode_bayar" name="metode_bayar" required>
                    @foreach ($metodeList as $m)
                        <option value="{{ $m }}" @selected(old('metode_bayar', $transaksi->metode_bayar) == $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="kanal_pemesanan">Kanal Pemesanan</label>
                <select id="kanal_pemesanan" name="kanal_pemesanan" required>
                    @foreach (['Loket', 'WhatsApp', 'Tiket.com'] as $k)
                        <option value="{{ $k }}" @selected(old('kanal_pemesanan', $transaksi->kanal_pemesanan) == $k)>{{ $k }}</option>
                    @endforeach
                </select>
            </div>

            <div class="aksi-baris">
                <button type="submit" class="btn">Simpan</button>
                <a href="{{ route('internal.transaksi.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
