@extends('layouts.internal')

@section('judul', 'Dashboard Manajer')

@section('konten')
    <h1>Dashboard Manajer</h1>
    <p style="color:#555; font-size:.88rem; margin-top:-.6rem;">Tampilan hanya baca (read-only).</p>

    <div class="grid-statistik">
        <div class="statistik">
            <div class="label">Okupansi Unit</div>
            <div class="nilai">{{ $okupansi }}%</div>
        </div>
        <div class="statistik">
            <div class="label">Unit Tersedia</div>
            <div class="nilai">{{ $unitTersedia }} / {{ $totalUnit }}</div>
        </div>
        <div class="statistik">
            <div class="label">Total Reservasi</div>
            <div class="nilai">{{ $totalReservasi }}</div>
        </div>
        <div class="statistik">
            <div class="label">Pendapatan Penginapan</div>
            <div class="nilai">Rp {{ number_format($pendapatanPenginapan, 0, ',', '.') }}</div>
        </div>
        <div class="statistik">
            <div class="label">Pendapatan Tiket &amp; Paket</div>
            <div class="nilai">Rp {{ number_format($pendapatanTiketPaket, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="panel">
        <h3>Reservasi per Status</h3>
        @php $maxStatus = $perStatusReservasi->max('jumlah') ?: 1; @endphp
        @foreach ($perStatusReservasi as $s)
            <div class="baris-batang">
                <div class="label-batang">{{ $s->status_reservasi }}</div>
                <progress value="{{ $s->jumlah }}" max="{{ $maxStatus }}"></progress>
                <div class="nilai-batang">{{ $s->jumlah }}</div>
            </div>
        @endforeach
    </div>

    <div class="panel">
        <h3>Unit per Status</h3>
        @php $maxUnit = $perStatusUnit->max('jumlah') ?: 1; @endphp
        @foreach ($perStatusUnit as $u)
            <div class="baris-batang">
                <div class="label-batang">{{ $u->status_unit }}</div>
                <progress value="{{ $u->jumlah }}" max="{{ $maxUnit }}"></progress>
                <div class="nilai-batang">{{ $u->jumlah }}</div>
            </div>
        @endforeach
    </div>
@endsection
