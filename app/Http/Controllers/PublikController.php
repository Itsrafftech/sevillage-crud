<?php

namespace App\Http\Controllers;

use App\Services\IdService;
use App\Services\PesanErrorService;
use App\Services\TarifService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PublikController extends Controller
{
    const PETUGAS_FO = 'K-008';

    const PETUGAS_TIX = 'K-007';

    public function beranda()
    {
        $tipePenginapan = DB::table('tipe_penginapan as tp')
            ->join('layanan as l', 'l.id_layanan', '=', 'tp.id_layanan')
            ->where('l.status_aktif', true)
            ->orderBy('l.nama_layanan')
            ->select('tp.id_layanan', 'l.nama_layanan', 'tp.kapasitas', 'tp.luas')
            ->get();

        foreach ($tipePenginapan as $tipe) {
            $tipe->fasilitas = DB::table('fasilitas_tipe')
                ->where('id_layanan', $tipe->id_layanan)
                ->pluck('fasilitas');
            $tipe->tarif_weekday = TarifService::hargaBerlaku($tipe->id_layanan, 'Weekday');
            $tipe->tarif_weekend = TarifService::hargaBerlaku($tipe->id_layanan, 'Weekend');
        }

        $tiketMasuk = DB::table('layanan')
            ->where('jenis_layanan', 'Tiket Masuk')
            ->where('status_aktif', true)
            ->orderBy('nama_layanan')
            ->get();

        foreach ($tiketMasuk as $t) {
            $t->tarif_weekday = TarifService::hargaBerlaku($t->id_layanan, 'Weekday');
            $t->tarif_weekend = TarifService::hargaBerlaku($t->id_layanan, 'Weekend');
        }

        $paket = DB::table('paket as pk')
            ->join('layanan as l', 'l.id_layanan', '=', 'pk.id_layanan')
            ->where('l.status_aktif', true)
            ->orderBy('l.nama_layanan')
            ->select('pk.id_layanan', 'l.nama_layanan', 'pk.kategori_paket', 'pk.min_pax', 'pk.jumlah_wahana')
            ->get();

        foreach ($paket as $p) {
            $p->tarif_weekday = TarifService::hargaBerlaku($p->id_layanan, 'Weekday');
            $p->tarif_weekend = TarifService::hargaBerlaku($p->id_layanan, 'Weekend');
        }

        return view('publik.beranda', compact('tipePenginapan', 'tiketMasuk', 'paket'));
    }

    // -------------------------------------------------------------
    // Pesan penginapan
    // -------------------------------------------------------------

    public function formPesan(Request $request)
    {
        $tipeList = DB::table('tipe_penginapan as tp')
            ->join('layanan as l', 'l.id_layanan', '=', 'tp.id_layanan')
            ->where('l.status_aktif', true)
            ->orderBy('l.nama_layanan')
            ->select('tp.id_layanan', 'l.nama_layanan', 'tp.kapasitas')
            ->get();

        return view('publik.pesan', [
            'tipeList' => $tipeList,
            'tipeTerpilih' => $request->query('tipe'),
        ]);
    }

    public function simpanPesan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'no_telepon' => ['required', 'regex:/^[0-9]{8,15}$/'],
            'id_layanan' => 'required|string|exists:tipe_penginapan,id_layanan',
            'tgl_check_in' => 'required|date',
            'tgl_check_out' => 'required|date',
            'jumlah_tamu' => 'required|integer|min:1',
            'jenis_kendaraan' => 'nullable|string|max:20',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.regex' => 'Nomor telepon harus berupa 8-15 digit angka.',
            'id_layanan.required' => 'Tipe penginapan wajib dipilih.',
            'id_layanan.exists' => 'Tipe penginapan tidak ditemukan.',
            'tgl_check_in.required' => 'Tanggal check-in wajib diisi.',
            'tgl_check_out.required' => 'Tanggal check-out wajib diisi.',
            'jumlah_tamu.required' => 'Jumlah tamu wajib diisi.',
            'jumlah_tamu.min' => 'Jumlah tamu minimal 1 orang.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $data = $validator->validated();

        if (strtotime($data['tgl_check_out']) <= strtotime($data['tgl_check_in'])) {
            return back()->withInput()->with('pesan_error', 'Tanggal check-out harus setelah tanggal check-in.');
        }

        $tipe = DB::table('tipe_penginapan')->where('id_layanan', $data['id_layanan'])->first();
        if ($data['jumlah_tamu'] > $tipe->kapasitas) {
            return back()->withInput()->with('pesan_error',
                'Jumlah tamu ('.$data['jumlah_tamu'].') melebihi kapasitas tipe ini ('.$tipe->kapasitas.' orang).');
        }

        // Cari unit yang tidak dalam perawatan dan tidak bentrok tanggal.
        $unitTerpakai = DB::table('reservasi_penginapan')
            ->where('id_layanan', $data['id_layanan'])
            ->whereIn('status_reservasi', ['Dipesan', 'Dibayar', 'Check-in'])
            ->where('tgl_check_in', '<', $data['tgl_check_out'])
            ->where('tgl_check_out', '>', $data['tgl_check_in'])
            ->pluck('no_unit');

        $unit = DB::table('unit_penginapan')
            ->where('id_layanan', $data['id_layanan'])
            ->where('status_unit', '!=', 'Perawatan')
            ->whereNotIn('no_unit', $unitTerpakai)
            ->orderBy('no_unit')
            ->first();

        if (! $unit) {
            return back()->withInput()->with('pesan_error',
                'Mohon maaf, semua unit untuk tipe ini penuh pada rentang tanggal tersebut. Silakan pilih tanggal lain.');
        }

        try {
            $idReservasi = DB::transaction(function () use ($data, $unit) {
                $idPelanggan = $this->cariAtauBuatPelangganTamu($data['nama'], $data['no_telepon']);

                $idReservasi = IdService::next('reservasi_penginapan', 'id_reservasi', 'R');

                DB::table('reservasi_penginapan')->insert([
                    'id_reservasi' => $idReservasi,
                    'id_pelanggan' => $idPelanggan,
                    'id_layanan' => $data['id_layanan'],
                    'no_unit' => $unit->no_unit,
                    'id_karyawan' => self::PETUGAS_FO,
                    'tgl_check_in' => $data['tgl_check_in'],
                    'tgl_check_out' => $data['tgl_check_out'],
                    'jumlah_tamu' => $data['jumlah_tamu'],
                    'jenis_kendaraan' => $data['jenis_kendaraan'] ?: null,
                    'kanal_pemesanan' => 'WhatsApp',
                    'status_reservasi' => 'Dipesan',
                ]);

                return $idReservasi;
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('pesan_error', PesanErrorService::dariException($e));
        }

        $layanan = DB::table('layanan')->where('id_layanan', $data['id_layanan'])->first();

        return view('publik.pesan_konfirmasi', [
            'idReservasi' => $idReservasi,
            'namaLayanan' => $layanan->nama_layanan,
            'noUnit' => $unit->no_unit,
            'tglCheckIn' => $data['tgl_check_in'],
            'tglCheckOut' => $data['tgl_check_out'],
        ]);
    }

    // -------------------------------------------------------------
    // Beli tiket / paket
    // -------------------------------------------------------------

    public function formTiket(Request $request)
    {
        $layananList = DB::table('layanan')
            ->whereIn('jenis_layanan', ['Tiket Masuk', 'Paket'])
            ->where('status_aktif', true)
            ->orderBy('jenis_layanan')
            ->orderBy('nama_layanan')
            ->get();

        foreach ($layananList as $l) {
            $l->tarif_weekday = TarifService::hargaBerlaku($l->id_layanan, 'Weekday');
            $l->tarif_weekend = TarifService::hargaBerlaku($l->id_layanan, 'Weekend');
            if ($l->jenis_layanan === 'Paket') {
                $paket = DB::table('paket')->where('id_layanan', $l->id_layanan)->first();
                $l->min_pax = $paket->min_pax ?? 20;
            }
        }

        return view('publik.tiket', [
            'layananList' => $layananList,
            'layananTerpilih' => $request->query('layanan'),
        ]);
    }

    public function simpanTiket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'no_telepon' => ['required', 'regex:/^[0-9]{8,15}$/'],
            'id_layanan' => 'required|string|exists:layanan,id_layanan',
            'jumlah' => 'required|integer|min:1',
            'tgl_kunjungan' => 'required|date',
            'metode_bayar' => 'required|in:Transfer,Debit',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.regex' => 'Nomor telepon harus berupa 8-15 digit angka.',
            'id_layanan.required' => 'Tiket/paket wajib dipilih.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.min' => 'Jumlah minimal 1.',
            'tgl_kunjungan.required' => 'Tanggal kunjungan wajib diisi.',
            'metode_bayar.required' => 'Metode bayar wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $data = $validator->validated();

        if (strtotime($data['tgl_kunjungan']) < strtotime(date('Y-m-d'))) {
            return back()->withInput()->with('pesan_error', 'Tanggal kunjungan tidak boleh sebelum hari ini.');
        }

        $layanan = DB::table('layanan')->where('id_layanan', $data['id_layanan'])->first();

        if ($layanan->jenis_layanan === 'Paket') {
            $paket = DB::table('paket')->where('id_layanan', $data['id_layanan'])->first();
            $minPax = $paket->min_pax ?? 20;
            if ($data['jumlah'] < $minPax) {
                return back()->withInput()->with('pesan_error', 'Paket ini membutuhkan minimal '.$minPax.' orang.');
            }
        }

        $harga = TarifService::harga($data['id_layanan'], $data['tgl_kunjungan']);
        if ($harga === null) {
            return back()->withInput()->with('pesan_error', 'Tarif untuk layanan ini belum tersedia pada tanggal tersebut.');
        }

        try {
            $idTransaksi = DB::transaction(function () use ($data, $harga) {
                $idPelanggan = $this->cariAtauBuatPelangganOts($data['nama'], $data['no_telepon']);

                $idTransaksi = IdService::next('transaksi', 'id_transaksi', 'T');

                DB::table('transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'id_pelanggan' => $idPelanggan,
                    'id_karyawan' => self::PETUGAS_TIX,
                    'waktu_transaksi' => now(),
                    'kanal_pemesanan' => 'WhatsApp',
                    'metode_bayar' => $data['metode_bayar'],
                ]);

                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'no_urut' => 1,
                    'id_layanan' => $data['id_layanan'],
                    'jumlah' => $data['jumlah'],
                    'harga_satuan' => $harga,
                ]);

                for ($i = 0; $i < $data['jumlah']; $i++) {
                    DB::table('tiket')->insert([
                        'no_tiket' => IdService::next('tiket', 'no_tiket', 'TK'),
                        'id_transaksi' => $idTransaksi,
                        'no_urut' => 1,
                        'tgl_kunjungan' => $data['tgl_kunjungan'],
                        'status_tiket' => 'Aktif',
                    ]);
                }

                return $idTransaksi;
            });
        } catch (QueryException $e) {
            return back()->withInput()->with('pesan_error', PesanErrorService::dariException($e));
        }

        return view('publik.tiket_konfirmasi', [
            'idTransaksi' => $idTransaksi,
            'namaLayanan' => $layanan->nama_layanan,
            'jumlah' => $data['jumlah'],
            'total' => $harga * $data['jumlah'],
        ]);
    }

    // -------------------------------------------------------------
    // Cek & batalkan reservasi
    // -------------------------------------------------------------

    public function formCek()
    {
        return view('publik.cek', ['reservasi' => null]);
    }

    public function prosesCek(Request $request)
    {
        $request->validate([
            'id_reservasi' => 'required|string',
            'no_telepon' => 'required|string',
        ], [
            'id_reservasi.required' => 'Kode reservasi wajib diisi.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
        ]);

        $reservasi = $this->cariReservasi($request->id_reservasi, $request->no_telepon);

        if (! $reservasi) {
            return view('publik.cek', [
                'reservasi' => null,
                'pesanTidakDitemukan' => 'Reservasi tidak ditemukan. Periksa kode dan nomor telepon.',
            ]);
        }

        return view('publik.cek', ['reservasi' => $reservasi, 'telepon' => $request->no_telepon]);
    }

    public function batalkan(Request $request, string $id)
    {
        $request->validate(['no_telepon' => 'required|string']);

        $reservasi = $this->cariReservasi($id, $request->no_telepon);

        if (! $reservasi) {
            return view('publik.cek', [
                'reservasi' => null,
                'pesanTidakDitemukan' => 'Reservasi tidak ditemukan. Periksa kode dan nomor telepon.',
            ]);
        }

        if (! in_array($reservasi->status_reservasi, ['Dipesan', 'Dibayar'])) {
            return view('publik.cek', [
                'reservasi' => $reservasi,
                'telepon' => $request->no_telepon,
                'pesanError' => 'Reservasi dengan status ini tidak dapat dibatalkan.',
            ]);
        }

        try {
            DB::table('reservasi_penginapan')->where('id_reservasi', $id)->update([
                'status_reservasi' => 'Dibatalkan',
            ]);
        } catch (QueryException $e) {
            return view('publik.cek', [
                'reservasi' => $reservasi,
                'telepon' => $request->no_telepon,
                'pesanError' => PesanErrorService::dariException($e),
            ]);
        }

        $reservasi = $this->cariReservasi($id, $request->no_telepon);

        return view('publik.cek', [
            'reservasi' => $reservasi,
            'telepon' => $request->no_telepon,
            'pesanSukses' => 'Reservasi berhasil dibatalkan.',
        ]);
    }

    // -------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------

    protected function cariReservasi(string $idReservasi, string $noTelepon)
    {
        return DB::table('v_reservasi as r')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 'r.id_pelanggan')
            ->join('layanan as l', 'l.id_layanan', '=', 'r.id_layanan')
            ->where('r.id_reservasi', $idReservasi)
            ->where('p.no_telepon', $noTelepon)
            ->select('r.*', 'p.nama as nama_pelanggan', 'l.nama_layanan')
            ->first();
    }

    /**
     * Cari pelanggan tamu penginapan berdasar no_telepon; bila belum ada,
     * buat baris pelanggan baru dengan is_tamu_penginapan = true.
     */
    protected function cariAtauBuatPelangganTamu(string $nama, string $noTelepon): string
    {
        $ada = DB::table('pelanggan')->where('no_telepon', $noTelepon)->first();

        if ($ada) {
            if (! $ada->is_tamu_penginapan) {
                DB::table('pelanggan')->where('id_pelanggan', $ada->id_pelanggan)
                    ->update(['is_tamu_penginapan' => true]);
            }

            return $ada->id_pelanggan;
        }

        $id = IdService::next('pelanggan', 'id_pelanggan', 'P');

        DB::table('pelanggan')->insert([
            'id_pelanggan' => $id,
            'nama' => $nama,
            'no_telepon' => $noTelepon,
            'is_grup' => false,
            'is_tamu_penginapan' => true,
            'is_ots' => false,
        ]);

        return $id;
    }

    /**
     * Cari pelanggan berdasar no_telepon untuk pembelian tiket/paket; bila
     * belum ada, buat baris pelanggan baru dengan is_ots = true (asumsi:
     * pembeli tiket/paket lewat web dianggap pengunjung on-the-spot/web,
     * karena skema tidak punya peran khusus untuk ini).
     */
    protected function cariAtauBuatPelangganOts(string $nama, string $noTelepon): string
    {
        $ada = DB::table('pelanggan')->where('no_telepon', $noTelepon)->first();

        if ($ada) {
            return $ada->id_pelanggan;
        }

        $id = IdService::next('pelanggan', 'id_pelanggan', 'P');

        DB::table('pelanggan')->insert([
            'id_pelanggan' => $id,
            'nama' => $nama,
            'no_telepon' => $noTelepon,
            'is_grup' => false,
            'is_tamu_penginapan' => false,
            'is_ots' => true,
        ]);

        return $id;
    }
}
