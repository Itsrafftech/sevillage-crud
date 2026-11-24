<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Internal') - Sevillage Puncak</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="internal-wrap">
        <aside class="sidebar">
            <div class="brand">Sevillage Puncak</div>
            <div class="peran">{{ config('akun.label_peran')[session('peran')] ?? session('peran') }}</div>

            <nav>
                <a href="{{ route('internal.dashboard') }}" class="{{ request()->routeIs('internal.dashboard') ? 'aktif' : '' }}">Dashboard</a>

                @if (in_array(session('peran'), ['fo', 'fin', 'mgr']))
                    <a href="{{ route('internal.reservasi.index') }}" class="{{ request()->routeIs('internal.reservasi.*') ? 'aktif' : '' }}">Reservasi</a>
                @endif

                @if (in_array(session('peran'), ['tix', 'fin', 'mgr']))
                    <a href="{{ route('internal.transaksi.index') }}" class="{{ request()->routeIs('internal.transaksi.*') ? 'aktif' : '' }}">Transaksi</a>
                @endif

                <a href="{{ route('publik.beranda') }}">Lihat Situs Publik</a>
            </nav>

            <div class="pengguna">
                <div class="nama">{{ session('nama') }}</div>
                <div>{{ session('id_karyawan') }}</div>
                <form class="keluar" method="POST" action="{{ route('internal.logout') }}">
                    @csrf
                    <button type="submit" class="btn-keluar">Keluar</button>
                </form>
            </div>
        </aside>

        <main class="konten">
            @if (session('pesan_sukses'))
                <div class="notif notif-sukses">{{ session('pesan_sukses') }}</div>
            @endif
            @if (session('pesan_error'))
                <div class="notif notif-error">{{ session('pesan_error') }}</div>
            @endif
            @if ($errors->any())
                <div class="notif notif-error">
                    <strong>Periksa kembali isian Anda:</strong>
                    <ul>
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('konten')
        </main>
    </div>
</body>
</html>
