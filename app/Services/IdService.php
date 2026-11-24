<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Membuat ID berikutnya untuk tabel yang memakai primary key teks
 * berformat PREFIX-NNN (mis. P-013, R-008, T-012, TK-0001).
 */
class IdService
{
    public static function next(string $table, string $kolom, string $prefix): string
    {
        $like = $prefix.'-%';
        $idList = DB::table($table)
            ->select($kolom)
            ->where($kolom, 'like', $like)
            ->pluck($kolom);

        $max = 0;
        $lebar = 3;

        foreach ($idList as $id) {
            $bagianNomor = substr((string) $id, strlen($prefix) + 1);
            if ($bagianNomor !== '' && ctype_digit($bagianNomor)) {
                $nomor = (int) $bagianNomor;
                if ($nomor > $max) {
                    $max = $nomor;
                    $lebar = strlen($bagianNomor);
                }
            }
        }

        $berikutnya = $max + 1;

        return $prefix.'-'.str_pad((string) $berikutnya, $lebar, '0', STR_PAD_LEFT);
    }
}
