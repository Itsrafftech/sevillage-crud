@extends('layouts.publik')

@section('judul', 'Sevillage Puncak')

@section('konten')
    <h1>Selamat Datang di Sevillage Puncak</h1>
    <p style="color:#555;">Taman wisata alam dengan penginapan, wahana, tiket masuk, dan paket wisata.</p>

    <h2 style="margin-top:2rem;">Tipe Penginapan</h2>
    <div class="grid-kartu">
        @forelse ($tipePenginapan as $t)
            <div class="kartu">
                <h3>{{ $t->nama_layanan }}</h3>
                <div>Kapasitas: {{ $t->kapasitas }} orang</div>
                @if ($t->fasilitas->count())
                    <ul class="fasilitas">
                        @foreach ($t->fasilitas as $f)
                            <li>{{ $f }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="harga">
                    Weekday: Rp {{ number_format($t->tarif_weekday ?? 0, 0, ',', '.') }} <small>/malam</small><br>
                    Weekend: Rp {{ number_format($t->tarif_weekend ?? 0, 0, ',', '.') }} <small>/malam</small>
                </div>
                <a href="{{ route('publik.pesan', ['tipe' => $t->id_layanan]) }}" class="btn btn-blok">Pesan Tipe Ini</a>
            </div>
        @empty
            <p>Belum ada data tipe penginapan.</p>
        @endforelse
    </div>

    <h2>Tiket Masuk</h2>
    <div class="grid-kartu">
        @forelse ($tiketMasuk as $t)
            <div class="kartu">
                <h3>{{ $t->nama_layanan }}</h3>
                <div class="harga">
                    Weekday: Rp {{ number_format($t->tarif_weekday ?? 0, 0, ',', '.') }}<br>
                    Weekend: Rp {{ number_format($t->tarif_weekend ?? 0, 0, ',', '.') }}
                </div>
                <a href="{{ route('publik.tiket', ['layanan' => $t->id_layanan]) }}" class="btn btn-blok">Beli</a>
            </div>
        @empty
            <p>Belum ada data tiket masuk.</p>
        @endforelse
    </div>

    <h2>Paket Wisata</h2>
    <div class="grid-kartu">
        @forelse ($paket as $p)
            <div class="kartu">
                <h3>{{ $p->nama_layanan }}</h3>
                <div>Kategori: {{ $p->kategori_paket }}</div>
                <div>Minimal {{ $p->min_pax }} orang &middot; {{ $p->jumlah_wahana }} wahana</div>
                <div class="harga">
                    Weekday: Rp {{ number_format($p->tarif_weekday ?? 0, 0, ',', '.') }} <small>/orang</small><br>
                    Weekend: Rp {{ number_format($p->tarif_weekend ?? 0, 0, ',', '.') }} <small>/orang</small>
                </div>
                <a href="{{ route('publik.tiket', ['layanan' => $p->id_layanan]) }}" class="btn btn-blok">Pesan Paket</a>
            </div>
        @empty
            <p>Belum ada data paket wisata.</p>
        @endforelse
    </div>
@endsection
