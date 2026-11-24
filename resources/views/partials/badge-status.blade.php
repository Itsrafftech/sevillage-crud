@php
    $kelas = match($status) {
        'Dipesan' => 'badge-dipesan',
        'Dibayar' => 'badge-dibayar',
        'Check-in' => 'badge-checkin',
        'Selesai' => 'badge-selesai',
        'Dibatalkan' => 'badge-dibatalkan',
        default => 'badge-selesai',
    };
@endphp
<span class="badge {{ $kelas }}">{{ $status }}</span>
