<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function tampilkanLogin()
    {
        if (session('id_karyawan')) {
            return redirect()->route('internal.dashboard');
        }

        $akunDemo = [];
        foreach (array_keys(config('akun.akun')) as $id) {
            $karyawan = DB::table('karyawan')->where('id_karyawan', $id)->first();
            if ($karyawan) {
                $peran = config('akun.peran_departemen')[$karyawan->id_departemen] ?? null;
                $akunDemo[] = [
                    'id_karyawan' => $id,
                    'nama' => $karyawan->nama,
                    'peran' => $peran,
                    'label_peran' => config('akun.label_peran')[$peran] ?? $peran,
                ];
            }
        }

        return view('internal.auth.login', [
            'akunDemo' => $akunDemo,
            'passwordDemo' => config('akun.password_demo'),
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'id_karyawan' => 'required|string',
            'password' => 'required|string',
        ], [
            'id_karyawan.required' => 'ID Karyawan wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $hash = config('akun.akun')[$data['id_karyawan']] ?? null;

        if (! $hash || ! Hash::check($data['password'], $hash)) {
            return back()->withInput(['id_karyawan' => $data['id_karyawan']])
                ->with('pesan_error', 'ID Karyawan atau password salah.');
        }

        $karyawan = DB::table('karyawan')->where('id_karyawan', $data['id_karyawan'])->first();

        if (! $karyawan) {
            return back()->with('pesan_error', 'Data karyawan tidak ditemukan.');
        }

        $peran = config('akun.peran_departemen')[$karyawan->id_departemen] ?? null;

        if (! $peran) {
            return back()->with('pesan_error', 'Departemen karyawan ini belum dipetakan ke peran aplikasi.');
        }

        $request->session()->regenerate();
        session([
            'id_karyawan' => $karyawan->id_karyawan,
            'nama' => $karyawan->nama,
            'id_departemen' => $karyawan->id_departemen,
            'peran' => $peran,
        ]);

        return redirect()->route('internal.dashboard')->with('pesan_sukses', 'Selamat datang, '.$karyawan->nama.'.');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect()->route('internal.login')->with('pesan_sukses', 'Anda telah keluar.');
    }
}
