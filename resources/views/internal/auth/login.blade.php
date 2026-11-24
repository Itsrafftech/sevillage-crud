@extends('layouts.publik')

@section('judul', 'Login Staf')

@section('konten')
    <div class="form-box">
        <h1>Login Staf Internal</h1>
        <p style="color:#555; font-size:.9rem;">Masuk menggunakan ID Karyawan dan password untuk mengakses halaman internal.</p>

        <form method="POST" action="{{ route('internal.login.proses') }}">
            @csrf
            <div class="form-group">
                <label for="id_karyawan">ID Karyawan</label>
                <input type="text" id="id_karyawan" name="id_karyawan" value="{{ old('id_karyawan') }}" placeholder="Contoh: K-008" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-blok">Masuk</button>
        </form>

        <hr style="border:none; border-top:1px solid var(--garis); margin:1.5rem 0;">

        <h3 style="font-size:.95rem;">Akun Demo (password: {{ $passwordDemo }})</h3>
        <div style="display:flex; flex-direction:column; gap:.5rem; margin-top:.6rem;">
            @foreach ($akunDemo as $akun)
                <form method="POST" action="{{ route('internal.login.proses') }}">
                    @csrf
                    <input type="hidden" name="id_karyawan" value="{{ $akun['id_karyawan'] }}">
                    <input type="hidden" name="password" value="{{ $passwordDemo }}">
                    <button type="submit" class="btn btn-outline btn-blok" style="text-align:left; display:flex; justify-content:space-between;">
                        <span>{{ $akun['id_karyawan'] }} - {{ $akun['nama'] }}</span>
                        <span>{{ $akun['label_peran'] }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endsection
