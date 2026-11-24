<?php

namespace App\Services;

use Throwable;

/**
 * Menerjemahkan error database (CHECK/FK/UNIQUE, atau RAISE EXCEPTION dari
 * trigger) menjadi pesan ramah berbahasa Indonesia untuk ditampilkan ke pengguna.
 */
class PesanErrorService
{
    /** @var array<string,string> nama constraint => pesan ramah */
    protected static array $petaConstraint = [
        'chk_minimal_satu_peran' => 'Pelanggan harus memiliki minimal satu peran (grup, tamu penginapan, atau OTS).',
        'chk_nama_wajib_kecuali_ots' => 'Nama pelanggan wajib diisi.',
        'chk_grup_punya_rombongan' => 'Nama rombongan wajib diisi untuk pelanggan grup.',
        'chk_tamu_punya_telepon' => 'Nomor telepon wajib diisi untuk tamu penginapan.',
        'chk_urutan_tanggal_inap' => 'Tanggal check-out harus setelah tanggal check-in.',
        'chk_pesan_sebelum_inap' => 'Tanggal pesan tidak boleh setelah tanggal check-in.',
        'chk_dibayar_punya_transaksi' => 'Reservasi berstatus Dibayar/Check-in/Selesai wajib memiliki transaksi.',
        'reservasi_penginapan_jumlah_tamu_check' => 'Jumlah tamu minimal 1 orang.',
        'detail_transaksi_jumlah_check' => 'Jumlah barang/tiket pada transaksi minimal 1.',
        'detail_transaksi_harga_satuan_check' => 'Harga satuan tidak valid.',
        'paket_min_pax_check' => 'Jumlah peserta paket minimal 20 orang.',
        'unit_penginapan_no_unit_check' => 'Nomor unit tidak valid.',
        'tarif_harga_check' => 'Harga tarif tidak valid.',
    ];

    public static function dariException(Throwable $e): string
    {
        $pesan = $e->getMessage();

        if (preg_match('/ERROR:\s*(.+?)(\\\\n|\\n|CONTEXT:|$)/is', $pesan, $m)) {
            $isi = trim($m[1]);

            foreach (self::$petaConstraint as $kunci => $teks) {
                if (str_contains($isi, $kunci)) {
                    return $teks;
                }
            }

            if (str_contains($isi, 'duplicate key value violates unique constraint')) {
                return 'Data dengan kode/nomor tersebut sudah ada. Periksa kembali isian Anda.';
            }

            if (str_contains($isi, 'violates foreign key constraint')) {
                return 'Data terkait tidak ditemukan, atau data ini masih dipakai oleh data lain sehingga tidak bisa dihapus/diubah.';
            }

            if (str_contains($isi, 'violates check constraint')) {
                return 'Data yang dimasukkan tidak memenuhi aturan yang berlaku. Periksa kembali isian Anda.';
            }

            if (str_contains($isi, 'violates not-null constraint')) {
                return 'Ada kolom wajib yang belum diisi.';
            }

            // Pesan dari trigger (RAISE EXCEPTION) biasanya sudah berbahasa
            // Indonesia dan cukup ramah untuk ditampilkan langsung.
            return $isi;
        }

        return 'Terjadi kesalahan saat menyimpan data ke database. Silakan periksa kembali isian Anda.';
    }
}
