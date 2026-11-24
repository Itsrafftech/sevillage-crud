<?php

use App\Http\Controllers\Internal\AuthController;
use App\Http\Controllers\Internal\DashboardController;
use App\Http\Controllers\Internal\ReservasiController;
use App\Http\Controllers\Internal\TransaksiController;
use App\Http\Controllers\PublikController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------
// Area publik (tanpa login)
// ---------------------------------------------------------------------
Route::get('/', [PublikController::class, 'beranda'])->name('publik.beranda');

Route::get('/pesan', [PublikController::class, 'formPesan'])->name('publik.pesan');
Route::post('/pesan', [PublikController::class, 'simpanPesan'])->name('publik.pesan.simpan');

Route::get('/tiket', [PublikController::class, 'formTiket'])->name('publik.tiket');
Route::post('/tiket', [PublikController::class, 'simpanTiket'])->name('publik.tiket.simpan');

Route::get('/cek', [PublikController::class, 'formCek'])->name('publik.cek');
Route::post('/cek', [PublikController::class, 'prosesCek'])->name('publik.cek.proses');
Route::post('/cek/{id}/batal', [PublikController::class, 'batalkan'])->name('publik.cek.batal');

// ---------------------------------------------------------------------
// Login area internal
// ---------------------------------------------------------------------
Route::get('/internal/login', [AuthController::class, 'tampilkanLogin'])->name('internal.login');
Route::post('/internal/login', [AuthController::class, 'login'])->name('internal.login.proses');
Route::post('/internal/logout', [AuthController::class, 'logout'])->name('internal.logout');

// ---------------------------------------------------------------------
// Area internal (wajib login)
// ---------------------------------------------------------------------
Route::middleware('login')->prefix('internal')->name('internal.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reservasi: lihat untuk fo, fin, mgr; ubah data hanya fo
    Route::middleware('peran:fo,fin,mgr')->group(function () {
        Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi.index');
    });
    Route::middleware('peran:fo')->group(function () {
        Route::get('/reservasi/create', [ReservasiController::class, 'create'])->name('reservasi.create');
        Route::post('/reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');
        Route::get('/reservasi/{id}/edit', [ReservasiController::class, 'edit'])->name('reservasi.edit');
        Route::put('/reservasi/{id}', [ReservasiController::class, 'update'])->name('reservasi.update');
        Route::get('/reservasi/{id}/hapus', [ReservasiController::class, 'konfirmasiHapus'])->name('reservasi.konfirmasi_hapus');
        Route::delete('/reservasi/{id}', [ReservasiController::class, 'destroy'])->name('reservasi.destroy');
    });
    Route::middleware('peran:fin')->group(function () {
        Route::post('/reservasi/{id}/bayar', [ReservasiController::class, 'tandaiDibayar'])->name('reservasi.bayar');
    });

    // Transaksi: lihat untuk tix, fin, mgr; ubah data hanya tix
    Route::middleware('peran:tix,fin,mgr')->group(function () {
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    });
    Route::middleware('peran:tix')->group(function () {
        Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi/{id}/edit', [TransaksiController::class, 'edit'])->name('transaksi.edit');
        Route::put('/transaksi/{id}', [TransaksiController::class, 'update'])->name('transaksi.update');
        Route::get('/transaksi/{id}/hapus', [TransaksiController::class, 'konfirmasiHapus'])->name('transaksi.konfirmasi_hapus');
        Route::delete('/transaksi/{id}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    });
});
