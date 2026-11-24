<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Sevillage Puncak')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="nav-publik">
        <div class="container">
            <a href="{{ route('publik.beranda') }}" class="brand">Sevillage Puncak</a>
            <nav>
                <a href="{{ route('publik.beranda') }}">Beranda</a>
                <a href="{{ route('publik.pesan') }}">Pesan Penginapan</a>
                <a href="{{ route('publik.tiket') }}">Beli Tiket</a>
                <a href="{{ route('publik.cek') }}">Cek Reservasi</a>
                <a href="{{ route('internal.login') }}">Login Staf</a>
            </nav>
        </div>
    </header>

    <main class="container" style="padding-top:1.5rem; padding-bottom:3rem;">
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
</body>
</html>
