<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Menentukan harga layanan yang berlaku pada tanggal tertentu, berdasarkan
 * tabel `tarif` (jenis_hari Weekday/Weekend, periode berlaku_mulai..berlaku_sampai).
 */
class TarifService
{
    public static function jenisHari(string|Carbon $tanggal): string
    {
        $c = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        // isDayOfWeek Sabtu/Minggu
        return $c->isWeekend() ? 'Weekend' : 'Weekday';
    }

    public static function harga(string $idLayanan, string|Carbon $tanggal): ?int
    {
        $tgl = $tanggal instanceof Carbon ? $tanggal->toDateString() : $tanggal;
        $jenisHari = self::jenisHari($tanggal);

        $row = DB::table('tarif')
            ->where('id_layanan', $idLayanan)
            ->where('jenis_hari', $jenisHari)
            ->where('berlaku_mulai', '<=', $tgl)
            ->where(function ($q) use ($tgl) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $tgl);
            })
            ->orderByDesc('berlaku_mulai')
            ->first();

        return $row ? (int) $row->harga : null;
    }

    /**
     * Mengambil harga untuk jenis_hari tertentu (Weekday/Weekend) yang
     * berlaku pada tanggal acuan (default: hari ini). Dipakai di halaman
     * beranda untuk menampilkan tarif weekday & weekend sekaligus.
     */
    public static function hargaBerlaku(string $idLayanan, string $jenisHari, ?string $tanggalAcuan = null): ?int
    {
        $tgl = $tanggalAcuan ?? Carbon::now()->toDateString();

        $row = DB::table('tarif')
            ->where('id_layanan', $idLayanan)
            ->where('jenis_hari', $jenisHari)
            ->where('berlaku_mulai', '<=', $tgl)
            ->where(function ($q) use ($tgl) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $tgl);
            })
            ->orderByDesc('berlaku_mulai')
            ->first();

        return $row ? (int) $row->harga : null;
    }
}
